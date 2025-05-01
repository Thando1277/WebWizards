document.addEventListener("DOMContentLoaded", function () {
    var uploadIcon = document.getElementById("uploadIcon");
    var fileInput = document.getElementById("fileInput");
    var clearBtn = document.getElementById("clearBtn");
    var imageUploaded = false; // Track upload status
  
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
      } else if (!imageUploaded) {
        imageUploaded = true;
        alert("Image uploaded: " + file.name);
      }
    });
  
    // Clear button logic
    clearBtn.addEventListener("click", function () {
      if (imageUploaded) {
        fileInput.value = "";
        imageUploaded = false;
        alert("Image upload has been cleared. You can upload a new one now.");
      } else {
        alert("No image to clear.");
      }
    });
  });
  
  
    const issueTextarea = document.getElementById('issue');
    const startVoiceBtn = document.getElementById('startVoice');
  
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    
    if (SpeechRecognition) {
      const recognition = new SpeechRecognition();
      recognition.lang = 'en-US';
      recognition.interimResults = false;
      recognition.continuous = false;
    
      startVoiceBtn.addEventListener('click', () => {
        recognition.start();
        startVoiceBtn.textContent = "🎙 Listening...";
      });
    
      recognition.onresult = function (event) {
        const transcript = event.results[0][0].transcript;
        issueTextarea.value += transcript + " ";
        startVoiceBtn.textContent = "🎤 Speak";
      };
    
      recognition.onerror = function (event) {
        console.error('Speech recognition error:', event.error);
        startVoiceBtn.textContent = "🎤 Speak";
      };
    
      recognition.onend = function () {
        startVoiceBtn.textContent = "🎤 Speak";
      };
    } else {
      startVoiceBtn.disabled = true;
      startVoiceBtn.textContent = "Speech not supported";
    }
    
    let issueCount = 0;
  
    const submitBtn = document.querySelector(".submit button");
    const issueText = document.getElementById("issue");
    const reportedCounter = document.querySelector(".cardBox .card:first-child .numbers");
    const statusText = document.querySelector(".status h4");
    
    submitBtn.addEventListener("click", function (e) {
      e.preventDefault();
    
      const issue = issueText.value.trim();
    
      if (issue.length > 0) {
        issueCount++;
        reportedCounter.textContent = issueCount;
        issueText.value = ""; 
        alert("Issue reported successfully!");
      } else {
        alert("Please describe the issue before submitting.");
      }
  
      statusText.textContent =`Status: Pending Verification ${issueCount}`;
      statusText.style.color = "green";
    });
