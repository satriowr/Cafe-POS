<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Nala</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    .login-card {
        max-width: 400px;
        margin: 100px auto;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        border-radius: 12px;
    }

    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
    }

    .btn-primary{
        background-color: #0E8636 !important;
        border-style:none;
    }

  </style>
</head>
<body>

<div class="container">
  <div class="card login-card p-4">
  <div class="text-center">
    <img src="{{ asset('images/logo_nala.png') }}" width="100" alt="Logo">
    <h4 class="mb-4 mt-3">Login Service Nala</h4>
</div>

    <form action="{{ route('admin.login') }}" method="POST">
      @csrf

      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email"
               class="form-control"
               id="email"
               name="email"
               value="{{ old('email') }}"
               required
               autofocus>
      </div>

      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password"
               class="form-control"
               id="password"
               name="password"
               required>
      </div>

      <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
  </div>
</div>

@if(session('error'))
  <div class="toast-container">
    <div class="toast align-items-center text-bg-danger border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body">
          {{ session('error') }}
        </div>
      </div>
    </div>
  </div>
@endif

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  setTimeout(() => {
    const toast = document.querySelector('.toast');
    if (toast) {
      const bsToast = bootstrap.Toast.getOrCreateInstance(toast);
      bsToast.hide();
    }
  }, 4000);
</script>

</body>
</html>