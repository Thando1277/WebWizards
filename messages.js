const savedFeedback = localStorage.getItem("feedbackMessage");
if (savedFeedback) {
  document.getElementById("feedbackDisplay").innerText = savedFeedback;
}

const feedback = localStorage.getItem("feedbackMessage");
const imageData = localStorage.getItem("uploadedImage");

document.getElementById("feedbackDisplay").textContent = feedback || "No feedback submitted.";

if (imageData) {
  const img = document.getElementById("imageDisplay");
  img.src = imageData;
  img.style.display = "block";
}