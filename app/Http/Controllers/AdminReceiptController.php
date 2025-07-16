<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\PDF;

class AdminReceiptController extends Controller
{
    public function show(Receipt $receipt)
    {
        $orders = \App\Models\Order::with('items.menu')
            ->where('receipt_id', $receipt->id)
            ->get();

        // generate pdf
        $pdf = Pdf::loadView('admin.receipt-template', [
            'receipt' => $receipt,
            'orders' => $orders,
        ])->setPaper([0, 0, 283.5, 600], 'portrait');


        return $pdf->stream('receipt-'.$receipt->invoice_number.'.pdf');
    }
}
