<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use App\Models\Cart;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Table;
use App\Models\Receipt;


class CartController extends Controller
{
    public function addToCart(Request $request)
    {
        $request->validate([
            'menu_id' => 'required|exists:menus,id',
            'table_number' => 'required|integer',
        ]);
    
        $token = $request->query('token');
    
        if (!$token) {
            return abort(404, 'Token tidak ditemukan');
        }
    
        try {
            $decryptedData = base64_decode(strtr($token, '-_', '+/'));
            list($table_number, $customer_identity, $orderType) = explode('|', $decryptedData);
        } catch (\Exception $e) {
            abort(403, 'Token tidak valid');
        }
    
        $table = \App\Models\Table::where('table_number', $table_number)
            ->where('is_active', 1)
            ->orderByDesc('id')
            ->first();
    
        if (!$table) {
            return abort(403, 'Meja sudah tidak aktif');
        }
    
        $existing = DB::table('carts')
            ->where('table_number', $table_number)
            ->where('menu_id', $request->menu_id)
            ->where('created_at', '>=', $table->created_at)
            ->first();
    
        if ($existing) {
            DB::table('carts')->where('id', $existing->id)->update([
                'quantity' => $existing->quantity + $request->quantity,
                'note' => $request->note,
                'updated_at' => now(),
            ]);
        } else {
            DB::table('carts')->insert([
                'table_number' => $table_number,
                'menu_id' => $request->menu_id,
                'quantity' => $request->quantity,
                'note' => $request->note,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    
        return back()->with('success', 'Item ditambahkan ke keranjang!');
    }


    public function showCart(Request $request)
    {
        $token = $request->query('token');
    
        if (!$token) {
            return abort(404, 'Token tidak ditemukan');
        }
    
        try {
            $decryptedData = base64_decode(strtr($token, '-_', '+/'));
            list($table_number, $customer_identity, $orderType) = explode('|', $decryptedData);
        } catch (\Exception $e) {
            abort(403, 'Token tidak valid');
        }
    
        $table = \App\Models\Table::where('table_number', $table_number)
            ->where('is_active', 1)
            ->orderByDesc('id')
            ->first();
    
        // if (!$table) {
        //     return abort(403, 'QR ini sudah tidak aktif');
        // }

        // if ($table->qr_token !== $token) {
        //     return abort(403, 'QR ini sudah tidak aktif');
        // }
    
        $cartItems = \App\Models\Cart::with('menu')
            ->where('table_number', $table_number)
            ->where('created_at', '>=', $table->created_at)
            ->get();
    
        return view('user.cart', [
            'cartItems' => $cartItems,
            'table_number' => $table_number,
            'token' => $token,
        ]);
    }

    public function delete(Request $request)
    {
        $request->validate([
            'cart_id' => 'required|exists:carts,id',
        ]);

        DB::table('carts')->where('id', $request->cart_id)->delete();

        return back()->with('success', 'Item berhasil dihapus');
    }

    public function update(Request $request)
    {
        $request->validate([
            'cart_id' => 'required|exists:carts,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = Cart::findOrFail($request->cart_id);
        $cart->quantity = $request->quantity;
        $cart->save();

        $newTotalPrice = $cart->quantity * $cart->menu->price;

        $newGrandTotal = Cart::join('menus', 'carts.menu_id', '=', 'menus.id')
        ->where('carts.table_number', $cart->table_number)
        ->sum(DB::raw('carts.quantity * menus.price'));

        return redirect()->route('user.cart.show', ['token' => $request->token])
            ->with(['newTotalPrice' => $newTotalPrice, 'newGrandTotal' => $newGrandTotal]);
    }

    public function checkAvailable(Request $request)
    {
        $token = $request->query('token');
        if (!$token) return response()->json([]);

        try {
            $decryptedData = base64_decode(strtr($token, '-_', '+/'));
            list($table_number, $customer_identity, $orderType) = explode('|', $decryptedData);
        } catch (\Exception $e) {
            return response()->json([]);
        }

        $notAvailableCartIds = \App\Models\Cart::with('menu')
            ->where('table_number', $table_number)
            ->whereHas('menu', function ($q) {
                $q->where('is_available', 0);
            })
            ->pluck('id')
            ->toArray();

        return response()->json($notAvailableCartIds);
    }


}
