<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Trade;

class TradesController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $trades = Trade::where('buyer_id', $userId)
            ->orWhere('seller_id', $userId)
            ->orderBy('id', 'desc')
            ->get()
            ->map(function($trade) use ($userId) {
                return [
                    'id' => $trade->id,
                    'order_id' => $trade->buyer_id == $userId ? $trade->buy_order_id : $trade->sell_order_id,
                    'symbol' => $trade->symbol,
                    'side' => $trade->buyer_id == $userId ? 'buy' : 'sell', 
                    'price' => $trade->price,
                    'amount' => $trade->amount,
                    'created_at' => $trade->created_at,
                ];
            });

        return response()->json([
            'trades' => $trades
        ]);
    }
}