let pieChart, lineChart;
let dashboardData = null;
let autoRefreshInterval = null;
let pendingChanges = []; // Stores all changes until save

document.addEventListener("DOMContentLoaded", () => {
  initializeCharts();
  fetchDashboardData();
  startAutoRefresh();
  document.getElementById('saveAllBtn').addEventListener('click', saveAllChanges);
});

async function fetchDashboardData() {
  try {
    const response = await fetch('dashboard_data.php');
    const result = await response.json();

    if (result.success) {
      dashboardData = result.data;
    } else {
      console.error('Failed to fetch dashboard data:', result.error);
      dashboardData = getFallbackData();
    }
  } catch (error) {
    console.error('Error fetching dashboard data:', error);
    dashboardData = getFallbackData();
  }

  updateDashboardUI();
  updateCharts();
  updateRecentReportsTable();
}

function getFallbackData() {
  return {
    totalReports: 0,
    totalProgress: 0,
    totalCompleted: 0,
    percentages: { reportsPercent: 0, progressPercent: 0, completedPercent: 0 },
    chartData: { labels: ['Reports', 'Progress', 'Completed'], values: [0, 0, 0] },
    recentReports: []
  };
}

function updateDashboardUI() {
  if (!dashboardData) return;

  document.getElementById('totalReports').textContent = dashboardData.totalReports;
  document.getElementById('totalProgress').textContent = dashboardData.totalProgress;
  document.getElementById('totalCompleted').textContent = dashboardData.totalCompleted;

  const percentages = [
    dashboardData.percentages.reportsPercent,
    dashboardData.percentages.progressPercent,
    dashboardData.percentages.completedPercent
  ];

  document.querySelectorAll('.insight-card .progress .number').forEach((el, i) => {
    el.textContent = `${percentages[i]}%`;
    updateProgressCircle(el.parentElement.querySelector('svg circle'), percentages[i]);
  });
}

function updateProgressCircle(circle, percent) {
  const radius = 30;
  const circumference = 2 * Math.PI * radius;
  const offset = circumference - (percent / 100) * circumference;
  circle.style.strokeDasharray = circumference;
  circle.style.strokeDashoffset = offset;
}

function updateRecentReportsTable() {
  if (!dashboardData || !dashboardData.recentReports) return;

  const tbody = document.getElementById('reportsTableBody');
  tbody.innerHTML = '';

  dashboardData.recentReports.forEach(report => {
    const row = document.createElement('tr');
    const imageDisplay = report.FeedbackImage
      ? `<img class="previewImage" src="uploads/${report.FeedbackImage}" alt="Feedback Image" style="max-height: 50px; max-width: 100px; object-fit: cover;" />`
      : '<img class="previewImage" src="" alt="No Image" style="display: none; max-height: 50px; max-width: 100px; object-fit: cover;" />';

    row.innerHTML = `
      <td>${report.Username}</td>
      <td>***hidden***</td>
      <td>${report.Email}</td>
      <td>${report.PhoneNumber}</td>
      <td>
        <select name="status" data-report-id="${report.ReportID}">
          <option value="Pending" ${report.Status === 'Pending' ? 'selected' : ''}>Pending</option>
          <option value="In Progress" ${report.Status === 'In Progress' ? 'selected' : ''}>In Progress</option>
          <option value="Completed" ${report.Status === 'Completed' ? 'selected' : ''}>Completed</option>
          <option value="Rejected" ${report.Status === 'Rejected' ? 'selected' : ''}>Rejected</option>
        </select>
      </td>
      <td class="primary">
        <a href="details.php?report_id=${report.ReportID}">Details</a>
      </td>
      <td class="feedback">
        <input type="text" name="feedback" data-report-id="${report.ReportID}" 
               value="${report.Feedback || ''}" placeholder="Add feedback..." />
      </td>
      <td>
        <div class="upload-wrapper">
          <input type="file" class="fileInput" accept="image/*" style="display: none;" 
                 data-report-id="${report.ReportID}" />
          <a href="#" class="upload-icon" title="Upload Image">
            <ion-icon name="images-outline"></ion-icon>
          </a>
          ${imageDisplay}
          <button type="button" class="clearBtn" style="margin-left: 5px; font-size: 12px;">Clear</button>
        </div>
      </td>
    `;
    tbody.appendChild(row);
  });

  setupUploadButtons();
  setupStatusChangeListeners();
}

function setupStatusChangeListeners() {
  document.querySelectorAll('select[name="status"]').forEach(select => {
    select.addEventListener('change', function() {
      this.style.borderColor = '#FFA333';
      setTimeout(() => this.style.borderColor = '', 1000);
      
      const reportId = this.dataset.reportId;
      const status = this.value;
      
      updatePendingChange(reportId, { status });
      
      console.log('Status change added to pending changes:', { reportId, status });
    });
  });

  document.querySelectorAll('input[name="feedback"]').forEach(input => {
    input.addEventListener('change', function() {
      const reportId = this.dataset.reportId;
      const feedback = this.value;
      
      updatePendingChange(reportId, { feedback });
      
      console.log('Feedback change added to pending changes:', { reportId, feedback });
    });
  });
}

function updatePendingChange(reportId, updates) {
  const existingChangeIndex = pendingChanges.findIndex(c => c.reportId === reportId && c.action === 'updateStatus');
  
  if (existingChangeIndex >= 0) {
    Object.assign(pendingChanges[existingChangeIndex], updates);
  } else {
    pendingChanges.push({
      reportId,
      action: 'updateStatus',
      ...updates
    });
  }
}

async function saveAllChanges() {
  console.log('saveAllChanges called, pending changes:', pendingChanges);
  
  if (pendingChanges.length === 0) {
    showNotification('No changes to save.', 'info');
    return;
  }

  const saveButton = document.getElementById('saveAllBtn');
  saveButton.disabled = true;
  saveButton.textContent = 'Saving...';

  try {
    let successCount = 0;
    const totalChanges = pendingChanges.length;

    const imageChanges = pendingChanges.filter(c => c.action === 'uploadImage');
    for (const change of imageChanges) {
      try {
        const formData = new FormData();
        formData.append('feedbackImage', change.imageFile);
        formData.append('reportId', change.reportId);
        formData.append('action', 'uploadImage');
        
        console.log('Uploading image for report:', change.reportId);
        
        const response = await fetch('dashboard_data.php', {
          method: 'POST',
          body: formData
        });
        
        const result = await response.json();
        console.log('Image upload response:', result);
        
        if (result.success) {
          successCount++;
          highlightRow(change.reportId);
        } else {
          console.error('Failed to upload image:', result.error);
        }
      } catch (error) {
        console.error(`Error uploading image for report ${change.reportId}:`, error);
      }
    }

    if (imageChanges.length > 0) {
      await new Promise(resolve => setTimeout(resolve, 1000));
    }

    const otherChanges = pendingChanges.filter(c => c.action !== 'uploadImage');
    for (const change of otherChanges) {
      try {
        let response;
        
        console.log('Processing change:', change);
        
        if (change.action === 'clearImage') {
          response = await fetch('dashboard_data.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              action: 'clearImage',
              reportId: change.reportId
            })
          });
          
        } else if (change.action === 'updateStatus') {
          const updateData = {
            action: 'updateStatus',
            reportId: change.reportId,
            status: change.status || '',
            feedback: change.feedback || ''
          };
          
          console.log('Sending combined update:', updateData);
          
          response = await fetch('dashboard_data.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(updateData)
          });
        }

        if (response) {
          const result = await response.json();
          console.log('Response for change:', change, 'Result:', result);
          
          if (result.success) {
            successCount++;
            highlightRow(change.reportId);
          } else {
            console.error('Failed to process change:', change, 'Error:', result.error);
          }
        }
      } catch (error) {
        console.error(`Error processing change for report ${change.reportId}:`, error);
      }
    }

    if (successCount > 0) {
      if (successCount === totalChanges) {
        showNotification(`All ${successCount} changes saved successfully!`, 'success');
      } else {
        showNotification(`${successCount} out of ${totalChanges} changes saved successfully.`, 'warning');
      }
      
      pendingChanges = [];
      await fetchDashboardData();
    } else {
      showNotification('No changes were saved. Please check console for errors.', 'error');
    }
    
  } catch (error) {
    console.error('Error in saveAllChanges:', error);
    showNotification('Error saving changes. Please try again.', 'error');
  } finally {
    saveButton.disabled = false;
    saveButton.textContent = 'Save All Changes';
  }
}

function highlightRow(reportId) {
  const row = document.querySelector(`tr select[data-report-id="${reportId}"]`)?.closest('tr');
  if (row) {
    row.style.backgroundColor = '#d4edda';
    setTimeout(() => row.style.backgroundColor = '', 2000);
  }
}

function showNotification(message, type) {
  const notification = document.createElement('div');
  notification.className = `notification ${type}`;
  notification.textContent = message;
  notification.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 15px 20px;
    border-radius: 5px;
    color: white;
    font-weight: bold;
    z-index: 1000;
    transition: opacity 0.5s ease;
    background-color: ${type === 'success' ? '#32CD32' : 
                      type === 'error' ? '#FF4444' : 
                      type === 'warning' ? '#FF8C00' : '#00BFFF'};
  `;

  document.body.appendChild(notification);

  setTimeout(() => {
    notification.style.opacity = '0';
    setTimeout(() => notification.remove(), 500);
  }, 3000);
}

function initializeCharts() {
  const pieCtx = document.getElementById('reportChart').getContext('2d');
  pieChart = new Chart(pieCtx, {
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
      responsive: true,
      plugins: {
        legend: {
          display: true,
          position: 'center',
          labels: { font: { size: 13, weight: 'bold' }, color: 'white' }
        },
        tooltip: { enabled: true },
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
        backgroundColor: 'rgba(202, 176, 31, 0.2)',
        borderColor: '#FFA333',
        borderWidth: 2,
        pointBackgroundColor: 'rgb(203, 144, 15)',
        pointRadius: 5,
        fill: true,
        tension: 0.4
      }]
    },
    options: {
      responsive: true,
      scales: { y: { beginAtZero: true }, x: {} }
    }
  });
}

function updateCharts() {
  if (!dashboardData || !pieChart || !lineChart) return;

  pieChart.data.datasets[0].data = dashboardData.chartData.values;
  pieChart.update();

  lineChart.data.datasets[0].data = dashboardData.chartData.values;
  lineChart.update();
}

function startAutoRefresh() {
  if (autoRefreshInterval) clearInterval(autoRefreshInterval);
  autoRefreshInterval = setInterval(fetchDashboardData, 30000);
}

function setupUploadButtons() {
  document.querySelectorAll('.upload-icon').forEach(uploadIcon => {
    const wrapper = uploadIcon.closest('.upload-wrapper');
    const fileInput = wrapper.querySelector('.fileInput');
    const clearBtn = wrapper.querySelector('.clearBtn');
    const previewImage = wrapper.querySelector('.previewImage');
    const reportId = fileInput.dataset.reportId;

    uploadIcon.addEventListener('click', e => {
      e.preventDefault();
      fileInput.click();
    });

    fileInput.addEventListener('change', () => {
      const file = fileInput.files[0];
      if (!file) return;

      console.log('File selected:', file.name, 'Size:', file.size, 'Type:', file.type);

      if (!file.type.startsWith("image/")) {
        showNotification("Only image files are allowed.", 'error');
        resetImagePreview(previewImage, fileInput);
        return;
      }

      if (file.size > 2 * 1024 * 1024) {
        showNotification("Image size must be less than 2MB.", 'error');
        resetImagePreview(previewImage, fileInput);
        return;
      }

      const reader = new FileReader();
      reader.onload = e => {
        previewImage.src = e.target.result;
        previewImage.style.display = 'block';
        
        const existingChangeIndex = pendingChanges.findIndex(c => c.reportId === reportId && (c.action === 'uploadImage' || c.action === 'clearImage'));
        if (existingChangeIndex >= 0) {
          pendingChanges[existingChangeIndex].imageFile = file;
          pendingChanges[existingChangeIndex].action = 'uploadImage';
        } else {
          pendingChanges.push({
            reportId,
            imageFile: file,
            action: 'uploadImage'
          });
        }
        
        console.log('Image upload added to pending changes:', { reportId, fileName: file.name, fileSize: file.size });
      };
      
      reader.onerror = () => {
        console.error('Error reading file');
        showNotification('Error reading file', 'error');
      };
      
      reader.readAsDataURL(file);
    });

    clearBtn.addEventListener('click', () => {
      if (!previewImage.src || previewImage.style.display === 'none') {
        showNotification("No image to clear.", 'warning');
        return;
      }
      
      const existingChangeIndex = pendingChanges.findIndex(c => c.reportId === reportId && (c.action === 'uploadImage' || c.action === 'clearImage'));
      if (existingChangeIndex >= 0) {
        pendingChanges[existingChangeIndex].action = 'clearImage';
        delete pendingChanges[existingChangeIndex].imageFile;
      } else {
        pendingChanges.push({
          reportId,
          action: 'clearImage'
        });
      }
      
      resetImagePreview(previewImage, fileInput);
      
      console.log('Image clear added to pending changes:', { reportId });
      showNotification("Image will be cleared when changes are saved.", 'info');
    });
  });
}

function resetImagePreview(previewImage, fileInput) {
  previewImage.src = '';
  previewImage.style.display = 'none';
  fileInput.value = '';
}

function stopAutoRefresh() {
  if (autoRefreshInterval) {
    clearInterval(autoRefreshInterval);
    autoRefreshInterval = null;
    console.log('Auto-refresh stopped');
  }
}

function manualRefresh() {
  console.log('Manual refresh triggered');
  fetchDashboardData();
}