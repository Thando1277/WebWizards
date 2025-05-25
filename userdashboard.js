document.addEventListener("DOMContentLoaded", function () {
  const uploadIcon = document.getElementById("uploadIcon");
  const fileInput = document.getElementById("fileInput");
  const clearBtn = document.getElementById("clearBtn");
  const submitBtn = document.getElementById("submitReportBtn");
  const reportedCounter = document.querySelector(".cardBox .card:first-child .numbers");
  const issueTextarea = document.getElementById("issue");
  const startVoiceBtn = document.getElementById("startVoice");
  // Optional: const preview = document.getElementById("imagePreview");
  let imageUploaded = false;
  let uploadedFile = null;

  function checkFormReady() {
    const issue = issueTextarea.value.trim();
    const locationText = document.querySelector(".myLocation").textContent.trim();
    if (issue && locationText && !locationText.startsWith("Unable to") && imageUploaded) {
      submitBtn.disabled = false;
    } else {
      submitBtn.disabled = true;
    }
  }
  

  // Load user's report count
  fetch('get-report-count.php')
    .then(res => res.json())
    .then(data => {
      if (data.count !== undefined) {
        reportedCounter.textContent = data.count;
      }
    })
    .catch(err => {
      console.error('Error fetching report count:', err);
    });

  uploadIcon.addEventListener("click", function (event) {
    event.preventDefault();
    if (imageUploaded) {
      alert("An image has already been uploaded. Please clear it first.");
      return;
    }
    fileInput.click();
    checkFormReady();

  });

  fileInput.addEventListener("change", () => {
    const file = fileInput.files[0];
    if (fileInput.files.length > 1) {
      alert("Only one image allowed.");
      fileInput.value = "";
      return;
    }
    if (file && !file.type.startsWith("image/")) {
      alert("Only image files are allowed.");
      fileInput.value = "";
      return;
    }
    imageUploaded = true;
    uploadedFile = file;
    alert("Image uploaded: " + file.name);

    // Optional: show preview
    // preview.src = URL.createObjectURL(file);
    checkFormReady();

  });

  clearBtn.addEventListener("click", () => {
    if (imageUploaded) {
      fileInput.value = "";
      imageUploaded = false;
      uploadedFile = null;
      // Optional: preview.src = "";
      alert("Image cleared.");
    } else {
      alert("No image to clear.");
    }
  });

  // Voice Input
  const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
  if (SpeechRecognition) {
    const recognition = new SpeechRecognition();
    recognition.lang = "en-US";
    recognition.interimResults = false;

    startVoiceBtn.addEventListener("click", () => {
      recognition.abort(); // Reset in case of previous attempt
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
    startVoiceBtn.textContent = "Not supported";
  }

  // Submit Report
  submitBtn.addEventListener("click", function (e) {
    e.preventDefault();
    const issue = issueTextarea.value.trim();
    const locationText = document.querySelector(".myLocation").textContent.replace("Location: ", "").trim();

    if (!issue || !locationText || !uploadedFile) {
      alert("Please fill in all fields and upload an image.");
      return;
    }

    if (locationText.startsWith("Unable to")) {
      alert("Please allow location access or try again.");
      return;
    }

    const formData = new FormData();
    formData.append("description", issue);
    formData.append("location", locationText);
    formData.append("image", uploadedFile);

    submitBtn.disabled = true;

    fetch("submit-issue.php", {
      method: "POST",
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        alert(data.message);
        reportedCounter.textContent = data.reportedCount;
        issueTextarea.value = "";
        fileInput.value = "";
        imageUploaded = false;
        uploadedFile = null;
        // Optional: preview.src = "";
      } else {
        alert("Error: " + (data.error || "Unknown error"));
      }
    })
    .catch(err => {
      alert("Failed to submit. Check console.");
      console.error(err);
    })
    .finally(() => {
      submitBtn.disabled = false;
    });
  });

    fetch('get-report-count.php')
    .then(res => res.json())
    .then(data => {
      if (data.count !== undefined) {
        reportedCounter.textContent = data.count;
      }
    })
    .catch(err => {
      console.error('Error fetching report count:', err);
      checkFormReady();

    });

  // Location Function
  window.getLocation = function () {
    const locationElement = document.querySelector(".myLocation");

    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        (position) => {
          const { latitude, longitude } = position.coords;
          const apiKey = "AIzaSyBozgzhXv7ZTh9OYVmZQ3N3dw6J-ml389s";
          const url = `https://maps.googleapis.com/maps/api/geocode/json?latlng=${latitude},${longitude}&key=${apiKey}`;

          fetch(url)
            .then(response => response.json())
            .then(data => {
              if (data.status === "OK") {
                const address = data.results[0].formatted_address;
                locationElement.textContent = `Location: ${address}`;
              } else {
                locationElement.textContent = "Unable to retrieve address.";
              }
            })
            .catch(() => {
              locationElement.textContent = "Error retrieving location data.";
            });
        },
        () => {
          locationElement.textContent = "Unable to retrieve your location.";
        }
      );
    } else {
      locationElement.textContent = "Geolocation is not supported by this browser.";
    }
  };
});
