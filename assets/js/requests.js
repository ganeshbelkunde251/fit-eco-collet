/**
 * Pickup Request Management JavaScript Module
 * EcoCollect Platform
 */

document.addEventListener('DOMContentLoaded', () => {
    // Admin Quick Status Update Modal / Select Handlers
    const statusSelects = document.querySelectorAll('.admin-status-select');
    
    statusSelects.forEach(select => {
        select.addEventListener('change', async function () {
            const requestId = this.dataset.requestId;
            const newStatus = this.value;
            const originalValue = this.dataset.currentStatus;

            if (newStatus === 'Cancelled') {
                if (!confirm(`Are you sure you want to cancel Request ${requestId}?`)) {
                    this.value = originalValue;
                    return;
                }
            }

            // Disable select during request
            this.disabled = true;

            try {
                const response = await fetch('../api/update-status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        request_id: requestId,
                        status: newStatus
                    })
                });

                const data = await response.json();

                if (data.status === 'success') {
                    showToast(`Request ${requestId} status updated to ${newStatus}`, 'success');
                    this.dataset.currentStatus = newStatus;
                    
                    // Reload page after brief delay if on request details page
                    if (window.location.pathname.includes('request-details.php')) {
                        setTimeout(() => window.location.reload(), 800);
                    }
                } else {
                    alert('Error updating status: ' + (data.message || 'Unknown error'));
                    this.value = originalValue;
                }
            } catch (err) {
                console.error('Update Status Error:', err);
                alert('Network error while updating status.');
                this.value = originalValue;
            } finally {
                this.disabled = false;
            }
        });
    });
});
