// Function to show only one chart at a time
function showChart(period) {
    var periods = ['daily', 'weekly', 'monthly'];
    periods.forEach(function(p) {
        var display = p === period ? 'block' : 'none';
        document.querySelectorAll('.chart-column canvas').forEach(function(chart) {
            if (chart.id.includes(p)) {
                chart.parentNode.style.display = display;
            }
        });
    });
}

// Add event listeners to buttons
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('dailyButton').addEventListener('click', function() {
        showChart('daily');
    });
    document.getElementById('weeklyButton').addEventListener('click', function() {
        showChart('weekly');
    });
    document.getElementById('monthlyButton').addEventListener('click', function() {
        showChart('monthly');
    });

    // Show daily charts by default
    showChart('daily');
});
