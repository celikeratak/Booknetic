/**
 * Dashboard JavaScript for Booknetic Help Center
 * Handles charts and interactive elements
 */

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Show loading indicators for all charts
    showChartLoadingIndicators();
    
    // Check and handle non-chart sections
    checkNonChartSections();
    
    // Initialize charts if Chart.js is loaded
    if (typeof Chart !== 'undefined') {
        // Small timeout to ensure loading indicators are visible
        setTimeout(() => {
            initEngagementChart();
            initTrendChart();
            initCategoryChart();
            initStatusChart();
        }, 300);
    } else {
        // If Chart.js is not loaded, show error message
        showChartLoadingErrors('Chart.js library not loaded');
    }

    // Initialize gauge animations
    initGaugeAnimations();
    
    // Initialize section toggles
    initSectionToggles();
});

/**
 * Check and handle non-chart sections that might be empty
 */
function checkNonChartSections() {
    // Define sections to check
    const sections = [
        { selector: '.helpful-card ul', title: 'Most Helpful Topics' },
        { selector: '.search-card ul', title: 'Popular Searches' },
        { selector: '.top-list:nth-child(1) ul', title: 'Most Viewed Topics' },
        { selector: '.top-list:nth-child(2) ul', title: 'Top Rated Topics' }
    ];
    
    // Check each section
    sections.forEach(section => {
        const container = document.querySelector(section.selector);
        if (container) {
            // Check if the container has any list items
            const items = container.querySelectorAll('li');
            if (items.length === 0) {
                // Create no-data message
                const noDataMessage = document.createElement('div');
                noDataMessage.className = 'section-no-data';
                noDataMessage.innerHTML = `
                    <div class="no-data-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <p>No data available to display</p>
                `;
                
                // Replace the empty list with the no-data message
                container.parentNode.replaceChild(noDataMessage, container);
            }
        }
    });
}

/**
 * Show loading indicators for all chart containers
 */
function showChartLoadingIndicators() {
    const chartElements = ['engagementChart', 'trendChart', 'categoryChart', 'statusChart'];
    
    chartElements.forEach(chartId => {
        const chartEl = document.getElementById(chartId);
        if (chartEl) {
            // Create loading container
            const loadingContainer = document.createElement('div');
            loadingContainer.className = 'chart-loading';
            loadingContainer.innerHTML = `
                <div class="spinner-border" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p>Loading chart data...</p>
            `;
            
            // Insert loading indicator before the canvas
            chartEl.parentNode.insertBefore(loadingContainer, chartEl);
            
            // Hide the canvas during loading
            chartEl.style.display = 'none';
        }
    });
}

/**
 * Show error message when chart data is not available
 * @param {string} chartId - The ID of the chart element
 * @param {string} message - The error message to display
 */
function showChartNoDataMessage(chartId, message = 'No data available to display') {
    const chartEl = document.getElementById(chartId);
    if (chartEl) {
        // Remove any existing loading indicators
        const loadingEl = chartEl.parentNode.querySelector('.chart-loading');
        if (loadingEl) {
            loadingEl.remove();
        }
        
        // Create no-data message container
        const noDataContainer = document.createElement('div');
        noDataContainer.className = 'chart-no-data section-no-data';
        noDataContainer.innerHTML = `
            <div class="no-data-icon">
                <i class="fas fa-chart-bar"></i>
            </div>
            <p>${message}</p>
        `;
        
        // Insert no-data message before the canvas
        chartEl.parentNode.insertBefore(noDataContainer, chartEl);
        
        // Hide the canvas
        chartEl.style.display = 'none';
    }
}

/**
 * Show error messages for all charts
 * @param {string} message - The error message to display
 */
function showChartLoadingErrors(message) {
    const chartElements = ['engagementChart', 'trendChart', 'categoryChart', 'statusChart'];
    
    chartElements.forEach(chartId => {
        showChartNoDataMessage(chartId, message);
    });
}

/**
 * Initialize the Engagement Chart (Combined Bar Chart)
 */
function initEngagementChart() {
    const engagementChartEl = document.getElementById('engagementChart');
    if (!engagementChartEl) return;
    
    // Remove loading indicator
    const loadingEl = engagementChartEl.parentNode.querySelector('.chart-loading');
    if (loadingEl) {
        loadingEl.remove();
    }
    
    // Check if we have data
    if (!dashboardData || 
        !dashboardData.engagementTopicTitles || 
        dashboardData.engagementTopicTitles.length === 0) {
        showChartNoDataMessage('engagementChart');
        return;
    }
    
    // Show the canvas
    engagementChartEl.style.display = 'block';

    new Chart(engagementChartEl, {
        type: 'bar',
        data: {
            labels: dashboardData.engagementTopicTitles,
            datasets: [{
                label: 'Views',
                data: dashboardData.engagementTopicViews,
                backgroundColor: '#2196F3'
            }, {
                label: 'Positive Feedback',
                data: dashboardData.engagementTopicPositiveFeedback,
                backgroundColor: '#4CAF50'
            }, {
                label: 'Negative Feedback',
                data: dashboardData.engagementTopicNegativeFeedback,
                backgroundColor: '#E91E63'
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += context.parsed.y;
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
}

/**
 * Initialize the Status Distribution Chart
 */
function initStatusChart() {
    const statusChartEl = document.getElementById('statusChart');
    if (!statusChartEl) return;
    
    // Remove loading indicator
    const loadingEl = statusChartEl.parentNode.querySelector('.chart-loading');
    if (loadingEl) {
        loadingEl.remove();
    }
    
    // Check if we have data
    if (!dashboardData || 
        !dashboardData.statusLabels || 
        dashboardData.statusLabels.length === 0 || 
        !dashboardData.statusCounts || 
        dashboardData.statusCounts.every(count => count === 0)) {
        showChartNoDataMessage('statusChart');
        return;
    }
    
    // Show the canvas
    statusChartEl.style.display = 'block';

    new Chart(statusChartEl, {
        type: 'doughnut',
        data: {
            labels: dashboardData.statusLabels,
            datasets: [{
                data: dashboardData.statusCounts,
                backgroundColor: ['#4CAF50', '#FFC107', '#E91E63']
            }]
        }
    });
}

/**
 * Initialize the Trend Chart (Line)
 */
function initTrendChart() {
    const trendChartEl = document.getElementById('trendChart');
    if (!trendChartEl) return;
    
    // Remove loading indicator
    const loadingEl = trendChartEl.parentNode.querySelector('.chart-loading');
    if (loadingEl) {
        loadingEl.remove();
    }
    
    // Check if we have data
    if (!dashboardData || 
        !dashboardData.trendMonths || 
        dashboardData.trendMonths.length === 0) {
        showChartNoDataMessage('trendChart');
        return;
    }
    
    // Show the canvas
    trendChartEl.style.display = 'block';

    new Chart(trendChartEl, {
        type: 'line',
        data: {
            labels: dashboardData.trendMonths,
            datasets: [{
                label: 'Topics Created',
                data: dashboardData.topicCounts,
                borderColor: '#4CAF50',
                tension: 0.3,
                fill: false
            }, {
                label: 'Liked',
                data: dashboardData.positiveFeedback,
                borderColor: '#2196F3',
                tension: 0.3,
                fill: false
            }, {
                label: 'Disliked',
                data: dashboardData.negativeFeedback,
                borderColor: '#E91E63',
                tension: 0.3,
                fill: false
            }, {
                label: 'Total Views',
                data: dashboardData.totalViews,
                borderColor: '#FFC107',
                tension: 0.3,
                fill: false
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += context.parsed.y;
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
}

/**
 * Initialize the Category Chart (Doughnut)
 */
function initCategoryChart() {
    const categoryChartEl = document.getElementById('categoryChart');
    if (!categoryChartEl) return;
    
    // Remove loading indicator
    const loadingEl = categoryChartEl.parentNode.querySelector('.chart-loading');
    if (loadingEl) {
        loadingEl.remove();
    }
    
    // Check if we have data
    if (!dashboardData || 
        !dashboardData.categoryNames || 
        dashboardData.categoryNames.length === 0 || 
        !dashboardData.categoryCounts || 
        dashboardData.categoryCounts.length === 0 || 
        dashboardData.categoryCounts.every(count => count === 0 || count === null || count === undefined)) {
        showChartNoDataMessage('categoryChart');
        return;
    }
    
    // Show the canvas
    categoryChartEl.style.display = 'block';

    new Chart(categoryChartEl, {
        type: 'doughnut',
        data: {
            labels: dashboardData.categoryNames,
            datasets: [{
                data: dashboardData.categoryCounts,
                backgroundColor: [
                    '#4CAF50', '#2196F3', '#FFC107', '#9C27B0', '#E91E63'
                ]
            }]
        }
    });
}

/**
 * Initialize gauge animations
 */
function initGaugeAnimations() {
    document.querySelectorAll('.gauge').forEach(gauge => {
        const value = parseFloat(gauge.dataset.value) || 0;
        
        // Set the CSS variable for the gauge
        gauge.style.setProperty('--value', value);
        
        // Animate the gauge filling
        let currentValue = 0;
        const animationDuration = 1500; // milliseconds
        const fps = 60;
        const steps = animationDuration / (1000 / fps);
        const increment = value / steps;
        
        const animation = setInterval(() => {
            currentValue += increment;
            
            if (currentValue >= value) {
                currentValue = value;
                clearInterval(animation);
            }
            
            // Update the background gradient
            gauge.style.background = `conic-gradient(
                var(--primary-color) ${currentValue}%, 
                var(--primary-color-back) ${currentValue}% 100%
            )`;
        }, 1000 / fps);
    });
    
    // Animate progress bars
    document.querySelectorAll('.progress-fill').forEach(bar => {
        const width = bar.style.width;
        
        // Start with 0 width
        bar.style.width = '0%';
        
        // Animate to target width
        setTimeout(() => {
            bar.style.width = width;
        }, 300);
    });
}

/**
 * Initialize section toggle functionality
 */
function initSectionToggles() {
    document.querySelectorAll('.toggle-section').forEach(btn => {
        btn.addEventListener('click', () => {
            const section = btn.closest('.manage-section');
            section.classList.toggle('collapsed');
            btn.innerHTML = section.classList.contains('collapsed') ? 
                '<i class="mdi mdi-chevron-up"></i>' : 
                '<i class="mdi mdi-chevron-down"></i>';
        });
    });
}
