<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Asset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Events\OrderMatched;
use App\Models\Trade;

class MatchingService
{
    public function match(int $orderId)
    {
        $order = Order::find($orderId);

        if (!$order || $order->status != 1) {
            return; 
        }

        if ($order->side === 'buy') {
            $matches = Order::where('symbol', $order->symbol)
                ->where('side', 'sell')
                ->where('status', 1)
                ->where('price', '<=', $order->price)
                ->orderBy('price', 'asc')
                ->orderBy('id', 'asc')
                ->get();
        } 
        else {
            $matches = Order::where('symbol', $order->symbol)
                ->where('side', 'buy')
                ->where('status', 1)
                ->where('price', '>=', $order->price)
                ->orderBy('price', 'desc')
                ->orderBy('id', 'asc')
                ->get();
        }

        foreach ($matches as $match) {
            if ($order->amount <= 0) break;

            $this->processTrade($order, $match);

            $order->refresh();
        }
    }

    // --------------------
    // TRADE SETTLEMENT
    // --------------------

    private function processTrade(Order $orderA, Order $orderB)
    {
        $buyOrder  = $orderA->side === 'buy' ? $orderA : $orderB;
        $sellOrder = $orderA->side === 'sell' ? $orderA : $orderB;

        $tradeAmount = min($buyOrder->amount, $sellOrder->amount);

        $tradePrice = $sellOrder->price;

        DB::transaction(function () use ($buyOrder, $sellOrder, $tradeAmount, $tradePrice) {

            $totalCost = $tradeAmount * $tradePrice;

            // -----------------------------
            // 1) UPDATE BUYER USD BALANCE
            // -----------------------------

            $buyer = $buyOrder->user;

            $buyOrder->locked_value -= $totalCost;

            $maxCost = $tradeAmount * $buyOrder->price;
            $refund = $maxCost - $totalCost;

            if ($refund > 0) {
                $buyer->balance += $refund;
            }

            $buyer->save();

            // ------------------------------
            // 2) UPDATE SELLER CRYPTO BALANCE
            // ------------------------------

            $sellerAsset = Asset::where('user_id', $sellOrder->user_id)
                ->where('symbol', $sellOrder->symbol)
                ->first();

            $feeAmount = $tradeAmount * 0.015;

            $sellerAsset->locked_amount -= $tradeAmount;
            $sellerAsset->amount -= $feeAmount; 
            $sellerAsset->save();

            // seller dobija USD
            $seller = $sellOrder->user;
            $seller->balance += $totalCost;
            $seller->save();

            // ------------------------------
            // 3) BUYER DOBIJA KRIPTO
            // ------------------------------

            $buyerAsset = Asset::firstOrCreate(
                ['user_id' => $buyer->id, 'symbol' => $buyOrder->symbol],
                ['amount' => 0, 'locked_amount' => 0]
            );

            $buyerAsset->amount += $tradeAmount;
            $buyerAsset->save();

            // ------------------------------
            // 4) SMANJI ORDER AMOUNTE
            // ------------------------------

            $buyOrder->amount  -= $tradeAmount;
            $sellOrder->amount -= $tradeAmount;

            // zatvori ako amount == 0
            if ($buyOrder->amount <= 0) {
                $buyOrder->status = 2; // Filled
            }

            if ($sellOrder->amount <= 0) {
                $sellOrder->status = 2; // Filled
            }

            $buyOrder->save();
            $sellOrder->save();

            Trade::create([
                'buy_order_id'  => $buyOrder->id,
                'sell_order_id' => $sellOrder->id,
                'buyer_id'      => $buyOrder->user_id,
                'seller_id'     => $sellOrder->user_id,
                'symbol'        => $buyOrder->symbol,
                'price'         => $tradePrice,
                'amount'        => $tradeAmount,
                'usd_volume'    => $totalCost,
                'fee_usd'       => $totalCost * 0.015, 
            ]);

            Log::info("Trade executed: {$buyOrder->id} <-> {$sellOrder->id}");

            event(new OrderMatched($buyOrder, $sellOrder->user_id));
            event(new OrderMatched($sellOrder, $buyOrder->user_id));
        });
    }
}
