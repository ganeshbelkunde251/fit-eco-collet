/**
 * EcoCollect Dashboard & Analytics Charting Module
 */

function initCategoryChart(canvasId, labels, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return;

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: [
                    '#2E7D32', '#43A047', '#0288D1', '#F57F17', 
                    '#7B1FA2', '#D32F2F', '#00796B', '#E65100', '#616161'
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 12, font: { family: 'Plus Jakarta Sans', size: 12 } }
                }
            },
            cutout: '70%'
        }
    });
}

function initStatusChart(canvasId, labels, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Pickup Requests',
                data: data,
                backgroundColor: [
                    '#FFF8E1', '#E1F5FE', '#EDE7F6', '#E0F2F1', '#E8F5E9', '#FFEBEE'
                ],
                borderColor: [
                    '#F57F17', '#0288D1', '#512DA8', '#00796B', '#2E7D32', '#C62828'
                ],
                borderWidth: 2,
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, precision: 0 }
                }
            }
        }
    });
}
