const navToggle = document.querySelector('.nav-toggle');
const navLinks = document.querySelector('.nav-links');
if (navToggle && navLinks) {
  navToggle.addEventListener('click', () => {
    const isOpen = navLinks.classList.toggle('open');
    navToggle.setAttribute('aria-expanded', String(isOpen));
  });

  navLinks.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', () => {
      navLinks.classList.remove('open');
      navToggle.setAttribute('aria-expanded', 'false');
    });
  });
}

const slides = [...document.querySelectorAll('.hero-slide')];
const dots = [...document.querySelectorAll('.hero-dot')];
let current = 0;
let timer;

function showSlide(index) {
  if (!slides.length) return;
  current = (index + slides.length) % slides.length;
  slides.forEach((slide, i) => {
    const active = i === current;
    slide.classList.toggle('active', active);
    slide.setAttribute('aria-hidden', String(!active));
  });
  dots.forEach((dot, i) => {
    const active = i === current;
    dot.classList.toggle('active', active);
    dot.setAttribute('aria-current', active ? 'true' : 'false');
  });
}

function startCarousel() {
  if (slides.length < 2) return;
  clearInterval(timer);
  timer = setInterval(() => showSlide(current + 1), 6000);
}

dots.forEach((dot, i) => {
  dot.addEventListener('click', () => {
    showSlide(i);
    startCarousel();
  });
});

showSlide(0);
startCarousel();

const startedField = document.querySelector('input[name="form_started"]');
if (startedField) startedField.value = String(Date.now());

const params = new URLSearchParams(window.location.search);
const formStatus = document.querySelector('[data-form-status]');
if (formStatus && params.has('sent')) {
  const sent = params.get('sent') === '1';
  formStatus.textContent = sent
    ? 'Thank you. Your message has been sent successfully.'
    : 'We could not send your message. Please try again or contact us by email.';
  formStatus.classList.add(sent ? 'success' : 'error');
  formStatus.hidden = false;
}
