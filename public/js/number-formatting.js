/**
 * Global Number Formatting Functions
 * Solusi untuk masalah input nominal yang terbatas pada 4 digit
 */

// Enhanced number formatting function
function formatNumber(input) {
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
            // Use toLocaleString with proper options to handle large numbers
            input.value = number.toLocaleString('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 2
            });
        } else {
            input.value = '';
        }
    }
}

// Simple number formatting function (alias for compatibility)
function formatNumberSimple(input) {
    formatNumber(input);
}

// Validate number input
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

// Get raw number without formatting
function getRawNumber(input) {
    // Return clean number without formatting
    const rawValue = input.value.replace(/[^0-9.]/g, '');
    return rawValue || '0';
}

// Auto-format all number inputs on page load
document.addEventListener('DOMContentLoaded', function() {
    // Find all inputs with number formatting attributes
    const numberInputs = document.querySelectorAll('input[oninput*="formatNumber"], input[oninput*="formatNumberSimple"]');
    
    numberInputs.forEach(input => {
        // Remove existing oninput handlers to avoid conflicts
        input.removeAttribute('oninput');
        
        // Add new event listener
        input.addEventListener('input', function() {
            formatNumber(this);
        });
        
        // Add blur validation
        input.addEventListener('blur', function() {
            validateNumber(this);
        });
    });
});

// Export functions for global use
window.formatNumber = formatNumber;
window.formatNumberSimple = formatNumberSimple;
window.validateNumber = validateNumber;
window.getRawNumber = getRawNumber;
