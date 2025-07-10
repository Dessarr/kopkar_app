@extends('layouts.app')

@section('title', 'Billing')
@section('sub-title', 'Tagihan Bulanan')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Billing</h1>
        <form method="GET" action="{{ route('billing.index') }}">
            <input type="hidden" name="periode" id="selectedPeriode">

            <div class="flex place-content-around items-center w-full gap-4 ">
                <!-- Tombol Pilih Bulan & Tahun -->
                <button type="button" onclick="toggleMonthPopup()"
                    class="cursor-pointer bg-gray-100 p-3 flex items-center rounded-lg border-2 border-gray-300 hover:bg-gray-200">
                    <img src="{{ asset('img/icons-bootstrap/calendar/calendar4.svg') }}" class="w-5 h-5"
                        alt="Calendar Icon">
                    <span id="selectedLabel" class="ml-2 text-sm text-gray-600 whitespace-nowrap">
                        {{ request('periode') ? \Carbon\Carbon::parse(request('periode') . '-01')->translatedFormat('F Y') : 'Pilih Bulan' }}
                    </span>
                </button>

                <!-- Tombol Load -->
                <button type="submit"
                    class="px-4 py-3 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 border-2 border-blue-700">
                    Filter
                </button>
            </div>

            <!-- Popup -->
            <div id="monthPopup"
                class="fixed inset-0 bg-black bg-opacity-50 z-50 items-center justify-center transition-opacity duration-200 hidden flex">
                <div class="bg-white p-6 rounded-lg w-[400px] shadow-lg space-y-4">
                    <h2 class="text-lg font-semibold text-gray-700 text-center">Pilih Bulan & Tahun</h2>

                    <!-- Grid Bulan -->
                    <div class="grid grid-cols-3 gap-2 text-sm" id="bulanGrid">
                        @php
                        $bulanList = [
                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                        ];
                        @endphp

                        @foreach($bulanList as $num => $bulan)
                        <button type="button" class="p-3 rounded bg-gray-100 hover:bg-gray-200 text-center w-full"
                            onclick="selectMonth(this, '{{ $num }}', '{{ $bulan }}')">
                            {{ $bulan }}
                        </button>
                        @endforeach
                    </div>

                    <!-- Tahun -->
                    <div class="flex justify-center">
                        <select id="yearSelect" class="border border-gray-300 rounded px-2 py-1 text-sm">
                            @for ($year = 2024; $year <= 2035; $year++) <option value="{{ $year }}">{{ $year }}</option>
                                @endfor
                        </select>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="closeMonthPopup()"
                            class="px-3 py-1 rounded text-sm text-gray-600 hover:bg-gray-100">Batal</button>
                        <button type="button" onclick="submitSelectedMonth()"
                            class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">Pilih</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
    let selectedMonth = null;
    let selectedMonthName = '';
    let selectedBtn = null;

    function toggleMonthPopup() {
        document.getElementById('monthPopup').classList.remove('hidden');
    }

    function closeMonthPopup() {
        document.getElementById('monthPopup').classList.add('hidden');
        selectedMonth = null;
        selectedMonthName = '';
        if (selectedBtn) {
            selectedBtn.classList.remove('bg-blue-200');
            selectedBtn = null;
        }
    }

    function selectMonth(btn, month, name) {
        selectedMonth = month.toString().padStart(2, '0');
        selectedMonthName = name;

        if (selectedBtn) {
            selectedBtn.classList.remove('bg-blue-200');
        }

        btn.classList.add('bg-blue-200');
        selectedBtn = btn;
    }

    function submitSelectedMonth() {
        const year = document.getElementById('yearSelect').value;

        if (!selectedMonth || !year) {
            alert('Silakan pilih bulan dan tahun.');
            return;
        }

        const periode = `${year}-${selectedMonth}`;
        const label = `${selectedMonthName} ${year}`;

        document.getElementById('selectedPeriode').value = periode;
        document.getElementById('selectedLabel').innerText = label;

        closeMonthPopup();
    }
    </script>


    <!-- Tabel Transaksi -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b">
            <h2 class="text-lg font-semibold">Riwayat Tagihan</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full border border-gray-300 text-center">
                <thead class="bg-gray-50">
                    <tr class="text-sm align-middle w-full">
                        <th class="py-2 px-5 border">No</th>
                        <th class="p-5 border whitespace-nowrap">Nama Anggota</th>
                        <th class="p-5 border whitespace-nowrap">KTP</th>
                        <th class="p-5 border whitespace-nowrap">ID Tagihan</th>
                        <th class="p-5 border whitespace-nowrap">Simpanan Wajib</th>
                        <th class="p-5 border whitespace-nowrap">Simpanan Sukarela</th>
                        <th class="p-5 border whitespace-nowrap">Simpanan Khusus 2</th>
                        <th class="p-5 border whitespace-nowrap">Billing Total</th>

                    </tr>
                </thead>
                <tbody>
                    @foreach($dataBilling as $billing)
                    <tr class="text-sm align-middle">
                        <td class="py-2 border">
                            {{ ($dataBilling->currentPage() - 1) * $dataBilling->perPage() + $loop->iteration }}
                        </td>
                        <td class="py-2 border">{{ $billing->nama }}</td>
                        <td class="py-2 border">{{ $billing->no_ktp }}</td>
                        <td class="py-2 border">{{ $billing->id_tagihan }}</td>
                        <td class="py-2 border">Rp {{ number_format($billing->simpanan_wajib, 0, ',', '.') }}</td>
                        <td class="py-2 border">Rp {{ number_format($billing->simpanan_sukarela, 0, ',', '.') }}</td>
                        <td class="py-2 border">Rp {{ number_format($billing->simpanan_khusus_2, 0, ',', '.') }}</td>
                        <td class="py-2 border">Rp {{ number_format($billing->total_billing, 0, ',', '.') }}</td>


                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-5 w-full relative px-2 py-2">
        <div class="mx-auto w-fit">
            <div
                class="bg-white px-4 py-1 flex flex-row rounded-full justify-center items-center space-x-2 border border-gray-300 shadow-sm">
                @for ($i = 1; $i <= $dataBilling->lastPage(); $i++)
                    @if ($i == 1 || $i == $dataBilling->lastPage() || ($i >= $dataBilling->currentPage() - 1 && $i <=
                        $dataBilling->
                        currentPage() + 1))
                        <a href="{{ $dataBilling->url($i) }}">
                            <div
                                class="rounded-md px-2 py-0.5 text-sm border border-gray-300 {{ $dataBilling->currentPage() == $i ? 'bg-gray-100 font-bold' : '' }}">
                                {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                            </div>
                        </a>
                        @elseif ($i == 2 || $i == $dataBilling->lastPage() - 1)
                        <div class="rounded-md px-2 py-0.5 text-sm">...</div>
                        @endif
                        @endfor
            </div>
        </div>


        <div class="absolute right-4 top-1/2 -translate-y-1/2 whitespace-nowrap text-sm text-gray-400">
            Displaying {{ $dataBilling->firstItem() }} to {{ $dataBilling->lastItem() }} of {{ $dataBilling->total() }}
            items
        </div>

    </div>
</div>

<div class="popup">

</div>

<style>
.active-month {
    background-color: #dbeafe;
    /* Tailwind bg-blue-100 */
    border: 2px solid #2563eb;
    /* Tailwind blue-600 */
    font-weight: bold;
}
</style>
@endsection