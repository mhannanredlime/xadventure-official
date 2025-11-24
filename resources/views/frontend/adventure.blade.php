@extends('layouts.frontend')

@section('title', 'Adventure Tours')

@push('styles')
  <link rel="stylesheet" href="{{ asset('frontEnd/css/advanture.css') }}">
@endpush

@section('content')
  <!-- Hero Section -->
  <section class="hero-section-about">
    <div>
      <h1 class="display-3 fw-bold">ADVENTURE DETAILS</h1>
      <p class="breadcrumb-custom">Home / ADVENTURE</p>
    </div>
  </section>

  <!-- Floating Cart -->
  <div class="floating-cart">
    <div class="d-flex align-items-center justify-content-between w-100">
      <i class="fa-solid fa-cart-shopping cart-icon"></i>
      <div>
        <div class="item-count">{{ $cartCount }}</div>
        <div class="item-text">{{ $cartCount == 1 ? 'Item' : 'Items' }}</div>
      </div>
    </div>
  </div>

  <!-- Adventure Sections -->
  <div class="container my-5">
    <!-- ATV Trail Rides -->
    <div class="row section-container g-4 align-items-center">
      <div class="col-md-6 order-md-2">
        <div id="carouselATV" class="carousel slide" data-bs-ride="carousel">
          <div class="carousel-inner carousel-container">
            <div class="carousel-item active">
              <img src="{{ asset('frontEnd/images/advan-slider-1.svg') }}" class="d-block w-100" alt="ATV Trail Rides 1">
            </div>
            <div class="carousel-item">
              <img src="{{ asset('frontEnd/images/advan-slider-2.svg') }}" class="d-block w-100" alt="ATV Trail Rides 2">
            </div>
          </div>
          <button class="carousel-control-prev" type="button" data-bs-target="#carouselATV" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#carouselATV" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
          </button>
        </div>
      </div>
      <div class="col-md-6 order-md-1 section-content">
        <h2>ATV Trail Rides</h2>
        <p class="subtitle">Fuel Your Fun. Ride Hard. Laugh Loud.</p>
        <p class="additional-info">What is an ATV?</p>
        <p class="description">ATV stands for All-Terrain Vehicle — a rugged, 4-wheeled motorbike built for off-road adventures. At Xtreme Adventure Bandarban, our ATVs are single-rider machines designed for one person per vehicle.</p>
        <p class="description">They're fast, nimble, and perfect for those who want full control as they ride through mud, hills, and jungle trails. You'll need balance, coordination, and a sense of adventure — no passengers here, just you and the thrill of the trail!</p>
        <p class="additional-info">Seating Capacity: 1–2 riders per ATV (front and back, like a motorbike)</p>
        <p class="additional-info">Driver Requirement: 18+ with a valid motorcycle license</p>
        <a href="{{ url('/packages') }}" class="btn btn-custom mt-3">Book Now</a>
      </div>
    </div>

    <!-- UTV Trail Rides -->
    <div class="row section-container g-4 align-items-center">
      <div class="col-md-6">
        <div id="carouselUTV" class="carousel slide" data-bs-ride="carousel">
          <div class="carousel-inner carousel-container">
            <div class="carousel-item active">
              <img src="{{ asset('frontEnd/images/advan-slider-2.svg') }}" class="d-block w-100" alt="UTV Trail Rides 1">
            </div>
            <div class="carousel-item">
              <img src="{{ asset('frontEnd/images/advan-slider-1.svg') }}" class="d-block w-100" alt="UTV Trail Rides 2">
            </div>
          </div>
          <button class="carousel-control-prev" type="button" data-bs-target="#carouselUTV" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#carouselUTV" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
          </button>
        </div>
      </div>
      <div class="col-md-6 section-content">
        <h2>UTV Trail Rides</h2>
        <p class="subtitle">Fuel Your Fun. Ride Hard. Laugh Loud.</p>
        <p class="additional-info">What is an UTV?</p>
        <p class="description">A UTV (Utility Terrain Vehicle) is a larger, four-wheeled off-road machine with a side-by-side seating layout — just like a small car. With steering wheels, seat belts, and roll cages, UTVs offer a safer, more stable ride that's perfect for couples or pairs who want to enjoy the trail together.</p>
        <p class="description">It's ideal for those looking for a shared, fun-filled adventure without the intensity of solo riding.</p>
        <p class="additional-info">Seating Capacity: Up to 2 riders per UTV (side-by-side, like a car)</p>
        <p class="additional-info">Driver Requirement: 18+ with a valid driving license</p>
        <a href="{{ url('/packages') }}" class="btn btn-custom mt-3">Book Now</a>
      </div>
    </div>

    <!-- Treetop Adventure -->
    <div class="row section-container g-4 align-items-center">
      <div class="col-md-6 order-md-2">
        <div id="carouselTreetop" class="carousel slide" data-bs-ride="carousel">
          <div class="carousel-inner carousel-container">
            <div class="carousel-item active">
              <img src="{{ asset('frontEnd/images/advan-slider-3.svg') }}" class="d-block w-100" alt="Treetop Adventure 1">
            </div>
            <div class="carousel-item">
              <img src="{{ asset('frontEnd/images/advan-slider-4.svg') }}" class="d-block w-100" alt="Treetop Adventure 2">
            </div>
          </div>
          <button class="carousel-control-prev" type="button" data-bs-target="#carouselTreetop" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#carouselTreetop" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
          </button>
        </div>
      </div>
      <div class="col-md-6 order-md-1 section-content">
        <h2>Treetop Adventure</h2>
        <p class="subtitle">Swing High, Walk Tall, Feel the Thrill!</p>
        <p class="description">Step into the trees and discover a thrilling world above the ground! Our treetop course includes rope bridges, swinging planks, net crossings, wobbly ladders, and even a mini jungle zip line. Suspended between tall trees, each section brings a new challenge and a big sense of achievement as you make your way through the canopy.</p>
        <p class="description">Don't worry—it's all safe and harnessed, with guides on hand to help. Whether you're testing your balance or simply soaking in the stunning jungle views, the Treetop Adventure is a perfect mix of nature, excitement, and fun for all ages.</p>
        <a href="{{ url('/packages') }}" class="btn btn-custom mt-3">Book Now</a>
      </div>
    </div>

    <!-- Outdoor Course -->
    <div class="row section-container g-4 align-items-center">
      <div class="col-md-6">
        <div id="carouselOutdoor" class="carousel slide" data-bs-ride="carousel">
          <div class="carousel-inner carousel-container">
            <div class="carousel-item active">
              <img src="{{ asset('frontEnd/images/advan-slider-4.svg') }}" class="d-block w-100" alt="Outdoor Course 1">
            </div>
            <div class="carousel-item">
              <img src="{{ asset('frontEnd/images/advan-slider-3.svg') }}" class="d-block w-100" alt="Outdoor Course 2">
            </div>
          </div>
          <button class="carousel-control-prev" type="button" data-bs-target="#carouselOutdoor" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#carouselOutdoor" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
          </button>
        </div>
      </div>
      <div class="col-md-6 section-content">
        <h2>Outdoor Course</h2>
        <p class="subtitle">Charge Through Mud. Climb Like A Beast. Own the Wild!</p>
        <p class="description">Our outdoor obstacle course is packed with heart-pounding action! You'll crawl through mud tunnels, climb over wooden walls, swing across rope ladders, hop between tire steps, and balance your way across narrow beams. Each obstacle is built to test your strength, coordination, and courage—with a whole lot of fun mixed in!</p>
        <p class="description">This is the perfect activity for anyone who loves outdoor physical fun—whether you're a competitive spirit or just here for the laughs. Challenge your friends, race the clock, or enjoy the course at your own pace. Great for adults, teens, and even group events like camps or team-building days!</p>
        <a href="{{ url('/packages') }}" class="btn btn-custom mt-3">Book Now</a>
      </div>
    </div>

    <!-- Kids Adventure -->
    <div class="row section-container g-4 align-items-center">
      <div class="col-md-6 order-md-2">
        <div id="carouselKids" class="carousel slide" data-bs-ride="carousel">
          <div class="carousel-inner carousel-container">
            <div class="carousel-item active">
              <img src="{{ asset('frontEnd/images/advan-slider-5.svg') }}" class="d-block w-100" alt="Kids Adventure 1">
            </div>
            <div class="carousel-item">
              <img src="{{ asset('frontEnd/images/advan-slider-6.svg') }}" class="d-block w-100" alt="Kids Adventure 2">
            </div>
          </div>
          <button class="carousel-control-prev" type="button" data-bs-target="#carouselKids" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#carouselKids" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
          </button>
        </div>
      </div>
      <div class="col-md-6 order-md-1 section-content">
        <h2>Kids Adventure</h2>
        <p class="subtitle">Big Adventures for Little Explorers! Indoor and Outdoor Fun!</p>
        <p class="description">Our Kids Adventure Zone is a colorful, safe, and exciting play space designed just for children aged 2 to 9. It features soft climbing walls, low rope bridges, bright tunnels, mini zip lines, bouncy bridges, ball pits, and outdoor games that let kids laugh, learn, and explore all at once.</p>
        <p class="description">Each two-hour session is packed with variety, helping kids build confidence while having tons of fun. With trained staff on site and shaded areas for parents to relax, it's the perfect mix of adventure and safety for your little ones!</p>
        <a href="{{ url('/packages') }}" class="btn btn-custom mt-3">Book Now</a>
      </div>
    </div>

    <!-- Human Foosball -->
    <div class="row section-container g-4 align-items-center">
      <div class="col-md-6">
        <div id="carouselFoosball" class="carousel slide" data-bs-ride="carousel">
          <div class="carousel-inner carousel-container">
            <div class="carousel-item active">
              <img src="{{ asset('frontEnd/images/advan-slider-6.svg') }}" class="d-block w-100" alt="Human Foosball 1">
            </div>
            <div class="carousel-item">
              <img src="{{ asset('frontEnd/images/advan-slider-5.svg') }}" class="d-block w-100" alt="Human Foosball 2">
            </div>
          </div>
          <button class="carousel-control-prev" type="button" data-bs-target="#carouselFoosball" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#carouselFoosball" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
          </button>
        </div>
      </div>
      <div class="col-md-6 section-content">
        <h2>Human Foosball</h2>
        <p class="subtitle">You Are the Player — Let the Game Begin!</p>
        <p class="description">It's foosball… but you're inside the game! Up to 10 players are strapped into rows like real foosball men—able to slide side-to-side, but not forward or backward. You'll need teamwork, timing, and laughter as you kick, block, and try to score goals while staying connected to your bar.</p>
        <p class="description">This game is super fun for groups, families, and teams looking to bond over something silly and energetic. With a full 1.5-hour session, there's plenty of time to warm up, compete, and even switch sides. It's one of our most popular activities for parties and group adventures!</p>
        <a href="{{ url('/packages') }}" class="btn btn-custom mt-3">Book Now</a>
      </div>
    </div>

    <!-- Target Practice -->
    <div class="row section-container g-4 align-items-center">
      <div class="col-md-6 order-md-2">
        <div id="carouselTarget" class="carousel slide" data-bs-ride="carousel">
          <div class="carousel-inner carousel-container">
            <div class="carousel-item active">
              <img src="{{ asset('frontEnd/images/advan-slider-7.svg') }}" class="d-block w-100" alt="Target Practice 1">
            </div>
            <div class="carousel-item">
              <img src="{{ asset('frontEnd/images/advan-slider-6.svg') }}" class="d-block w-100" alt="Target Practice 2">
            </div>
          </div>
          <button class="carousel-control-prev" type="button" data-bs-target="#carouselTarget" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#carouselTarget" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
          </button>
        </div>
      </div>
      <div class="col-md-6 order-md-1 section-content">
        <h2>Target Practice</h2>
        <p class="subtitle">Channel Your Inner Hunter. Aim for the Wild!</p>
        <p class="additional-info">Archery & Slingshot</p>
        <p class="description">Feel like a jungle warrior as you take on our Target Practice zone! Choose between classic archery with real bows and arrows, or go for the old-school fun of slingshot shooting. Our range includes targets at different distances, skill levels, and fun scoring games to keep things exciting.</p>
        <p class="description">Whether you're competing with friends or just trying something new, this activity is great for all ages. Friendly instructors are on hand to show you the ropes and help you hit your mark. A great combo of focus, fun, and a little friendly competition in nature.</p>
        <a href="{{ url('/packages') }}" class="btn btn-custom mt-3">Book Now</a>
      </div>
    </div>
  </div>

  <!-- FAQ Section -->
  <div class="container my-5 faq-section" style="min-height: 80vh !important; padding-top: 10%;">
    <div class="row gx-5 align-items-center">
      <div class="col-lg-6">
        <div class="mb-4">
          <div class="faq-title">FAQ</div>
          <h2 class="faq-heading">Frequently Asked Question</h2>
          <p class="faq-text">Get answers to common questions about our adventure activities, safety measures, booking process, and what to expect during your visit.</p>
          <button class="btn btn-accent mt-3">View More <i class="fa-solid fa-arrow-right-long ms-2"></i></button>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="accordion faq-accordion" id="faqAccordion">
          <div class="accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                What does adventours include?
              </button>
            </h2>
            <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
              <div class="accordion-body">
                <p>Our adventure tours include professional guides, safety equipment, training sessions, and access to all adventure activities. We provide helmets, safety gear, and ensure all participants are properly briefed before each activity.</p>
              </div>
            </div>
          </div>

          <div class="accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                Why do adventours activities?
              </button>
            </h2>
            <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
              <div class="accordion-body">
                <p>Our adventure activities are designed to provide thrilling experiences while maintaining the highest safety standards. We offer a unique combination of excitement, nature exploration, and skill development that creates unforgettable memories.</p>
              </div>
            </div>
          </div>

          <div class="accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                What is adventours advantage?
              </button>
            </h2>
            <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
              <div class="accordion-body">
                <p>Our advantages include experienced guides, state-of-the-art equipment, diverse activity options, flexible booking, competitive pricing, and a commitment to safety. We provide comprehensive adventure experiences suitable for all skill levels.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Special Section with Vertical Images -->
  <section id="special-section" class="for-100vh">
    <div class="container-full default-active" id="special-container">
      <div class="panel panel-0" data-index="0">
        <div class="label-static">Adult Challenge Courses</div>
        <div class="label-hover"></div>
      </div>
      <div class="panel panel-1" data-index="1">
        <div class="label-static">ATV / UTV Trail Ride</div>
        <div class="label-hover"></div>
      </div>
      <div class="panel panel-2" data-index="2">
        <div class="label-static">Kids Fun Zone</div>
        <div class="label-hover"></div>
      </div>
      <div class="panel panel-3" data-index="3">
        <div class="label-static">Water Activities</div>
        <div class="label-hover"></div>
      </div>
    </div>

    <div class="shared-images">
      <img src="{{ asset('frontEnd/images/4-img-sec-1.jpg') }}" class="img-0" alt="Image 1" />
      <img src="{{ asset('frontEnd/images/4-img-sec-2.jpg') }}" class="img-1" alt="Image 2" />
      <img src="{{ asset('frontEnd/images/4-img-sec-3.jpg') }}" class="img-2" alt="Image 3" />
      <img src="{{ asset('frontEnd/images/4-img-sec-4.jpg') }}" class="img-3" alt="Image 4" />
    </div>
  </section>

  <!-- Testimonials Section -->
  <div class="main-wrapper">
    <div class="left-side">
      <div class="testimonial-container">
        <div class="testimonial-row" id="testimonialRow">
          <div class="testimonial-card">
            <img src="https://i.pravatar.cc/150?img=1" alt="Asif">
            <h5>Asif H.</h5>
            <small>Designer</small>
            <p>Driving a UTV through the hills of Bandarban felt like a scene from a movie!</p>
          </div>
          <div class="testimonial-card">
            <img src="https://i.pravatar.cc/150?img=2" alt="Rumana">
            <h5>Rumana T.</h5>
            <small>Entrepreneur</small>
            <p>Stunning views and perfect mix of thrill and safety!</p>
          </div>
          <div class="testimonial-card">
            <img src="https://i.pravatar.cc/150?img=3" alt="Farhan">
            <h5>Farhan A.</h5>
            <small>Adventurer</small>
            <p>Perfect for adrenaline junkies. Safety and excitement together!</p>
          </div>
          <div class="testimonial-card">
            <img src="https://i.pravatar.cc/150?img=4" alt="Mehzabin">
            <h5>Mehzabin K.</h5>
            <small>Photographer</small>
            <p>Best nature shots while riding. A dream ride!</p>
          </div>
          <div class="testimonial-card">
            <img src="https://i.pravatar.cc/150?img=5" alt="Rafiq">
            <h5>Rafiq I.</h5>
            <small>Tour Guide</small>
            <p>Everyone was thrilled. Well organized!</p>
          </div>
          <div class="testimonial-card">
            <img src="https://i.pravatar.cc/150?img=6" alt="Sadia">
            <h5>Sadia M.</h5>
            <small>Travel Blogger</small>
            <p>So much content in one trip! Unreal experience!</p>
          </div>
        </div>
      </div>
    </div>
    <div class="right-side">
      <p class="jatio-color fw-bold">Our Testimonials</p>
      <h1>Customer Says</h1>
      <p>Real Adventures. Real People. Real Reviews.</p>
      <a href="#" class="btn btn-orange text-white" style="width: 35%;">View More →</a>
    </div>
  </div>

  <!-- Newsletter Section -->
  <center style="padding: 15% 0%; margin-top: 2%;">
    <h1 style="font-weight: 700;">Join Our Newsletter</h1>
    <p>Stay updated with our latest adventures, special offers, and exciting news from Xtreme Adventure Bandarban.</p>

    <div class="input-group mb-3" style="width: 40%;">
      <input type="text" class="form-control" placeholder="Enter Your Email Address">
      <span class="input-group-text jatio-bg-color" id="basic-addon2">Sign Up</span>
    </div>
  </center>
@endsection

@push('scripts')
  <script src="{{ asset('frontEnd/js/adventure.js') }}"></script>
@endpush


