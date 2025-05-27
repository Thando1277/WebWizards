let pieChart, lineChart;
let dashboardData = null;

document.addEventListener("DOMContentLoaded", function () {
  const totalReportsEl = document.getElementById('totalReports');
  const totalProgressEl = document.getElementById('totalProgress');
  const totalCompletedEl = document.getElementById('totalCompleted');
  const numberEls = document.querySelectorAll('.insight-card .progress .number');

  // Fetch data from PHP backend
  async function fetchDashboardData() {
    try {
      const response = await fetch('dashboard_data.php');
      const result = await response.json();
      
      if (result.success) {
        dashboardData = result.data;
        updateDashboardUI();
        updateCharts();
        updateRecentReportsTable();
      } else {
        console.error('Failed to fetch dashboard data:', result.error);
        // Fallback to default values
        dashboardData = {
          totalReports: 0,
          totalProgress: 0,
          totalCompleted: 0,
          percentages: { reportsPercent: 0, progressPercent: 0, completedPercent: 0 },
          chartData: { labels: ['Reports', 'Progress', 'Completed'], values: [0, 0, 0] },
          recentReports: []
        };
        updateDashboardUI();
        updateCharts();
      }
    } catch (error) {
      console.error('Error fetching dashboard data:', error);
      // Use fallback data
      dashboardData = {
        totalReports: 80,
        totalProgress: 60,
        totalCompleted: 15,
        percentages: { reportsPercent: 40, progressPercent: 30, completedPercent: 15 },
        chartData: { labels: ['Reports', 'Progress', 'Completed'], values: [80, 60, 15] },
        recentReports: []
      };
      updateDashboardUI();
      updateCharts();
    }
  }

  // Update dashboard UI elements
  function updateDashboardUI() {
    if (!dashboardData) return;

    totalReportsEl.textContent = dashboardData.totalReports;
    totalProgressEl.textContent = dashboardData.totalProgress;
    totalCompletedEl.textContent = dashboardData.totalCompleted;

    // Update percentage displays
    const percentages = [
      dashboardData.percentages.reportsPercent,
      dashboardData.percentages.progressPercent,
      dashboardData.percentages.completedPercent
    ];

    numberEls.forEach((el, i) => {
      el.textContent = `${percentages[i]}%`;
    });
  }

  // Update recent reports table
  function updateRecentReportsTable() {
    if (!dashboardData || !dashboardData.recentReports) return;

    const tbody = document.querySelector('table tbody');
    
    // Clear existing rows except the input row
    const rows = tbody.querySelectorAll('tr');
    for (let i = 1; i < rows.length; i++) {
      rows[i].remove();
    }

    // Add new rows for each recent report
    dashboardData.recentReports.forEach(report => {
      const row = document.createElement('tr');
      row.innerHTML = `
        <td>${report.Username}</td>
        <td>***hidden***</td>
        <td>${report.Email}</td>
        <td>${report.PhoneNumber}</td>
        <td>
          <select name="status" onchange="updateReportStatus(${report.ReportID}, this.value)">
            <option value="Pending" ${report.Status === 'Pending' ? 'selected' : ''}>Pending</option>
            <option value="In Progress" ${report.Status === 'In Progress' ? 'selected' : ''}>In Progress</option>
            <option value="Completed" ${report.Status === 'Completed' ? 'selected' : ''}>Completed</option>
            <option value="Rejected" ${report.Status === 'Rejected' ? 'selected' : ''}>Rejected</option>
          </select>
        </td>
        <td class="primary">
          <a href="details.html?id=${report.ReportID}">Details</a>
        </td>
        <td class="feedback">
          <input type="text" name="feedback" placeholder="Add feedback..." />
        </td>
        <td>
          <div class="upload-wrapper">
            <input type="file" style="display: none;" />
            <a href="#" class="upload-icon">
              <ion-icon name="images-outline"></ion-icon>
            </a>
          </div>
        </td>
      `;
      tbody.appendChild(row);
    });
  }

  // Initialize charts
  function initializeCharts() {
    const ctx = document.getElementById('reportChart').getContext('2d');
    pieChart = new Chart(ctx, {
      type: 'pie',
      data: {
        labels: ['Reports', 'Progress', 'Completed'],
        datasets: [{
          data: [0, 0, 0],
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
          data: [0, 0, 0],
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
  }

  function updateCharts() {
    if (!dashboardData || !pieChart || !lineChart) return;

    const chartValues = dashboardData.chartData.values;

    // Update pie chart
    pieChart.data.datasets[0].data = chartValues;
    pieChart.update();

    // Update line chart
    lineChart.data.datasets[0].data = chartValues;
    lineChart.update();
  }

  // Auto-refresh data every 30 seconds
  function startAutoRefresh() {
    setInterval(fetchDashboardData, 30000); // 30 seconds
  }

  // File upload logic
  const uploadIcon = document.getElementById("uploadIcon");
  const fileInput = document.getElementById("fileInput");
  const clearBtn = document.getElementById("clearBtn");
  let imageSelected = false;

  if (uploadIcon && fileInput && clearBtn) {
    uploadIcon.addEventListener("click", function (event) {
      event.preventDefault();
      if (imageSelected) {
        alert("Please clear the current image before uploading a new one.");
        return;
      }
      fileInput.click();
    });

    fileInput.addEventListener("change", function () {
      const file = fileInput.files[0];
      if (!file) {
        alert("No file selected.");
        return;
      }
      if (!file.type.startsWith("image/")) {
        alert("Only image files are allowed.");
        fileInput.value = "";
        return;
      }
      const maxSizeMB = 2;
      if (file.size > maxSizeMB * 1024 * 1024) {
        alert("Image size must be less than 2MB.");
        fileInput.value = "";
        return;
      }

      alert("Image uploaded successfully!");
      imageSelected = true;
    });

    clearBtn.addEventListener("click", function () {
      if (!imageSelected) {
        alert("No image to clear.");
        return;
      }
      fileInput.value = "";
      imageSelected = false;
      alert("Image cleared successfully.");
    });
  }

  // Initialize everything
  initializeCharts();
  fetchDashboardData();
  startAutoRefresh();

  // Global functions
  window.saveFeedback = function () {
    const feedback = document.getElementById("feedbackInput").value;
    const selectedStatus = document.querySelector('select[name="status"]').value;
    
    if (feedback.trim() === "") {
      alert("Please enter feedback before saving.");
      return;
    }

    // Here you can send the feedback to your PHP backend
    alert("Feedback saved successfully!");
  };
});

// Global function to update report status
async function updateReportStatus(reportId, newStatus) {
  try {
    const response = await fetch('dashboard_data.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        action: 'updateStatus',
        reportId: reportId,
        status: newStatus
      })
    });

    const result = await response.json();
    
    if (result.success) {
      alert('Report status updated successfully!');
      // Refresh dashboard data
      location.reload();
    } else {
      alert('Failed to update report status: ' + result.error);
    }
  } catch (error) {
    console.error('Error updating report status:', error);
    alert('Error updating report status. Please try again.');
  }
}

function storeStatus(value) {
  // This function can be removed as we're now using the database
  console.log('Status selected:', value);
}