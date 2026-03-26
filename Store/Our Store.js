const header = document.getElementById('main-header');
const heroSection = document.querySelector('.about-section');

// Hide navbar after scrolling past hero section
window.addEventListener('scroll', () => {
  const heroBottom = heroSection.offsetHeight;
  if (window.scrollY > heroBottom - 100) {
    header.classList.add('hide');
  } else {
    header.classList.remove('hide');
  }
});
