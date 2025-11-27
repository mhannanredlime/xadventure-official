@extends('layouts.frontend')

@section('title', 'Hard Cut Zoom Slider + Navbar')


@section('content')
    <!-- Hero Slider -->
    <div class="hero-slider">
        <section class="hero-section overlay-text-home-hero-slider">
            <div class="hero-overlay"></div>

            <div class="social-icons" style="padding-right: 3%;">
                <a href="https://www.facebook.com/xadventure" target="_blank"><i class="bi bi-facebook"></i></a>
                <a href="#"><i class="bi bi-twitter"></i></a>
                <a href="#"><i class="bi bi-instagram"></i></a>
                <a href="#"><i class="bi bi-linkedin"></i></a>
            </div>

            <div class="container hero-content text-start slide-left">
                <h1 class="hero-title">
                    Bangladesh’s First & Only <br>
                    <span>UTV & ATV Off-Road Trail Experience</span><br>
                    Only Full-Spectrum Outdoor <br> Adventure Park
                </h1>

                <div class="mt-4 d-flex align-items-center">
                    <a href="{{ route('contact') }}" class="btn btn-orange jatio-bg-color primary-btn-border-radius">Contact Us →</a>
                    <a href="#" id="playVideoBtn" class="play-btn" data-bs-toggle="modal"
                        data-bs-target="#videoModal"><i class="bi bi-play-fill"></i></a>
                    <span class="ms-2">Play Video</span>


                </div>
                <!-- Video Modal -->
                <!-- Video Modal -->
                <div class="modal fade" id="videoModal" tabindex="-1" aria-labelledby="videoModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content bg-transparent border-0">
                            <div class="modal-body p-0 position-relative">
                                <button type="button" id="closeVideoBtn"
                                    class="btn-close btn-close-white position-absolute top-0 end-0 m-3" aria-label="Close">
                                </button>

                                <div class="ratio ratio-16x9">
                                    <iframe id="youtubeVideo" class="rounded-3" src="" title="Adventure Park Video"
                                        frameborder="0" allow="autoplay; encrypted-media" allowfullscreen>
                                    </iframe>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- end video maodal -->
            </div>
        </section>
        <img src="images/atv1.jpg" class="active" alt="1">
        <img src="images/atv2.jpg" alt="2">
        <img src="images/nature2.jpg" alt="3">
        <img src="images/atv4.jpg" alt="3">
    </div>

    <!-- Logo slider -->
    <div class="container py-5">
        <div class="slider1-container">
            <div class="slider1-track" id="logoslider1">
                <div class="logo-item"><img src="images/Cleverland-Logo.png" alt="Logo 1"></div>
                <div class="logo-item"><img src="images/north-carolina-Logo.png" alt="Logo 2"></div>
                <div class="logo-item"><img src="images/riverside-Logo.png" alt="Logo 3"></div>
                <div class="logo-item"><img src="images/colombus-Logo-KHA97GX.png" alt="Logo 4"></div>
                <div class="logo-item"><img src="images/arlington-Logo.png" alt="Logo 5"></div>
            </div>
        </div>
    </div>

    <!-- Pricing section -->
    <section class="pricing-section">
        <div class="container">
            <div class="row justify-content-center">
                @forelse($regularPackages as $package)
                    @php
                        $firstVariant = $package->variants->first();
                        $firstPrice = $firstVariant ? $firstVariant->prices->first() : null;
                        $price = $package->display_starting_price ?? ($firstPrice ? $firstPrice->amount : 0);
                        $capacity = $firstVariant ? $firstVariant->capacity : 1;
                        $priceType = $capacity > 1 ? 'group' : 'person';

                        // Define features dynamically based on capacity
                        $features = [];
                        if ($capacity > 1) {
                            // Group packages
                            $features = [
                                $capacity . ' Persons',
                                $capacity >= 10 ? 'Private Gazebo' : ($capacity >= 5 ? 'Premium Gazebo' : 'Get Gazebo'),
                                'Pro Instructor',
                                'Free Soft Drink',
                                'Free Four Towel',
                            ];
                        } else {
                            // Individual packages
                            $features = [
                                '1 Person',
                                'Get Gazebo',
                                'Pro Instructor',
                                'Free Soft Drink',
                                'Free Four Towel',
                            ];
                        }
                    @endphp
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="card pricing-card">
                            <div class="pricing-header">
                                <h3 class="pricing-title">{{ $package->name }}</h3>
                                <div class="pricing-price">Starting from ৳{{ number_format($price) }}<span
                                        class="pricing-period">/{{ $priceType }}</span></div>
                            </div>
                            <div class="card-body pricing-body">
                                <ul class="pricing-features">
                                    @foreach ($features as $feature)
                                        <li><i class="bi  bi-check"></i> {{ $feature }}</li>
                                    @endforeach
                                </ul>
                                <a href="{{ route('custom-packages') }}" class="btn btn-pricing">Learn More</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center">
                        <p class="text-muted">No packages available at the moment.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- 3rd pera -->
    <section class="py-5 animation-img" style="min-height: 100vh; position: relative; z-index: 1; overflow: hidden;">
        <div class="container">
            <div class="container-fluid h-100">
                <div class="row align-items-center h-100 flex-column flex-md-row">
                    <div class="col-md-5 d-flex justify-content-center align-items-center mb-4 mb-md-0">
                        <img src="images/multi.png" alt="Left Image" class="img-fluid"
                            style="max-width: 95%;min-width: 95%; max-height: 95%; height: auto;">
                    </div>
                    <div
                        class="col-md-7 d-flex flex-column justify-content-center align-items-start px-4 text-dark text-center text-md-start">
                        <h1 class=" mb-3 " style="color: #FC692A; font-size: 16px !important;">Welcome To Xtreme Adventure
                            Bandarban</h1>
                        <h2 class="display-7 fontFamily mb-3">Your Ultimate Outdoor Destination in Bandarban</h2>
                        <p class="fw-semibold text-secondary mt-3">
                            Bangladesh’s Most Complete Outdoor Adventure Park <br>
                            <span class="fst-italic text-muted">
                                ATV Trails • UTV Rides • Challenge Courses • Team Games & More
                            </span>
                        </p>
                        <ul class="list-unstyled mt-4">
                            <li class="mb-2"><i class="bi bi-check-lg orange-check"></i> Guided UTV & UTV Trail Rides
                            </li>
                            <li class="mb-2"><i class="bi bi-check-lg orange-check"></i> Ground & Tree Challenge Courses
                            </li>
                            <li class="mb-2"><i class="bi bi-check-lg orange-check"></i> Kids Fun zone (2 to 10 Years
                                old)
                            </li>
                            <li class="mb-2"><i class="bi bi-check-lg orange-check"></i> Archery and Target Games</li>
                            <li class="mb-2"><i class="bi bi-check-lg orange-check"></i> Human Foosball & Volleyball
                                Court
                            </li>
                            <li class="mb-2"><i class="bi bi-check-lg orange-check"></i> Corporate & School
                                Team-Building
                                Activities</li>
                        </ul>
                        <a href="#" class="btn btn-orange mt-4">
                            About Us <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Count Down -->
    <section class="counter-section" id="stats">
        <div class="container">
            <div class="row g-4 justify-content-center text-center">
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="counter-box">
                        <img src="https://img.icons8.com/ios-filled/50/thumb-up.png" alt="Service Guarantee">
                        <h2><span class="counter" data-target="99">0</span>%</h2>
                        <p>Service Guarantee</p>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="counter-box">
                        <img src="https://img.icons8.com/ios-filled/50/user.png" alt="Happy Customer">
                        <h2><span class="counter" data-target="579">0</span>+</h2>
                        <p>Happy Customer</p>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="counter-box">
                        <img src="https://img.icons8.com/ios-filled/50/certificate.png" alt="Certified">
                        <h2><span class="counter" data-target="99">0</span>%</h2>
                        <p>Certified</p>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="counter-box">
                        <img src="https://img.icons8.com/ios-filled/50/medal.png" alt="Professional Team">
                        <h2><span class="counter" data-target="49">0</span>+</h2>
                        <p>Professional Team</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services intro -->
    <div class="container text-center my-5 fade-up">
        <p class="text-orange fw-bold mb-2" style="color: #ff6a00;">Our Services</p>
        <h2 class="fw-bold mb-3">Bangladesh’s Most Complete Outdoor Adventure Park</h2>
        <h5 class="text-secondary mb-4">ATV Trails · UTV Rides · Challenge Courses · Team Games & More</h5>
        <p class="text-muted px-lg-5">
            Push your limits, build trust, and create unforgettable memories in our dynamic <strong>Challenge
                Course</strong>—a thrilling combination of
            <strong>Ground Challenges</strong>, <strong>Tree Activities</strong>, and a high-flying
            <strong>Aerial Ropes Course</strong>.
        </p>
    </div>

    <!-- Learn More cards -->
    <section>
        <div class="container-fluid full-cart-section animation-img" style="min-height: 100vh;">
            <div class="container">
                <div class="row g-4">
                    <div class="col-12 col-sm-6 col-lg-4 cart-parent-div">
                        <div class="image-card">
                            <img src="images/utvl1.jpg" alt="Challenge" class="img-fluid">
                            <div class="overlay-card">
                                <h5>Challenge Courses</h5>
                                <p>Get Dirty. Get Dizzy. Get Wild. This isn't your average walk in the woods—this is the
                                    ultimate off-ground and on-ground challenge in the hills of Bandarban.</p>
                                <a href="#">Learn More</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-4 cart-parent-div">
                        <div class="image-card">
                            <img src="images/atvl1.jpg" alt="ATV Ride" class="img-fluid">
                            <div class="overlay-card">
                                <h5>ATV Trail Ride</h5>
                                <p>Whether you're a thrill-seeker, nature lover, or first-timer, our trails are designed to
                                    deliver adrenaline, serenity, and everything in between.</p>
                                <a href="#">Learn More</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-4 cart-parent-div">
                        <div class="image-card">
                            <img src="images/nature3.jpg" alt="Kids Fun Zone" class="img-fluid">
                            <div class="overlay-card">
                                <h5>Kids Fun Zone</h5>
                                <p>Let your little explorers have their own adventure! Kids Fun Zone is designed with safety
                                    and smiles in mind—perfect for ages 3–7.</p>
                                <a href="#">Learn More</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Choose us -->
    <section>
        <div class="container-fluid px-4 py-5">
            <div class="row align-items-center">
                <div class="col-lg-6" style="padding-left: 6%;">
                    <h6 class="highlight mb-2">Why Choose Us</h6>
                    <h2 class="fontFamily2 mb-4">Not just an <br>adventure park - Bangladesh’s most complete outdoor
                        experience.</h2>
                    <p class="text-muted mb-3">Whether you're planning a weekend getaway, school trip, or corporate
                        offsite, here’s why <strong>Xtreme Adventure Bandarban</strong> is your top choice for thrill and
                        fun:</p>
                    <p class="text-muted mb-4">We are the <strong>first and only park in Bangladesh</strong> offering a
                        complete mix of:</p>
                    <ul class="feature-list text-muted">
                        <li><strong>Guided ATV & UTV Trail Rides</strong></li>
                        <li><strong>Tree-top Aerial Ropes & Obstacle Courses</strong></li>
                        <li><strong>Ground Challenge Games</strong></li>
                        <li><strong>Archery, Trampoline, and Human Foosball</strong></li>
                        <li><strong>Kids Fun Zone & Team-Building Events</strong></li>
                    </ul>
                    <a href="#" class="cta-btn mt-4 d-inline-block" style="width: 35%;">Learn More →</a>
                </div>
                <div class="col-lg-6 image-section">
                    <img src="images/utvl1.jpg" alt="Adventure" class="img-fluid rounded-2 shadow" />
                </div>
            </div>
        </div>
    </section>

    <!-- Special section images -->
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
            <img src="images/nature1.jpg" class="img-0" alt="Image 1" />
            <img src="images/atv5.jpg" class="img-1" alt="Image 2" />
            <img src="images/natura2.jpg" class="img-2" alt="Image 3" />
            <img src="images/atv7.jpg" class="img-3" alt="Image 4" />
        </div>
    </section>
    <!-- CTA -->
    <section class="hero-section-down shadow">
        <div class="container my-5">
            <h2>Feel The Adventure Experience With Us, <br>Don't Hesitate To <a href="{{ route('contact') }}"
                    class="text-decoration-none jatio-color">Contact Us</a> !</h2>
            <p>Our team is ready to help you plan the perfect day —</p>
            <p>whether it’s a solo ride, family outing, or group event.</p>
            <p>Let’s make your adventure unforgettable!</p>
            <a href="{{ url('contact') }}" class="btn btn-orange">Get Appointment →</a>
        </div>
    </section>
@endsection
