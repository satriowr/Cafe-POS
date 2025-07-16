@extends('admin.layout')

@section('content')
<h2 class="mb-4">Laporan Penjualan</h2>

<!-- Form Pilih Periode -->
<form method="GET" class="mb-4">
    <div class="range d-flex gap-4">
        <div>
            <label>Dari</label>
            <input type="date" name="from" value="{{ request('from', date('Y-m-d')) }}" class="border p-2 rounded">
        </div>
        <div>
            <label>Hingga</label>
            <input type="date" name="to" value="{{ request('to', date('Y-m-d')) }}" class="border p-2 rounded">
        </div>
    </div>
    <br>
    <div class="button-aksi d-flex gap-2">
        <div class="btn-terapkan">
            <button type="submit" class="btn" style="background-color:#0E8636 !important; color:white;">Terapkan</button>
        </div>
        <div class="btn-download">
            <a href="{{ route('laporan.download', request()->only(['from', 'to'])) }}">
                <button type="button" class="btn" style="background-color:#0E8636 !important; color:white;">Download sebagai PDF</button>
            </a>
        </div>

    </div>

</form>

<div class="mb-6">
    <h3 style="font-size: 20px;" class="font-semibold">Total Pendapatan: Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h3>
</div>
<br>
<div class="mb-6">
    <h3 style="font-size: 20px;" class="font-semibold mb-2">Perbandingan Pendapatan</h3>
    <canvas id="revenueChart" height="100"></canvas>
</div>
<br>
<div>
    <h3 style="font-size: 20px;" class="font-semibold mb-2">Menu Terlaris</h3>
    <canvas id="bestSellerChart" height="100"></canvas>
</div>
<br>
<div>
    <h3 style="font-size: 20px;" class="font-semibold mb-2">Rekap Penjualan</h3>
    <div class="overflow-x-auto">
        <table class="table-auto w-full border">
            <thead>
                <tr class="bg-gray-200">
                    <th class="border p-2">Tanggal</th>
                    <th class="border p-2">Nama Menu</th>
                    <th class="border p-2">Jumlah</th>
                    <th class="border p-2">Harga Total</th>
                </tr>
            </thead>
            <tbody>
                @php $grandTotal = 0; @endphp
                @foreach($orderItems as $item)
                <tr>
                    <td class="border p-2">{{ \Carbon\Carbon::parse($item->created_at)->format('d M Y') }}</td>
                    <td class="border p-2">{{ $item->menu->name }}</td>
                    <td class="border p-2">{{ $item->quantity }}</td>
                    <td class="border p-2">Rp{{ number_format($item->menu->price * $item->quantity, 0, ',', '.') }}</td>
                </tr>
                @php $grandTotal += $item->menu->price * $item->quantity; @endphp
                @endforeach
                <tr class="font-bold bg-gray-100">
                    <td colspan="3" class="border p-2 text-right">Total</td>
                    <td class="border p-2">Rp{{ number_format($grandTotal, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [{
                label: 'Pendapatan',
                data: {!! json_encode($chartData) !!},
                backgroundColor: '#328E6E',
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });

    const bestCtx = document.getElementById('bestSellerChart').getContext('2d');
    const bestSellerChart = new Chart(bestCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($bestSellerLabels) !!},
            datasets: [{
                label: 'Jumlah Terjual',
                data: {!! json_encode($bestSellerData) !!},
                backgroundColor: '#328E6E',
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    precision: 0
                }
            }
        }
    });
</script>

@endsection
