@extends('admin.layout')

@section('content')
    <h2 class="mb-4">Detail Pembayaran</h2>

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
                        </tr>
                    </thead>
                    <tbody>
                        @php $subtotal = 0; @endphp
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
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-md-6">
            <h5>Detail Pembayaran:</h5>
            <p><strong>Invoice Number:</strong> {{ $receipt->invoice_number }}</p>
            <p><strong>Meja:</strong> {{ $order->table_number }}</p>
            <p><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($receipt->paid_at)->format('d M Y H:i') }}</p>
            <p><strong>Total:</strong> Rp {{ number_format($receipt->grand_total, 0, ',', '.') }}</p>

            <!-- Button to print receipt -->
            <a href="{{ route('admin.receipt.show', ['receipt' => $receipt->id]) }}" class="btn btn-warning">Cetak Nota</a>
        </div>
    </div>
@endsection
