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
    <h3 class="mb-4">Tambah Menu</h3>
    
    <form action="{{ route('menus.store') }}" method="POST" enctype="multipart/form-data">
      @csrf

      <div class="mb-3">
        <label for="name" class="form-label">Nama Menu</label>
        <input type="text" name="name" id="name" class="form-control" required>
      </div>

      <div class="mb-3">
        <label for="price" class="form-label">Harga (Rp)</label>
        <input type="number" name="price" id="price" class="form-control" required>
      </div>

     <div class="mb-3">
       <label for="category" class="form-label">Kategori</label>
       <select name="category" id="category" class="form-select" required>
         <option value="" disabled selected>Pilih Kategori</option>
         <option value="makanan">Makanan</option>
         <option value="minuman">Minuman</option>
       </select>
     </div>

      <div class="mb-3">
        <label for="image" class="form-label">Foto Menu (opsional)</label>
        <input type="file" name="image" id="image" class="form-control">
      </div>
    <br>
    <div class="d-flex gap-2">
      <button type="submit" name="action" value="save" class="btn btn-success">Simpan</button>
      <button type="submit" name="action" value="save_and_add" class="btn btn-success">Simpan & Tambah Menu</button>
      <a href="{{ url()->previous() }}" class="btn btn-secondary">Batal</a>
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