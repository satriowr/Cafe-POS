@extends('user.layout')

@section('content')
<div class="px-3 pt-3">
<header class="d-flex align-items-center px-2 py-2 mb-3" style="background-color: #f8f9fa; border-radius: 12px;">
    <div>
        <h5 class="mb-1 fw-bold" style="color: #038447;">Status Pesanan</h5>
        <p class="mb-0 text-muted" style="font-size: 0.9rem;">Cek status pesananmu disini</p>
    </div>
</header>

    @if($orders->isEmpty())
    <br><br>
    <div class="text-center my-5">
        <img src="https://img.freepik.com/free-vector/startled-concept-illustration_114360-1864.jpg?t=st=1748224120~exp=1748227720~hmac=e8dc492f7ac1b8670bc958c24b6aeb3ddff0fa81dcca01942cad7f4b2be0e1d2" width=250 alt="">
        <p class="mb-0 mt-3 text-muted" style="font-size: 0.9rem;">Belum ada pesanan untuk meja ini</p>
    </div>
    @else
        @foreach($orders as $order)
            <div class="mb-3 rounded p-3" style="background-color: #EFEFEF; color: black;">
                <h5>Antrian #{{ $order->queue_number }} dari {{ $maxQueue }}</h5>
                <p>Status: <strong>{{ ucfirst($order->status) }}</strong></p>
                <ul class="mb-0">
                    @foreach($order->items as $item)
                        <li>{{ $item->menu->name }} x {{ $item->quantity }}</li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    @endif
</div>
@endsection
