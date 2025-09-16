<!-- Include required libraries -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script>
// Global variables
let selectedRowData = null;

// Function to show message notifications
function showMessage(title, message, type = 'info') {
    const alertClass = type === 'error' ? 'bg-red-100 border-red-400 text-red-700' : 
                      type === 'warning' ? 'bg-yellow-100 border-yellow-400 text-yellow-700' : 
                      'bg-green-100 border-green-400 text-green-700';
    
    const icon = type === 'error' ? 'fa-ban' : 
                 type === 'warning' ? 'fa-warning' : 
                 'fa-check';
    
    const alertHtml = `
        <div class="${alertClass} px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">
                <i class="fas ${icon} mr-2"></i>${message}
            </span>
        </div>
    `;
    
    // Create temporary alert element
    const alertDiv = document.createElement('div');
    alertDiv.innerHTML = alertHtml;
    alertDiv.className = 'fixed top-4 right-4 z-50 max-w-sm';
    
    document.body.appendChild(alertDiv);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.parentNode.removeChild(alertDiv);
        }
    }, 3000);
}

// Initialize when DOM is loaded
$(document).ready(function() {
    initializeEventListeners();
    initializeDateRangePicker();
    initializeRowSelection();
    initializeAnggotaDropdown();
});

// Initialize all event listeners
function initializeEventListeners() {
    // Add form submission
    const addForm = document.getElementById('addForm');
    if (addForm) {
        addForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);

            // Validasi jenis harus dipilih
            if (!data.jenis_id || data.jenis_id === '') {
                showMessage('Peringatan!', 'Maaf, Jenis Akun belum dipilih.', 'warning');
                document.getElementById('jenis_id').focus();
                return;
            }
            
            // Simple validation - check required fields
            if (!data.tgl_transaksi || !data.no_ktp || !data.jumlah || !data.jenis_id) {
                showMessage('Peringatan!', 'Lengkapi seluruh pengisian data.', 'warning');
                return;
            }

            // Clean the amount value - remove commas and dots (thousand separators)
            const jumlahInput = document.getElementById('jumlah');
            data.jumlah = jumlahInput.value.replace(/[^0-9]/g, '');
            
            // Validasi jumlah harus lebih dari 0
            if (parseFloat(data.jumlah) <= 0) {
                showMessage('Peringatan!', 'Gagal menyimpan data, pastikan nilai lebih dari 0 (NOL).', 'error');
                return;
            }
            
            // Pastikan jumlah adalah number yang valid
            if (isNaN(parseFloat(data.jumlah))) {
                showMessage('Peringatan!', 'Format jumlah tidak valid.', 'error');
                return;
            }

            // Only send fields that exist in the database table
            const cleanData = {
                tgl_transaksi: data.tgl_transaksi,
                no_ktp: data.no_ktp,
                jenis_id: data.jenis_id,
                jumlah: data.jumlah
            };

            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan...';

            try {
                const response = await fetch("{{ route('anggota.toserda.store') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(cleanData)
                });

                const result = await response.json();

                if (result.success) {
                    showMessage('Informasi', 'Data TOSERDA berhasil ditambahkan', 'success');
                    closeModal('addModal');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showMessage('Error', result.message || 'Gagal menyimpan data, pastikan nilai lebih dari 0 (NOL).', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showMessage('Error', 'Terjadi kesalahan saat menyimpan data', 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    }

    // Edit form submission
    const editForm = document.getElementById('editForm');
    if (editForm) {
        editForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);

            // Clean the amount value - remove commas and dots (thousand separators)
            const jumlahInput = document.getElementById('edit_jumlah');
            data.jumlah = jumlahInput.value.replace(/[^0-9]/g, '');
            
            // Validasi jumlah harus lebih dari 0
            if (parseFloat(data.jumlah) <= 0) {
                showMessage('Peringatan!', 'Gagal mengupdate data, pastikan nilai lebih dari 0 (NOL).', 'error');
                return;
            }
            
            // Pastikan jumlah adalah number yang valid
            if (isNaN(parseFloat(data.jumlah))) {
                showMessage('Peringatan!', 'Format jumlah tidak valid.', 'error');
                return;
            }

            // Only send fields that exist in the database table
            const cleanData = {
                tgl_transaksi: data.tgl_transaksi,
                no_ktp: data.no_ktp,
                jenis_id: data.jenis_id,
                jumlah: data.jumlah
            };

            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Mengupdate...';

            try {
                const toserdaId = document.getElementById('edit_toserda_id').value;
                const response = await fetch(`{{ url('anggota/toserda/update') }}/${toserdaId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-HTTP-Method-Override': 'PUT'
                    },
                    body: JSON.stringify(cleanData)
                });

                const result = await response.json();

                if (result.success) {
                    showMessage('success', result.message);
                    closeModal('editModal');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showMessage('error', result.message || 'Terjadi kesalahan saat mengupdate data');
                }
            } catch (error) {
                console.error('Error:', error);
                showMessage('error', 'Terjadi kesalahan saat mengupdate data');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    }
}

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

// Initialize row selection
function initializeRowSelection() {
    const rows = document.querySelectorAll('.row-selectable');
    rows.forEach(row => {
        row.addEventListener('click', function() {
            // Remove previous selection
            rows.forEach(r => r.classList.remove('bg-blue-100'));
            
            // Add selection to current row
            this.classList.add('bg-blue-100');
            
            // Store selected row data
            selectedRowData = {
                id: this.dataset.id,
                kode: this.dataset.kode,
                tanggal: this.dataset.tanggal,
                no_ktp: this.dataset.noKtp,
                nama_anggota: this.dataset.namaAnggota,
                id_anggota: this.dataset.idAnggota,
                jumlah: this.dataset.jumlah,
                jenis_id: this.dataset.jenisId,
                keterangan: this.dataset.keterangan,
                user: this.dataset.user
            };
        });
    });
}

// Handle Anggota Dropdown
function initializeAnggotaDropdown() {
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

        // Hide dropdown
        $('#anggotaDropdown').addClass('hidden');
    });
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
    const tanggal = new Date(selectedRowData.tanggal);
    const formattedDate = tanggal.toISOString().slice(0, 16);

    document.getElementById('edit_tgl_transaksi').value = formattedDate;
    document.getElementById('edit_nama_anggota').value = selectedRowData.nama_anggota;
    document.getElementById('edit_no_ktp').value = selectedRowData.no_ktp;
    document.getElementById('edit_toserda_id').value = selectedRowData.id;
    document.getElementById('edit_jenis_id').value = selectedRowData.jenis_id;

    // Format the amount value for display with thousand separators
    const amount = parseFloat(selectedRowData.jumlah);
    const formattedAmount = amount.toLocaleString('id-ID');
    document.getElementById('edit_jumlah').value = formattedAmount;
}

function deleteData() {
    if (!selectedRowData) {
        alert('Pilih data yang akan dihapus terlebih dahulu!');
        return;
    }

    if (confirm(`Apakah Anda yakin ingin menghapus data TOSERDA ${selectedRowData.kode}?`)) {
        // Show loading
        const deleteBtn = document.querySelector('button[onclick="deleteData()"]');
        const originalText = deleteBtn.innerHTML;
        deleteBtn.disabled = true;
        deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Menghapus...';

        fetch(`{{ url('anggota/toserda/delete') }}/${selectedRowData.id}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-HTTP-Method-Override': 'DELETE',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showMessage('success', result.message);
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showMessage('error', result.message || 'Terjadi kesalahan saat menghapus data');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('error', 'Terjadi kesalahan saat menghapus data');
        })
        .finally(() => {
            deleteBtn.disabled = false;
            deleteBtn.innerHTML = originalText;
        });
    }
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
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

// Utility functions
function doSearch() {
    $('#filterForm').submit();
}

function clearFilters() {
    $('#tgl_dari').val('');
    $('#tgl_sampai').val('');
    $('#search').val('');
    $('#daterange-text').text('Pilih Tanggal');
    $('#filterForm').submit();
}

function cetakLaporan() {
    const params = new URLSearchParams();
    if ($('#tgl_dari').val()) params.append('start_date', $('#tgl_dari').val());
    if ($('#tgl_sampai').val()) params.append('end_date', $('#tgl_sampai').val());
    if ($('#search').val()) params.append('search', $('#search').val());
    
    window.open(`{{ route('anggota.toserda.export.pdf') }}?${params.toString()}`, '_blank');
}

function showMessage(type, message) {
    // Create message element
    const messageDiv = document.createElement('div');
    messageDiv.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg ${
        type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
    }`;
    messageDiv.innerHTML = `
        <div class="flex items-center">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(messageDiv);
    
    // Remove message after 3 seconds
    setTimeout(() => {
        messageDiv.remove();
    }, 3000);
}

function formatNumberSimple(input) {
    // Remove non-numeric characters except decimal point
    let value = input.value.replace(/[^0-9.]/g, '');
    
    // Remove multiple decimal points
    const parts = value.split('.');
    if (parts.length > 2) {
        value = parts[0] + '.' + parts.slice(1).join('');
    }
    
    // Don't format if empty
    if (value === '') {
        input.value = '';
        return;
    }
    
    // Format with thousand separators for display
    if (value && !isNaN(parseFloat(value))) {
        const number = parseFloat(value);
        if (number > 0) {
            input.value = number.toString();
        } else {
            input.value = '';
        }
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    const addModal = document.getElementById('addModal');
    const editModal = document.getElementById('editModal');
    
    if (event.target === addModal) {
        closeModal('addModal');
    }
    if (event.target === editModal) {
        closeModal('editModal');
    }
}
</script>
