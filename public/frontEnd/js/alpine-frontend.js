
document.addEventListener('alpine:init', () => {
    // ======================================
    //  Full Search Box
    // ======================================
    Alpine.data('searchOverlay', () => ({
        active: false,
        toggle() {
            this.active = !this.active;
        },
        close() {
            this.active = false;
        },
        init() {
            this.$watch('active', value => {
                if (value) {
                    this.$nextTick(() => this.$refs.searchInput?.focus());
                }
            });
        }
    }));

    // ======================================
    //  Hero Image Slider
    // ======================================
    Alpine.data('heroSlider', () => ({
        currentSlide: 0,
        slides: [],
        interval: null,
        init() {
            // Wait for DOM to be ready to count slides or pass them in via x-init
            this.slides = this.$el.querySelectorAll('img');
            if (this.slides.length > 0) {
                this.slides[0].classList.add('active'); // Ensure first is active
                this.startAutoPlay();
            }
        },
        startAutoPlay() {
            this.interval = setInterval(() => {
                this.next();
            }, 10000);
        },
        next() {
            this.slides[this.currentSlide].classList.remove('active');
            this.currentSlide = (this.currentSlide + 1) % this.slides.length;
            this.slides[this.currentSlide].classList.add('active');
        },
        destroy() {
            clearInterval(this.interval);
        }
    }));

    // ======================================
    //   Logo Slider
    // ======================================
    Alpine.data('logoSlider', () => ({
        currentIndex: 0,
        totalLogos: 0,
        interval: null,
        init() {
            const track = this.$el; // The slider1-track
            this.totalLogos = track.children.length;

            // Clone first 4 items for seamless logic if needed, 
            // but the original script cloned *visibleItems* amount.
            // Let's replicate the logic:
            const visibleItems = 4;
            for (let i = 0; i < visibleItems; i++) {
                if (track.children[i]) {
                    track.appendChild(track.children[i].cloneNode(true));
                }
            }

            this.startAutoPlay();
        },
        startAutoPlay() {
            this.interval = setInterval(() => {
                this.currentIndex++;
                this.$el.style.transform = `translateX(-${this.currentIndex * 25}%)`;
                this.$el.style.transition = 'transform 0.5s ease-in-out';

                if (this.currentIndex >= this.totalLogos) {
                    setTimeout(() => {
                        this.$el.style.transition = 'none';
                        this.currentIndex = 0;
                        this.$el.style.transform = 'translateX(0%)';
                        // Force reflow
                        void this.$el.offsetHeight;
                    }, 500);
                }
            }, 6000);
        },
        destroy() {
            clearInterval(this.interval);
        }
    }));

    // ======================================
    // 4 items vertically image (Hover Panels)
    // ======================================
    Alpine.data('hoverPanels', () => ({
        hoverClass: 'default-active',
        setHover(index) {
            this.hoverClass = `hover-${index}`;
        },
        resetHover() {
            this.hoverClass = 'default-active';
        }
    }));

    // ======================================
    // Counter Number
    // ======================================
    Alpine.data('counter', (targetValue) => ({
        count: 0,
        target: targetValue,
        duration: 2000,
        started: false,
        init() {
            let observer = new IntersectionObserver((entries) => {
                if (entries[0].isIntersecting && !this.started) {
                    this.startCounting();
                    this.started = true;
                }
            }, { threshold: 0.5 });

            observer.observe(this.$el);
        },
        startCounting() {
            let start = 0;
            const step = Math.ceil(this.target / (this.duration / 10)); // approximate step

            let timer = setInterval(() => {
                start += step;
                if (start >= this.target) {
                    this.count = this.target;
                    clearInterval(timer);
                } else {
                    this.count = start;
                }
            }, 10);
        }
    }));

    // ======================================
    // Testimonial
    // ======================================
    Alpine.data('testimonialSlider', () => ({
        index: 0,
        cardWidth: 320,
        interval: null,
        init() {
            this.startAutoPlay();
        },
        startAutoPlay() {
            this.interval = setInterval(() => {
                const totalCards = this.$el.children.length;
                const maxIndex = Math.floor(totalCards / 2);
                this.index = (this.index + 1) % maxIndex;
                this.$el.style.transform = `translateX(-${this.index * this.cardWidth * 2}px)`;
            }, 3000);
        },
        destroy() {
            clearInterval(this.interval);
        }
    }));
});
