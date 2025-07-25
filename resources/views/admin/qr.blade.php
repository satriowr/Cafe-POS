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
  
  <!-- Meja Select Button -->
  <div class="mb-3">
    <label for="table_number" class="form-label">Nomor Meja</label>
    <button type="button" class="btn btn-outline-success w-100" data-bs-toggle="modal" data-bs-target="#tableModal">Pilih Nomor Meja</button>
    <input type="hidden" name="table_number" id="table_number" required>
    <!-- Display the selected table number -->
    <div id="selected-table" class="mt-2"></div>
  </div>

  <!-- Customer Identity -->
  <div class="mb-3">
    <label for="customer_identity" class="form-label">Email / No. Telepon</label>
    <input type="text" name="customer_identity" id="customer_identity" class="form-control" required>
  </div>

  <!-- Order Type Radio Buttons -->
  <div class="mb-3">
    <label for="order_type" class="form-label">Tipe Pesanan</label><br>
    <div class="d-flex">
      <div class="me-2">
        <input type="radio" name="order_type" value="1" id="dine_in" required class="btn-check" />
        <label for="dine_in" class="btn btn-outline-success">Dine-In</label>
      </div>
      <div>
        <input type="radio" name="order_type" value="2" id="takeaway" class="btn-check" />
        <label for="takeaway" class="btn btn-outline-success">Takeaway</label>
      </div>
    </div>
  </div>

  <br><br>
  <button type="submit" class="btn btn-dark">Buat QR</button>
</form>

<!-- Modal for Table Selection -->
<div class="modal fade" id="tableModal" tabindex="-1" aria-labelledby="tableModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="tableModalLabel">Pilih Nomor Meja</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          @for ($i = 1; $i <= 10; $i++)
            <div class="col-6 mb-2">
              <button type="button" class="btn btn-outline-success w-100" onclick="selectTable({{ $i }})">{{ $i }}</button>
            </div>
          @endfor
        </div>
        <!-- Meja 0 placed below -->
        <div class="row mt-3">
          <div class="col-12">
            <button type="button" class="btn btn-outline-success w-100" onclick="takeaway()">Takeaway</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@endif

<!-- Custom Styles for Radio Button -->
<style>
  .btn-check:checked + .btn-outline-success {
    background-color: #0E8636 !important;
    color: white;
  }
  .btn-outline-success {
    border-color: #0E8636;
    color: #0E8636;
  }
  .btn-check:focus + .btn-outline-success {
    box-shadow: none;
  }
  .btn-check:checked + .btn-outline-success:hover {
    background-color: #0E8636 !important;
    color: white;
  }
</style>

<script>
let generatedNumbers = new Set();

function selectTable(tableNumber) {
    document.getElementById('table_number').value = tableNumber;

    if (tableNumber >= 100) {
        document.getElementById('selected-table').innerHTML = "Tipe Pemesanan: Takeaway";
    } else {
        document.getElementById('selected-table').innerHTML = "Nomor Meja yang Dipilih: " + tableNumber;
    }

    if (tableNumber <= 10) {
        document.getElementById('dine_in').checked = true; 
        document.getElementById('takeaway').disabled = true; 
    }

    var modal = bootstrap.Modal.getInstance(document.getElementById('tableModal'));
    modal.hide();
}

function takeaway() {
    let randomNumber = generateUniqueRandomNumber();

    document.getElementById('table_number').value = randomNumber;
    document.getElementById('selected-table').innerHTML = "Tipe Pemesanan: Takeaway, Nomor Meja: " + randomNumber;
    console.log("Generated Unique Takeaway Number: ", randomNumber);

    if (randomNumber >= 100) {
        document.getElementById('selected-table').innerHTML = "Tipe Pemesanan: Takeaway";
    }

    if (randomNumber >= 100) {
        document.getElementById('dine_in').disabled = true; 
        document.getElementById('takeaway').checked = true; 
    } else {
        document.getElementById('dine_in').disabled = false;  
        document.getElementById('takeaway').checked = false; 
    }

    var modal = bootstrap.Modal.getInstance(document.getElementById('tableModal'));
    modal.hide();
}

function generateUniqueRandomNumber() {
    let randomNumber;
    do {
        randomNumber = Math.floor(Math.random() * (999 - 100 + 1)) + 100; 
    } while (generatedNumbers.has(randomNumber)); 

    generatedNumbers.add(randomNumber);  
    return randomNumber;
}
</script>


@endsection
