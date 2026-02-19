let lineChart;
let typeChart;


/* =========================
   Line Chart (Incoming / Outgoing)
========================= */
function loadNumberDocumentChart(range = 3) {

    fetch('/fetch_data.php?chart=1&range=' + range)

    .then(res => res.json())

    .then(response => {

        const data = response.data ?? response;

        const ctx = document
            .getElementById('document_line_chart')
            .getContext('2d');

        // Destroy only line chart
        if (lineChart) {
            lineChart.destroy();
        }

        lineChart = new Chart(ctx, {

            type: 'line',

            data: {

                labels: data.labels,

                datasets: [
                    {
                        label: 'Incoming',
                        data: data.incoming,
                        tension: 0.3,
                        fill: false,
                        borderWidth: 2,
                        pointRadius: 4
                    },
                    {
                        label: 'Outgoing',
                        data: data.outgoing,
                        tension: 0.3,
                        fill: false,
                        borderWidth: 2,
                        pointRadius: 4
                    }
                ]
            },

            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }

        });

    });

}


/* =========================
   Doughnut Chart (Document Type)
========================= */
function loadNumberDocumentTypeChart(category = 1) {
    fetch('/fetch_data.php?chart=2&category='+ category)
    .then(res => res.json())
    .then(response => {
        const data = response.data ?? response;
        const ctx = document
            .getElementById('document_type_doughnut')
            .getContext('2d');
        // Destroy only type chart
        if (typeChart) {
            typeChart.destroy();
        }
        typeChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.labels,
                datasets: [{
                    data: data.counts
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    });
}

/* =========================
   Load defaults
========================= */
loadNumberDocumentChart(3);
loadNumberDocumentTypeChart(1);


/* =========================
   Range filter
========================= */
document.querySelectorAll('input[name="range"]').forEach(radio => {
    radio.addEventListener('click', function () {
        const range = this.value;
        loadNumberDocumentChart(range);
    });

});


/* =========================
   Category filter
========================= */
document.querySelectorAll('input[name="category"]').forEach(radio => {
    radio.addEventListener('click', function () {
        const category = this.value;
        loadNumberDocumentTypeChart(category);
    });
});
