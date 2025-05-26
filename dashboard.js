
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


  
  // File upload icon logic (move this inside the DOMContentLoaded block)
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

      // ✅ Save image to localStorage
      const reader = new FileReader();
      reader.onload = function (e) {
        const base64Image = e.target.result;
        localStorage.setItem("uploadedImage", base64Image); // ✅ Save base64 string
        alert("Image saved!");
        imageSelected = true;
      };
      reader.readAsDataURL(file);
    });

    clearBtn.addEventListener("click", function () {
      if (!imageSelected) {
        alert("No image to clear.");
        return;
      }

      // Clear image from localStorage
      localStorage.removeItem("uploadedImage");

      const newInput = fileInput.cloneNode(true);
      fileInput.replaceWith(newInput);
      newInput.id = "fileInput"; // maintain ID
      document.getElementById("fileInput").addEventListener("change", function () {
        // rebind same logic here if needed
        // or extract the change logic into a named function and reuse
      });

      imageSelected = false;
      alert("Image cleared successfully.");
    });
  }

  // ✅ Still used to save feedback text only
  window.saveFeedback = function () {
    const feedback = document.getElementById("feedbackInput").value;
    localStorage.setItem("feedbackMessage", feedback);

    const selectedStatus = document.querySelector('select[name="status"]').value;
    localStorage.setItem("status", selectedStatus);
  
   

    if (localStorage.getItem("uploadedImage")) {
      alert("Saving feedback and image...");
    } else {
      alert("Saving feedback only (no image uploaded).");
    }
  };
});
function storeStatus(value) {
  localStorage.setItem("savedStatus", value);
}

// Auto-fill the dropdown if status already saved
function storeStatus(value) {
  localStorage.setItem("status", value); // Save selected value
}

// Load previously saved status into dropdown
function storeStatus(value) {
  localStorage.setItem("status", value); // Save to localStorage
}

// Optional: Restore last selected option in dropdown

  const savedStatus = localStorage.getItem("status");

  const statusElement = document.getElementById("status");

  if (savedStatus && savedStatus.trim() !== "") {
    const formattedStatus = savedStatus.charAt(0).toUpperCase() + savedStatus.slice(1);
    statusElement.innerText = `Status: ${formattedStatus}`;
    statusElement.style.color = "orange";
  } else {
    // Keep default text and optionally style it
    statusElement.innerText = "Status: No issues reported";
    statusElement.style.color = "green"; // optional
  }
  


function saveFeedback(event) {
  // Prevent page refresh if inside a form
  if (event) event.preventDefault();

  const feedback = document.getElementById("feedbackInput").value;
  localStorage.setItem("feedbackMessage", feedback);
  alert("Feedback saved!");
}

