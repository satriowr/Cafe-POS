@extends('admin.layout')

@section('content')

@if(session('receipt_id'))
    <script>
        window.open('{{ route('admin.receipt.show', ['receipt' => session('receipt_id')]) }}', '_blank');
    </script>
@endif

<h2 class="mb-4">Detail Pembayaran - Meja {{ $tableNumber }}</h2>

<div class="mb-3">
    <h5>Pesanan:</h5>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="">
                <tr>
                    <th style="background-color:#0E8636 !important; color:white;">Menu</th>
                    <th style="background-color:#0E8636 !important; color:white;">Qty</th>
                    <th style="background-color:#0E8636 !important; color:white;">Harga Satuan</th>
                    <th style="background-color:#0E8636 !important; color:white;">Subtotal</th>
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
                            <td>{{ $item->quantity }}</td>
                            <td>Rp {{ number_format($item->menu->price, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($itemSubtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@php
    $tax = $subtotal * 0.11;
    $service = $subtotal * 0.05;
    $total = $subtotal + $tax + $service;
@endphp

<div class="mb-3">
    <p>Subtotal: <strong>Rp {{ number_format($subtotal, 0, ',', '.') }}</strong></p>
    <p>Pajak (10%): <strong>Rp {{ number_format($tax, 0, ',', '.') }}</strong></p>
    <p>Biaya Layanan (5%): <strong>Rp {{ number_format($service, 0, ',', '.') }}</strong></p>
    <h4>Total: <strong>Rp {{ number_format($total, 0, ',', '.') }}</strong></h4>
</div>

@if (!$isPaid)
    <div id="payment-buttons" class="d-flex gap-2">
        <form method="POST" action="{{ route('admin.cashier.pay', ['table_number' => $tableNumber]) }}">
            @csrf
            <input type="hidden" name="payment_type" value="qris"> <!-- Menambahkan payment_type untuk QRIS -->
            <button type="submit" class="btn" style="background-color:#0E8636 !important; color:white;">Bayar Non Tunai (QRIS)</button>
        </form>

        <button class="btn" data-bs-toggle="modal" data-bs-target="#tunaiModal" style="background-color:#0E8636 !important; color:white;">Bayar Tunai</button>
    </div>
@endif

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
                    <input type="number" class="form-control" id="uang-pelanggan" placeholder="Masukkan nominal">
                </div>
                <div id="tunai-info" class="d-none">
                    <p>Uang Diterima: <strong id="uang-diterima"></strong></p>
                    <p>Kembalian: <strong id="kembalian"></strong></p>
                    <form method="POST" action="{{ route('admin.cashier.pay', ['table_number' => $tableNumber]) }}">
                        @csrf
                        <input type="hidden" name="payment_type" value="cash"> <!-- Menambahkan payment_type untuk cash -->
                        <input type="hidden" name="cash_amount" id="cash-amount"> <!-- Menambahkan cash_amount -->
                        <input type="hidden" name="change" id="change"> <!-- Menambahkan change -->
                        <button type="submit" id="btn-cetak-receipt" class="btn btn-success mt-2">Bayar & Cetak Receipt</button>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="btn-bayar-tunai" class="btn" style="background-color:#0E8636 !important; color:white;">Bayar</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('btn-bayar-tunai').addEventListener('click', function () {
        const total = {{ (int) $total }};
        const input = document.getElementById('uang-pelanggan');
        const uang = parseInt(input.value);

        if (!isNaN(uang) && uang >= total) {
            const kembalian = uang - total;
            document.getElementById('uang-diterima').textContent = `Rp ${uang.toLocaleString('id-ID')}`;
            document.getElementById('kembalian').textContent = `Rp ${kembalian.toLocaleString('id-ID')}`;
            document.getElementById('tunai-info').classList.remove('d-none');

            // Set cash amount and change
            document.getElementById('cash-amount').value = uang;
            document.getElementById('change').value = kembalian;

            document.getElementById('btn-bayar-tunai').classList.add('d-none');
            document.getElementById('payment-buttons').classList.add('d-none');
        } else {
            alert('Nominal uang tidak mencukupi.');
        }
    });
</script>

@endsection
