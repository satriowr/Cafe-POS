@component('mail::message')
# Terima Kasih Telah Memesan di NALA 🍽️

**Invoice**: {{ $receipt->invoice_number }}  
**Meja**: {{ $receipt->table_number }}  
**Tanggal**: {{ \Carbon\Carbon::parse($receipt->paid_at)->format('d M Y H:i') }}

## Rincian Pesanan:

@foreach($orders as $order)
@foreach($order->items as $item)
- {{ $item->menu->name }} x{{ $item->quantity }}: Rp {{ number_format($item->menu->price * $item->quantity, 0, ',', '.') }}
@endforeach
@endforeach

**Subtotal**: Rp {{ number_format($receipt->total_price, 0, ',', '.') }}  
**Pajak (10%)**: Rp {{ number_format($receipt->tax_amount, 0, ',', '.') }}  
**Service (1%)**: Rp {{ number_format($receipt->service_charge, 0, ',', '.') }}  

## **Total: Rp {{ number_format($receipt->grand_total, 0, ',', '.') }}**

Terima kasih,  
**NALA Coffee**
@endcomponent
