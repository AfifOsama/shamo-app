<?php

namespace App\Http\Controllers\API;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\TransactionItem;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function all(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit', 6);
        $status = $request->input('status');

        if ($id) {
            $transaction = Transaction::with(['items.product'])->find($id);
            if ($transaction) {
                return ResponseFormatter::success($transaction, 'Get transaction success');
            } else {
            }
            return ResponseFormatter::error(null, 'Get transaction is empty', 404);
        }

        $transactions = Transaction::with(['items.product'])->where('users_id', Auth::user()->id);
        if ($status) {
            $transactions->where('status', $status);
        }

        return ResponseFormatter::success($transactions->paginate($limit), 'Get list transactions success');
    }

    public function checkout(Request $request)
    {
        try {

            $request->validate([
                'items' => 'required|array',
                'items.*.id' => 'exists:products,id',
                'address' => 'required',
                'price_total' => 'required',
                'shipment_total' => 'required',
                'payment_method' => 'required|in:MANUAL',
                'status' => 'required|in:PENDING,SUCCESS,CANCELLED,FAILED,SHIPPING,SHIPPED',
            ]);

            $transaction = Transaction::create([
                'users_id' => Auth::user()->id,
                'address' => $request->address,
                'price_total' => $request->price_total,
                'shipment_total' => $request->shipment_total,
                'payment_method' => $request->payment_method,
                'status' => $request->status,
            ]);

            foreach ($request->items as $product) {
                TransactionItem::create([
                    'users_id' => Auth::user()->id,
                    'products_id' => $request['id'],
                    'transactions_id' => $transaction->id,
                    'quantity' => $request['quantity'],
                ]);
            };

            return ResponseFormatter::success($transaction->load('items.product'), 'Checkout success');
        } catch (Exception $exception) {
            $error = preg_split('#\r?\n#', $exception, 2)[0];
            return ResponseFormatter::error([
                "message" => "Something went wrong: $error",
            ], 'Checkout failed', 500);
        }
    }
}
