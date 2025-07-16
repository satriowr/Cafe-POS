@extends('admin.layout')

@section('content')

<style>
.bg-dark{
    background-color: #0E8636 !important;
}
</style>
<h2>Status Meja</h2>

<div class="row row-cols-5 g-3 mt-4">
    @for ($i = 1; $i <= 10; $i++)
        @php
            $meja = $tables->firstWhere('table_number', $i);
            $isActive = $meja?->is_active ?? 0;
            $isOccupied = $meja && $isActive == 1;
        @endphp

        <div class="col">
            <button class="btn w-100 py-4 position-relative
                {{ $isOccupied ? 'bg-dark text-white' : 'border border-dark text-dark bg-white' }}"
                data-bs-toggle="modal" data-bs-target="#confirmModal{{ $i }}">
                <strong>{{ $i }}</strong>
            </button>
        </div>

        <div class="modal fade" id="confirmModal{{ $i }}" tabindex="-1" aria-hidden="true">
            <br><br>
            <div class="modal-dialog">
                <form action="{{ route('tables.updateStatus', ['table_number' => $i]) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Ubah Status Meja {{ $i }}</h5>
                        </div>
                        <div class="modal-body">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="is_active" id="aktif{{ $i }}" value="1"
                                    {{ $isActive == 1 ? 'checked' : '' }}>
                                <label class="form-check-label" for="aktif{{ $i }}">
                                    Terisi
                                </label>
                            </div>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="radio" name="is_active" id="kosong{{ $i }}" value="0"
                                    {{ $isActive == 0 ? 'checked' : '' }}>
                                <label class="form-check-label" for="kosong{{ $i }}">
                                    Kosong
                                </label>
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
    @endfor
</div>
@endsection
