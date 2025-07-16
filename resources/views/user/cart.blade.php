@extends('user.layout')

@section('content')
<div class="px-3 pt-3">
<header class="d-flex align-items-center mb-3" style="background-color: #f8f9fa; border-radius: 12px; padding-left: 15px; padding-right: 15px; padding-top: 10px; padding-bottom: 10px;">
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
                    $notAvailable = $item->menu->is_available == 0;
                @endphp
                <div class="list-group-item" style="background-color:#EFEFEF" id="cart-item-{{ $item->id }}">
                    <div class="d-flex justify-content-between align-items-center" style="background-color:#EFEFEF">
                        <div>
                            <strong>{{ $item->menu->name }}</strong>
                            @if($notAvailable)
                                <span class="badge bg-danger ms-1">Tidak tersedia</span>
                            @endif
                            <br>
                            <small>{{ $item->quantity }} x Rp {{ number_format($item->menu->price, 0, ',', '.') }}</small>
                            <br>
                            <small>{{ $item->note }}</small>
                        </div>
                        <div class="text-end">
                            <strong>Rp <span class="item-total" data-item-id="{{ $item->id }}" data-price="{{ $item->menu->price }}">{{ number_format($totalPrice, 0, ',', '.') }}</span></strong>
                            <form action="{{ route('user.cart.delete', ['token' => $token]) }}" method="POST" class="d-inline ms-2 form-cart-delete" data-item-id="{{ $item->id }}">
                                @csrf
                                <input type="hidden" name="cart_id" value="{{ $item->id }}">
                                <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">✕</button>
                            </form>
                        </div>
                    </div>
                    @if(!$notAvailable)
                    <div class="mt-3 d-flex align-items-center">
                        <form action="{{ route('user.cart.update', ['token' => $token]) }}" method="POST">
                            @csrf
                            <input type="hidden" name="cart_id" value="{{ $item->id }}">
                            <input type="hidden" name="token" value="{{ $token }}">
                            <button type="submit" class="btn btn-outline-success btn-sm" name="quantity" value="{{ $item->quantity - 1 }}" {{ $item->quantity <= 1 ? 'disabled' : '' }}>-</button>
                        </form>
                        <span class="mx-2" id="quantity-{{ $item->id }}">{{ $item->quantity }}</span>
                        <form action="{{ route('user.cart.update', ['token' => $token]) }}" method="POST">
                            @csrf
                            <input type="hidden" name="cart_id" value="{{ $item->id }}">
                            <input type="hidden" name="token" value="{{ $token }}">
                            <button type="submit" class="btn btn-outline-success btn-sm" name="quantity" value="{{ $item->quantity + 1 }}">+</button>
                        </form>
                    </div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-3 d-flex justify-content-between">
            <strong>Total</strong>
            <strong>Rp <span id="grand-total">{{ number_format($grandTotal, 0, ',', '.') }}</span></strong>
        </div>
        <small>Harga sebelum pajak</small>
        <br><br>
        <div class="container-button-submit">
            <div class="mt-4">
                <form action="{{ route('user.order.create', ['token' => $token]) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary w-100">Bayar Dengan Tunai</button>
                </form>
            </div>
            <div class="mt-2">
                <form action="{{ route('user.order.create', ['token' => $token]) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success w-100">Bayar Non Tunai</button>
                </form>
            </div>
        </div>
    @endif
</div>

<script>
document.querySelectorAll('.form-cart-delete').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var itemId = this.getAttribute('data-item-id');
        var formData = new FormData(this);

        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
        }).then(res => {
            if(res.ok) {
                document.getElementById('cart-item-' + itemId).remove();
                updateGrandTotal();
            }
        });
    });
});

function updateGrandTotal() {
    let grandTotal = 0;
    document.querySelectorAll('.item-total').forEach(function(itemTotalElement) {
        var itemTotal = itemTotalElement.innerText.replace('Rp ', '').replace('.', '');
        grandTotal += parseInt(itemTotal);
    });
    document.getElementById('grand-total').innerText = grandTotal.toLocaleString();
}

function checkAndRemoveUnavailableCart() {
    fetch("{{ route('user.cart.checkAvailable', ['token' => $token]) }}", {
        headers: {"X-Requested-With": "XMLHttpRequest"}
    })
    .then(res => res.json())
    .then(ids => {
        ids.forEach(function(id) {
            var form = document.querySelector('.form-cart-delete[data-item-id="' + id + '"]');
            if (form) {
                form.requestSubmit();
            }
        });
    });
}

setInterval(checkAndRemoveUnavailableCart, 5000);
</script>

@endsection
