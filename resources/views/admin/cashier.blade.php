@extends('admin.layout')

@section('content')
<h2 class="mb-4">Kasir</h2>

<div class="row" id="cashier-container"></div>

<script>
    function fetchCashierData() {
        fetch("{{ route('admin.cashier.data') }}")
            .then(response => response.json())
            .then(tables => {
                const container = document.getElementById('cashier-container');
                container.innerHTML = ''; // Clear existing content

                if (tables.length === 0) {
                    container.innerHTML = `
                        <div class="col-12 text-center">
                            <br> <br> <br>
                            <img src="https://img.icons8.com/?size=100&id=5kocMC03z1Jq&format=png" alt="No orders" class="mb-3">
                            <h5>Belum ada pesanan masuk</h5>
                        </div>
                    `;
                } else {
                    tables.forEach(table => {
                        const col = document.createElement('div');
                        col.className = 'col-md-3 mb-3';

                        col.innerHTML = `
                            <div class="card shadow-sm h-100">
                                <div class="card-body">
                                    <h5 class="card-title">Meja ${table.table_number}</h5>
                                    <p>Total: <strong>Rp ${new Intl.NumberFormat('id-ID').format(table.total)}</strong></p>
                                    <a href="/admin/cashier/${table.table_number}" class="btn w-100" style="background-color:#0E8636 !important; color:white;">Detail</a>
                                </div>
                            </div>
                        `;

                        container.appendChild(col);
                    });
                }
            });
    }

    // Load awal dan setiap 5 detik
    fetchCashierData();
    setInterval(fetchCashierData, 4000);
</script>

@endsection
