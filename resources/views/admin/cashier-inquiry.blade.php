@extends('admin.layout')

@section('content')
    <h2 class="mb-4">Inquiry Pesanan</h2>

    <form method="GET" action="{{ route('admin.cashier.inquiry') }}">
        <div class="mb-3">
            <label for="queue_number" class="form-label">ID pesanan</label>
            <input type="text" class="form-control" id="id" name="id" required>
        </div>
        <button type="submit" class="btn btn-success">Cari Pesanan</button>
    </form>

@endsection
