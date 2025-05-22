document.addEventListener("DOMContentLoaded", function () {
  const uploadIcon = document.getElementById("uploadIcon");
  const fileInput = document.getElementById("fileInput");
  const clearBtn = document.getElementById("clearBtn");
  const submitBtn = document.querySelector(".submit button");
  const reportedCounter = document.querySelector(".cardBox .card:first-child .numbers");
  const issueTextarea = document.getElementById("issue");
  const startVoiceBtn = document.getElementById("startVoice");
  const statusText = document.getElementById("status");
  let imageUploaded = false;
  let uploadedFile = null;

  // Fetch report count
  fetch('get-reort-count.php')
    .then(res => res.json())
    .then(data => {
      if (data.count !== undefined) {
        reportedCounter.textContent = data.count;
        statusText.textContent = `Status: Pending Verification (${data.count})`;
        statusText.style.color = "orange";
      }
    })
    .catch(err => console.error("Error fetching count:", err));

  // Upload image
  uploadIcon.addEventListener("click", (e) => {
    e.preventDefault();
    if (imageUploaded) {
      alert("An image has already been uploaded. Please clear it first.");
    } else {
      fileInput.click();
    }
  });

  fileInput.addEventListener("change", () => {
    const file = fileInput.files[0];
    if (fileInput.files.length > 1 || (file && !file.type.startsWith("image/"))) {
      alert("Only one image file is allowed.");
      fileInput.value = "";
      return;
    }
    imageUploaded = true;
    uploadedFile = file;
    alert("Image uploaded: " + file.name);
  });

  clearBtn.addEventListener("click", () => {
    if (imageUploaded) {
      fileInput.value = "";
      uploadedFile = null;
      imageUploaded = false;
      alert("Image cleared.");
    } else {
      alert("No image to clear.");
    }
  });

  // Voice input
  const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
  if (SpeechRecognition) {
    const recognition = new SpeechRecognition();
    recognition.lang = "en-US";
    recognition.interimResults = false;

    startVoiceBtn.addEventListener("click", () => {
      recognition.start();
      startVoiceBtn.textContent = "🎙️ Listening...";
    });

    recognition.onresult = (event) => {
      issueTextarea.value += event.results[0][0].transcript + " ";
      startVoiceBtn.textContent = "🎤 Speak";
    };

    recognition.onerror = () => {
      startVoiceBtn.textContent = "🎤 Speak";
    };

    recognition.onend = () => {
      startVoiceBtn.textContent = "🎤 Speak";
    };
  } else {
    startVoiceBtn.disabled = true;
    startVoiceBtn.textContent = "Speech not supported";
  }

  // Submit report
  submitBtn.addEventListener("click", (e) => {
    e.preventDefault();
    const issue = issueTextarea.value.trim();
    const locationText = document.querySelector(".myLocation").textContent.replace("Location: ", "").trim();

    if (!issue || !uploadedFile || !locationText) {
      alert("Please describe the issue, upload an image, and ensure location is detected.");
      return;
    }

    const formData = new FormData();
    formData.append("description", issue);
    formData.append("location", locationText);
    formData.append("image", uploadedFile);

    fetch("submitReport.php", {
      method: "POST",
      body: formData
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          alert(data.message || "Report submitted!");
          reportedCounter.textContent = data.reportedCount;
          statusText.textContent = `Status: Pending Verification (${data.reportedCount})`;
          statusText.style.color = "orange";
          issueTextarea.value = "";
          fileInput.value = "";
          imageUploaded = false;
          uploadedFile = null;
        } else {
          alert("Error: " + (data.error || "Unknown error"));
        }
      })
      .catch(err => {
        console.error("Submit error:", err);
        alert("Failed to submit. Please try again.");
      });
  });

  // Get user location
  window.getLocation = function () {
    const locationElement = document.querySelector(".myLocation");
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        (position) => {
          const { latitude, longitude } = position.coords;
          const apiKey = "AIzaSyBozgzhXv7ZTh9OYVmZQ3N3dw6J-ml389s";
          const url = `https://maps.googleapis.com/maps/api/geocode/json?latlng=${latitude},${longitude}&key=${apiKey}`;

          fetch(url)
            .then(res => res.json())
            .then(data => {
              if (data.status === "OK") {
                locationElement.textContent = `Location: ${data.results[0].formatted_address}`;
              } else {
                locationElement.textContent = "Unable to retrieve address.";
              }
            })
            .catch(() => locationElement.textContent = "Error retrieving location.");
        },
        () => locationElement.textContent = "Location access denied."
      );
    } else {
      locationElement.textContent = "Geolocation not supported.";
    }
  };

  // Auto-open menu toggle if needed
  const menuToggle = document.getElementById('menu-toggle');
  if (menuToggle) {
    menuToggle.checked = true;
  }
});
