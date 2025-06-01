document.addEventListener("DOMContentLoaded", function () {
  const uploadIcon = document.getElementById("uploadIcon");
  const fileInput = document.getElementById("fileInput");
  const clearBtn = document.getElementById("clearBtn");
  const submitBtn = document.getElementById("submitReportBtn");
  const reportedCounter = document.querySelector(".cardBox .card:first-child .numbers");
  const solvedCounter = document.querySelector(".cardBox .card:nth-child(2) .numbers");
  const issueTextarea = document.getElementById("issue");
  const startVoiceBtn = document.getElementById("startVoice");
  const locationElement = document.querySelector(".myLocation");
  let imageUploaded = false;
  let uploadedFile = null;

  function checkFormReady() {
    const issue = issueTextarea.value.trim();
    const locationText = locationElement.textContent.trim();
    if (issue && locationText && !locationText.startsWith("Unable to") && imageUploaded) {
      submitBtn.disabled = false;
    } else {
      submitBtn.disabled = true;
    }
  }

function updateIssueCount() {
  fetch('get-report-count.php')
    .then(res => res.json())
    .then(data => {
      if (data.total !== undefined) {
        issueCount = data.total;
        reportedCounter.textContent = data.total;
        solvedCounter.textContent = data.completed;
      }
    })
    .catch(err => {
      console.error('Error fetching report count:', err);
    });
}
  updateIssueCount()
  uploadIcon.addEventListener("click", function (e) {
    e.preventDefault();
    if (imageUploaded) {
      alert("An image has already been uploaded. Please clear it first.");
      return;
    }
    fileInput.click();
  });

  fileInput.addEventListener("change", () => {
    const file = fileInput.files[0];
    if (!file) return;

    if (!file.type.startsWith("image/")) {
      alert("Only image files are allowed.");
      fileInput.value = "";
      return;
    }

    imageUploaded = true;
    uploadedFile = file;
    alert("Image uploaded: " + file.name);
    checkFormReady();
  });

  clearBtn.addEventListener("click", () => {
    if (imageUploaded) {
      fileInput.value = "";
      imageUploaded = false;
      uploadedFile = null;
      alert("Image cleared.");
    } else {
      alert("No image to clear.");
    }
    checkFormReady();
  });

  // Voice recognition
  const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
  if (SpeechRecognition) {
    const recognition = new SpeechRecognition();
    recognition.lang = "en-US";
    recognition.interimResults = false;

    startVoiceBtn.addEventListener("click", () => {
      recognition.abort();
      recognition.start();
      startVoiceBtn.textContent = "🎙️ Listening...";
    });

    recognition.onresult = (event) => {
      issueTextarea.value += event.results[0][0].transcript + " ";
      startVoiceBtn.textContent = "🎤 Speak";
      checkFormReady();
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

  window.getLocation = function () {
    if (navigator.geolocation) {
      locationElement.textContent = "Getting location...";
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
              checkFormReady();
            })
            .catch(() => {
              locationElement.textContent = "Error retrieving location data.";
              checkFormReady();
            });
        },
        () => {
          locationElement.textContent = "Unable to retrieve your location.";
          checkFormReady();
        }
      );
    } else {
      locationElement.textContent = "Geolocation is not supported by this browser.";
      checkFormReady();
    }
  };

  submitBtn.addEventListener("click", function (e) {
    e.preventDefault();

    const issue = issueTextarea.value.trim();
    const locationText = locationElement.textContent.replace("Location: ", "").trim();

    if (!issue || !locationText || !uploadedFile) {
      alert("Please fill in all fields and upload an image.");
      return;
    }

    if (locationText.startsWith("Unable to")) {
      alert("Please get a valid location before submitting.");
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
          issueTextarea.value = "";
          fileInput.value = "";
          imageUploaded = false;
          uploadedFile = null;
          locationElement.textContent = "";
          updateIssueCount();
        } else {
          alert("Error: " + (data.error || "Unknown error"));
        }
      })
      .catch(err => {
        console.error("Submit failed:", err);
        alert("Failed to submit. Check console for details.");
      })
      .finally(() => {
        submitBtn.disabled = false;
      });
  });

  // Trigger form check on text input
  issueTextarea.addEventListener("input", checkFormReady);
});
//For the ad
const adsBanner = document.getElementById('ads-banner');
  const closeBtn = adsBanner.querySelector('.close-btn');

  closeBtn.addEventListener('click', () => {
    adsBanner.style.display = 'none';
  });