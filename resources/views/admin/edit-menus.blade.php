<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Nala</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

@if(session('success'))
  <div id="snackbar" class="position-fixed bottom-0 end-0 m-4 bg-success text-white p-3 rounded shadow">
    {{ session('success') }}
  </div>
@endif

<div class="container mt-5">
  <h3 class="mb-4">Edit Menu</h3>

  <form action="{{ route('menus.update', $menu->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('POST')

    <div class="mb-3">
      <label for="name" class="form-label">Nama Menu</label>
      <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $menu->name) }}" required>
    </div>

    <div class="mb-3">
      <label for="price" class="form-label">Harga (Rp)</label>
      <input type="number" name="price" id="price" class="form-control" value="{{ old('price', $menu->price) }}" required>
    </div>

    <div class="mb-3">
      <label for="category" class="form-label">Kategori</label>
      <select name="category" id="category" class="form-select" required>
        <option value="makanan" {{ $menu->category == 'makanan' ? 'selected' : '' }}>Makanan</option>
        <option value="minuman" {{ $menu->category == 'minuman' ? 'selected' : '' }}>Minuman</option>
      </select>
    </div>

    <div class="mb-3">
      <label for="image" class="form-label">Ganti Foto Menu (opsional)</label>
      <input type="file" name="image" id="image" class="form-control">
      @if ($menu->image)
        <small class="text-muted">Saat ini: <img src="{{ asset('storage/' . $menu->image) }}" height="50"></small>
      @endif
    </div>

    <div class="mb-3">
      <label class="form-label d-block">Status Ketersediaan</label>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="is_available" value="1" id="available"
          {{ $menu->is_available ? 'checked' : '' }}>
        <label class="form-check-label" for="available">Tersedia</label>
      </div>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="is_available" value="0" id="not_available"
          {{ !$menu->is_available ? 'checked' : '' }}>
        <label class="form-check-label" for="not_available">Habis</label>
      </div>
    </div>

    <br>
    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-warning">Simpan Perubahan</button>
      <a href="{{ route('menus.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
  </form>
</div>

<script>
  window.onload = function () {
    const snackbar = document.getElementById('snackbar');
    if (snackbar) {
      setTimeout(() => {
        snackbar.classList.add('fade');
        setTimeout(() => snackbar.remove(), 500);
      }, 4000);
    }
  };
</script>

<style>
  #snackbar.fade {
    opacity: 0;
    transition: opacity 0.5s ease-out;
  }
</style>

</body>
</html>
