document.addEventListener('DOMContentLoaded', function() {
    // Get form elements
    const loanForm = document.getElementById('loan-form');
    const jenisPinjamanSelect = document.getElementById('jenis_pinjaman');
    const nominalInput = document.getElementById('nominal');
    const lamaAngsuranSelect = document.getElementById('lama_angsuran');
    const simulationTable = document.getElementById('loan-simulation-table');
    const simulationTableBody = document.getElementById('simulation-table-body');
    
    // Add event listeners for real-time simulation
    const formInputs = [jenisPinjamanSelect, nominalInput, lamaAngsuranSelect];
    
    formInputs.forEach(input => {
        input.addEventListener('change', handleSimulationUpdate);
        input.addEventListener('input', handleSimulationUpdate);
    });
    
    // Handle simulation update
    function handleSimulationUpdate() {
        // Check if all required fields are filled
        const jenisPinjaman = jenisPinjamanSelect.value;
        const nominal = nominalInput.value.replace(/[^\d]/g, ''); // Remove non-digits
        const lamaAngsuran = lamaAngsuranSelect.value;
        
        // Show/hide simulation table based on form completion
        if (jenisPinjaman && nominal && lamaAngsuran) {
            calculateSimulation();
        } else {
            hideSimulationTable();
        }
    }
    
    // Calculate simulation
    function calculateSimulation() {
        const formData = new FormData();
        formData.append('jenis_pinjaman', jenisPinjamanSelect.value);
        formData.append('nominal', nominalInput.value.replace(/[^\d]/g, ''));
        formData.append('lama_angsuran', lamaAngsuranSelect.value);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        
        // Show loading state
        showLoadingState();
        
        fetch('/member/pengajuan/simulasi-angsuran', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                displaySimulation(data.data, data.summary);
            } else {
                showError(data.message || 'Terjadi kesalahan dalam perhitungan simulasi');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Terjadi kesalahan dalam menghitung simulasi. Silakan coba lagi.');
        });
    }
    
    // Display simulation results
    function displaySimulation(simulationData, summary) {
        // Clear existing table body
        simulationTableBody.innerHTML = '';
        
        // Add rows to table
        simulationData.forEach((row, index) => {
            const tableRow = document.createElement('tr');
            tableRow.className = 'border-b border-gray-200 hover:bg-gray-50 transition-colors duration-200';
            
            // Add alternating row colors
            if (index % 2 === 0) {
                tableRow.classList.add('bg-gray-50');
            }
            
            tableRow.innerHTML = `
                <td class="px-4 py-3 text-center border-r border-gray-200 font-medium">${row.angsuran_ke}</td>
                <td class="px-4 py-3 text-center border-r border-gray-200">${row.tanggal_tempo}</td>
                <td class="px-4 py-3 text-center border-r border-gray-200 font-medium text-blue-600">Rp ${row.angsuran_pokok}</td>
                <td class="px-4 py-3 text-center border-r border-gray-200 text-gray-500">Rp ${row.biaya_bunga}</td>
                <td class="px-4 py-3 text-center border-r border-gray-200 text-gray-500">Rp ${row.biaya_admin}</td>
                <td class="px-4 py-3 text-center font-semibold text-[#14AE5C]">Rp ${row.jumlah_tagihan}</td>
            `;
            
            simulationTableBody.appendChild(tableRow);
        });
        
        // Show simulation table
        showSimulationTable();
        
        // Add summary information
        addSummaryInfo(summary);
    }
    
    // Add summary information
    function addSummaryInfo(summary) {
        // Remove existing summary if any
        const existingSummary = document.getElementById('simulation-summary');
        if (existingSummary) {
            existingSummary.remove();
        }
        
        // Create summary element
        const summaryDiv = document.createElement('div');
        summaryDiv.id = 'simulation-summary';
        summaryDiv.className = 'mt-6 p-6 bg-gradient-to-r from-blue-50 to-green-50 rounded-lg border border-blue-200';
        
        summaryDiv.innerHTML = `
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-chart-pie mr-2 text-[#14AE5C]"></i>
                Ringkasan Simulasi
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center p-4 bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-2">
                        <i class="fas fa-money-bill-wave text-blue-600"></i>
                    </div>
                    <p class="text-sm text-gray-600 mb-1">Total Pinjaman</p>
                    <p class="text-lg font-bold text-gray-800">Rp ${summary.total_pinjaman}</p>
                </div>
                <div class="text-center p-4 bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2">
                        <i class="fas fa-calculator text-green-600"></i>
                    </div>
                    <p class="text-sm text-gray-600 mb-1">Total Angsuran</p>
                    <p class="text-lg font-bold text-gray-800">Rp ${summary.total_angsuran}</p>
                </div>
                <div class="text-center p-4 bg-white rounded-lg shadow-sm border border-gray-200">
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-2">
                        <i class="fas fa-calendar-alt text-purple-600"></i>
                    </div>
                    <p class="text-sm text-gray-600 mb-1">Jangka Waktu</p>
                    <p class="text-lg font-bold text-gray-800">${summary.jumlah_bulan} Bulan</p>
                </div>
            </div>
        `;
        
        // Insert summary after table
        simulationTable.appendChild(summaryDiv);
    }
    
    // Show simulation table
    function showSimulationTable() {
        simulationTable.classList.remove('hidden');
        simulationTable.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    
    // Hide simulation table
    function hideSimulationTable() {
        simulationTable.classList.add('hidden');
    }
    
    // Show loading state
    function showLoadingState() {
        simulationTableBody.innerHTML = `
            <tr>
                <td colspan="6" class="px-4 py-8 text-center">
                    <div class="flex items-center justify-center">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[#14AE5C]"></div>
                        <span class="ml-2 text-gray-600">Menghitung simulasi...</span>
                    </div>
                </td>
            </tr>
        `;
        showSimulationTable();
    }
    
    // Show error message
    function showError(message) {
        simulationTableBody.innerHTML = `
            <tr>
                <td colspan="6" class="px-4 py-8 text-center">
                    <div class="text-red-600">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        ${message}
                    </div>
                </td>
            </tr>
        `;
        showSimulationTable();
    }
    
    // Format number input for better UX
    nominalInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/[^\d]/g, '');
        if (value) {
            value = parseInt(value).toLocaleString('id-ID');
        }
        e.target.value = value;
    });
    
    // Handle form submission
    loanForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate form
        if (!validateForm()) {
            return;
        }
        
        // Show loading state for form submission
        const submitButton = loanForm.querySelector('button[type="submit"]');
        const originalText = submitButton.innerHTML;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Mengirim Pengajuan...';
        submitButton.disabled = true;
        
        // Simulate form submission (replace with actual form submission logic)
        setTimeout(() => {
            showSuccessMessage();
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;
        }, 2000);
    });
    
    // Validate form
    function validateForm() {
        const jenisPinjaman = jenisPinjamanSelect.value;
        const nominal = nominalInput.value.replace(/[^\d]/g, '');
        const lamaAngsuran = lamaAngsuranSelect.value;
        const keterangan = document.getElementById('keterangan').value.trim();
        
        if (!jenisPinjaman) {
            showFieldError('Jenis Pinjaman harus dipilih');
            return false;
        }
        
        if (!nominal || nominal < 1000) {
            showFieldError('Nominal minimal Rp 1.000');
            return false;
        }
        
        if (!lamaAngsuran) {
            showFieldError('Lama Angsuran harus dipilih');
            return false;
        }
        
        if (!keterangan) {
            showFieldError('Keterangan harus diisi');
            return false;
        }
        
        return true;
    }
    
    // Show field error
    function showFieldError(message) {
        // Create or update error message
        let errorDiv = document.getElementById('form-error');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.id = 'form-error';
            errorDiv.className = 'mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded';
            loanForm.insertBefore(errorDiv, loanForm.firstChild);
        }
        errorDiv.innerHTML = `<i class="fas fa-exclamation-circle mr-2"></i>${message}`;
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            if (errorDiv) {
                errorDiv.remove();
            }
        }, 5000);
    }
    
    // Show success message
    function showSuccessMessage() {
        // Create success message
        const successDiv = document.createElement('div');
        successDiv.className = 'mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded';
        successDiv.innerHTML = `
            <i class="fas fa-check-circle mr-2"></i>
            Pengajuan pinjaman berhasil dikirim! Tim kami akan menghubungi Anda segera.
        `;
        
        loanForm.insertBefore(successDiv, loanForm.firstChild);
        
        // Reset form
        loanForm.reset();
        hideSimulationTable();
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            if (successDiv) {
                successDiv.remove();
            }
        }, 5000);
    }
});
