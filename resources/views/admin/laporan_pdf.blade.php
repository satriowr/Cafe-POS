<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid black; padding: 6px; text-align: left; }
        th { background-color: #eee; }
    </style>
</head>
<body>

<h2>Laporan Penjualan</h2>
<p>Periode: {{ \Carbon\Carbon::parse($from)->format('d M Y') }} - {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</p>

<table>
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>Nama Menu</th>
            <th>Jumlah</th>
            <th>Harga Total</th>
        </tr>
    </thead>
    <tbody>
        @php $grandTotal = 0; @endphp
        @foreach($orderItems as $item)
            <tr>
                <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d M Y') }}</td>
                <td>{{ $item->menu->name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>Rp{{ number_format($item->menu->price * $item->quantity, 0, ',', '.') }}</td>
            </tr>
            @php $grandTotal += $item->menu->price * $item->quantity; @endphp
        @endforeach
        <tr>
            <td colspan="3" style="text-align: right; font-weight: bold;">Total</td>
            <td>Rp{{ number_format($grandTotal, 0, ',', '.') }}</td>
        </tr>
    </tbody>
</table>

</body>
</html>
