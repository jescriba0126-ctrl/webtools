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

 
    window.addEventListener('scroll', function () {
      const header = document.querySelector('header');
      header.classList.toggle('scrolled', window.scrollY > 50);
    });


    let index = 0;
    const slides = document.getElementsByClassName('mySlides');
    function showSlides() {
      for (let i = 0; i < slides.length; i++) {
        slides[i].style.display = 'none';
      }
      index++;
      if (index > slides.length) index = 1;
      slides[index - 1].style.display = 'block';
      setTimeout(showSlides, 2000);
    }
    showSlides();

