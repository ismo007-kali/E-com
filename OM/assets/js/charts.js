document.addEventListener('DOMContentLoaded', function() {
    // Configuration des diagrammes avec animations
    const config = {
        type: 'doughnut',
        data: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: [
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(255, 206, 86, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(153, 102, 255, 0.8)',
                    'rgba(255, 159, 64, 0.8)'
                ],
                borderColor: [
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)'
                ],
                borderWidth: 1,
                animation: {
                    duration: 1000,
                    easing: 'easeOutQuart',
                    animateRotate: true,
                    animateScale: true
                }
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        font: {
                            size: 14
                        }
                    }
                },
                title: {
                    display: true,
                    text: '',
                    font: {
                        size: 18
                    }
                }
            },
            animation: {
                duration: 1000,
                easing: 'easeOutQuart'
            }
        }
    };

    // Créer les diagrammes
    const ordersChart = new Chart(document.getElementById('ordersChart'), config);
    const productsChart = new Chart(document.getElementById('productsChart'), config);
    const visitorsChart = new Chart(document.getElementById('visitorsChart'), config);

    // Mettre à jour les diagrammes avec les données
    function updateCharts(orderData, productData, visitorData) {
        ordersChart.data.labels = orderData.labels;
        ordersChart.data.datasets[0].data = orderData.values;
        ordersChart.update();

        productsChart.data.labels = productData.labels;
        productsChart.data.datasets[0].data = productData.values;
        productsChart.update();

        visitorsChart.data.labels = visitorData.labels;
        visitorsChart.data.datasets[0].data = visitorData.values;
        visitorsChart.update();
    }

    // Charger les données via AJAX
    function loadChartData() {
        fetch('?page=get_chart_data')
            .then(response => response.json())
            .then(data => {
                updateCharts(data.orders, data.products, data.visitors);
            })
            .catch(error => console.error('Erreur:', error));
    }

    // Charger les données au chargement de la page
    loadChartData();

    // Mettre à jour les données toutes les 5 minutes
    setInterval(loadChartData, 5 * 60 * 1000);
});
