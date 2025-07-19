<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

class menuController extends Controller
{
    public function showMenu(Request $request)
    {
        $token = $request->query('token');
        $category = $request->query('category');
    
        if (!$token) {
            abort(404, 'Token tidak ditemukan');
        }
    
        try {
            $decryptedData = base64_decode(strtr($token, '-_', '+/'));
            list($table_number, $customer_identity, $orderType) = explode('|', $decryptedData);
        } catch (\Exception $e) {
            abort(403, 'Token tidak valid');
        }

        //dd($decryptedData);
    
        $table = DB::table('tables')
            ->where('table_number', $table_number)
            ->where('qr_token', $token)
            ->where('is_active', 1)
            ->first();
    
        if (!$table) {
            abort(403, 'QR ini sudah tidak aktif');
        }
    
        $query = DB::table('menus');
        
        if ($category) {
            $query->where('category', $category);
        }

        $menus = $query->get();
    
        $categories = DB::table('menus')
            ->select('category')
            ->distinct()
            ->pluck('category');
    
        return view('user.menu', [
            'token' => $token,
            'menus' => $menus,
            'categories' => $categories,
            'table_number' => $table_number,
            'selected_category' => $category, 
            'orderType' => $orderType,
        ]);
    }
    
}
