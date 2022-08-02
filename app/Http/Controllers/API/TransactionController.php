<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
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
}
