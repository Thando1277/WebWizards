
let pieChart, lineChart;

document.addEventListener("DOMContentLoaded", function () {
  const totalReportsEl = document.getElementById('totalReports');
  const totalProgressEl = document.getElementById('totalProgress');
  const totalCompletedEl = document.getElementById('totalCompleted');

  const numberEls = document.querySelectorAll('.insight-card .progress .number');

  function getCurrentData() {
    return [
      parseInt(totalReportsEl.textContent || '0'),
      parseInt(totalProgressEl.textContent || '0'),
      parseInt(totalCompletedEl.textContent || '0')
    ];
  }

  function updatePercentDisplays(data) {
    const total = data.reduce((a, b) => a + b, 0);
    const percentages = data.map(value => {
      return total > 0 ? `${Math.round((value / total) * 100)}%` : '0%';
    });

    numberEls.forEach((el, i) => el.textContent = percentages[i]);
  }

  const ctx = document.getElementById('reportChart').getContext('2d');
  pieChart = new Chart(ctx, {
    type: 'pie',
    data: {
      labels: ['Reports', 'Progress', 'Completed'],
      datasets: [{
        data: getCurrentData(),
        backgroundColor: ['#00BFFF', '#FF8C00', '#32CD32'],
        borderColor: '#fff',
        borderWidth: 2
      }]
    },
    options: {
      plugins: {
        legend: {
          display: true,
          position: 'center',
          labels: {
            font: { size: 13, weight: 'bold' },
            color: 'white'
          }
        },
        tooltip: { enabled: false },
        datalabels: {
          color: 'white',
          font: { weight: 'bold', size: 16 },
          formatter: (value, context) => {
            const total = context.dataset.data.reduce((a, b) => a + b, 0);
            return total > 0 ? `${((value / total) * 100).toFixed(0)}%` : '0%';
          }
        }
      }
    },
    plugins: [ChartDataLabels]
  });

  const lineCtx = document.getElementById('lineChart').getContext('2d');
  lineChart = new Chart(lineCtx, {
    type: 'line',
    data: {
      labels: ['Reports', 'Progress', 'Completed'],
      datasets: [{
        label: 'Statistics',
        data: getCurrentData(),
        backgroundColor: '#111',
        borderColor: 'gold',
        borderWidth: 2,
        pointBackgroundColor: 'gold',
        pointRadius: 5,
        fill: true,
        tension: 0.4
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: { beginAtZero: true, ticks: { color: '#333' } },
        x: { ticks: { color: '#333' } }
      },
      plugins: {
        legend: {
          labels: { color: '#333' }
        }
      }
    }
  });

  function updateCharts() {
    const updatedData = getCurrentData();
    pieChart.data.datasets[0].data = updatedData;
    pieChart.update();

    lineChart.data.datasets[0].data = updatedData;
    lineChart.update();

    updatePercentDisplays(updatedData);
  }

  const observer = new MutationObserver(updateCharts);
  [totalReportsEl, totalProgressEl, totalCompletedEl].forEach(el => {
    observer.observe(el, { childList: true });
  });

  // Initial update
  updateCharts();
});

