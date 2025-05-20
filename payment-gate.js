document.addEventListener('DOMContentLoaded', function () {
  const paymentForm = document.getElementById('payment-form');
  const cardOptions = document.querySelectorAll('.card-option');
  let selectedCardType = null;

  // Card type selection
  cardOptions.forEach(option => {
    option.addEventListener('click', function () {
      cardOptions.forEach(opt => opt.classList.remove('selected'));
      this.classList.add('selected');
      selectedCardType = this.dataset.card;
    });
  });

  paymentForm.addEventListener('submit', function (e) {
    e.preventDefault();

    // Gather form data
    const formData = new FormData(paymentForm);

    // Basic front-end validation
    if (
      !formData.get('full-name').trim() ||
      !formData.get('email').trim() ||
      !formData.get('address').trim() ||
      !formData.get('card-number').trim() ||
      !formData.get('exp-date').trim() ||
      !formData.get('cvv').trim()
    ) {
      showError('Please fill in all required fields.');
      return;
    }

    if (!selectedCardType) {
      showError('Please select a payment method.');
      return;
    }

    // Send form data to server
    fetch('payment-gate.php', {
      method: 'POST',
      body: formData
    })
      .then(response => response.json())
      .then(data => {
        console.log(data); // ✅ Debugging: shows server response in browser console

        if (data.error) {
          showError(data.error);
        } else if (data.redirect) {
          window.location.href = data.redirect; // ✅ Use PHP-provided redirect URL
        } else {
          showError('Unexpected server response.');
        }
      })
      .catch(() => {
        showError('Network error. Please try again.');
      });
  });

  function showError(message) {
    const oldMsg = paymentForm.querySelector('.error-message');
    if (oldMsg) oldMsg.remove();

    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.style.color = 'red';
    errorDiv.style.marginTop = '1rem';
    errorDiv.style.textAlign = 'center';
    errorDiv.textContent = message;
    paymentForm.appendChild(errorDiv);
  }
});
