<!-- Include required libraries -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script>
// Simple Filter System
$(document).ready(function() {
    // Initialize Date Range Picker
    initializeDateRangePicker();

    // Add click event listener for row selection
    $(document).on('click', '.row-selectable', function() {
        selectRow(this, $(this).data('id'));
    });
});

// Initialize Date Range Picker with Preset Ranges
function initializeDateRangePicker() {
    $('#daterange-btn').daterangepicker({
        ranges: {
            'Hari ini': [moment(), moment()],
            'Kemarin': [moment().subtract('days', 1), moment().subtract('days', 1)],
            '7 Hari yang lalu': [moment().subtract('days', 6), moment()],
            '30 Hari yang lalu': [moment().subtract('days', 29), moment()],
            'Bulan ini': [moment().startOf('month'), moment().endOf('month')],
            'Bulan kemarin': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1)
                .endOf('month')
            ],
            'Tahun ini': [moment().startOf('year'), moment().endOf('year')],
            'Tahun kemarin': [moment().subtract('year', 1).startOf('year'), moment().subtract('year', 1).endOf(
                'year')]
        },
        showDropdowns: true,
        format: 'YYYY-MM-DD',
        startDate: moment().startOf('year'),
        endDate: moment().endOf('year'),
        autoApply: true,
        locale: {
            format: 'DD/MM/YYYY',
            separator: ' - ',
            applyLabel: 'Terapkan',
            cancelLabel: 'Batal',
            fromLabel: 'Dari',
            toLabel: 'Sampai',
            customRangeLabel: 'Pilih Manual',
            weekLabel: 'W',
            daysOfWeek: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
            monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ],
            firstDay: 1
        }
    }, function(start, end, label) {
        // Update hidden inputs
        $('#tgl_dari').val(start.format('YYYY-MM-DD'));
        $('#tgl_sampai').val(end.format('YYYY-MM-DD'));

        // Update display text
        $('#daterange-text').text(start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY'));
    });

    // Set initial values if they exist in URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('tgl_dari') && urlParams.has('tgl_sampai')) {
        const tglDari = urlParams.get('tgl_dari');
        const tglSampai = urlParams.get('tgl_sampai');
        $('#tgl_dari').val(tglDari);
        $('#tgl_sampai').val(tglSampai);
        $('#daterange-text').text(moment(tglDari).format('DD/MM/YYYY') + ' - ' + moment(tglSampai).format(
            'DD/MM/YYYY'));
    }
}

// Main Search Function
function doSearch() {
    const form = document.getElementById("filterForm");
    const formData = new FormData(form);

    // Build query parameters
    const params = new URLSearchParams();

    // Kode Transaksi
    const kodeTransaksi = formData.get('kode_transaksi');
    if (kodeTransaksi && kodeTransaksi.trim() !== '') {
        let cleanCode = kodeTransaksi.replace(/PK/gi, '').replace(/^0+/, '');
        params.append('kode_transaksi', cleanCode);
    }

    // Date Range
    const tglDari = formData.get('tgl_dari');
    const tglSampai = formData.get('tgl_sampai');
    if (tglDari && tglSampai) {
        params.append('tgl_dari', tglDari);
        params.append('tgl_sampai', tglSampai);
    }

    // Kas Filter
    const kasFilter = formData.get('kas_filter');
    if (kasFilter && kasFilter !== '') {
        params.append('kas_filter', kasFilter);
    }

    // Redirect with parameters
    window.location.href = "{{ route('angkutan.pengeluaran') }}?" + params.toString();
}

// Clear all filters
function clearFilters() {
    // Reset date range picker
    $('#daterange-text').text('Pilih Tanggal');
    $('#tgl_dari').val('');
    $('#tgl_sampai').val('');

    // Reset kode transaksi
    $('#kode_transaksi').val('');

    // Reset kas filter
    $('#kas_filter').val('');

    // Reload page
    window.location.href = "{{ route('angkutan.pengeluaran') }}";
}

// Cetak Laporan PDF
function cetakLaporan() {
    const form = document.getElementById("filterForm");
    const formData = new FormData(form);

    const params = new URLSearchParams();

    const kodeTransaksi = formData.get('kode_transaksi');
    if (kodeTransaksi && kodeTransaksi.trim() !== '') {
        let cleanCode = kodeTransaksi.replace(/PK/gi, '').replace(/^0+/, '');
        params.append('kode_transaksi', cleanCode);
    }

    const tglDari = formData.get('tgl_dari');
    const tglSampai = formData.get('tgl_sampai');
    if (tglDari && tglSampai) {
        params.append('tgl_dari', tglDari);
        params.append('tgl_sampai', tglSampai);
    }

    const kasFilter = formData.get('kas_filter');
    if (kasFilter && kasFilter !== '') {
        params.append('kas_filter', kasFilter);
    }

    // Redirect ke route export PDF
    window.open("{{ route('angkutan.export.pdf.pengeluaran') }}?" + params.toString(), '_blank');
}

// Modal functions
function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');

    // Set default values untuk form add
    if (modalId === 'addModal') {
        // Set tanggal sekarang sebagai default
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');

        const datetimeString = `${year}-${month}-${day}T${hours}:${minutes}`;
        document.querySelector('#addModal input[name="tgl_catat"]').value = datetimeString;

        // Reset form
        document.getElementById('addForm').reset();
        document.querySelector('#addModal input[name="tgl_catat"]').value = datetimeString;
    }
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

// Global variable untuk menyimpan data row yang dipilih
let selectedRowData = null;

// Function untuk select row (click to edit)
function selectRow(row, id) {
    // Remove highlight dari semua row
    document.querySelectorAll('tbody tr').forEach(r => {
        r.classList.remove('bg-yellow-100', 'border-yellow-300');
        r.classList.add('hover:bg-gray-50');
    });

    // Add highlight ke row yang dipilih
    row.classList.remove('hover:bg-gray-50');
    row.classList.add('bg-yellow-100', 'border-yellow-300');

    // Simpan data row yang dipilih
    selectedRowData = {
        id: row.dataset.id,
        kode: row.dataset.kode,
        tanggal: row.dataset.tanggal,
        keterangan: row.dataset.keterangan,
        dari_kas_id: row.dataset.dariKasId,
        dari_kas_nama: row.dataset.dariKasNama,
        untuk_akun_id: row.dataset.untukAkunId,
        jumlah: row.dataset.jumlah,
        user: row.dataset.user
    };
}

// CRUD functions
function editData() {
    if (!selectedRowData) {
        alert('Pilih data yang akan diedit terlebih dahulu!');
        return;
    }

    // Buka modal edit dengan data terisi
    openModal('editModal');

    // Populate form dengan data yang dipilih
    // Format tanggal untuk input datetime-local
    const tanggal = new Date(selectedRowData.tanggal);
    const formattedDate = tanggal.toISOString().slice(0, 16);

    document.getElementById('edit_tgl_catat').value = formattedDate;
    document.getElementById('edit_jumlah').value = parseInt(selectedRowData.jumlah).toLocaleString('id-ID');
    document.getElementById('edit_keterangan').value = selectedRowData.keterangan;
    document.getElementById('edit_dari_kas_id').value = selectedRowData.dari_kas_id;
    document.getElementById('edit_untuk_akun_id').value = selectedRowData.untuk_akun_id;
}

function deleteData() {
    if (!selectedRowData) {
        alert('Pilih data yang akan dihapus terlebih dahulu!');
        return;
    }

    if (confirm(`Apakah Anda yakin ingin menghapus data kode transaksi: ${selectedRowData.kode}?`)) {
        // Kirim request delete
        const deleteUrl = `{{ url('toserda/angkutan/pengeluaran') }}/${selectedRowData.id}`;
        fetch(deleteUrl, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('Data berhasil dihapus');
                    location.reload();
                } else {
                    alert('Gagal menghapus data: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menghapus data: ' + error.message);
            });
    }
}

// Enhanced form validation function
function validateForm(formData) {
    const errors = [];
    
    // Validate tanggal
    if (!formData.tgl_catat) {
        errors.push('Tanggal Transaksi harus diisi');
    }
    
    // Validate jumlah
    const jumlah = parseInt(formData.jumlah);
    if (!formData.jumlah || isNaN(jumlah) || jumlah <= 0) {
        errors.push('Jumlah harus berupa angka yang valid dan lebih dari 0');
    }
    
    // Validate keterangan
    if (!formData.keterangan || formData.keterangan.trim() === '') {
        errors.push('Keterangan harus diisi');
    }
    
    // Validate dari kas
    if (!formData.dari_kas_id || formData.dari_kas_id === '') {
        errors.push('Dari Kas harus dipilih');
    }
    
    // Validate untuk akun
    if (!formData.untuk_akun_id || formData.untuk_akun_id === '') {
        errors.push('Untuk Akun harus dipilih');
    }
    
    return errors;
}

// Enhanced form submission dengan validasi lengkap dan async/await
document.getElementById('addForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    // Validasi form
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);

    // Convert formatted number to raw number
    const jumlahInput = document.getElementById('jumlah');
    data.jumlah = getRawNumber(jumlahInput);

    // Debug: Log data yang akan dikirim
    console.log('Data yang akan dikirim:', data);

    // Enhanced validation
    const validationErrors = validateForm(data);
    if (validationErrors.length > 0) {
        alert('Error:\n' + validationErrors.join('\n'));
        return;
    }

    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan...';

    try {
        const response = await fetch("{{ route('angkutan.store.pengeluaran') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(data)
        });

        // Enhanced response handling
        if (!response.ok) {
            let errorMessage = 'Network response was not ok';
            try {
                const errorData = await response.json();
                errorMessage = errorData.message || errorData.error || errorMessage;
            } catch (parseError) {
                console.error('Error parsing response:', parseError);
                errorMessage = `HTTP ${response.status}: ${response.statusText}`;
            }
            throw new Error(errorMessage);
        }

        const result = await response.json();

        if (result.success) {
            alert('Data berhasil disimpan!');
            closeModal('addModal');
            location.reload();
        } else {
            alert('Gagal menyimpan data: ' + (result.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menyimpan data: ' + error.message);
    } finally {
        // Reset button state
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

// Edit form submission
document.getElementById('editForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    if (!selectedRowData) {
        alert('Tidak ada data yang dipilih untuk diedit');
        return;
    }

    const formData = new FormData(this);

    // Convert formatted number to raw number
    const jumlahInput = document.getElementById('edit_jumlah');
    const rawJumlah = getRawNumber(jumlahInput);
    formData.set('jumlah', rawJumlah);

    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Mengupdate...';

    try {
        // Convert formatted number to raw number
        const jumlahInput = document.getElementById('edit_jumlah');
        const data = Object.fromEntries(formData);
        data.jumlah = getRawNumber(jumlahInput);

        // Debug: Log data yang akan dikirim
        console.log('Data yang akan diupdate:', data);

        const updateUrl = `{{ url('toserda/angkutan/pengeluaran') }}/${selectedRowData.id}`;
        const response = await fetch(updateUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Network response was not ok');
        }

        const result = await response.json();

        if (result.success) {
            alert('Data berhasil diupdate!');
            closeModal('editModal');
            location.reload();
        } else {
            alert('Gagal mengupdate data: ' + (result.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat mengupdate data: ' + error.message);
    } finally {
        // Reset button state
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl+Enter atau Cmd+Enter: Trigger search
    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
        e.preventDefault();
        doSearch();
    }

    // Escape: Clear filters
    if (e.key === 'Escape') {
        e.preventDefault();
        clearFilters();
    }
});

// Enhanced number formatting functions
function formatNumber(input) {
    // Remove non-numeric characters except decimal point
    let value = input.value.replace(/[^0-9]/g, '');
    
    // Don't format if empty
    if (value === '') {
        input.value = '';
        return;
    }
    
    // Convert to number and format with thousand separators
    const number = parseInt(value);
    if (!isNaN(number) && number > 0) {
        // Format with thousand separators using Indonesian format
        input.value = number.toLocaleString('id-ID');
    } else {
        input.value = '';
    }
}

function validateNumber(input) {
    const rawValue = input.value.replace(/[^0-9]/g, '');
    const number = parseInt(rawValue);
    
    if (rawValue && (isNaN(number) || number <= 0)) {
        alert('Jumlah harus berupa angka yang valid dan lebih dari 0');
        input.focus();
        input.value = '';
        return false;
    }
    return true;
}

function getRawNumber(input) {
    // Return clean number without formatting
    const rawValue = input.value.replace(/[^0-9]/g, '');
    return rawValue || '0';
}

// Auto-focus pada kode transaksi jika URL parameter ada
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('kode_transaksi')) {
        document.getElementById('kode_transaksi').focus();
    }
});
</script>
