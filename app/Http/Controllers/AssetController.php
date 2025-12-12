<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class AssetController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'symbol' => 'required|string',
            'amount' => 'required|numeric|min:0',
        ]);

        $user = Auth::user();

        $existing = $user->assets()->where('symbol', $request->symbol)->first();

         if ($existing) {
            $existing->amount += $request->amount;
            $existing->save();

            return response()->json($existing, 200);
        }

        $asset = $user->assets()->create($request->only(['symbol', 'amount']));

        return response()->json($asset, 201);
    }

    public function destroy($id)
    {
        $user = auth()->user();
        $asset = $user->assets()->findOrFail($id);
        $asset->delete();

        return response()->json(['message' => 'Asset deleted successfully.']);
    }

    public function dashboard()
    {
        $user = auth()->user();

        $assets = $user->assets()->get(['id', 'symbol', 'amount', 'locked_amount']);

        return Inertia::render('Dashboard', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'usd_balance' => $user->balance,
            'assets' => $assets,
        ]);
    }

    public function profile()
    {
        $user = Auth::user();

        $assets = $user->assets()->get(['id', 'symbol', 'amount', 'locked_amount']);

        $lockedUSD = $user->orders()->where('side', 'buy')->where('status', 1)->sum(DB::raw('price * amount'));

        // Izračunaj koliko kripto je blokirano za sell naloge (open orders), grupisano po simbolu
        $assetsData = $assets->map(function ($asset) use ($user) {
            $lockedSell = $asset->locked_amount;

            // Available kripto = total amount - locked from sell orders
            $available = $asset->amount;

            return [
                'id' => $asset->id,
                'symbol' => $asset->symbol,
                'available' => $available,
                'locked' => $lockedSell,
            ];
        });

        return response()->json([
            'usd_balance' => $user->balance,
            'locked_usd' => $lockedUSD,
            'assets' => $assetsData,
        ]);
    }
}
