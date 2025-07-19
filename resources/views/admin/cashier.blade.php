@extends('admin.layout')

@section('content')
    <h2 class="mb-4">Kasir</h2>

    <div class="mb-4">
        <form action="{{ route('admin.cashier.searchOrder') }}" method="GET" class="form-inline">
            @csrf
            <div class="input-group">
                <input type="text" name="order_ids" class="form-control" placeholder="Masukkan ID Order" required>
                <div class="input-group-append">
                    <button class="btn btn-success" type="submit">Cari</button>
                </div>
            </div>
        </form>

        @if(session('error'))
            <div class="alert alert-danger mt-3">
                {{ session('error') }}
            </div>
        @endif
    </div>

    <div class="row">
        @forelse($tables as $table)
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <p>#{{ implode(', ', $table['order_ids']) }}</p>
                        <h5 class="card-title">Meja {{ $table['table_number'] }}</h5>
                        <p>Total: <strong>Rp {{ number_format($table['total'], 0, ',', '.') }}</strong></p>
                        <a href="{{ url('/admin/cashier/'.$table['table_number'].'?order_ids='.implode(',', $table['order_ids'])) }}"
                           class="btn w-100"
                           style="background-color:#0E8636 !important; color:white;"
                           target="_blank">
                            Detail
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center">
                <br><br><br>
                <img src="https://img.icons8.com/?size=100&id=5kocMC03z1Jq&format=png" alt="No orders" class="mb-3">
                <h5>Belum ada pesanan masuk</h5>
            </div>
        @endforelse
    </div>

    <script>
        setInterval(function() {
            location.reload(); 
        }, 5000);
    </script>
@endsection
