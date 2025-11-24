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
// 4 ta vertically image 
// ======================================

const container = document.querySelector('#special-section .container-full');
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


// ======================================
// testimonial 
// ======================================

const row = document.getElementById('testimonialRow');
const cardWidth = 320; 
const totalCards = row.children.length;


for (let i = 0; i < 3; i++) {
  const clone = row.children[i].cloneNode(true);
  row.appendChild(clone);
}

let index = 0;

function autoScroll() {
  index++;

  row.style.transition = 'transform 0.5s ease-in-out';
  row.style.transform = `translateX(-${index * cardWidth}px)`;


  if (index >= totalCards) {
    setTimeout(() => {
      row.style.transition = 'none';
      index = 0;
      row.style.transform = `translateX(0px)`;
    }, 500); 
  }
}

setInterval(autoScroll, 3000); 