@extends('user.layout')

<style>
    .category-btn {
        background-color: #038447;
        color: white;
        border: none;
    }

    .category-btn:hover,
    .category-btn:focus,
    .category-btn:active {
        background-color: #038447;
        color: white;
        outline: none;
        box-shadow: none;
    }

    .category-btn.active {
        background-color: #038447 !important;
        color: white !important;
    }
    @media (max-width: 400px) {
        .card {
            max-width: 160px;
        }
    } 
</style>

@section('content')
    @if(session('success'))
        <div class="alert alert-success" id="success-alert">{{ session('success') }}</div>
    @endif

<div class="px-3 pt-3">
<header class="d-flex align-items-center px-2 py-2 mb-3" style="background-color: #f8f9fa; border-radius: 12px;">
    <div>
        <h5 class="mb-1 fw-bold" style="color: #038447;">Menu</h5>
        <p class="mb-0 text-muted" style="font-size: 0.9rem;">Silakan pilih menu favoritmu</p>
    </div>
</header>
<br>
    

    @php
        $currentCategory = request()->query('category');
    @endphp 

    <div class="d-flex gap-2 overflow-auto pb-2">
        <a href="{{ route('user.menu', ['token' => request('token')]) }}"
           class="btn category-btn {{ !$currentCategory ? 'active' : '' }}">
            Semua
        </a>
        @foreach ($categories as $category)
            <a href="{{ route('user.menu', ['token' => request('token'), 'category' => $category]) }}"
               class="btn category-btn {{ $currentCategory === $category ? 'active' : '' }}">
                {{ $category }}
            </a>
        @endforeach
    </div>

    <div class="mt-4">
        <div class="row row-cols-2 g-3">
            @foreach ($menus as $menu)
                <div class="col d-flex justify-content-center">
                    <div class="card shadow-sm border-0 p-2 h-100 w-100" style="max-width: 200px;">
                        <img src="{{ asset('storage/' . $menu->image) }}" alt="{{ $menu->name }}"
                            class="rounded" style="width: 100%; height: 120px; object-fit: cover;"> 

                        <h6 class="mt-2 mb-1" style="font-size: 0.95rem;">{{ $menu->name }}</h6>
                        <p class="mb-0 text-muted" style="font-size: 0.9rem;">Rp {{ number_format($menu->price, 0, ',', '.') }}</p> 

                        <button class="btn mt-3 w-100 py-1" 
                            data-bs-toggle="modal" 
                            data-bs-target="#addToCartModal{{ $menu->id }}"
                            style="background-color: #038447; color: white; border: none;">
                            Tambah
                        </button>
                    </div>
                </div>

                <div class="modal fade" id="addToCartModal{{ $menu->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <form action="{{ route('user.cart.add', ['token' => request('token')]) }}" method="POST" class="w-100">
                            @csrf
                            <input type="hidden" name="menu_id" value="{{ $menu->id }}">
                            <input type="hidden" name="table_number" value="{{ $table_number }}">
                            <div class="modal-content rounded-top">
                                <div class="modal-header">
                                    <h5 class="modal-title">Catatan</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>{{ $menu->name }}</strong></p>
                                    <div class="mb-3">
                                        <label for="note{{ $menu->id }}" class="form-label">Catatan (opsional)</label>
                                        <textarea name="note" id="note{{ $menu->id }}" class="form-control" rows="2"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label d-block">Jumlah</label>
                                        <div class="d-flex align-items-center justify-content-start gap-2">
                                            <button type="button" class="btn btn-sm rounded-circle d-flex justify-content-center align-items-center" style="width: 32px; height: 32px; background-color: #038447; color: white;" onclick="decreaseQuantity({{ $menu->id }})">−</button>

                                            <input type="number" name="quantity" id="quantity{{ $menu->id }}" value="1" min="1" class="form-control text-center" style="width: 60px;" readonly>

                                            <button type="button" class="btn btn-sm rounded-circle d-flex justify-content-center align-items-center" style="width: 32px; height: 32px; background-color: #038447; color: white;" onclick="increaseQuantity({{ $menu->id }})">+</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-success w-100">Tambah ke Keranjang</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            @endforeach
        </div>
    </div>  


</div>

<script>
    function increaseQuantity(menuId) {
        const input = document.getElementById('quantity' + menuId);
        input.value = parseInt(input.value) + 1;
    }

    function decreaseQuantity(menuId) {
        const input = document.getElementById('quantity' + menuId);
        if (parseInt(input.value) > 1) {
            input.value = parseInt(input.value) - 1;
        }
    }
</script>

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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

@endsection
