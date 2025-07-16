<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;


class ReportController extends Controller
{
    public function index(Request $request)
    {
        $userId = session('user_id');
    
        $role = DB::table('users')->where('id', $userId)->value('role');
        $name = DB::table('users')->where('id', $userId)->value('name');
    
        if ($role !== 'admin') {
            return redirect('/login');
        }

        $from = $request->input('from', now()->format('Y-m-d'));
        $to = $request->input('to', now()->format('Y-m-d'));
    
        $orders = Order::whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])->get();
    
        $totalRevenue = $orders->sum(function($order) {
            return $order->items->sum(function($item) {
                return $item->quantity * $item->menu->price;
            });
        });
    
        $groupedOrders = $orders->groupBy(function($order) {
            return $order->created_at->format('d M');
        });
    
        $chartLabels = $groupedOrders->keys();
        $chartData = $groupedOrders->map(function($dayOrders) {
            return $dayOrders->sum(function($order) {
                return $order->items->sum(function($item) {
                    return $item->quantity * $item->menu->price;
                });
            });
        })->values();
    
        $orderItems = OrderItem::with('menu')
            ->whereHas('order', function($query) use ($from, $to) {
                $query->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
            })
            ->get();
    
        $groupedItems = $orderItems->groupBy('menu_id');
    
        $bestSellerLabels = $groupedItems->map(function($items) {
            return $items->first()->menu->name;
        })->values();
    
        $bestSellerData = $groupedItems->map(function($items) {
            return $items->sum('quantity');
        })->values();
    
        return view('admin.report', compact(
            'totalRevenue', 
            'chartLabels', 
            'chartData', 
            'orderItems', 
            'bestSellerLabels', 
            'bestSellerData'
        ));
    }
    

    public function download(Request $request)
    {
        $from = $request->input('from', now()->format('Y-m-d'));
        $to = $request->input('to', now()->format('Y-m-d'));

        $orderItems = OrderItem::with('menu')
            ->whereHas('order', function($query) use ($from, $to) {
                $query->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
            })
            ->get();

        $pdf = Pdf::loadView('admin.laporan_pdf', compact('orderItems', 'from', 'to'));
        return $pdf->download('laporan-penjualan-' . now()->format('Ymd_His') . '.pdf');
    }
}
