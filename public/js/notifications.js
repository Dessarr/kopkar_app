/**
 * Notification System for Kopkar App
 * Handles real-time updates of notification badges
 */

class NotificationManager {
    constructor() {
        this.updateInterval = 30000; // 30 seconds
        this.init();
    }

    init() {
        // Start periodic updates
        this.startPeriodicUpdates();
        
        // Update when page becomes visible
        this.handleVisibilityChange();
        
        // Initial update
        this.updateNotificationCounts();
    }

    /**
     * Update notification counts from server
     */
    async updateNotificationCounts() {
        try {
            const response = await fetch('/notifications/counts', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin'
            });

            const data = await response.json();
            
            if (data.success) {
                this.updateLoanBadge(data.data.pending_loan_count);
                this.updateWithdrawalBadge(data.data.pending_withdrawal_count);
            }
        } catch (error) {
            console.error('Error updating notifications:', error);
        }
    }

    /**
     * Update loan application badge (envelope icon)
     */
    updateLoanBadge(count) {
        const loanBadge = document.querySelector('.fa-envelope')?.closest('.relative')?.querySelector('.absolute');
        if (!loanBadge) return;

        if (count > 0) {
            loanBadge.textContent = count;
            loanBadge.className = 'absolute -top-2 -right-2 bg-green-800 text-white text-xs rounded-full w-6 h-6 flex items-center justify-center font-semibold';
        } else {
            loanBadge.innerHTML = '<i class="fas fa-check text-xs"></i>';
            loanBadge.className = 'absolute -top-2 -right-2 bg-green-500 text-white text-xs rounded-full w-6 h-6 flex items-center justify-center';
        }
    }

    /**
     * Update withdrawal request badge (document icon)
     */
    updateWithdrawalBadge(count) {
        const withdrawalBadge = document.querySelector('.fa-file-lines')?.closest('.relative')?.querySelector('.absolute');
        if (!withdrawalBadge) return;

        if (count > 0) {
            withdrawalBadge.textContent = count;
            withdrawalBadge.className = 'absolute -top-2 -right-2 bg-green-800 text-white text-xs rounded-full w-6 h-6 flex items-center justify-center font-semibold';
        } else {
            withdrawalBadge.innerHTML = '<i class="fas fa-check text-xs"></i>';
            withdrawalBadge.className = 'absolute -top-2 -right-2 bg-green-500 text-white text-xs rounded-full w-6 h-6 flex items-center justify-center';
        }
    }

    /**
     * Start periodic updates
     */
    startPeriodicUpdates() {
        setInterval(() => {
            this.updateNotificationCounts();
        }, this.updateInterval);
    }

    /**
     * Handle page visibility changes
     */
    handleVisibilityChange() {
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                this.updateNotificationCounts();
            }
        });
    }

    /**
     * Force update notifications (can be called from other parts of the app)
     */
    forceUpdate() {
        this.updateNotificationCounts();
    }
}

// Initialize notification manager when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.notificationManager = new NotificationManager();
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = NotificationManager;
}
