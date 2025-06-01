document.addEventListener('DOMContentLoaded', () => {
  const faqs = document.querySelectorAll('[data-type="faq"]');
  const modal = document.getElementById('faqModal');
  const modalTitle = document.getElementById('modalTitle');
  const modalDescription = document.getElementById('modalDescription');
  const closeModal = document.getElementById('closeModal');

  faqs.forEach(faq => {
    faq.addEventListener('click', () => {
      modalTitle.textContent = faq.getAttribute('data-title');
      modalDescription.textContent = faq.getAttribute('data-description');
      modal.style.display = 'flex';
    });
  });

  closeModal.addEventListener('click', () => {
    modal.style.display = 'none';
  });

  window.addEventListener('click', (e) => {
    if (e.target === modal) {
      modal.style.display = 'none';
    }
  });
});
