<div class="space-y-6">
    <!-- Header -->
    <div class="border-b border-gray-200 pb-4">
        <h4 class="text-lg font-semibold text-gray-900">Detail Pengajuan Penarikan</h4>
        <p class="text-sm text-gray-600">ID: {{ $pengajuan->ajuan_id }}</p>
    </div>

    <!-- Status Badge -->
    <div class="flex items-center justify-between">
        <div>
            <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full {{ $pengajuan->status_badge }}">
                {{ $pengajuan->status_text }}
            </span>
            @if($pengajuan->status == 3 && $pengajuan->tgl_cair)
                <p class="text-sm text-gray-600 mt-1">Tanggal Cair: {{ $pengajuan->tgl_cair_formatted }}</p>
            @endif
        </div>
        <div class="text-right">
            <p class="text-sm text-gray-600">Tanggal Pengajuan</p>
            <p class="text-sm font-medium text-gray-900">{{ $pengajuan->tgl_input_formatted }}</p>
        </div>
    </div>

    <!-- Informasi Pengajuan -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Jenis Simpanan</label>
                <p class="mt-1 text-sm text-gray-900">{{ $pengajuan->jenisSimpanan->jns_simpan ?? 'N/A' }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Nominal Penarikan</label>
                <p class="mt-1 text-lg font-semibold text-gray-900">Rp {{ $pengajuan->nominal_formatted }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Keterangan</label>
                <p class="mt-1 text-sm text-gray-900">{{ $pengajuan->keterangan ?: '-' }}</p>
            </div>
        </div>
        
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">No. Ajuan</label>
                <p class="mt-1 text-sm text-gray-900">{{ $pengajuan->no_ajuan }}</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Tanggal Update</label>
                <p class="mt-1 text-sm text-gray-900">{{ $pengajuan->tgl_update_formatted }}</p>
            </div>
            
            @if($pengajuan->alasan)
            <div>
                <label class="block text-sm font-medium text-gray-700">Alasan</label>
                <p class="mt-1 text-sm text-gray-900">{{ $pengajuan->alasan }}</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Informasi Member -->
    <div class="bg-gray-50 rounded-lg p-4">
        <h5 class="text-sm font-medium text-gray-700 mb-3">Informasi Member</h5>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <span class="font-medium text-gray-700">Nama:</span>
                <span class="text-gray-900">{{ $member->nama }}</span>
            </div>
            <div>
                <span class="font-medium text-gray-700">No. KTP:</span>
                <span class="text-gray-900">{{ $member->no_ktp }}</span>
            </div>
            <div>
                <span class="font-medium text-gray-700">Alamat:</span>
                <span class="text-gray-900">{{ $member->alamat ?: '-' }}</span>
            </div>
            <div>
                <span class="font-medium text-gray-700">No. Telepon:</span>
                <span class="text-gray-900">{{ $member->notelp ?: '-' }}</span>
            </div>
        </div>
    </div>

    <!-- Timeline Status -->
    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <h5 class="text-sm font-medium text-gray-700 mb-3">Timeline Status</h5>
        <div class="space-y-3">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-3 h-3 bg-green-400 rounded-full"></div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-900">Pengajuan Dibuat</p>
                    <p class="text-sm text-gray-600">{{ $pengajuan->tgl_input_formatted }}</p>
                </div>
            </div>
            
            @if($pengajuan->status >= 1)
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-3 h-3 bg-blue-400 rounded-full"></div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-900">Disetujui</p>
                    <p class="text-sm text-gray-600">{{ $pengajuan->tgl_update_formatted }}</p>
                    @if($pengajuan->tgl_cair)
                        <p class="text-sm text-gray-600">Tanggal Cair: {{ $pengajuan->tgl_cair_formatted }}</p>
                    @endif
                </div>
            </div>
            @endif
            
            @if($pengajuan->status == 2)
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-3 h-3 bg-red-400 rounded-full"></div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-900">Ditolak</p>
                    <p class="text-sm text-gray-600">{{ $pengajuan->tgl_update_formatted }}</p>
                    @if($pengajuan->alasan)
                        <p class="text-sm text-gray-600">Alasan: {{ $pengajuan->alasan }}</p>
                    @endif
                </div>
            </div>
            @endif
            
            @if($pengajuan->status == 3)
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-3 h-3 bg-purple-400 rounded-full"></div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-900">Terlaksana</p>
                    <p class="text-sm text-gray-600">{{ $pengajuan->tgl_update_formatted }}</p>
                </div>
            </div>
            @endif
            
            @if($pengajuan->status == 4)
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-3 h-3 bg-yellow-400 rounded-full"></div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-900">Dibatalkan</p>
                    <p class="text-sm text-gray-600">{{ $pengajuan->tgl_update_formatted }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
        <button onclick="closeDetail()" 
            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm">
            Tutup
        </button>
        
        @if($pengajuan->status == 0)
        <form action="{{ route('member.pengajuan.penarikan.cancel', $pengajuan->id) }}" 
            method="POST" class="inline"
            onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pengajuan ini?')">
            @csrf
            <button type="submit" 
                class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md text-sm">
                Batalkan Pengajuan
            </button>
        </form>
        @endif
    </div>
</div>
