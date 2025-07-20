<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        .order-table-wrapper {
            height: 400px; /* Atur ketinggian maksimum */
            overflow-y: auto;  /* Memberikan scroll vertikal jika melebihi batas */
            border: 1px solid #ddd; /* Memberikan border pada tabel */
            padding: 10px;
            border-radius: 6px;
        }
    </style>
</head>
<body>
    <div class="container my-5">
        @php
            $orderIds = request()->query('order_ids');
            $orderIdArray = explode(',', $orderIds);
            $orderIds = $orderIdArray[0];
            $difference_plus = number_format($difference, 0, ',', '.');
        @endphp

        <div class="row">
            <div class="col-md-6">
                <h5>Pesanan:</h5>
                <div class="order-table-wrapper">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th style="background-color:#0E8636 !important; color:white;">Menu</th>
                                    <th style="background-color:#0E8636 !important; color:white;">Qty</th>
                                    <th style="background-color:#0E8636 !important; color:white;">Harga</th>
                                    <th style="background-color:#0E8636 !important; color:white;">Subtotal</th>
                                    <th style="background-color:#0E8636 !important; color:white;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $subtotal = 0; @endphp
                                @foreach($orderItems as $item)
                                    @php
                                        $itemSubtotal = $item->quantity * $item->menu_price;
                                        $subtotal += $itemSubtotal;
                                    @endphp
                                    <tr>
                                        <td>{{ $item->menu_name }}</td>
                                        <td>
                                            <form action="{{ route('admin.cashier.updateItem', ['order_id' => $item->order_id, 'item_id' => $item->id]) }}" method="POST">
                                                @csrf
                                                <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" class="form-control" style="width: 100px;" required />
                                                <button type="submit" class="btn btn-warning btn-sm mt-2">Update</button>
                                            </form>
                                        </td>
                                        <td>Rp {{ number_format($item->menu_price, 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($itemSubtotal, 0, ',', '.') }}</td>
                                        <td>
                                            <form action="{{ route('admin.cashier.removeItem', ['order_id' => $item->order_id, 'item_id' => $item->id]) }}" method="POST" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
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
                                <form action="{{ route('admin.cashier.addItem', ['menu_id' => $menu->id]) }}?order_ids={{ $orderIds }}" method="POST" style="width: 100%;">
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

        <div class="wrap-payment d-flex justify-content-between">
            <div class="mt-4">
                @php
                    $tax = $subtotal * 0.1;
                    $service = $subtotal * 0.05;
                    $total = $subtotal + $tax + $service;
                @endphp

                <p style="margin:0"><strong>Subtotal:</strong> Rp {{ number_format($subtotal, 0, ',', '.') }}</p>
                <p style="margin:0"><strong>Pajak (10%):</strong> Rp {{ number_format($tax, 0, ',', '.') }}</p>
                <p style="margin:0"><strong>Biaya Layanan (5%):</strong> Rp {{ number_format($service, 0, ',', '.') }}</p>
                <h4 class="mt-3"><strong>Total:</strong> Rp {{ number_format($total, 0, ',', '.') }}</h4>

                <div class="d-flex gap-2">
                    <form method="POST" action="{{ route('admin.cashier.pay', ['table_number' => $tableNumber]) }}">
                        @csrf
                        <input type="hidden" name="payment_type" value="qris"> 
                        <button type="submit" class="btn btn-success"
                        @if($orders[0]->payment_method != 'cash') disabled @endif>
                        Bayar Non Tunai (QRIS)
                        </button>
                    </form>

                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#tunaiModal"
                    @if($orders[0]->payment_method != 'cash') disabled @endif>
                    Bayar Tunai
                    </button>
                    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#updateOrderModal" 
                        @if($orders[0]->payment_method != 'cashless') disabled @endif>
                        Perbarui Pesanan
                    </button> &nbsp&nbsp&nbsp
                    <button class="btn btn-danger" onclick="window.close();">Kembali</button>
                </div>
            </div>
            <div class="order-information mt-4">
                <p>Metode pembayaran: {{ $orders[0]->payment_method }}</p>
                <p style="margin:0"><strong>Total uang masuk:</strong> Rp {{ number_format($orders[0]->grand_total, 0, ',', '.') }}</p>
                <div class="payment-difference">
                    <strong>Selisih Pembayaran: </strong> 
                    <span id="payment-difference">Rp {{ number_format($difference, 0, ',', '.') }}</span> <!-- PHP calculated difference -->
                </div>
            </div>
        </div>

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
                        <button type="button" id="btn-bayar-tunai-trigger" class="btn btn-success" >Bayar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
    // Event listener untuk tombol "Bayar" di modal tunai
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

    // Function untuk memperbarui kembalian setiap kali input berubah
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

    <div class="modal fade" id="updateOrderModal" tabindex="-1" aria-labelledby="updateOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateOrderModalLabel">Perbarui Pesanan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>Selisih Pembayaran: </strong> 
                    <span id="payment-difference">Rp {{ number_format($difference, 0, ',', '.') }}</span>
                    <div class="alert alert-success" role="alert">
                        Selalu pastikan dan <strong>hitung kembali</strong> total selisih sebelum menyelesaikan pesanan.
                    </div>
                </p>

                <div id="modal-buttons">
                    @if($difference < 0)
                        <button class="btn btn-success" id="pay-qris-btn" data-bs-dismiss="modal" 
                                onclick="payWithQRIS()" style="width: 100%;">Bayar Non Tunai (QRIS)</button><br>
                        
                        <form id="qris-payment-form" action="{{ route('admin.cashier.updateReceiptQris', ['receipt_id' =>  $orders[0]->receipt_id]) }}" method="POST" style="display: none;">
                            @csrf
                            <input type="hidden" name="total_price" value="{{ $subtotal }}">
                            <input type="hidden" name="tax_amount" value="{{ $tax }}">
                            <input type="hidden" name="service_charge" value="{{ $service }}">
                            <input type="hidden" name="grand_total" value="{{ $total }}">
                            <input type="hidden" name="difference" value="{{ number_format($difference, 0, ',', '.') }}">

                        </form>

                        <script>
                            function payWithQRIS() {
                                document.getElementById('qris-payment-form').submit();
                            }
                        </script>

                        <button class="btn btn-success mt-2" id="pay-cash-btn" data-bs-dismiss="modal" 
                                onclick="payWithCash()" style="width: 100%;" data-bs-toggle="modal" data-bs-target="#modaltunai">Bayar Tunai</button>
                    @else
                    <form 
                        action="{{ $orders[0]->receipt_id ? route('admin.cashier.updateReceipt', ['receipt_id' => $orders[0]->receipt_id]) : '#' }}" 
                        method="POST" 
                        @if(!$orders[0]->receipt_id) 
                            disabled 
                        @endif
                    >
                        @csrf
                        <input type="hidden" name="total_price" value="{{ $subtotal }}">
                        <input type="hidden" name="tax_amount" value="{{ $tax }}">
                        <input type="hidden" name="service_charge" value="{{ $service }}">
                        <input type="hidden" name="grand_total" value="{{ $total }}">
                        <input type="hidden" name="difference" value="{{ number_format($difference, 0, ',', '.') }}">
                        
                        <button type="submit" 
                            class="btn btn-success mt-3" 
                            style="width: 100%;" 
                            @if(!$orders[0]->receipt_id) 
                                disabled 
                            @endif
                        >
                            Cetak Struk Pembayaran
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modaltunai" tabindex="-1" aria-labelledby="tunaiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pembayaran Tunai</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @if(isset($difference) && $difference < 0)
                    <strong>Total Tambahan: Rp {{ number_format(abs($difference), 0, ',', '.') }}</strong>
                @else
                    <strong>Total: Rp 0</strong>
                @endif

                <!-- Form Submit -->
                <form method="POST" 
                    action="{{ $orders[0]->receipt_id ? route('admin.cashier.updateReceiptCash', ['receipt_id' => $orders[0]->receipt_id]) : '#' }}" 
                    @if(!$orders[0]->receipt_id) 
                        disabled 
                    @endif
                >
                    @csrf
                    <div class="mb-3 mt-3">
                        <label for="uang-pelanggan" class="form-label">Uang Pelanggan</label>
                        <input type="number" class="form-control" name="cash_amount" id="uang-pelanggan2" placeholder="Masukkan nominal" min="1" required />
                    </div>
                    <div class="mb-3">
                        <label for="kembalian" class="form-label">Kembalian</label>
                        <input type="text" name="change" class="form-control" id="kembalian2" disabled />
                        <input type="hidden" name="change" id="change-numeric">
                        <input type="hidden" name="difference" value="{{ $difference_plus }}">
                        <input type="hidden" name="total_price" value="{{ $subtotal }}">
                        <input type="hidden" name="tax_amount" value="{{ $tax }}">
                        <input type="hidden" name="service_charge" value="{{ $service }}">
                        <input type="hidden" name="grand_total" value="{{ $total }}">
                    </div>
                    <div class="alert alert-success" role="alert">
                        Selalu Hitung kembali sebelum menyelesaikan pembayaran.
                    </div>
                    <button type="submit" 
                            class="btn btn-success mt-3" 
                            id="btn-bayar-tunai" 
                            style="width: 100%;"
                            @if(!$orders[0]->receipt_id || $difference > 0) 
                                disabled 
                            @endif
                    >
                        Bayar & Cetak Receipt
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('uang-pelanggan2').addEventListener('input', function () {
        const total = {{ abs($difference) }}; // Menggunakan nilai absolute dari selisih pembayaran
        const uang = parseInt(this.value);

        if (!isNaN(uang) && uang >= total) {
            let kembalian = uang - total;
            kembalian = Math.round(kembalian);

            const kembalianFormatted = kembalian.toLocaleString('id-ID');

            document.getElementById('kembalian2').value = kembalianFormatted;
            document.getElementById('change-numeric').value = kembalian;
            document.getElementById('btn-bayar-tunai').disabled = false;
            document.getElementById('cash-amount').value = uang;
        } else {
            //
        }
    });
</script>


</body>
</html>
