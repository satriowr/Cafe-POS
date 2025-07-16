@extends('admin.layout')

@section('content')
<h2>Buat Sesi QR Baru</h2>

@if(session('error'))
  <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if(isset($qr))
  <div class="text-center mt-4" id="qr-area">
    <h4>QR untuk Meja {{ $table_number }}</h4>
    <div class="my-3" id="qr-print-area">
      {!! $qr !!}
    </div>
    <br>
    <p style="font-size:10px;" class="text-muted">Link: <a href="{{ $url }}" target="_blank">{{ $url }}</a></p>

    <button onclick="window.print()" class="btn" style="background-color:#0E8636 !important; color:white;">Print QR</button>
  </div>
@else
<form action="{{ route('admin.qr.preview') }}" method="GET" class="mt-4">
  <div class="mb-3">
    <label for="table_number" class="form-label">Nomor Meja</label>
    <select name="table_number" id="table_number" class="form-select" required>
      <option value="" disabled selected>Pilih Nomor Meja</option>
      @for ($i = 1; $i <= 10; $i++)
        <option value="{{ $i }}">{{ $i }}</option>
      @endfor
    </select>
  </div>

  <div class="mb-3">
    <label for="customer_identity" class="form-label">Email / No. Telepon</label>
    <input type="text" name="customer_identity" id="customer_identity" class="form-control" required>
  </div>

  <button type="submit" class="btn" style="background-color:#0E8636 !important; color:white;">Buat QR</button>
</form>
@endif

<style>
  @media print {
    body * {
      visibility: hidden;
    }
    #qr-print-area, #qr-print-area * {
      visibility: visible;
    }
    #qr-print-area {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      text-align: center;
      padding: 2cm;
    }
    #qr-print-area svg {
      width: 10cm !important; 
      height: 10cm !important;
    }
  }
</style>

@endsection
