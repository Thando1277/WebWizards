document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('form');
  const popup = document.getElementById('myPopup');
  const popupMessage = document.getElementById('popupMessage');
  const closeBtn = document.querySelector('.popup .close');


  form.addEventListener('submit', function(event) {
    event.preventDefault(); 
    popupMessage.textContent = 'Thank you for submitting your tender application!';
    popup.style.display = 'block';
  });


  window.closePopup = function() {
    popup.style.display = 'none';
    form.reset(); 
  };

  
  closeBtn.addEventListener('click', () => {
    closePopup();
  });
  
      document.getElementById('BackBtn').addEventListener('click', () => {
      window.location.href = 'premium-dashboard.html';
    });


  window.addEventListener('click', (event) => {
    if (event.target === popup) {
      closePopup();
    }
  });
});
