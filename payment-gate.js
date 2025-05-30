document.addEventListener('DOMContentLoaded', function () {
  // Initialize EmailJS with your public key
  emailjs.init("WGXfkFJ3DAITmYlCH"); // Replace with your actual EmailJS public key
  
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

  // Function to send welcome email using EmailJS
  function sendWelcomeEmail(userName, userEmail) {
    const templateParams = {
      user_name: userName,
      user_email: userEmail,
      to_name: userName,
      to_email: userEmail
    };

    return emailjs.send(
      'service_c0oqnhr',
      'template_x7r0dzp',
      templateParams
    );
  }

  paymentForm.addEventListener('submit', function (e) {
    e.preventDefault();

    // Show loading state
    const submitBtn = paymentForm.querySelector('.submit-btn');
    const originalBtnText = submitBtn.textContent;
    submitBtn.textContent = 'Processing...';
    submitBtn.disabled = true;

    // Gather form data
    const formData = new FormData(paymentForm);
    const userName = formData.get('full-name').trim();
    const userEmail = formData.get('email').trim();

    // Basic front-end validation
    if (
      !userName ||
      !userEmail ||
      !formData.get('address').trim() ||
      !formData.get('card-number').trim() ||
      !formData.get('exp-date').trim() ||
      !formData.get('cvv').trim()
    ) {
      showError('Please fill in all required fields.');
      resetSubmitButton(submitBtn, originalBtnText);
      return;
    }

    if (!selectedCardType) {
      showError('Please select a payment method.');
      resetSubmitButton(submitBtn, originalBtnText);
      return;
    }

    // Send form data to server
    fetch('payment-gate.php', {
      method: 'POST',
      body: formData
    })
      .then(response => response.json())
      .then(data => {
        console.log(data); // Debugging: shows server response in browser console

        if (data.error) {
          showError(data.error);
          resetSubmitButton(submitBtn, originalBtnText);
        } else if (data.redirect) {
          // Payment successful, now send welcome email
          submitBtn.textContent = 'Sending welcome email...';
          
          sendWelcomeEmail(userName, userEmail)
            .then((emailResponse) => {
              console.log('Welcome email sent successfully:', emailResponse);
              showSuccess('Payment successful! Welcome email sent. Redirecting...');
              
              // Redirect after a short delay to show success message
              setTimeout(() => {
                window.location.href = data.redirect;
              }, 2000);
            })
            .catch((emailError) => {
              console.error('Failed to send welcome email:', emailError);
              showError('Payment successful, but welcome email failed to send. Redirecting...');
              
              // Still redirect even if email fails
              setTimeout(() => {
                window.location.href = data.redirect;
              }, 3000);
            });
        } else {
          showError('Unexpected server response.');
          resetSubmitButton(submitBtn, originalBtnText);
        }
      })
      .catch(() => {
        showError('Network error. Please try again.');
        resetSubmitButton(submitBtn, originalBtnText);
      });
  });

  function resetSubmitButton(button, originalText) {
    button.textContent = originalText;
    button.disabled = false;
  }

  function showError(message) {
    const oldMsg = paymentForm.querySelector('.error-message, .success-message');
    if (oldMsg) oldMsg.remove();

    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.style.color = 'red';
    errorDiv.style.marginTop = '1rem';
    errorDiv.style.textAlign = 'center';
    errorDiv.style.padding = '10px';
    errorDiv.style.backgroundColor = '#fee';
    errorDiv.style.border = '1px solid #fcc';
    errorDiv.style.borderRadius = '4px';
    errorDiv.textContent = message;
    paymentForm.appendChild(errorDiv);
  }

  function showSuccess(message) {
    const oldMsg = paymentForm.querySelector('.error-message, .success-message');
    if (oldMsg) oldMsg.remove();

    const successDiv = document.createElement('div');
    successDiv.className = 'success-message';
    successDiv.style.color = 'green';
    successDiv.style.marginTop = '1rem';
    successDiv.style.textAlign = 'center';
    successDiv.style.padding = '10px';
    successDiv.style.backgroundColor = '#efe';
    successDiv.style.border = '1px solid #cfc';
    successDiv.style.borderRadius = '4px';
    successDiv.textContent = message;
    paymentForm.appendChild(successDiv);
  }
});