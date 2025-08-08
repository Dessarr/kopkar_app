@extends('layouts.member')

@section('title', 'Detail Pengajuan Pinjaman')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow p-6">
        <h1 class="text-xl font-semibold mb-4">Detail Pengajuan</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <div class="text-gray-500">ID Ajuan</div>
                <div class="font-medium">{{ $pengajuan->ajuan_id }}</div>
            </div>
            <div>
                <div class="text-gray-500">Tanggal Pengajuan</div>
                <div class="font-medium">{{ \Carbon\Carbon::parse($pengajuan->tgl_input)->format('d/m/Y H:i') }}</div>
            </div>
            <div>
                <div class="text-gray-500">Jenis</div>
                <div class="font-medium">{{ $pengajuan->jenis == '1' ? 'Biasa' : $pengajuan->jenis }}</div>
            </div>
            <div>
                <div class="text-gray-500">Jumlah</div>
                <div class="font-medium">Rp {{ number_format($pengajuan->nominal,0,',','.') }}</div>
            </div>
            <div>
                <div class="text-gray-500">Lama Angsuran</div>
                <div class="font-medium">{{ $pengajuan->lama_ags }} bulan</div>
            </div>
            <div>
                <div class="text-gray-500">Status</div>
                @php $statusMap = [0=>'Pending',1=>'Disetujui',2=>'Ditolak',3=>'Terlaksana',4=>'Batal']; @endphp
                <div class="font-medium">{{ $statusMap[$pengajuan->status] ?? $pengajuan->status }}</div>
            </div>
            <div class="md:col-span-2">
                <div class="text-gray-500">Keterangan</div>
                <div class="font-medium">{{ $pengajuan->keterangan }}</div>
            </div>
        </div>

        <div class="mt-6 flex gap-2">
            <a href="{{ route('member.pengajuan.pinjaman') }}" class="px-4 py-2 rounded bg-gray-100 border">Kembali</a>
            @if((int)$pengajuan->status === 1)
                <a target="_blank" href="{{ route('member.pengajuan.pinjaman.cetak', $pengajuan->id) }}" class="px-4 py-2 rounded bg-purple-50 text-purple-700 border border-purple-200">Cetak</a>
            @endif
        </div>
    </div>
</div>
@endsection


