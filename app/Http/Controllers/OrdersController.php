<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Asset;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Jobs\MatchOrdersJob;

class OrdersController extends Controller
{
    public function index() 
    {
        return Inertia::render('Orders');
    }

    public function getAll(Request $request)
    {
        $query = Order::query();

        $query = Order::where('user_id', auth()->id());

        if ($request->has('symbol') && $request->symbol !== '') {
            $query->where('symbol', $request->symbol);
        }

        if ($request->has('side') && $request->side !== '') {
            $query->where('side', $request->side);
        }

        // Filter status (1=open, 2=filled, 3=cancelled)
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $sortField = $request->get('sortField', 'created_at');
        $sortOrder = $request->get('sortOrder', 'desc');

        $allowedSortFields = ['price', 'amount', 'created_at'];
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'created_at';
        }

        $orders = $query->orderBy($sortField, $sortOrder)->get();

        $total = $query->count();
        $limit = intval($request->get('limit', 10));
        $page = intval($request->get('page', 1));
        $orders = $query->skip(($page - 1) * $limit)->take($limit)->get();

        return response()->json([
            'orders' => $orders,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
        ]);

        return response()->json(['orders' => $orders]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'symbol' => 'required|string',
            'side' => 'required|in:buy,sell',
            'price' => 'required|numeric|min:0',
            'amount' => 'required|numeric|min:0',
        ]);

        $user = Auth::user();
        $symbol = strtoupper($request->symbol);
        $side = $request->side;
        $price = $request->price;
        $amount = $request->amount;

        DB::beginTransaction();

        try {
            if ($side === 'buy') {
                $usd_needed = $price * $amount;

                if ($user->balance < $usd_needed) {
                    return response()->json(['error' => 'Insufficient USD balance'], 400);
                }

                $user->balance -= $usd_needed;
                $user->save();

                $order = Order::create([
                    'user_id' => $user->id,
                    'symbol' => $symbol,
                    'side' => 'buy',
                    'price' => $price,
                    'amount' => $amount,
                    'locked_value' => $usd_needed,
                    'status' => 1,
                ]);
            } else { // sell
                $asset = Asset::firstOrCreate(
                    ['user_id' => $user->id, 'symbol' => $symbol],
                    ['amount' => 0, 'locked_amount' => 0]
                );

                if ($asset->amount < $amount) {
                    return response()->json(['error' => 'Insufficient asset balance'], 400);
                }

                $asset->amount -= $amount;
                $asset->locked_amount += $amount;
                $asset->save();

                $order = Order::create([
                    'user_id' => $user->id,
                    'symbol' => $symbol,
                    'side' => 'sell',
                    'price' => $price,
                    'amount' => $amount,
                    'locked_value' => 0,
                    'status' => 1,
                ]);
            }

            DB::commit();

            MatchOrdersJob::dispatch($order->id);

            return response()->json($order, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function cancel($id)
    {
        $user = Auth::user();
        $order = Order::where('id', $id)->where('user_id', $user->id)->first();

        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        if ($order->status != 1) {
            return response()->json(['error' => 'Only open orders can be cancelled'], 400);
        }

        DB::beginTransaction();
        try {
            if ($order->side === 'buy') {
                $user->balance += $order->locked_value;
                $user->save();
            } else {
                $asset = Asset::firstOrCreate(
                    ['user_id' => $user->id, 'symbol' => $order->symbol],
                    ['amount' => 0, 'locked_amount' => 0]
                );
                $asset->amount += $order->amount;
                $asset->locked_amount -= $order->amount;
                $asset->save();
            }

            $order->status = 3; // cancelled
            $order->save();

            DB::commit();

            return response()->json($order);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
