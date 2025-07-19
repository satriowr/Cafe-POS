<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Crypt;


class adminController extends Controller
{
    public function getMenuAdmin(){

        $userId = session('user_id');
    
        $role = DB::table('users')->where('id', $userId)->value('role');
        $name = DB::table('users')->where('id', $userId)->value('name');
    
        if ($role !== 'admin') {
            return redirect('/login');
        }

        $menus = Menu::all();
        return view('admin.menus', compact('menus'));
    }
    public function getCreateMenuAdmin(){

        $userId = session('user_id');
    
        $role = DB::table('users')->where('id', $userId)->value('role');
        $name = DB::table('users')->where('id', $userId)->value('name');
    
        if ($role !== 'admin') {
            return redirect('/login');
        }

        return view('admin.create-menus');
    }

    public function postCreateMenuAdmin(Request $request){

        $userId = session('user_id');
    
        $role = DB::table('users')->where('id', $userId)->value('role');
        $name = DB::table('users')->where('id', $userId)->value('name');
    
        if ($role !== 'admin') {
            return redirect('/login');
        }

        $validated = $request->validate([
            'name' => 'required',
            'price' => 'required|integer',
            'category' => 'required',
            'image' => 'nullable|image|max:2048',
            'is_available' => 'nullable|boolean',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('menus', 'public');
        }

        $validated['is_available'] = 1;

        Menu::create($validated);

        if ($request->action === 'save_and_add') {
            return redirect()->route('menus.create')->with('success', 'Menu berhasil ditambahkan.');
        }

        return redirect()->route('menus.index')->with('success', 'Menu berhasil disimpan.');
    }

    public function postUpdateMenuAdmin(Request $request, Menu $menu, $id)
    {   

        $userId = session('user_id');
    
        $role = DB::table('users')->where('id', $userId)->value('role');
        $name = DB::table('users')->where('id', $userId)->value('name');
    
        if ($role !== 'admin') {
            return redirect('/login');
        }

        if ($request->has('status_only')) {
            //dd($id);
    
            DB::table('menus')
                ->where('id', $id)
                ->update(['is_available' => $request->is_available]);
    
            return redirect()->back()->with('success', 'Status menu berhasil diperbarui.');
        }
    
        $request->validate([
            'name' => 'required',
            'price' => 'required|integer',
            'category' => 'required',
        ]);
    
        $data = $request->only(['name', 'price', 'category', 'is_available']);
    
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('menus', 'public');
        }
    
        DB::table('menus')->where('id', $id)->update($data);
    
        return redirect()->route('menus.index')->with('success', 'Menu berhasil diperbarui.');
    }

    public function destroy(Menu $menu)
    {
        $userId = session('user_id');
    
        $role = DB::table('users')->where('id', $userId)->value('role');
        $name = DB::table('users')->where('id', $userId)->value('name');
    
        if ($role !== 'admin') {
            return redirect('/login');
        }

        if ($menu->image) {
            Storage::disk('public')->delete($menu->image);
        }

        $menu->delete();

        return redirect()->route('menus.index')->with('success', 'Menu berhasil dihapus.');
    }

    public function getEditMenuAdmin($id)
    {

        $userId = session('user_id');
    
        $role = DB::table('users')->where('id', $userId)->value('role');
        $name = DB::table('users')->where('id', $userId)->value('name');
    
        if ($role !== 'admin') {
            return redirect('/login');
        }

        $menu = Menu::findOrFail($id);
        return view('admin.edit-menus', compact('menu'));
    }


    public function showQrPreview(Request $request)
    {
        $userId = session('user_id');
        
        $role = DB::table('users')->where('id', $userId)->value('role');
        
        if ($role !== 'admin') {
            return redirect('/login');
        }

        $table = $request->query('table_number');
        $customerIdentity = $request->query('customer_identity');
        $orderType = $request->query('order_type');
        
        if (!$table || !$customerIdentity || !$orderType) {
            return view('admin.qr');
        }

        $existing = DB::table('tables')
            ->where('table_number', $table)
            ->where('is_active', true)
            ->where('table_number', '!=', 100)
            ->first();
        
        if ($existing) {
            return redirect()->back()->with('error', "QR untuk meja $table masih aktif. Harap nonaktifkan dulu sebelum membuat baru.");
        }

        $combinedData = $table . '|' . $customerIdentity . '|' . $orderType;
        $qr_token = rtrim(strtr(base64_encode($combinedData), '+/', '-_'), '=');
        
        //dd($qr_token);
        DB::table('tables')->insert([
            'table_number' => $table,
            'qr_token' => $qr_token,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $url = url("/menu?token=" . urlencode($qr_token));
        $qr = QrCode::size(200)->generate($url);
        
        return view('admin.qr', [
            'qr' => $qr,
            'table_number' => $table,
            'url' => $url,
        ]);
    }

    
    public function showTableStatus()
    {
        $userId = session('user_id');
        
        $role = DB::table('users')->where('id', $userId)->value('role');
        $name = DB::table('users')->where('id', $userId)->value('name');
        
        if ($role !== 'admin') {
            return redirect('/login');
        }
    
        $tables = DB::table('tables')
            ->select('table_number', 'is_active', 'created_at')
            ->where('is_active', 1)
            ->orderByDesc('created_at')
            ->get()
            ->unique('table_number');
    
        return view('admin.table', compact('tables'));
    }
    
    public function updateTableStatus(Request $request, $table_number)
    {
        $userId = session('user_id');
    
        $role = DB::table('users')->where('id', $userId)->value('role');
        $name = DB::table('users')->where('id', $userId)->value('name');
    
        if ($role !== 'admin') {
            return redirect('/login');
        }
        
        $request->validate([
            'is_active' => 'required|in:0,1',
        ]);

        DB::table('tables')
            ->where('table_number', $table_number)
            ->update([
                'is_active' => $request->is_active,
                'updated_at' => now(),
            ]);
        
        if ((int) $request->is_active === 0) {
            DB::table('carts')->where('table_number', $table_number)->delete();
            DB::table('tables')->where('table_number', $table_number)->delete();
        }

        return redirect()->route('admin.tables')->with('success', 'Status meja berhasil diperbarui.');
    }

}
