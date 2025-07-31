<div x-data="{ openMenu: '' }" class="flex flex-col h-full p-6">
    <!-- Logo & Judul -->
    <div class="flex items-center mb-8 bg-[#14AE5C] p-2 rounded-lg">
        <div class="bg-white p-2 rounded-md mr-4">
            <svg class="w-10 h-10 text-[#14AE5C]" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <circle cx="12" cy="12" r="10" stroke="#14AE5C" stroke-width="2" fill="#fff" />
                <path d="M12 6v6l4 2" stroke="#14AE5C" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" />
            </svg>
        </div>
        <span class="text-lg font-bold text-white">KOPERASI INDONESIA</span>
    </div>
    <!-- Menu -->
    <nav class="flex-1 space-y-2">
        <!-- Home -->
        <a href="/admin/dashboard" class="flex items-center p-3 rounded-lg sidebar-item mb-2">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                </path>
            </svg>
            <span class="">Home</span>
        </a>
        <!-- Transaksi Kas -->
        <div x-data="{ openKas: false }">
            <button @click="openKas = !openKas" class="flex items-center w-full p-3 rounded-lg sidebar-item">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                    </path>
                </svg>
                <span class="flex-1">Transaksi Kas</span>
                <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': openKas }" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="openKas" class="pl-11 mt-2 space-y-2">
                <a href="{{ route('kas.pemasukan') }}"
                    class="block py-2 px-3 rounded-lg sidebar-item hover:bg-[#14AE5C] hover:text-white transition-colors duration-200">
                    Pemasukan
                </a>
                <a href="{{ route('kas.pengeluaran') }}"
                    class="block py-2 px-3 rounded-lg sidebar-item hover:bg-[#14AE5C] hover:text-white transition-colors duration-200">
                    Pengeluaran
                </a>
                <a href="{{ route('kas.transfer') }}"
                    class="block py-2 px-3 rounded-lg sidebar-item hover:bg-[#14AE5C] hover:text-white transition-colors duration-200">
                    Transfer
                </a>
            </div>
        </div>
        <!-- Toserda -->
        <div>
            <button @click="openMenu === 'toserda' ? openMenu = '' : openMenu = 'toserda'"
                class="flex items-center w-full p-3 rounded-lg sidebar-item focus:outline-none">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18" />
                </svg>
                Toserda
                <svg class="w-4 h-4 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path :class="{'rotate-180': openMenu === 'toserda'}" class="transition-transform"
                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div x-show="openMenu === 'toserda'" class="pl-8 space-y-1 mt-1" x-transition>
                <a href="{{ route('toserda.penjualan') }}"
                    class="block py-2 px-3 rounded-lg hover:bg-[#14AE5C] hover:text-white transition-colors duration-200">Penjualan</a>
                <a href="{{ route('toserda.pembelian') }}"
                    class="block py-2 px-3 rounded-lg hover:bg-[#14AE5C] hover:text-white transition-colors duration-200">Pembelian</a>
                <a href="{{ route('toserda.biaya-usaha') }}"
                    class="block py-2 px-3 rounded-lg hover:bg-[#14AE5C] hover:text-white transition-colors duration-200">Biaya
                    Usaha</a>
                <a href="{{ route('toserda.lain-lain') }}"
                    class="block py-2 px-3 rounded-lg hover:bg-[#14AE5C] hover:text-white transition-colors duration-200">Toserda/Lain-lain</a>
            </div>
        </div>
        <!-- Angkutan -->
        <div>
            <button @click="openMenu === 'angkutan' ? openMenu = '' : openMenu = 'angkutan'"
                class="flex items-center w-full p-3 rounded-lg sidebar-item focus:outline-none">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 17v-5a2 2 0 012-2h12a2 2 0 012 2v5M16 21v-2a2 2 0 00-2-2H10a2 2 0 00-2 2v2" />
                </svg>
                Angkutan
                <svg class="w-4 h-4 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path :class="{'rotate-180': openMenu === 'angkutan'}" class="transition-transform"
                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div x-show="openMenu === 'angkutan'" class="pl-8 space-y-1 mt-1" x-transition>
                <a href="{{ route('angkutan.pemasukan') }}"
                    class="block py-2 px-3 rounded-lg hover:bg-[#14AE5C] hover:text-white  transition-colors duration-200">Pemasukan</a>
                <a href="{{ route('angkutan.pengeluaran') }}"
                    class="block py-2 px-3 rounded-lg hover:bg-[#14AE5C] hover:text-white  transition-colors duration-200">Pengeluaran</a>
            </div>
        </div>
        <!-- Anggota -->
        <div>
            <button @click="openMenu === 'anggota' ? openMenu = '' : openMenu = 'anggota'"
                class="flex items-center w-full p-3 rounded-lg sidebar-item focus:outline-none">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Anggota
                <svg class="w-4 h-4 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path :class="{'rotate-180': openMenu === 'anggota'}" class="transition-transform"
                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div x-show="openMenu === 'anggota'" class="pl-8 space-y-1 mt-1" x-transition>
                <a href="#"
                    class="block py-2 px-3 rounded-lg hover:bg-[#14AE5C] hover:text-white  transition-colors duration-200">SHU</a>
                <a href="{{ route('anggota.bayar.toserda') }}"
                    class="block py-2 px-3 rounded-lg hover:bg-[#14AE5C] hover:text-white  transition-colors duration-200">Bayar
                    Toserda/Lain-lain</a>
            </div>
        </div>

        <!--BILLING NEW 1 Section-->
        <div>
            <button @click="openMenu === 'billing' ? openMenu = '' : openMenu = 'billing'"
                class="flex items-center w-full p-3 rounded-lg sidebar-item focus:outline-none">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m0 0l-6-6m6 6H3" />
                </svg>
                Billing
                <svg class="w-4 h-4 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path :class="{'rotate-180': openMenu === 'billing'}" class="transition-transform"
                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div x-show="openMenu === 'billing'" class="pl-8 space-y-1 mt-1" x-transition>
                <a href="{{ route('billing.index') }}"
                    class="block py-2 px-3 rounded-lg hover:bg-[#14AE5C] hover:text-white transition-colors duration-200">Billing Utama</a>
                <a href="{{ route('billing.toserda') }}"
                    class="block py-2 px-3 rounded-lg hover:bg-[#14AE5C] hover:text-white transition-colors duration-200">Billing Toserda</a>
                <a href="{{ route('billing.toserda.processed') }}"
                    class="block py-2 px-3 rounded-lg hover:bg-[#14AE5C] hover:text-white transition-colors duration-200">Billing Toserda Diproses</a>
            </div>
        </div>

        <!-- Billing  OLD-->
        <!-- <div>
            <button @click="openMenu === 'billing' ? openMenu = '' : openMenu = 'billing'"
                class="flex items-center w-full p-3 rounded-lg sidebar-item focus:outline-none">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 14l6-6m0 0l-6-6m6 6H3" />
                </svg>
                Billing
                <svg class="w-4 h-4 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path :class="{'rotate-180': openMenu === 'billing'}" class="transition-transform"
                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div x-show="openMenu === 'billing'" class="pl-8 space-y-1 mt-1" x-transition>
                <a href="#"
                    class="block py-2 px-3 rounded-lg hover:bg-[#14AE5C] hover:text-white  transition-colors duration-200">Sim
                    Pokok</a>
                <a href="#"
                    class="block py-2 px-3 rounded-lg hover:bg-[#14AE5C] hover:text-white  transition-colors duration-200">Sim
                    Wajib</a>
                <a href="#"
                    class="block py-2 px-3 rounded-lg hover:bg-[#14AE5C] hover:text-white  transition-colors duration-200">Sim
                    Sukarela</a>
                <a href="#"
                    class="block py-2 px-3 rounded-lg hover:bg-[#14AE5C] hover:text-white  transition-colors duration-200">Sim
                    Khusus 2</a>
            </div>
        </div> -->
        <!-- Simpanan -->
        <div>
            <button @click="openMenu === 'simpanan' ? openMenu = '' : openMenu = 'simpanan'"
                class="flex items-center w-full p-3 rounded-lg sidebar-item focus:outline-none">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21H5a2 2 0 01-2-2V7a2 2 0 012-2h14a2 2 0 012 2v12a2 2 0 01-2 2z" />
                </svg>
                Simpanan
                <svg class="w-4 h-4 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path :class="{'rotate-180': openMenu === 'simpanan'}" class="transition-transform"
                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div x-show="openMenu === 'simpanan'" class="pl-8 space-y-1 mt-1" x-transition>
                <a href="{{ route('simpanan.setoran') }}"
                    class="block py-2 px-3 rounded-lg hover:bg-[#14AE5C] hover:text-white  transition-colors duration-200">Setoran
                    Tunai</a>
                <a href="{{ route('simpanan.upload') }}"
                    class="block py-2 px-3 rounded-lg hover:bg-[#14AE5C] hover:text-white  transition-colors duration-200">Setoran
                    Upload</a>
                <a href="{{ route('simpanan.tagihan') }}"
                    class="block py-2 px-3 rounded-lg hover:bg-[#14AE5C] hover:text-white  transition-colors duration-200">Tagihan</a>
                <a href="{{ route('simpanan.pengajuan_penarikan') }}"
                    class="block py-2 px-3 rounded-lg hover:bg-[#14AE5C] hover:text-white  transition-colors duration-200">Pengajuan
                    Penarikan</a>
                <a href="{{ route('simpanan.penarikan') }}"
                    class="block py-2 px-3 rounded-lg hover:bg-[#14AE5C] hover:text-white  transition-colors duration-200">Penarikan
                    Tunai</a>
            </div>
        </div>
        <!-- Pinjaman -->
        <div>
            <button @click="openMenu === 'pinjaman' ? openMenu = '' : openMenu = 'pinjaman'"
                class="flex items-center w-full p-3 rounded-lg sidebar-item focus:outline-none">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21H5a2 2 0 01-2-2V7a2 2 0 012-2h14a2 2 0 012 2v12a2 2 0 01-2 2z" />
                </svg>
                Pinjaman
                <svg class="w-4 h-4 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path :class="{'rotate-180': openMenu === 'pinjaman'}" class="transition-transform"
                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div x-show="openMenu === 'pinjaman'" class="pl-8 space-y-1 mt-1" x-transition>
                <a href="{{ route('pinjaman.data_pengajuan') }}"
                    class="block py-2 px-3 rounded-lg hover:bg-[#14AE5C] hover:text-white  transition-colors duration-200">Data
                    Pengajuan</a>
                <a href="#"
                    class="block py-2 px-3 rounded-lg hover:bg-[#14AE5C] hover:text-white  transition-colors duration-200">Data
                    Pinjaman</a>
                <a href="#"
                    class="block py-2 px-3 rounded-lg hover:bg-[#14AE5C] hover:text-white  transition-colors duration-200">Data
                    Angsuran</a>
                <a href="#"
                    class="block py-2 px-3 rounded-lg hover:bg-[#14AE5C] hover:text-white  transition-colors duration-200">Pinjaman
                    Lunas</a>
            </div>
        </div>

        <!-- Master Data -->
        <div>
            <button @click="openMenu === 'master-data' ? openMenu = '' : openMenu = 'master-data'"
                class="flex items-center w-full p-3 rounded-lg sidebar-item focus:outline-none">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                Master Data
                <svg class="w-4 h-4 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path :class="{'rotate-180': openMenu === 'master-data'}" class="transition-transform"
                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div x-show="openMenu === 'master-data'" class="pl-8 space-y-1 mt-1" x-transition>
                <a href="{{ route('master-data.jns_akun') }}"
                    class="block py-2 px-3 rounded-lg hover:bg-[#14AE5C] hover:text-white transition-colors duration-200">
                    Jenis Akun
                </a>
                <a href="{{ route('master-data.jns_simpan') }}"
                    class="block py-2 px-3 rounded-lg hover:bg-[#14AE5C] hover:text-white transition-colors duration-200">
                    Jenis Simpanan
                </a>
                <a href="{{ route('master-data.data_pengguna') }}"
                    class="block py-2 px-3 rounded-lg hover:bg-[#14AE5C] hover:text-white transition-colors duration-200">
                    Data Pengguna
                </a>
                <a href="{{ route('master-data.data_barang') }}"
                    class="block py-2 px-3 rounded-lg hover:bg-[#14AE5C] hover:text-white transition-colors duration-200">
                    Data Barang
                </a>
                <a href="{{ route('master-data.data_mobil') }}"
                    class="block py-2 px-3 rounded-lg hover:bg-[#14AE5C] hover:text-white transition-colors duration-200">
                    Data Mobil
                </a>
                <a href="{{ route('master-data.jenis_angsuran') }}"
                    class="block py-2 px-3 rounded-lg hover:bg-[#14AE5C] hover:text-white transition-colors duration-200">
                    Jenis Angsuran
                </a>
                <a href="{{ route('master-data.data_anggota') }}"
                    class="block py-2 px-3 rounded-lg hover:bg-[#14AE5C] hover:text-white transition-colors duration-200">
                    Data Anggota
                </a>
                <a href="{{ route('master-data.data_kas') }}"
                    class="block py-2 px-3 rounded-lg hover:bg-[#14AE5C] hover:text-white transition-colors duration-200">
                    Data Kas
                </a>
            </div>
        </div>

        <!-- Laporan Section -->
        <div>
            <button @click="openMenu === 'laporan' ? openMenu = '' : openMenu = 'laporan'"
                class="flex items-center w-full p-3 rounded-lg sidebar-item focus:outline-none">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18" />
                </svg>
                Laporan
                <svg class="w-4 h-4 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path :class="{'rotate-180': openMenu === 'laporan'}" class="transition-transform"
                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div x-show="openMenu === 'laporan'" class="pl-8 space-y-1 mt-1" x-transition>
                <a href="{{ route('laporan.buku_besar') }}" class="block py-2 px-3 rounded-lg transition-colors duration-200 {{ request()->routeIs('laporan.buku_besar') ? 'bg-[#14AE5C] text-white' : '' }}">Buku Besar</a>
                <a href="{{ route('laporan.neraca_saldo') }}" class="block py-2 px-3 rounded-lg transition-colors duration-200 {{ request()->routeIs('laporan.neraca_saldo') ? 'bg-[#14AE5C] text-white' : '' }}">Neraca Saldo</a>
                <a href="{{ route('laporan.kas_simpanan') }}" class="block py-2 px-3 rounded-lg transition-colors duration-200 {{ request()->routeIs('laporan.kas_simpanan') ? 'bg-[#14AE5C] text-white' : '' }}">Kas Simpanan</a>
                <a href="{{ route('laporan.kas_pinjaman') }}" class="block py-2 px-3 rounded-lg transition-colors duration-200 {{ request()->routeIs('laporan.kas_pinjaman') ? 'bg-[#14AE5C] text-white' : '' }}">Kas Pinjaman</a>
                <a href="{{ route('laporan.target_realisasi') }}" class="block py-2 px-3 rounded-lg transition-colors duration-200 {{ request()->routeIs('laporan.target_realisasi') ? 'bg-[#14AE5C] text-white' : '' }}">Target & Realisasi</a>
                <a href="{{ route('laporan.pengeluaran_pinjaman') }}" class="block py-2 px-3 rounded-lg transition-colors duration-200 {{ request()->routeIs('laporan.pengeluaran_pinjaman') ? 'bg-[#14AE5C] text-white' : '' }}">Pengeluaran Pinjaman</a>
                <a href="{{ route('laporan.angsuran_pinjaman') }}" class="block py-2 px-3 rounded-lg transition-colors duration-200 {{ request()->routeIs('laporan.angsuran_pinjaman') ? 'bg-[#14AE5C] text-white' : '' }}">Angsuran Pinjaman</a>
                <a href="{{ route('laporan.rekapitulasi') }}" class="block py-2 px-3 rounded-lg transition-colors duration-200 {{ request()->routeIs('laporan.rekapitulasi') ? 'bg-[#14AE5C] text-white' : '' }}">Rekapitulasi</a>
                <a href="{{ route('laporan.saldo_kas') }}" class="block py-2 px-3 rounded-lg transition-colors duration-200 {{ request()->routeIs('laporan.saldo_kas') ? 'bg-[#14AE5C] text-white' : '' }}">Saldo Kas</a>
                <a href="{{ route('laporan.shu') }}" class="block py-2 px-3 rounded-lg transition-colors duration-200 {{ request()->routeIs('laporan.shu') ? 'bg-[#14AE5C] text-white' : '' }}">SHU</a>
                <a href="{{ route('laporan.kas.anggota') }}" class="block py-2 px-3 rounded-lg transition-colors duration-200 {{ request()->routeIs('laporan.kas.anggota') ? 'bg-[#14AE5C] text-white' : '' }}">Kas Anggota</a>
                <a href="{{ route('laporan.data.anggota') }}" class="block py-2 px-3 rounded-lg transition-colors duration-200 {{ request()->routeIs('laporan.data.anggota') ? 'bg-[#14AE5C] text-white' : '' }}">Data Anggota</a>
                <a href="{{ route('laporan.jatuh.tempo') }}" class="block py-2 px-3 rounded-lg transition-colors duration-200 {{ request()->routeIs('laporan.jatuh.tempo') ? 'bg-[#14AE5C] text-white' : '' }}">Jatuh Tempo</a>
                <a href="{{ route('laporan.kredit.macet') }}" class="block py-2 px-3 rounded-lg transition-colors duration-200 {{ request()->routeIs('laporan.kredit.macet') ? 'bg-[#14AE5C] text-white' : '' }}">Kredit Macet</a>
                <a href="{{ route('laporan.transaksi.kas') }}" class="block py-2 px-3 rounded-lg transition-colors duration-200 {{ request()->routeIs('laporan.transaksi.kas') ? 'bg-[#14AE5C] text-white' : '' }}">Transaksi Kas</a>
                <a href="{{ route('laporan.angkutan.karyawan') }}" class="block py-2 px-3 rounded-lg transition-colors duration-200 {{ request()->routeIs('laporan.angkutan.karyawan') ? 'bg-[#14AE5C] text-white' : '' }}">Angkutan Karyawan</a>
                <a href="{{ route('laporan.toserda') }}" class="block py-2 px-3 rounded-lg transition-colors duration-200 {{ request()->routeIs('laporan.toserda') ? 'bg-[#14AE5C] text-white' : '' }}">Toserda</a>
            </div>
        </div>
        <!-- Setting -->
        <div>
            <button @click="openMenu === 'setting' ? openMenu = '' : openMenu = 'setting'"
                class="flex items-center w-full p-3 rounded-lg sidebar-item focus:outline-none">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3" />
                </svg>
                Setting
                <svg class="w-4 h-4 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path :class="{'rotate-180': openMenu === 'setting'}" class="transition-transform"
                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div x-show="openMenu === 'setting'" class="pl-8 space-y-1 mt-1" x-transition>
                <a href="{{ route('settings.identitas_koperasi') }}"
                    class="block py-2 px-3 rounded-lg hover:bg-[#14AE5C] hover:text-white  transition-colors duration-200">Identitas
                    Koperasi</a>
                <a href="{{ route('settings.suku_bunga') }}"
                    class="block py-2 px-3 rounded-lg hover:bg-[#14AE5C] hover:text-white  transition-colors duration-200">Suku
                    Bunga</a>
            </div>
        </div>
    </nav>
    <!-- Logout Button -->
    <div class="mt-auto pt-4">
        <form action="{{ route('admin.logout') }}" method="POST">
            @csrf
            <button type="submit" class="w-full text-left flex items-center p-3 rounded-lg sidebar-item">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                    </path>
                </svg>
                Logout
            </button>
        </form>
    </div>
</div>
<!-- Alpine.js for dropdown -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>