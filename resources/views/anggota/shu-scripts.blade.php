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

    // Handle anggota dropdown
    handleAnggotaDropdown();
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
            'Bulan kemarin': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')],
            'Tahun ini': [moment().startOf('year'), moment().endOf('year')],
            'Tahun kemarin': [moment().subtract('year', 1).startOf('year'), moment().subtract('year', 1).endOf('year')]
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
    if (urlParams.has('start_date') && urlParams.has('end_date')) {
        const tglDari = urlParams.get('start_date');
        const tglSampai = urlParams.get('end_date');
        $('#tgl_dari').val(tglDari);
        $('#tgl_sampai').val(tglSampai);
        $('#daterange-text').text(moment(tglDari).format('DD/MM/YYYY') + ' - ' + moment(tglSampai).format('DD/MM/YYYY'));
    }
}

// Handle Anggota Dropdown
function handleAnggotaDropdown() {
    // Show dropdown when clicking nama anggota input
    $('#nama_anggota').on('click', function() {
        $('#anggotaDropdown').toggleClass('hidden');
    });

    // Hide dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#nama_anggota, #anggotaDropdown').length) {
            $('#anggotaDropdown').addClass('hidden');
        }
    });

    // Handle anggota selection seperti project CodeIgniter lama
    $('.anggota-option').on('click', function() {
        const id = $(this).data('id');
        const noKtp = $(this).data('no-ktp');
        const nama = $(this).data('nama');
        const foto = $(this).data('foto');

        // Set values
        $('#nama_anggota').val(nama);
        $('#no_ktp').val(noKtp);

        // Photo preview removed as requested

        // Hide dropdown
        $('#anggotaDropdown').addClass('hidden');
    });
}

// Update Photo Preview seperti project CodeIgniter lama
function updatePhotoPreviewLaravel(foto, containerId) {
    const container = document.getElementById(containerId);
    
    if (foto && foto !== '' && foto !== null) {
        // Handle foto - path yang benar: /storage/anggota/
        let photoPath = '';
        if (foto.startsWith('http') || foto.startsWith('/')) {
            photoPath = foto;
        } else {
            // Path yang benar: /storage/anggota/filename
            photoPath = '/storage/anggota/' + foto;
        }
        
        container.innerHTML = `
            <img src="${photoPath}" 
                 alt="Foto Anggota" 
                 class="w-full h-full object-cover rounded-lg"
                 style="width: 90px; height: 120px; object-fit: cover; border: 1px solid #ccc;"
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
            <div class="w-full h-full flex items-center justify-center bg-gray-100 rounded-lg" style="display: none; width: 90px; height: 120px;">
                <i class="fas fa-user text-4xl text-gray-400"></i>
            </div>
        `;
    } else {
        // Default photo - gunakan placeholder
        container.innerHTML = `
            <div class="w-full h-full flex items-center justify-center bg-gray-100 rounded-lg" style="width: 90px; height: 120px;">
                <i class="fas fa-user text-4xl text-gray-400"></i>
            </div>
        `;
    }
}

// Update Photo Preview (legacy function)
function updatePhotoPreview(foto, containerId) {
    updatePhotoPreviewLaravel(foto, containerId);
}

// Main Search Function
function doSearch() {
    const form = document.getElementById("filterForm");
    const formData = new FormData(form);

    // Build query parameters
    const params = new URLSearchParams();

    // Search
    const search = formData.get('search');
    if (search && search.trim() !== '') {
        let cleanSearch = search.replace(/TRD/gi, '').replace(/^0+/, '');
        params.append('search', cleanSearch);
    }

    // Date Range
    const tglDari = formData.get('start_date');
    const tglSampai = formData.get('end_date');
    if (tglDari && tglSampai) {
        params.append('start_date', tglDari);
        params.append('end_date', tglSampai);
    }

    // Redirect with parameters
    window.location.href = "{{ route('anggota.shu') }}?" + params.toString();
}

// Clear all filters
function clearFilters() {
    // Reset date range picker
    $('#daterange-text').text('Pilih Tanggal');
    $('#tgl_dari').val('');
    $('#tgl_sampai').val('');

    // Reset search
    $('#search').val('');

    // Reload page
    window.location.href = "{{ route('anggota.shu') }}";
}

// Cetak Laporan PDF
function cetakLaporan() {
    const form = document.getElementById("filterForm");
    const formData = new FormData(form);

    const params = new URLSearchParams();

    const search = formData.get('search');
    if (search && search.trim() !== '') {
        let cleanSearch = search.replace(/TRD/gi, '').replace(/^0+/, '');
        params.append('search', cleanSearch);
    }

    const tglDari = formData.get('start_date');
    const tglSampai = formData.get('end_date');
    if (tglDari && tglSampai) {
        params.append('start_date', tglDari);
        params.append('end_date', tglSampai);
    }

    // Redirect ke route export PDF
    window.open("{{ route('anggota.shu.export.pdf') }}?" + params.toString(), '_blank');
}

// Modal functions
function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');

    // Set default values untuk form add only
    if (modalId === 'addModal') {
        // Reset form first
        document.getElementById('addForm').reset();
        
        // Set tanggal sekarang sebagai default
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');

        const datetimeString = `${year}-${month}-${day}T${hours}:${minutes}`;
        document.querySelector('#addModal input[name="tgl_transaksi"]').value = datetimeString;
    }
    // Don't reset edit form - let editData() handle population
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
        no_ktp: row.dataset.noKtp,
        nama_anggota: row.dataset.namaAnggota,
        id_anggota: row.dataset.idAnggota,
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

    document.getElementById('edit_tgl_transaksi').value = formattedDate;
    document.getElementById('edit_nama_anggota').value = selectedRowData.nama_anggota;
    document.getElementById('edit_no_ktp').value = selectedRowData.no_ktp;
    document.getElementById('edit_shu_id').value = selectedRowData.id;
    
    // Clean the amount value for display - remove any formatting
    const cleanAmount = selectedRowData.jumlah.toString().replace(/[^0-9.]/g, '');
    document.getElementById('edit_jumlah_bayar').value = cleanAmount;
}

function deleteData() {
    if (!selectedRowData) {
        alert('Pilih data yang akan dihapus terlebih dahulu!');
        return;
    }

    if (confirm(`Apakah Anda yakin ingin menghapus data kode transaksi: ${selectedRowData.kode}?`)) {
        // Kirim request delete
        const deleteUrl = `{{ url('anggota/shu/delete') }}/${selectedRowData.id}`;
        
        // Debug: Log the URL being called
        console.log('Delete URL:', deleteUrl);
        console.log('Selected Row Data:', selectedRowData);
        
        fetch(deleteUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-HTTP-Method-Override': 'DELETE',
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
    if (!formData.tgl_transaksi) {
        errors.push('Tanggal Transaksi harus diisi');
    }

    // Validate jumlah - improved to handle decimal numbers
    const jumlah = parseFloat(formData.jumlah_bayar);
    if (!formData.jumlah_bayar || isNaN(jumlah) || jumlah <= 0) {
        errors.push('Jumlah SHU harus berupa angka yang valid dan lebih dari 0');
    }

    // Validate no ktp
    if (!formData.no_ktp || formData.no_ktp.trim() === '') {
        errors.push('Anggota harus dipilih');
    }

    return errors;
}

// Enhanced form submission dengan format response seperti project CodeIgniter lama
$(document).ready(function() {
    // Wait a bit to ensure DOM is fully loaded
    setTimeout(function() {
        const addForm = document.getElementById('addForm');
        const editForm = document.getElementById('editForm');
        
        console.log('Forms found:', { addForm: !!addForm, editForm: !!editForm });
        
        if (addForm) {
            addForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const data = Object.fromEntries(formData);

            // Simple validation - check required fields
            if (!data.tgl_transaksi || !data.no_ktp || !data.jumlah_bayar) {
                alert('Lengkapi seluruh pengisian data.');
                return;
            }
    
            // Clean the amount value
            const jumlahInput = document.getElementById('jumlah_bayar');
            data.jumlah_bayar = jumlahInput.value.replace(/[^0-9.]/g, '');

            // Only send fields that exist in the database table
            const cleanData = {
                tgl_transaksi: data.tgl_transaksi,
                no_ktp: data.no_ktp,
                jumlah_bayar: data.jumlah_bayar
            };

            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan...';

            try {
                const response = await fetch("{{ route('anggota.shu.store') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(cleanData)
                });
        
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const result = await response.json();
        console.log('Response data:', result);

        // Handle response seperti project CodeIgniter lama
        showMessage(result.ok ? 'success' : 'error', result.msg);
        
        if (result.ok) {
            // Close modal dan reload data seperti project lama
            closeModal('addModal');
            setTimeout(() => {
                location.reload();
            }, 1000);
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('error', 'Terjadi kesalahan koneksi, silahkan ulangi.');
    } finally {
        // Reset button state
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
        });
    }
    
    // Edit form submission - using simple approach like other CRUD files
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();

            if (!selectedRowData) {
                alert('Tidak ada data yang dipilih untuk diedit');
                return;
            }

            const formData = new FormData(this);
            const data = Object.fromEntries(formData);

            // Clean the amount value
            const jumlahInput = document.getElementById('edit_jumlah_bayar');
            data.jumlah_bayar = jumlahInput.value.replace(/[^0-9.]/g, '');

            // Only send fields that exist in the database table
            const cleanData = {
                tgl_transaksi: data.tgl_transaksi,
                no_ktp: data.no_ktp,
                jumlah_bayar: data.jumlah_bayar
            };

            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

            const updateUrl = `{{ url('anggota/shu/update') }}/${selectedRowData.id}`;
            fetch(updateUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-HTTP-Method-Override': 'PUT'
                },
                body: JSON.stringify(cleanData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Data berhasil diupdate');
                    closeModal('editModal');
                    location.reload();
                } else {
                    alert('Gagal mengupdate data: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat mengupdate data');
            })
            .finally(() => {
                // Reset button state
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }
    }, 100); // Wait 100ms for DOM to be fully ready
});

// Simple validation function - removed complex logic
function validateShuForm() {
    // This function is no longer used in the simplified approach
    // Validation is now handled directly in the form submission
    return true;
}

// Show message seperti project CodeIgniter lama
function showMessage(type, message) {
    // Remove existing alerts
    const existingAlert = document.querySelector('.alert');
    if (existingAlert) {
        existingAlert.remove();
    }
    
    // Create alert container if not exists
    let alertContainer = document.getElementById('alertContainer');
    if (!alertContainer) {
        alertContainer = document.createElement('div');
        alertContainer.id = 'alertContainer';
        alertContainer.className = 'fixed top-4 right-4 z-50';
        document.body.appendChild(alertContainer);
    }
    
    // Create alert
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const icon = type === 'success' ? 'fa-check' : 'fa-ban';
    
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show shadow-lg" role="alert" style="min-width: 300px;">
            <div class="flex items-center">
                <i class="fa ${icon} mr-2"></i>
                <div class="flex-1">${message}</div>
                <button type="button" class="ml-2 text-lg" onclick="this.parentElement.parentElement.remove()">&times;</button>
            </div>
        </div>
    `;
    
    alertContainer.innerHTML = alertHtml;
    
    // Auto hide after 3 seconds
    setTimeout(() => {
        const alert = alertContainer.querySelector('.alert');
        if (alert) {
            alert.remove();
        }
    }, 3000);
}

// Edit form submission - handled above in the main form handler

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

// Simple number formatting function - no complex formatting
function formatNumberSimple(input) {
    // Remove all non-numeric characters except decimal point
    let value = input.value.replace(/[^0-9.]/g, '');

    // Remove multiple decimal points
    const parts = value.split('.');
    if (parts.length > 2) {
        value = parts[0] + '.' + parts.slice(1).join('');
    }

    // Just store the clean number, no formatting
    input.value = value;
}

// Enhanced number formatting functions (legacy)
function formatNumber(input) {
    formatNumberSimple(input);
}

function validateNumber(input) {
    const rawValue = input.value.replace(/[^0-9.]/g, '');
    const number = parseFloat(rawValue);

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
    const rawValue = input.value.replace(/[^0-9.]/g, '');
    // Ensure we return a valid number string
    if (rawValue === '' || rawValue === '.') {
        return '0';
    }
    return rawValue;
}

// Auto-focus pada search jika URL parameter ada
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('search')) {
        document.getElementById('search').focus();
    }
});
</script>
