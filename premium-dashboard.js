
window.addEventListener('DOMContentLoaded', function () {
  document.getElementById('menu-toggle').checked = true;
});

document.addEventListener("DOMContentLoaded", function () {
  const uploadIcon = document.getElementById("uploadIcon");
  const fileInput = document.getElementById("fileInput");
  const clearBtn = document.getElementById("clearBtn");
  const issueTextarea = document.getElementById("issue");
  const startVoiceBtn = document.getElementById("startVoice");
  const submitBtn = document.querySelector(".submit button");
  const reportedCounter = document.querySelector(".cardBox .card:first-child .numbers");
  const statusText = document.getElementById("status");
  const locationElement = document.querySelector(".myLocation");

  const video = document.getElementById("camera");
  const canvas = document.getElementById("snapshot");
  const startCameraBtn = document.getElementById("startCameraBtn");
  const takePhotoBtn = document.getElementById("takePhotoBtn");
  const stopCameraBtn = document.getElementById("stopCameraBtn");

  let imageUploaded = false;
  let issueFilled = false;
  let locationFetched = false;
  let photoTaken = false;
  let issueCount = 0;
  let cameraStream;

  function checkFormReadiness() {
    const ready = (imageUploaded || photoTaken) && locationFetched && issueFilled;
    submitBtn.disabled = !ready;
  }

  uploadIcon.addEventListener("click", function (event) {
    event.preventDefault();
    if (imageUploaded) {
      alert("An image has already been uploaded. Please clear it first to upload a new one.");
      return;
    }
    fileInput.click();
  });

  fileInput.addEventListener("change", () => {
    const file = fileInput.files[0];
    if (fileInput.files.length > 1) {
      alert("Please select only one image.");
      fileInput.value = "";
    } else if (file && !file.type.startsWith("image/")) {
      alert("Only image files are allowed.");
      fileInput.value = "";
    } else {
      imageUploaded = true;
      photoTaken = false;
      alert("Image uploaded: " + file.name);
      checkFormReadiness();
    }
  });

  clearBtn.addEventListener("click", function () {
    if (imageUploaded || photoTaken) {
      fileInput.value = "";
      imageUploaded = false;
      photoTaken = false;
      canvas.style.display = "none";
      stopCamera();
      alert("Image cleared.");
      checkFormReadiness();
    } else {
      alert("No image to clear.");
    }
  });

  if (window.SpeechRecognition || window.webkitSpeechRecognition) {
    const recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
    recognition.lang = 'en-US';
    recognition.interimResults = false;
    recognition.continuous = false;

    startVoiceBtn.addEventListener('click', () => {
      recognition.start();
      startVoiceBtn.textContent = "🎙️ Listening...";
    });

    recognition.onresult = function (event) {
      const transcript = event.results[0][0].transcript;
      issueTextarea.value += transcript + " ";
      issueFilled = issueTextarea.value.trim().length > 0;
      checkFormReadiness();
      startVoiceBtn.textContent = "🎤 Speak";
    };

    recognition.onerror = function () {
      startVoiceBtn.textContent = "🎤 Speak";
    };

    recognition.onend = function () {
      startVoiceBtn.textContent = "🎤 Speak";
    };
  } else {
    startVoiceBtn.disabled = true;
    startVoiceBtn.textContent = "Speech not supported";
  }

  issueTextarea.addEventListener("input", () => {
    issueFilled = issueTextarea.value.trim().length > 0;
    checkFormReadiness();
  });

  submitBtn.disabled = true;
  submitBtn.addEventListener("click", function (e) {
    e.preventDefault();
    issueCount++;
    reportedCounter.textContent = issueCount;
    statusText.textContent = `Status: Pending Verification (${issueCount})`;
    statusText.style.color = "orange";

    let currentPoints = parseInt(localStorage.getItem("points")) || 0;
    currentPoints += 20;
    localStorage.setItem("points", currentPoints);
    localStorage.setItem("availablePoints", currentPoints);
    localStorage.setItem("status", `Pending Verification (${issueCount})`);

    issueTextarea.value = "";
    fileInput.value = "";
    canvas.style.display = "none";
    stopCamera();

    imageUploaded = false;
    photoTaken = false;
    issueFilled = false;
    checkFormReadiness();
    alert("Issue reported successfully!");
  });

  window.getLocation = function () {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        (position) => {
          const latitude = position.coords.latitude;
          const longitude = position.coords.longitude;
          const apiKey = "AIzaSyBozgzhXv7ZTh9OYVmZQ3N3dw6J-ml389s";
          const geocodeUrl = `https://maps.googleapis.com/maps/api/geocode/json?latlng=${latitude},${longitude}&key=${apiKey}`;

          fetch(geocodeUrl)
            .then(response => response.json())
            .then(data => {
              if (data.status === "OK") {
                const address = data.results[0].formatted_address;
                locationElement.textContent = `Location: ${address}`;
                locationFetched = true;
                checkFormReadiness();
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

  function stopCamera() {
    if (cameraStream) {
      cameraStream.getTracks().forEach(track => track.stop());
      video.srcObject = null;
      cameraStream = null;
    }
    video.style.display = "none";
    startCameraBtn.style.display = "inline-block";
    takePhotoBtn.style.display = "none";
    stopCameraBtn.style.display = "none";
  }

  startCameraBtn.addEventListener("click", async () => {
    try {
      cameraStream = await navigator.mediaDevices.getUserMedia({ video: true });
      video.srcObject = cameraStream;
      video.style.display = "block";
      startCameraBtn.style.display = "none";
      takePhotoBtn.style.display = "inline-block";
      stopCameraBtn.style.display = "inline-block";
    } catch (error) {
      alert("Could not access webcam.");
    }
  });

  takePhotoBtn.addEventListener("click", () => {
    if (!cameraStream) {
      alert("Camera not started.");
      return;
    }
    const context = canvas.getContext("2d");
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    context.drawImage(video, 0, 0);
    canvas.style.display = "block";

    imageUploaded = false;
    photoTaken = true;
    checkFormReadiness();
    alert("Photo taken successfully!");
  });

  stopCameraBtn.addEventListener("click", stopCamera);

  const savedStatus = localStorage.getItem("status");
  if (savedStatus) {
    const formattedStatus = savedStatus.charAt(0).toUpperCase() + savedStatus.slice(1);
    statusText.innerText = `Status: ${formattedStatus}`;
    statusText.style.color = "gold";
  }
});

