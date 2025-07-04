@extends('layouts.app')

@section('title', 'Identitas Koperasi')
@section('sub-title', 'Identitas Koperasi')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Identitas Koperasi</h1>
        <div class="flex place-content-around items-center w-1/2">
            <div class="bg-green-100 p-2 rounded-lg border-2 border-green-400 space-x-2 flex justify-around">
                <p class="text-sm">Export</p> <img src="{{ asset('img/icons-bootstrap/export/cloud-download.svg') }}"
                    class="h-auto w-[20px]">
            </div>
            <div class="bg-gray-100 p-2 flex flex-row space-x-2 item-center rounded-lg border-2 border-gray-300">
                <i class="fa-solid fa-magnifying-glass  " style="color:gray;"></i>
                <p class="text-sm text-gray-500  ">Nama Koperasi</p>
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
            'nama_lembaga' => 'Nama Koperasi',
            'nama_ketua' => 'Nama Pimpinan',
            'hp_ketua' => 'No HP',
            'alamat' => 'Alamat',
            'telepon' => 'Telepon',
            'kota' => 'Kota/Kabupaten',
            'email' => 'Email',
            'web' => 'Web',
            'npwp' => 'NPWP',
            'no_badan_hukum' => 'No. Badan Hukum',
            'tgl_berdiri' => 'Tanggal Berdiri',
            'tgl_pengesahan' => 'Tanggal Pengesahan',
            'bidang_usaha' => 'Bidang Usaha',
            'status_kantor' => 'Status Kantor',
            'status_kepemilikan' => 'Status Kepemilikan',
            'luas_tanah' => 'Luas Tanah',
            'luas_bangunan' => 'Luas Bangunan',
            'modal_sendiri' => 'Modal Sendiri',
            'modal_luar' => 'Modal Luar',
            'jumlah_anggota' => 'Jumlah Anggota',
            'jumlah_karyawan' => 'Jumlah Karyawan',
            'jumlah_pengurus' => 'Jumlah Pengurus',
            'jumlah_pengawas' => 'Jumlah Pengawas',
            'jumlah_simpanan' => 'Jumlah Simpanan',
            'jumlah_pinjaman' => 'Jumlah Pinjaman',
        ];
        $data = [];
        foreach($identitasKoperasi as $item) {
            $data[$item->opsi_key] = $item->opsi_val;
        }
    @endphp
    <div class="bg-white rounded-lg shadow overflow-hidden w-full max-w-5xl mx-auto mb-8 p-8 md:p-12 relative">
        <div class="p-4 border-b mb-6">
            <h2 class="text-lg font-semibold">Data Koperasi</h2>
        </div>
        <form id="identitasForm" method="POST" action="{{ route('settings.identitas_koperasi.update') }}">
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