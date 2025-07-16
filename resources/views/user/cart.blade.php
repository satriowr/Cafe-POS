@extends('user.layout')

@section('content')
<div class="px-3 pt-3">
<header class="d-flex align-items-center px-2 py-2 mb-3" style="background-color: #f8f9fa; border-radius: 12px;">
    <div>
        <h5 class="mb-1 fw-bold" style="color: #038447;">Keranjang</h5>
        <p class="mb-0 text-muted" style="font-size: 0.9rem;">Menampilkan pesanan kamu</p>
    </div>
</header>

    @if(session('success'))
        <div class="alert alert-success" id="success-alert">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($cartItems->isEmpty())
    <br><br>
    <div class="text-center my-5">
        <img src="https://img.freepik.com/free-vector/shopping-cart-with-bags-gifts-concept-illustration_114360-18775.jpg?t=st=1748223026~exp=1748226626~hmac=167ef9cdb4be270d329fe7666e3346a33b08139673a97c772a8f4f5fd2c58e5a" width=250 alt="">
        <p class="mb-0 mt-3 text-muted" style="font-size: 0.9rem;">Anda belum memiliki pesanan</p>
    </div>
    @else
        <div class="list-group">
            @php $grandTotal = 0; @endphp
            @foreach($cartItems as $item)
                @php
                    $totalPrice = $item->quantity * $item->menu->price;
                    $grandTotal += $totalPrice;
                @endphp
                <div class="list-group-item" style="background-color:#EFEFEF">
                    <div class="d-flex justify-content-between align-items-center" style="background-color:#EFEFEF">
                        <div>
                            <strong>{{ $item->menu->name }}</strong><br>
                            <small>{{ $item->quantity }} x Rp {{ number_format($item->menu->price, 0, ',', '.') }}</small>
                            <br>
                            <small>{{ $item->note }}</small>
                        </div>
                        <div class="text-end">
                            <strong>Rp {{ number_format($totalPrice, 0, ',', '.') }}</strong>
                            <form action="{{ route('user.cart.delete', ['token' => $token]) }}" method="POST" class="d-inline ms-2" onsubmit="return confirm('Hapus item ini dari keranjang?')">
                                @csrf
                                <input type="hidden" name="cart_id" value="{{ $item->id }}">
                                <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">✕</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-3 d-flex justify-content-between">
            <strong>Total</strong>
            <strong>Rp {{ number_format($grandTotal, 0, ',', '.') }}</strong>
        </div>
        <small>Harga sebelum pajak</small>

        <div class="mt-4">
            <form action="{{ route('user.order.create', ['token' => $token]) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success w-100">Lanjutkan Pemesanan</button>
            </form>
        </div>
    @endif
</div>

<script>
    setTimeout(function() {
        const alert = document.getElementById('success-alert');
        if (alert) {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }
    }, 3000);
</script>
@endsection
