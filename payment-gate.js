document.addEventListener('DOMContentLoaded', function () {
  const paymentForm = document.getElementById('payment-form');
  const cardOptions = document.querySelectorAll('.card-option');
  let selectedCardType = null;

  cardOptions.forEach(option => {
    option.addEventListener('click', function () {
      cardOptions.forEach(opt => opt.classList.remove('selected'));
      this.classList.add('selected');
      selectedCardType = this.dataset.card;
    });
  });

  paymentForm.addEventListener('submit', function (e) {
    e.preventDefault();

    const fullName = document.getElementById('full-name').value.trim();
    const email = document.getElementById('email').value.trim();
    const address = document.getElementById('address').value.trim();
    const cardNumber = document.getElementById('card-number').value.replace(/\s/g, '');
    const expDate = document.getElementById('exp-date').value;
    const cvv = document.getElementById('cvv').value.trim();

    if (!fullName || !email || !address || !cardNumber || !expDate || !cvv) {
      showError('Please fill in all required fields');
      return;
    }

    if (!selectedCardType) {
      showError('Please select a payment method');
      return;
    }

    if (!validateEmail(email)) {
      showError('Please enter a valid email address');
      return;
    }

    if (!validateCardNumber(cardNumber, selectedCardType)) {
      showError('Please enter a valid card number for the selected card type');
      return;
    }

    if (!validateCVV(cvv, selectedCardType)) {
      showError('Please enter a valid CVV');
      return;
    }

    if (!validateExpDate(expDate)) {
      showError('Please enter a valid expiration date');
      return;
    }

    showSuccess('Payment processed successfully! Redirecting...');

    setTimeout(() => {
      paymentForm.reset();
      cardOptions.forEach(opt => opt.classList.remove('selected'));
      selectedCardType = null;
      window.location.href = 'log-in.html'; // redirect after success
    }, 3000);
  });

  document.getElementById('card-number').addEventListener('input', function () {
    this.value = formatCardNumber(this.value);
  });

  function showError(message) {
    const errorElement = document.createElement('div');
    errorElement.className = 'error-message';
    errorElement.textContent = message;
    errorElement.style.color = 'var(--error-color)';
    errorElement.style.marginTop = '1rem';
    errorElement.style.textAlign = 'center';

    const existingMessage = paymentForm.querySelector('.error-message, .success-message');
    if (existingMessage) existingMessage.remove();

    paymentForm.appendChild(errorElement);
  }

  function showSuccess(message) {
    const successElement = document.createElement('div');
    successElement.className = 'success-message';
    successElement.textContent = message;
    successElement.style.color = 'var(--success-color)';
    successElement.style.marginTop = '1rem';
    successElement.style.textAlign = 'center';

    const existingMessage = paymentForm.querySelector('.error-message, .success-message');
    if (existingMessage) existingMessage.remove();

    paymentForm.appendChild(successElement);
  }

  function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
  }

  function validateCardNumber(number, cardType) {
    if (cardType === 'visa' && (number.length < 13 || number.length > 16)) return false;
    if (cardType === 'mastercard' && number.length !== 16) return false;
    if (cardType === 'paypal') return true;
    return luhnCheck(number);
  }

  function luhnCheck(value) {
    let sum = 0;
    let shouldDouble = false;

    for (let i = value.length - 1; i >= 0; i--) {
      let digit = parseInt(value.charAt(i), 10);

      if (shouldDouble) {
        digit *= 2;
        if (digit > 9) digit -= 9;
      }

      sum += digit;
      shouldDouble = !shouldDouble;
    }

    return sum % 10 === 0;
  }

  function validateCVV(cvv, cardType) {
    if (cardType === 'visa' || cardType === 'mastercard') {
      return cvv.length === 3 || cvv.length === 4;
    }
    return true; // e.g., PayPal doesn't use CVV
  }

  function validateExpDate(dateString) {
    if (!dateString) return false;

    const [year, month] = dateString.split('-');
    const expDate = new Date(parseInt(year), parseInt(month) - 1);
    const currentDate = new Date();

    return expDate > currentDate;
  }

  function formatCardNumber(value) {
    const v = value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
    const matches = v.match(/\d{4,16}/g);
    const match = matches && matches[0] || '';
    const parts = [];

    for (let i = 0, len = match.length; i < len; i += 4) {
      parts.push(match.substring(i, i + 4));
    }

    return parts.length ? parts.join(' ') : value;
  }
});
