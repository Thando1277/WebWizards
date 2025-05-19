const savedFeedback = localStorage.getItem("feedbackMessage");
if (savedFeedback) {
  document.getElementById("feedbackDisplay").innerText = savedFeedback;
}