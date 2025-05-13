document.addEventListener("DOMContentLoaded", function () {
  const totalReports = parseInt(document.getElementById('totalReports')?.textContent || '40');
  const totalProgress = parseInt(document.getElementById('totalProgress')?.textContent || '30');
  const totalCompleted = parseInt(document.getElementById('totalCompleted')?.textContent || '15');

  const ctx = document.getElementById('reportChart').getContext('2d');

  new Chart(ctx, {
    type: 'pie',
    data: {
      labels: ['Reports', 'Progress', 'Completed'],
      datasets: [{
        data: [totalReports, totalProgress, totalCompleted],
        backgroundColor: ['#00BFFF', '#FF8C00', '#32CD32'],
        borderColor: '#fff',
        borderWidth: 2
      }]
    },
    options: {
      plugins: {
        title: {
          display: true,
          text: '',
          font: {
            size: 24,
            weight: 'bold'
          },
          color: 'white'
        },
        legend: {
          display: true,
          position: 'bottom',
          labels: {
            font: {
              size: 16,
              weight: 'bold'
            },
            color: 'white'
          }
        },
        tooltip: {
          enabled: false
        },
        datalabels: {
          color: 'white',
          font: {
            weight: 'bold',
            size: 16
          },
          formatter: (value, context) => {
            const total = context.dataset.data.reduce((a, b) => a + b, 0);
            const percent = total > 0 ? (value / total * 100).toFixed(0) : 0;
            return percent + '%';
          }
        }
      }
    },
    plugins: [ChartDataLabels]
  });
});