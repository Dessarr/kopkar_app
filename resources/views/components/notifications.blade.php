@php
use App\Models\NotificationModel;

// Get notification counts
$pendingLoanCount = NotificationModel::getPendingLoanCount();
$pendingWithdrawalCount = NotificationModel::getPendingWithdrawalCount();
@endphp

<!-- Documents Icon with Badge - Pengajuan Penarikan Simpanan -->
<div class="flex items-center relative">
    <a href="{{ route('admin.pengajuan.penarikan.index') }}" class="block">
        <div class="bg-white/20 p-2 rounded-lg hover:bg-white/30 transition-colors duration-200 cursor-pointer" 
             title="Pengajuan Penarikan Simpanan">
            <i class="fas fa-file-lines text-white text-lg"></i>
        </div>
    </a>
    @if($pendingWithdrawalCount > 0)
        <div class="absolute -top-2 -right-2 bg-green-800 text-white text-xs rounded-full w-6 h-6 flex items-center justify-center font-semibold">
            {{ $pendingWithdrawalCount }}
        </div>
    @else
        <div class="absolute -top-2 -right-2 bg-green-500 text-white text-xs rounded-full w-6 h-6 flex items-center justify-center">
            <i class="fas fa-check text-xs"></i>
        </div>
    @endif
</div>

<!-- Envelope Icon with Badge - Pengajuan Pinjaman -->
<div class="flex items-center relative">
    <a href="{{ route('pinjaman.data_pengajuan') }}" class="block">
        <div class="bg-white/20 p-2 rounded-lg hover:bg-white/30 transition-colors duration-200 cursor-pointer"
             title="Pengajuan Pinjaman">
            <i class="fas fa-envelope text-white text-lg"></i>
        </div>
    </a>
    @if($pendingLoanCount > 0)
        <div class="absolute -top-2 -right-2 bg-green-800 text-white text-xs rounded-full w-6 h-6 flex items-center justify-center font-semibold">
            {{ $pendingLoanCount }}
        </div>
    @else
        <div class="absolute -top-2 -right-2 bg-green-500 text-white text-xs rounded-full w-6 h-6 flex items-center justify-center">
            <i class="fas fa-check text-xs"></i>
        </div>
    @endif
</div>
