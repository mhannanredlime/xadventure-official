
// ======================================
//  full search box
// ======================================

function toggleSearch() {
    document.getElementById('searchOverlay').classList.toggle('active');
}

document.addEventListener('keydown', function (e) {
    if (e.key === "Escape") {
        document.getElementById('searchOverlay').classList.remove('active');
    }
});

// ======================================
//    Hero image slider
// ======================================

const slides = document.querySelectorAll('.hero-slider img');
let currentSlide = 0;

// Only run the slider if there are slides
if (slides.length > 0) {
    setInterval(() => {
        if (slides[currentSlide]) {
            slides[currentSlide].classList.remove('active');
        }
        currentSlide = (currentSlide + 1) % slides.length;
        if (slides[currentSlide]) {
            slides[currentSlide].classList.add('active');
        }
    }, 10000); // change every 10 seconds
}



// ======================================
//   logo slider 
// ======================================

const slider1 = document.getElementById('logoslider1');
if (slider1) {
    const totalLogos = slider1.children.length;
    const visibleItems = 4;
    let currentIndex = 0;

    for (let i = 0; i < visibleItems; i++) {
        const clone = slider1.children[i].cloneNode(true);
        slider1.appendChild(clone);
    }

    setInterval(() => {
        currentIndex++;

        slider1.style.transform = `translateX(-${currentIndex * 25}%)`;

        if (currentIndex >= totalLogos) {
            setTimeout(() => {
                slider1.style.transition = 'none';
                currentIndex = 0;
                slider1.style.transform = 'translateX(0%)';
                slider1.offsetHeight;
                slider1.style.transition = 'transform 0.5s ease-in-out';
            }, 500);
        }
    }, 6000);
}

// ======================================
// 4 ta vertically image 
// ======================================

const container = document.querySelector('#special-section .container-full');
if (container) {
    const panels = container.querySelectorAll('.panel');

    panels.forEach((panel, index) => {
        panel.addEventListener('mouseenter', () => {
            container.classList.remove('hover-0', 'hover-1', 'hover-2', 'hover-3', 'default-active');
            container.classList.add(`hover-${index}`);
        });

        panel.addEventListener('mouseleave', () => {
            container.classList.remove(`hover-${index}`);
            container.classList.add('default-active');
        });
    });
}


// ======================================
// Counter Number  
// ======================================

const counters = document.querySelectorAll('.counter');
    let counted = false;

    const startCounting = () => {
      counters.forEach(counter => {
        const target = +counter.dataset.target;
        let count = 0;

        const update = () => {
          if (count < target) {
            count++;
            counter.innerText = count;
            setTimeout(update, 10);
          } else {
            counter.innerText = target;
          }
        };

        update();
      });
    };

    const statsElement = document.querySelector('#stats');
    if (statsElement) {
        const observer = new IntersectionObserver(entries => {
          entries.forEach(entry => {
            if (entry.isIntersecting && !counted) {
              startCounting();
              counted = true;
            }
          });
        }, { threshold: 0.5 });

        observer.observe(statsElement);
    }



// ======================================
// testimonial 
// ======================================

      const row = document.getElementById('testimonialRow');
      if (row) {
          const cardWidth = 320; // 300px card + 20px margin
          let index = 0;

          function autoScroll() {
            const totalCards = row.children.length;
            const maxIndex = Math.floor(totalCards / 2);
            index = (index + 1) % maxIndex;
            row.style.transform = `translateX(-${index * cardWidth * 2}px)`;
          }

          setInterval(autoScroll, 3000);
      }