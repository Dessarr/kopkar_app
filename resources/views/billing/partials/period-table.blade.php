{{-- Period Summary Table Partial --}}
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="px-6 py-4 border-b border-gray-200">
        <h6 class="text-sm font-semibold text-gray-700">Ringkasan Periode</h6>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <!-- Periode -->
            <div class="text-center">
                <div class="text-xs font-medium text-gray-500 mb-1">Periode</div>
                <div id="period-value" class="text-sm font-semibold text-gray-900">{{ $periodData['periode'] ?? 'N/A' }}
                </div>
            </div>
            <!-- Total Anggota -->
            <div class="text-center">
                <div class="text-xs font-medium text-gray-500 mb-1">Total Anggota</div>
                <div id="total-anggota-value" class="text-sm font-semibold text-blue-600">
                    {{ number_format($periodData['total_anggota'] ?? 0, 0, ',', '.') }}</div>
            </div>
            <!-- Simpanan Sukarela -->
            <div class="text-center">
                <div class="text-xs font-medium text-gray-500 mb-1">Simpanan Sukarela</div>
                <div id="simpanan-sukarela-value" class="text-sm font-semibold text-green-600">
                    {{ number_format($periodData['simpanan_sukarela'] ?? 0, 0, ',', '.') }}</div>
            </div>
            <!-- Simpanan Pokok (dari Wajib) -->
            <div class="text-center">
                <div class="text-xs font-medium text-gray-500 mb-1">Simpanan Pokok*</div>
                <div id="simpanan-pokok-value" class="text-sm font-semibold text-purple-600">
                    {{ number_format($periodData['simpanan_pokok'] ?? 0, 0, ',', '.') }}</div>
            </div>
            <!-- Simpanan Wajib -->
            <div class="text-center">
                <div class="text-xs font-medium text-gray-500 mb-1">Simpanan Wajib</div>
                <div id="simpanan-wajib-value" class="text-sm font-semibold text-orange-600">
                    {{ number_format($periodData['simpanan_wajib'] ?? 0, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
</div>