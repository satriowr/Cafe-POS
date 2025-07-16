@extends('admin.layout')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/menu.css') }}">
@endpush

@section('content')
  <style>
    .btn-success{
      background-color:#0E8636 !important; 
      color:fff;
    }
    .btn-primary{
      background-color:#0E8636 !important; 
      color:fff;
    }
  </style>
  <h2>Pengaturan Menu</h2>

  @if(session('success'))
    <div id="snackbar" class="position-fixed bottom-0 end-0 m-4 bg-success text-white p-3 rounded shadow">
      {{ session('success') }}
    </div>
  @endif

  <div class="container-btn mb-3">
    <a href="/admin/menus/create">
      <button type="button" class="btn" style="background-color:#0E8636 !important; color:white;">Tambahkan Menu</button>
    </a>
  </div>


  <table class="table mt-4" style="border-collapse: collapse; width: 100%;">
  <thead>
    <tr>
      <th style="border: 1px solid #ccc; padding: 8px; background-color: #0E8636; color:white;">Nama</th>
      <th style="border: 1px solid #ccc; padding: 8px; background-color: #0E8636; color:white;">Harga</th>
      <th style="border: 1px solid #ccc; padding: 8px; background-color: #0E8636; color:white;">Kategori</th>
      <th style="border: 1px solid #ccc; padding: 8px; background-color: #0E8636; color:white;">Status</th>
      <th style="border: 1px solid #ccc; padding: 8px; background-color: #0E8636; color:white;">Aksi</th>
    </tr>
  </thead>
  <tbody>
    @foreach($menus as $index => $menu)
      <tr>
        <td style="border: 1px solid #ccc; padding: 8px;">{{ $menu->name }}</td>
        <td style="border: 1px solid #ccc; padding: 8px;">Rp. {{ number_format($menu->price, 0, ',', '.') }}</td>
        <td style="border: 1px solid #ccc; padding: 8px;">{{ ucfirst($menu->category) }}</td>
        <td style="border: 1px solid #ccc; padding: 8px;">
          <button type="button" class="btn btn-sm {{ $menu->is_available ? 'btn-success' : 'btn-secondary' }}"
            data-bs-toggle="modal" data-bs-target="#statusModal{{ $menu->id }}">
            {{ $menu->is_available ? 'Tersedia' : 'Habis' }}
          </button>
        </td>
        <td style="border: 1px solid #ccc; padding: 8px;">
          <a href="{{ route('menus.edit', $menu->id) }}" style="text-decoration:none">
          <button class="btn btn-sm btn-warning">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="white" class="bi bi-pencil-fill" viewBox="0 0 16 16">
            <path d="M12.854.146a.5.5 0 0 0-.707 0L10.5 1.793 14.207 5.5l1.647-1.646a.5.5 0 0 0 0-.708zm.646 6.061L9.793 2.5 3.293 9H3.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.207zm-7.468 7.468A.5.5 0 0 1 6 13.5V13h-.5a.5.5 0 0 1-.5-.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.5-.5V10h-.5a.5.5 0 0 1-.175-.032l-.179.178a.5.5 0 0 0-.11.168l-2 5a.5.5 0 0 0 .65.65l5-2a.5.5 0 0 0 .168-.11z"/>
          </svg>
          </button>
          </a>

            <form action="{{ route('menus.destroy', $menu->id) }}" method="POST" style="display:inline-block;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus menu ini?')">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="white" class="bi bi-trash2-fill" viewBox="0 0 16 16">
              <path d="M2.037 3.225A.7.7 0 0 1 2 3c0-1.105 2.686-2 6-2s6 .895 6 2a.7.7 0 0 1-.037.225l-1.684 10.104A2 2 0 0 1 10.305 15H5.694a2 2 0 0 1-1.973-1.671zm9.89-.69C10.966 2.214 9.578 2 8 2c-1.58 0-2.968.215-3.926.534-.477.16-.795.327-.975.466.18.14.498.307.975.466C5.032 3.786 6.42 4 8 4s2.967-.215 3.926-.534c.477-.16.795-.327.975-.466-.18-.14-.498-.307-.975-.466z"/>
            </svg>
            </button>
          </form>
        </td>
      </tr>

      <div class="modal fade" id="statusModal{{ $menu->id }}" tabindex="-1" aria-labelledby="statusModalLabel{{ $menu->id }}" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <form action="/admin/menus/update/{{ $menu->id }}" method="POST">
              @csrf
              <input type="hidden" name="status_only" value="1">

              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="statusModalLabel{{ $menu->id }}">Ubah Ketersediaan Menu</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                  <p>Menu: <strong>{{ $menu->name }}</strong></p>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="is_available" id="available{{ $menu->id }}" value="1" {{ $menu->is_available ? 'checked' : '' }} required>
                    <label class="form-check-label" for="available{{ $menu->id }}">Tersedia</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="is_available" id="not_available{{ $menu->id }}" value="0" {{ !$menu->is_available ? 'checked' : '' }} required>
                    <label class="form-check-label" for="not_available{{ $menu->id }}">Habis</label>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                  <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
              </div>
            </form>
          </div>
        </div>
    @endforeach
  </tbody>
</table>

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
@endsection
