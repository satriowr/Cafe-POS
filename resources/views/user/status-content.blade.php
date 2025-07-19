@if($orders->isEmpty())
    <br><br>
    <div class="text-center my-5">
        <img src="https://img.freepik.com/free-vector/startled-concept-illustration_114360-1864.jpg?t=st=1748224120~exp=1748227720~hmac=e8dc492f7ac1b8670bc958c24b6aeb3ddff0fa81dcca01942cad7f4b2be0e1d2" width=250 alt="">
        <p class="mb-0 mt-3 text-muted" style="font-size: 0.9rem;">Belum ada pesanan untuk meja ini</p>
    </div>
@else
    @foreach($orders as $order)
        <div class="mb-3 rounded p-3" style="background-color: #EFEFEF; color: black;">
            <h5>Antrian #{{ $order->queue_number }}</h5>
            <p style="margin:0"><strong>ID Order: {{ $order->id }}</strong></p>
            <p>Status: <strong>{{ ucfirst($order->status) }}</strong></p>
            <ul class="mb-0">
                @foreach($order->items as $item)
                    <li>{{ $item->menu->name }} x {{ $item->quantity }}</li>
                @endforeach
            </ul>
        </div>
    @endforeach
@endif
