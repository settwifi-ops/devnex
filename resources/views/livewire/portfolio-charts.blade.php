<div class="bg-white rounded-lg shadow p-6 mb-8">
    <h2 class="text-xl font-bold mb-6">📊 Performance Charts</h2>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Equity Curve -->
        <div>
            <h3 class="text-lg font-semibold mb-4">Equity Curve</h3>
            <canvas id="equityChart" width="400" height="200"></canvas>
        </div>

        <!-- PNL History -->
        <div>
            <h3 class="text-lg font-semibold mb-4">Cumulative PNL</h3>
            <canvas id="pnlChart" width="400" height="200"></canvas>
        </div>
    </div>

    <!-- Performance Stats -->
    <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="text-center p-4 bg-gray-50 rounded-lg">
            <p class="text-2xl font-bold text-blue-600">{{ $performanceData['total_trades'] }}</p>
            <p class="text-sm text-gray-600">Total Trades</p>
        </div>
        <div class="text-center p-4 bg-gray-50 rounded-lg">
            <p class="text-2xl font-bold text-green-600">{{ number_format($performanceData['win_rate'], 1) }}%</p>
            <p class="text-sm text-gray-600">Win Rate</p>
        </div>
        <div class="text-center p-4 bg-gray-50 rounded-lg">
            <p class="text-2xl font-bold text-green-600">{{ $performanceData['winning_trades'] }}</p>
            <p class="text-sm text-gray-600">Winning Trades</p>
        </div>
        <div class="text-center p-4 bg-gray-50 rounded-lg">
            <p class="text-2xl font-bold text-red-600">{{ $performanceData['losing_trades'] }}</p>
            <p class="text-sm text-gray-600">Losing Trades</p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('livewire:initialized', () => {
    // Equity Chart
    const equityCtx = document.getElementById('equityChart').getContext('2d');
    new Chart(equityCtx, {
        type: 'line',
        data: {
            labels: @json($equityHistory['labels']),
            datasets: [{
                label: 'Equity',
                data: @json($equityHistory['data']),
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: false
                }
            }
        }
    });

    // PNL Chart
    const pnlCtx = document.getElementById('pnlChart').getContext('2d');
    new Chart(pnlCtx, {
        type: 'line',
        data: {
            labels: @json($pnlHistory['labels']),
            datasets: [{
                label: 'Cumulative PNL',
                data: @json($pnlHistory['data']),
                borderColor: '#10B981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});
</script>