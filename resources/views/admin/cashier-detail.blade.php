<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pembayaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
@php
    $orderIds = request()->query('order_ids');
@endphp

<div class="container my-5">
    <h2 class="mb-4">Detail Pembayaran - Meja {{ $tableNumber }}</h2>

    <div class="row">
        <div class="col-md-6">
            <h5>Pesanan:</h5>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th style="background-color:#0E8636 !important; color:white;">Menu</th>
                            <th style="background-color:#0E8636 !important; color:white;">Qty</th>
                            <th style="background-color:#0E8636 !important; color:white;">Harga Satuan</th>
                            <th style="background-color:#0E8636 !important; color:white;">Subtotal</th>
                            <th style="background-color:#0E8636 !important; color:white;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $subtotal = 0; @endphp
                        @foreach($orders as $order)
                            @foreach($order->items as $item)
                                @php
                                    $itemSubtotal = $item->quantity * $item->menu->price;
                                    $subtotal += $itemSubtotal;
                                @endphp
                                <tr>
                                    <td>{{ $item->menu->name }}</td>
                                    <td>
                                        <form action="{{ route('admin.cashier.updateItem', ['order_id' => $order->id, 'item_id' => $item->id]) }}" method="POST">
                                            @csrf
                                            <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" class="form-control w-25" required />
                                            <button type="submit" class="btn btn-warning btn-sm mt-2">Update</button>
                                        </form>
                                    </td>
                                    <td>Rp {{ number_format($item->menu->price, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($itemSubtotal, 0, ',', '.') }}</td>
                                    <td>
                                        <!-- Remove Item -->
                                        <form action="{{ route('admin.cashier.removeItem', ['order_id' => $order->id, 'item_id' => $item->id]) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>


        <!-- Right Column: Available Menu -->
<div class="col-md-6">
    <h5>Menu Tersedia:</h5>
    <div class="list-group" style="max-height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;">
        @foreach($availableMenus as $menu)
            <div class="list-group-item">
                <div class="d-flex flex-column align-items-start">
                    <span><strong>{{ $menu->name }}</strong></span>
                    <span class="text-muted">Rp {{ number_format($menu->price, 0, ',', '.') }}</span>

                    <form action="{{ route('admin.cashier.addItem', ['table_number' => $tableNumber, 'menu_id' => $menu->id]) }}?order_ids={{ $orderIds }}" method="POST" style="width: 100%;">
                        @csrf
                        <div class="d-flex gap-2 mt-2">
                            <input type="number" name="quantity" value="1" min="1" class="form-control w-50" required />
                            <button type="submit" class="btn btn-success btn-sm w-50">Tambah</button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</div>
    </div>

    <!-- Payment Summary -->
    <div class="mt-4">
        @php
            $tax = $subtotal * 0.1;
            $service = $subtotal * 0.05;
            $total = $subtotal + $tax + $service;
        @endphp

        <p><strong>Subtotal:</strong> Rp {{ number_format($subtotal, 0, ',', '.') }}</p>
        <p><strong>Pajak (10%):</strong> Rp {{ number_format($tax, 0, ',', '.') }}</p>
        <p><strong>Biaya Layanan (5%):</strong> Rp {{ number_format($service, 0, ',', '.') }}</p>
        <h4><strong>Total:</strong> Rp {{ number_format($total, 0, ',', '.') }}</h4>

        <div class="d-flex gap-2">
            <form method="POST" action="{{ route('admin.cashier.pay', ['table_number' => $tableNumber]) }}">
                @csrf
                <input type="hidden" name="payment_type" value="qris"> 
                <button type="submit" class="btn btn-success">Bayar Non Tunai (QRIS)</button>
            </form>

            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#tunaiModal">Bayar Tunai</button>
        </div>
    </div>

    <!-- Modal Tunai -->
<div class="modal fade" id="tunaiModal" tabindex="-1" aria-labelledby="tunaiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pembayaran Tunai</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Total: <strong>Rp {{ number_format($total, 0, ',', '.') }}</strong></p>
                <div class="mb-3">
                    <label class="form-label">Uang Pelanggan</label>
                    <input type="number" class="form-control" id="uang-pelanggan" placeholder="Masukkan nominal" min="1" required />
                </div>
                <div id="tunai-info" class="d-none">
                    <p>Uang Diterima: <strong id="uang-diterima"></strong></p>
                    <p>Kembalian: <strong id="kembalian"></strong></p>
                    <form method="POST" action="{{ route('admin.cashier.pay', ['table_number' => $tableNumber]) }}">
                        @csrf
                        <input type="hidden" name="payment_type" value="cash"> <!-- Menambahkan payment_type untuk cash -->
                        <input type="hidden" name="cash_amount" id="cash-amount">
                        <input type="hidden" name="change" id="change">
                        <button type="submit" class="btn btn-success mt-2" id="btn-bayar-tunai" disabled>Bayar & Cetak Receipt</button>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="btn-bayar-tunai-trigger" class="btn btn-success" disabled>Bayar</button>
            </div>
        </div>
    </div>
</div>
</div>

<script>
    document.getElementById('btn-bayar-tunai-trigger').addEventListener('click', function () {
        const total = {{ (int) $total }};
        const input = document.getElementById('uang-pelanggan');
        const uang = parseInt(input.value);

        if (!isNaN(uang) && uang >= total) {
            const kembalian = uang - total;

            document.getElementById('uang-diterima').textContent = `Rp ${uang.toLocaleString('id-ID')}`;
            document.getElementById('kembalian').textContent = `Rp ${kembalian.toLocaleString('id-ID')}`;

            document.getElementById('tunai-info').classList.remove('d-none');

            document.getElementById('cash-amount').value = uang;
            document.getElementById('change').value = kembalian;

            document.getElementById('btn-bayar-tunai').disabled = false;
            document.getElementById('btn-bayar-tunai-trigger').disabled = true; // Disable tombol "Bayar" yang pertama kali
        } else {
            alert('Nominal uang tidak mencukupi.');
        }
    });

    document.getElementById('uang-pelanggan').addEventListener('input', function () {
        const total = {{ (int) $total }};
        const uang = parseInt(this.value);

        if (!isNaN(uang) && uang >= total) {
            const kembalian = uang - total;
            document.getElementById('kembalian').textContent = `Rp ${kembalian.toLocaleString('id-ID')}`;
            document.getElementById('btn-bayar-tunai-trigger').disabled = false;
        } else {
            document.getElementById('kembalian').textContent = '';
            document.getElementById('btn-bayar-tunai-trigger').disabled = true;
        }
    });
</script>

</body>
</html>
