@extends('layouts.app')

@section('title', 'Suku Bunga')
@section('sub-title', 'Suku Bunga')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Suku Bunga</h1>
        <div class="flex place-content-around items-center w-1/2">
            <div class="bg-green-100 p-2 rounded-lg border-2 border-green-400 space-x-2 flex justify-around">
                <p class="text-sm">Export</p> <img src="{{ asset('img/icons-bootstrap/export/cloud-download.svg') }}"
                    class="h-auto w-[20px]">
            </div>
            <div class="bg-gray-100 p-2 flex flex-row space-x-2 item-center rounded-lg border-2 border-gray-300">
                <i class="fa-solid fa-magnifying-glass  " style="color:gray;"></i>
                <p class="text-sm text-gray-500  ">Suku Bunga</p>
            </div>

            <div class="bg-gray-100 p-3 flex flex-row item-center rounded-lg border-2 border-gray-300">
                <img src="{{ asset('img/icons-bootstrap/calendar/calendar4.svg') }}">
            </div>

            <div class="bg-green-100 py-2 px-5 rounded-lg border-2 border-green-400">
                <i class="fa-solid fa-ellipsis-vertical"></i>
            </div>
        </div>
    </div>

    @php
        $fieldKeys = [
            'bg_tab' => 'Bunga Tabungan',
            'bg_pinjam' => 'Bunga Pinjaman',
            'biaya_adm' => 'Biaya Administrasi',
            'denda' => 'Denda',
            'denda_hari' => 'Denda per Hari',
            'dana_cadangan' => 'Dana Cadangan',
            'jasa_anggota' => 'Jasa Anggota',
            'dana_pengurus' => 'Dana Pengurus',
            'dana_karyawan' => 'Dana Karyawan',
            'dana_pend' => 'Dana Pendidikan',
            'dana_sosial' => 'Dana Sosial',
            'jasa_usaha' => 'Jasa Usaha',
            'jasa_modal' => 'Jasa Modal',
            'pjk_pph' => 'Pajak PPh',
            'pinjaman_bunga_tipe' => 'Tipe Bunga Pinjaman',
            'bunga_biasa' => 'Bunga Biasa',
            'bunga_barang' => 'Bunga Barang',
        ];
        $data = [];
        foreach($sukuBunga as $item) {
            $data[$item->opsi_key] = $item->opsi_val;
        }
    @endphp
    <div class="bg-white rounded-lg shadow overflow-hidden w-full max-w-5xl mx-auto mb-8 p-8 md:p-12 relative">
        <div class="p-4 border-b mb-6">
            <h2 class="text-lg font-semibold">Data Suku Bunga</h2>
        </div>
        <form id="identitasForm" method="POST" action="{{ route('settings.suku_bunga.update') }}">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                @foreach($fieldKeys as $key => $label)
                    <div class="flex flex-col mb-2">
                        <label class="font-semibold mb-1">{{ $label }}</label>
                        <input type="text" name="{{ $key }}" class="border rounded px-3 py-2 bg-gray-100 identitas-input" value="{{ $data[$key] ?? '-' }}" readonly>
                    </div>
                @endforeach
            </div>
            <div class="flex justify-end space-x-2 mt-8" style="position: absolute; right: 2rem; bottom: 2rem;">
                <button type="button" id="editBtn" class="export-btn">Edit</button>
                <button type="submit" id="saveBtn" class="export-btn" style="display:none;">Simpan</button>
                <button type="button" id="cancelBtn" class="export-btn" style="display:none;">Batal</button>
            </div>
        </form>
    </div>
    <style>
        .export-btn {
            background-color: #e6fff2;
            border: 2px solid #14AE5C;
            color: #222;
            font-weight: 500;
            border-radius: 0.5rem;
            padding: 0.5rem 1.5rem;
            transition: background 0.2s, color 0.2s;
        }
        .export-btn:hover {
            background-color: #b2f5d6;
            color: #111;
        }
    </style>
    <script>
        const editBtn = document.getElementById('editBtn');
        const saveBtn = document.getElementById('saveBtn');
        const cancelBtn = document.getElementById('cancelBtn');
        const inputs = document.querySelectorAll('.identitas-input');
        let beforeEdit = [];
        if(editBtn) {
            editBtn.addEventListener('click', function() {
                beforeEdit = Array.from(inputs).map(i => i.value);
                inputs.forEach(i => i.readOnly = false);
                saveBtn.style.display = 'inline-block';
                cancelBtn.style.display = 'inline-block';
                editBtn.style.display = 'none';
            });
        }
        if(cancelBtn) {
            cancelBtn.addEventListener('click', function() {
                inputs.forEach((i, idx) => { i.value = beforeEdit[idx]; i.readOnly = true; });
                saveBtn.style.display = 'none';
                cancelBtn.style.display = 'none';
                editBtn.style.display = 'inline-block';
            });
        }
    </script>
</div>

<div class="popup">

</div>
@endsection