<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\adminController;
use App\Http\Controllers\menuController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\AdminReceiptController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReceiptEmail;


Route::get('/logout', function () {
    Auth::logout();
    Session::flush();
    return redirect('/admin/login')->with('success', 'Berhasil logout!');
})->name('logout');


Route::get('/menu', [menuController::class, 'showMenu'])->name('user.menu');
//Route::get('/status', [menuController::class, 'showMenu'])->name('user.status');
//Route::get('/statuss', [menuController::class, 'showMenus'])->name('user.cart');


Route::get('/login', [AdminAuthController::class, 'getLoginAdmin'])->name('admin.login.form');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login');

Route::get('/admin/menus', [adminController::class, 'getMenuAdmin'])->name('menus.index');
Route::get('/admin/menus/create', [adminController::class, 'getCreateMenuAdmin'])->name('menus.create');
Route::post('/admin/menus/create', [adminController::class, 'postCreateMenuAdmin'])->name('menus.store');

Route::post('/admin/menus/update/{id}', [adminController::class, 'postUpdateMenuAdmin'])->name('menus.update');
Route::get('/admin/menus/edit/{id}', [adminController::class, 'getEditMenuAdmin'])->name('menus.edit');

Route::delete('/admin/menus/{menu}', [adminController::class, 'destroy'])->name('menus.destroy');

Route::get('/admin/qr', fn() => view('admin.qr'))->name('admin.qr');
Route::get('/admin/qr-preview', [adminController::class, 'showQrPreview'])->name('admin.qr.preview');

Route::get('/admin/tables', [adminController::class, 'showTableStatus'])->name('admin.tables');
Route::put('/admin/tables/status/{table_number}', [adminController::class, 'updateTableStatus'])->name('tables.updateStatus');

Route::get('/admin/cashier/search', [CashierController::class, 'searchOrder'])->name('admin.cashier.searchOrder');
Route::get('/admin/cashier/data', [CashierController::class, 'getData'])->name('admin.cashier.data');
Route::get('/admin/cashier', [CashierController::class, 'index'])->name('admin.cashier');
Route::get('/admin/cashier/{table_number}', [CashierController::class, 'show'])->name('admin.cashier.show');
Route::post('/admin/cashier/{table_number}/pay', [CashierController::class, 'payBill'])->name('admin.cashier.pay');
Route::get('/admin/cashier/{table_number}/receipt', [CashierController::class, 'receipt'])->name('admin.cashier.receipt');
Route::get('/admin/receipt/{receipt}', [CashierController::class, 'showReceipt'])->name('admin.receipt.show');


Route::post('/cart/add', [CartController::class, 'addToCart'])->name('user.cart.add');
Route::get('/cart', [CartController::class, 'showCart'])->name('user.cart.show');
Route::post('/cart/delete', [CartController::class, 'delete'])->name('user.cart.delete');
Route::post('/cart/update', [CartController::class, 'update'])->name('user.cart.update');
Route::get('/cart/check-available', [CartController::class, 'checkAvailable'])->name('user.cart.checkAvailable');


Route::post('/order/create', [OrderController::class, 'create'])->name('user.order.create');
Route::post('/order/create/cashless', [OrderController::class, 'createCashless'])->name('user.order.Createcashhless');

Route::post('/payment/success', [PaymentController::class, 'handleSuccess'])->name('payment.success');
Route::get('/payment/update', [PaymentController::class, 'updatePayment'])->name('payment.update');


Route::get('/status', [StatusController::class, 'index'])->name('user.status');

Route::get('/kitchen', [KitchenController::class, 'dashboard'])->name('kitchen.dashboard');
Route::get('/kitchen/orders', [KitchenController::class, 'fetchOrders'])->name('kitchen.orders');
Route::post('/kitchen/update-status', [KitchenController::class, 'updateStatus'])->name('kitchen.updateStatus');


Route::post('/admin/cashier/{table_number}/receipt', [CashierController::class, 'storeReceipt'])->name('admin.cashier.receipt.store');

Route::get('/admin/receipt/{receipt}', [AdminReceiptController::class, 'show'])->name('admin.receipt.show');

Route::get('/admin/report', [ReportController::class, 'index'])->name('admin.report');
Route::get('/laporan-penjualan/download', [ReportController::class, 'download'])->name('laporan.download');

Route::get('/admin/inquiry', [CashierController::class, 'inquiry_index'])->name('admin.cashier.inquiry');
Route::get('admin/payment/inquiry', [CashierController::class, 'inquiry'])->name('admin.cashier.inquiry');
// Menambahkan route untuk print receipt
Route::get('/admin/print-receipt/{receipt}', [AdminController::class, 'printReceipt'])->name('admin.print.receipt');


Route::post('/admin/cashier/add-item/{menu_id}', [CashierController::class, 'addItem'])->name('admin.cashier.addItem');
Route::post('/admin/cashier/update-item/{order_id}/{item_id}', [CashierController::class, 'updateItem'])->name('admin.cashier.updateItem');
Route::post('/admin/cashier/remove-item/{order_id}/{item_id}', [CashierController::class, 'removeItem'])->name('admin.cashier.removeItem');

Route::post('/admin/cashier/update-receipt/{receipt_id}', [CashierController::class, 'updateReceipt'])->name('admin.cashier.updateReceipt');
Route::post('/admin/cashier/update-receipt/{receipt_id}/qris', [CashierController::class, 'updateReceiptQRIS'])->name('admin.cashier.updateReceiptQris');
Route::post('/admin/cashier/update-receipt/{receipt_id}/cash', [CashierController::class, 'updateReceiptCash'])->name('admin.cashier.updateReceiptCash');


Route::fallback(function () {
    return redirect('/login');
});