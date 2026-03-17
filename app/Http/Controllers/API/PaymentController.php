<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\FoodOrder;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'food_order_id' => 'required|exists:food_orders,id',
            'amount' => 'required|numeric|min:0',
            'method' => 'required|in:cash,card,online,wallet',
            'transaction_id' => 'nullable|string',
        ]);

        $order = FoodOrder::findOrFail($request->food_order_id);
        if ($order->user_id != $request->user()->id) abort(403);

        $payment = Payment::create([
            'food_order_id' => $order->id,
            'amount' => $request->amount,
            'method' => $request->method,
            'status' => 'completed',
            'transaction_id' => $request->transaction_id,
        ]);

        return response()->json($payment, 201);
    }
}
