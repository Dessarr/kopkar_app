<!-- External CSS/JS for daterangepicker -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script>
let selectedRow = null;

$(document).ready(function() {
    initializeDateRangePicker();
    initializeAnggotaDropdown();
    initializeRowSelection();
    initializeFormSubmissions();
});

function initializeDateRangePicker() {
    $('#daterange-btn').daterangepicker({
        locale: {
            format: 'DD/MM/YYYY',
            separator: ' - ',
            applyLabel: 'Terapkan',
            cancelLabel: 'Batal',
            fromLabel: 'Dari',
            toLabel: 'Sampai',
            customRangeLabel: 'Kustom',
            weekLabel: 'M',
            daysOfWeek: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
            monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
            firstDay: 1
        },
        ranges: {
            'Hari ini': [moment(), moment()],
            'Kemarin': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            '7 Hari Terakhir': [moment().subtract(6, 'days'), moment()],
            '30 Hari Terakhir': [moment().subtract(29, 'days'), moment()],
            'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
            'Bulan Lalu': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
        startDate: moment().subtract(29, 'days'),
        endDate: moment()
    }, function(start, end, label) {
        $('#daterange-text').html(start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY'));
        $('#tgl_dari').val(start.format('YYYY-MM-DD'));
        $('#tgl_sampai').val(end.format('YYYY-MM-DD'));
    });

    // Set initial text
    const startDate = $('#tgl_dari').val();
    const endDate = $('#tgl_sampai').val();
    if (startDate && endDate) {
        $('#daterange-text').html(moment(startDate).format('DD/MM/YYYY') + ' - ' + moment(endDate).format('DD/MM/YYYY'));
    }
}

function initializeAnggotaDropdown() {
    // Close dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#nama_anggota, #anggotaDropdown').length) {
            $('#anggotaDropdown').hide();
        }
    });
}

// Toggle anggota dropdown
function toggleAnggotaDropdown() {
    $('#anggotaDropdown').toggle();
}

// Select anggota from dropdown
function selectAnggota(element) {
    const id = $(element).data('id');
    const noKtp = $(element).data('no-ktp');
    const nama = $(element).data('nama');

    console.log('Selecting anggota:', { id, noKtp, nama });

    $('#nama_anggota').val(nama);
    $('#no_ktp').val(noKtp);
    $('#anggota_id').val(id);
    $('#anggotaDropdown').hide();

    // Update photo
    updateAnggotaPhoto(id, noKtp, nama);
}

// Filter anggota in dropdown
function filterAnggota(searchTerm) {
    const options = $('.anggota-option');
    const term = searchTerm.toLowerCase();

    options.each(function() {
        const nama = $(this).data('nama').toLowerCase();
        const noKtp = $(this).data('no-ktp').toLowerCase();
        
        if (nama.includes(term) || noKtp.includes(term)) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
}

// Update anggota photo
function updateAnggotaPhoto(anggotaId, noKtp, nama) {
    const photoContainer = $('#anggotaPhoto');
    
    // Make AJAX request to get photo
    $.ajax({
        url: '/api/anggota/photo/' + anggotaId,
        method: 'GET',
        success: function(response) {
            if (response.photo_url) {
                photoContainer.html(`
                    <img src="${response.photo_url}" 
                         class="w-32 h-40 rounded-lg object-cover" 
                         alt="Photo ${nama}">
                `);
            } else {
                photoContainer.html(`
                    <div class="text-center text-gray-500">
                        <i class="fas fa-user text-4xl mb-2"></i>
                        <p class="text-sm">${nama}</p>
                        <p class="text-xs">ID: AG${String(anggotaId).padStart(4, '0')}</p>
                    </div>
                `);
            }
        },
        error: function() {
            photoContainer.html(`
                <div class="text-center text-gray-500">
                    <i class="fas fa-user text-4xl mb-2"></i>
                    <p class="text-sm">${nama}</p>
                    <p class="text-xs">ID: AG${String(anggotaId).padStart(4, '0')}</p>
                </div>
            `);
        }
    });
}

// Auto-fill jumlah based on jenis simpanan
function autoFillJumlah() {
    const jenisId = $('#jenis_id').val();
    const selectedOption = $('#jenis_id option:selected');
    const jumlah = selectedOption.data('jumlah');
    
    if (jumlah && jumlah > 0) {
        $('#jumlah').val(formatNumber(jumlah));
    }
}

// Open date picker
function openDatePicker() {
    // Create a simple date picker modal or use existing date picker
    const currentDate = new Date();
    const formattedDate = currentDate.toISOString().slice(0, 16);
    
    const newDate = prompt('Masukkan tanggal dan waktu (YYYY-MM-DD HH:MM):', formattedDate);
    if (newDate) {
        const date = new Date(newDate);
        if (!isNaN(date.getTime())) {
            $('#tgl_transaksi').val(newDate);
            $('#tgl_transaksi_txt').val(formatDateIndonesian(date));
        }
    }
}

// Format date to Indonesian format
function formatDateIndonesian(date) {
    const months = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    const day = String(date.getDate()).padStart(2, '0');
    const month = months[date.getMonth()];
    const year = date.getFullYear();
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    
    return `${day} ${month} ${year} - ${hours}:${minutes}`;
}

function initializeRowSelection() {
    $('.row-selectable').on('click', function() {
        $('.row-selectable').removeClass('bg-blue-50 border-blue-200');
        $(this).addClass('bg-blue-50 border-blue-200');
        selectedRow = $(this);
    });
}

function initializeFormSubmissions() {
    // Add form submission
    $('#addForm').on('submit', function(e) {
        e.preventDefault();
        
        const cleanData = {
            tgl_transaksi: $('#tgl_transaksi').val(),
            no_ktp: $('#no_ktp').val(),
            anggota_id: $('#anggota_id').val(),
            jenis_id: $('#jenis_id').val(),
            jumlah: parseFloat($('#jumlah').val().replace(/[^\d]/g, '')) || 0,
            keterangan: $('#keterangan').val(),
            akun: $('#akun').val(),
            dk: $('#dk').val(),
            kas_id: $('#kas_id').val(),
            nama_penyetor: $('#nama_penyetor').val(),
            no_identitas: $('#no_identitas').val(),
            alamat: $('#alamat').val(),
            id_cabang: $('#id_cabang').val()
        };

        // Debug: Log the data being sent
        console.log('Form data being sent:', cleanData);
        console.log('Hidden field values:', {
            akun: $('#akun').val(),
            dk: $('#dk').val(),
            id_cabang: $('#id_cabang').val()
        });
        
        // Validate required fields before sending
        if (!cleanData.akun || !cleanData.dk || !cleanData.id_cabang || !cleanData.anggota_id || !cleanData.no_ktp || !cleanData.jumlah || cleanData.jumlah <= 0) {
            console.error('Missing required fields:', {
                akun: cleanData.akun,
                dk: cleanData.dk,
                id_cabang: cleanData.id_cabang,
                anggota_id: cleanData.anggota_id,
                no_ktp: cleanData.no_ktp,
                jumlah: cleanData.jumlah
            });
            showNotification('error', 'Data form tidak lengkap. Pastikan semua field terisi dengan benar dan jumlah lebih dari 0.');
            return;
        }

        fetch('{{ route("simpanan.setoran.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(cleanData)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showNotification('success', result.message);
                closeModal('addModal');
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification('error', result.message || 'Terjadi kesalahan');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('error', 'Terjadi kesalahan saat menyimpan data');
        });
    });

    // Edit form submission
    $('#editForm').on('submit', function(e) {
        e.preventDefault();
        
        const cleanData = {
            tgl_transaksi: $('#edit_tgl_transaksi').val(),
            no_ktp: $('#edit_no_ktp').val(),
            anggota_id: $('#edit_anggota_id').val(),
            jenis_id: $('#edit_jenis_id').val(),
            jumlah: $('#edit_jumlah').val().replace(/[^\d]/g, ''),
            keterangan: $('#edit_keterangan').val(),
            akun: $('#edit_akun').val(),
            dk: $('#edit_dk').val(),
            kas_id: $('#edit_kas_id').val(),
            nama_penyetor: $('#edit_nama_penyetor').val(),
            no_identitas: $('#edit_no_identitas').val(),
            alamat: $('#edit_alamat').val(),
            id_cabang: $('#edit_id_cabang').val()
        };

        const setId = $('#edit_setoran_id').val();
        
        fetch(`{{ url('simpanan/setoran') }}/${setId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-HTTP-Method-Override': 'PUT'
            },
            body: JSON.stringify(cleanData)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showNotification('success', result.message);
                closeModal('editModal');
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification('error', result.message || 'Terjadi kesalahan');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('error', 'Terjadi kesalahan saat mengupdate data');
        });
    });
}

function openModal(modalId) {
    $('#' + modalId).removeClass('hidden');
    
    if (modalId === 'addModal') {
        // Set default date to now
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        
        $('#tgl_transaksi').val(`${year}-${month}-${day}T${hours}:${minutes}`);
        
        // Reset form
        $('#addForm')[0].reset();
        $('#tgl_transaksi').val(`${year}-${month}-${day}T${hours}:${minutes}`);
        
        // Set default values for hidden fields
        $('#akun').val('Setoran');
        $('#dk').val('D');
        $('#id_cabang').val('CB0001');
        
        // Debug: Log hidden field values
        console.log('Modal opened - Hidden field values:', {
            akun: $('#akun').val(),
            dk: $('#dk').val(),
            id_cabang: $('#id_cabang').val()
        });
    }
}

function closeModal(modalId) {
    $('#' + modalId).addClass('hidden');
}

function closeImportModal() {
    $('#importModal').addClass('hidden');
}

function editData() {
    if (!selectedRow) {
        showNotification('warning', 'Pilih data yang akan diedit terlebih dahulu');
        return;
    }

    const data = selectedRow.data();
    
    // Populate edit form
    $('#edit_setoran_id').val(data.id);
    $('#edit_tgl_transaksi').val(data.tanggal ? moment(data.tanggal).format('YYYY-MM-DDTHH:mm') : '');
    $('#edit_nama_anggota').val(data.namaAnggota);
    $('#edit_no_ktp').val(data.noKtp);
    $('#edit_anggota_id').val(data.idAnggota);
    $('#edit_jenis_id').val(data.jenisId);
    $('#edit_jumlah').val(formatNumber(data.jumlah));
    $('#edit_keterangan').val(data.keterangan);
    $('#edit_akun').val(data.akun);
    $('#edit_dk').val(data.dk);
    $('#edit_kas_id').val(data.kasId);
    $('#edit_nama_penyetor').val(data.namaPenyetor);
    $('#edit_no_identitas').val(data.noIdentitas);
    $('#edit_alamat').val(data.alamat);
    $('#edit_id_cabang').val(data.idCabang);

    openModal('editModal');
}

function deleteData() {
    if (!selectedRow) {
        showNotification('warning', 'Pilih data yang akan dihapus terlebih dahulu');
        return;
    }

    if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
        const id = selectedRow.data('id');
        
        fetch(`{{ url('simpanan/setoran') }}/${id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-HTTP-Method-Override': 'DELETE'
            }
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showNotification('success', result.message);
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification('error', result.message || 'Terjadi kesalahan');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('error', 'Terjadi kesalahan saat menghapus data');
        });
    }
}

function doSearch() {
    $('#filterForm').submit();
}

function clearFilters() {
    $('#search').val('');
    $('#tgl_dari').val('');
    $('#tgl_sampai').val('');
    $('#daterange-text').text('Pilih Tanggal');
    $('#filterForm').submit();
}

function cetakLaporan() {
    const startDate = $('#tgl_dari').val();
    const endDate = $('#tgl_sampai').val();
    const search = $('#search').val();
    
    let url = '{{ route("simpanan.setoran.export") }}?';
    if (startDate) url += `start_date=${startDate}&`;
    if (endDate) url += `end_date=${endDate}&`;
    if (search) url += `search=${search}&`;
    
    window.open(url, '_blank');
}

function cetakNota(id) {
    window.open(`{{ url('simpanan/setoran/nota') }}/${id}`, '_blank');
}

function formatNumberSimple(input) {
    let value = input.value.replace(/[^\d.]/g, '');
    if (value) {
        const number = parseFloat(value);
        if (!isNaN(number) && number > 0) {
            value = number.toString();
        } else {
            value = '';
        }
    }
    input.value = value;
}

function formatNumber(num) {
    return num ? parseFloat(num).toString() : '';
}

function showNotification(type, message) {
    const notification = $(`
        <div class="fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm ${type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-yellow-500'} text-white">
            <div class="flex items-center">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-exclamation-triangle'} mr-2"></i>
                <span>${message}</span>
            </div>
        </div>
    `);
    
    $('body').append(notification);
    
    setTimeout(() => {
        notification.fadeOut(() => notification.remove());
    }, 3000);
}
</script>
