@extends('kitchen.layout')

@section('content')
<div class="content-kitchen">
    <h3 class="mb-4">Daftar Pesanan</h3>
    <div id="order-container" class="row g-3">
    </div>
</div>

<!-- Modal Konfirmasi -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">Konfirmasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Lanjutkan pesanan ini sebagai <strong>Selesai</strong>?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="confirmServeBtn">Lanjutkan</button>
            </div>
        </div>
    </div>
</div>

<script>
let pendingServeId = null;

function fetchOrders() {
    fetch("{{ route('kitchen.orders') }}")
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('order-container');
            container.innerHTML = '';

            data.filter(order => order.status !== 'Selesai').forEach(order => {
                const card = document.createElement('div');
                card.className = 'col-md-4';
                card.innerHTML = `
                    <div class="card h-100" data-order-id="${order.id}" onclick="confirmServe(${order.id})" style="background-color: #EFEFEF; color:black">
                        <div class="card-body">
                            <h5 class="card-title">Meja ${order.table_number} - Antrian #${order.queue_number}</h5>
                            <p>Status: <strong>${order.status.charAt(0).toUpperCase() + order.status.slice(1)}</strong></p>
                            <ul>
                                ${order.items.map(item => `
                                    <li>
                                        ${item.menu.name} x ${item.quantity}
                                        ${item.note ? `<br><small class="text-muted">Catatan: ${item.note}</small>` : ''}
                                    </li>
                                `).join('')}
                            </ul>
                        </div>
                    </div>
                `;
                container.appendChild(card);
            });
        });
}

function confirmServe(orderId) {
    pendingServeId = orderId;
    const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
    modal.show();
}

document.getElementById('confirmServeBtn').addEventListener('click', function () {
    if (pendingServeId) {
        updateStatus(pendingServeId, 'Selesai');
        const modal = bootstrap.Modal.getInstance(document.getElementById('confirmModal'));
        modal.hide();
        pendingServeId = null;
    }
});

function updateStatus(orderId, status) {
    fetch("{{ route('kitchen.updateStatus') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ order_id: orderId, status: status })
    })
    .then(response => response.json())
    .then(() => fetchOrders());
}

fetchOrders();
setInterval(fetchOrders, 5000);
</script>
@endsection
