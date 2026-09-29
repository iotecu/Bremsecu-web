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


document.querySelectorAll('[data-youtube-id]').forEach((poster) => {
  poster.addEventListener('click', () => {
    const id = poster.dataset.youtubeId;
    if (!id) return;
    const iframe = document.createElement('iframe');
    iframe.className = 'youtube-frame';
    iframe.src = 'https://www.youtube-nocookie.com/embed/' + encodeURIComponent(id) + '?autoplay=1&rel=0&controls=1&playsinline=1';
    iframe.title = poster.getAttribute('aria-label') || 'Bremsecu video';
    iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
    iframe.allowFullscreen = true;
    poster.replaceChildren(iframe);
  }, { once: true });
});


const uiOrbit = document.querySelector('[data-ui-orbit]');
if (uiOrbit) {
  const uiShots = [...uiOrbit.querySelectorAll('.ui-shot')];
  const uiLabel = document.querySelector('[data-ui-label]');
  const uiNames = [
    'Login',
    'Vehicle Entry',
    'ISO 7638 Voltage',
    'ISO 12098 Voltage',
    'Cable Test',
    'ISO 7638 Cable',
    'ISO 12098 Cable',
    'CAN Termination',
    'ISO 7638 Tractor CAN',
    'ISO 7638 Trailer CAN',
    'ISO 12098 Tractor CAN',
    'ISO 12098 Trailer CAN',
    'Lamp Test',
    'Reports',
    'Settings',
    'Battery Status'
  ];
  let uiCurrent = 0;
  let uiTimer;

  function renderUiOrbit() {
    uiShots.forEach((shot, index) => {
      const relative = (index - uiCurrent + uiShots.length) % uiShots.length;
      let slot = 6;

      if (uiCurrent === 0 && index === 0) slot = 2;
      else if (uiCurrent === 0 && index > 0 && index <= 3) slot = index + 2;
      else if (relative === 0) slot = 2;
      else if (relative === 1) slot = 3;
      else if (relative === 2) slot = 4;
      else if (relative === 3) slot = 5;
      else if (relative === uiShots.length - 1) slot = 1;
      else if (relative === uiShots.length - 2) slot = 0;

      shot.setAttribute('data-slot', String(slot));
    });

    if (uiLabel) uiLabel.textContent = uiNames[uiCurrent] || 'Bremsecu G1';
  }

  function startUiOrbit() {
    clearInterval(uiTimer);
    uiTimer = setInterval(() => {
      uiCurrent = (uiCurrent + 1) % uiShots.length;
      renderUiOrbit();
    }, 1000);
  }

  renderUiOrbit();
  startUiOrbit();

  uiOrbit.addEventListener('mouseenter', () => clearInterval(uiTimer));
  uiOrbit.addEventListener('mouseleave', startUiOrbit);
  uiOrbit.addEventListener('focusin', () => clearInterval(uiTimer));
  uiOrbit.addEventListener('focusout', startUiOrbit);
}
