@extends('user.layout')

@section('content')
<div class="px-3 pt-4 text-center" id="loading-screen">
    <img src="https://img.freepik.com/free-vector/paper-money-dollar-bills-blue-credit-card-3d-illustration-cartoon-drawing-payment-options-3d-style-white-background-payment-finances-shopping-banking-commerce-concept_778687-724.jpg?t=st=1752801611~exp=1752805211~hmac=0bae59dc69415af116d9b923b823026f146b9c1d6e2f40f39e0baa764df6f0e6&w=1800" 
        alt="Ilustrasi Pembayaran"
        style="max-width: 250px;" class="mb-3">
    <h5 class="fw-bold" style="color: #038447;">Anda sedang berada di halaman pembayaran</h5>
    <p class="text-muted">Mohon untuk tidak menutup atau refresh</p>
</div>

<button id="pay-button" class="btn btn-success d-none">Bayar Sekarang</button>

<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>
<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function () {
        setTimeout(function () {
            document.getElementById('pay-button').click();
        }, 2000);
    });

    document.getElementById('pay-button').addEventListener('click', function () {
        window.snap.pay('{{ $snapToken }}', {
            onSuccess: function(result){
                fetch("{{ route('payment.success') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        order_id: result.order_id
                    })
                }).then(() => {
                    window.location.href = "/payment/update?token=" + encodeURIComponent("{{ $token }}");
                });
            },
            onPending: function(result){
                window.location.href = "{{ route('user.cart.show', ['token' => $token]) }}";
                alert("Menunggu pembayaran...");
            },
            onError: function(result){
                alert("Pembayaran gagal!");
            },
            onClose: function(){
                window.location.href = "{{ route('user.cart.show', ['token' => $token]) }}";
            }
        });
    });
</script>
@endsection
