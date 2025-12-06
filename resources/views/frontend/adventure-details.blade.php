@extends('layouts.frontend')

@section('title', 'ATV UTV Trial Rides - Xtreme Adventure')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('frontEnd/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('frontEnd/css/csutom.css') }}">
    <link rel="stylesheet" href="{{ asset('frontEnd/css/advanture.css') }}">
    <style>
        .hero-section-about {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('{{ asset('frontEnd/images/Helmet.svg') }}') center center/cover no-repeat;
            min-height: 10vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #fff;
            position: relative;
            text-align: center;
            overflow: hidden;
        }

        h3 {
            font-size: 1rem;
            margin-top: 2rem;
            margin-bottom: 1rem;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 0.5rem;
            color: rgb(200, 0, 0);
            font-weight: 700;
        }

        p,
        li {
            font-size: 1rem;
            line-height: 1.6;
            color: #555;
        }

        ul {
            padding-left: 20px;
        }

        li {
            margin-bottom: 0.5rem;
        }

        .policy-title {
            font-weight: 700;
            color: #343a40;
            margin-top: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .navbar .nav-item.dropdown .dropdown-toggle::after {
            display: none;
        }

        .navbar {
            background-color: rgba(0, 0, 0, 0.8) !important;
            backdrop-filter: blur(10px);
            position: fixed !important;
            top: 0;
            width: 100%;
            z-index: 1000;
        }

        @media (min-width: 992px) {
            .navbar .nav-item.dropdown:hover .dropdown-menu {
                display: block;
                margin-top: 0;
            }
        }
    </style>
@endpush

@section('content')
    <!-- Hero Section -->
    <section class="hero-section-about">
        <div>
            <h1 class="display-3 fw-bold">ATV UTV TRIAL RIDES</h1>
        </div>
    </section>

    <!-- ATV Section -->
    <div class="container my-5">
        <div class="row section-container g-4 align-items-center">
            <div class="col-md-6 order-md-2">
                <div id="carouselATV" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner carousel-container">
                        <div class="carousel-item active">
                            <img src="{{ asset('frontEnd/images/advan-slider-1.svg') }}" class="d-block w-100"
                                alt="ATV Trail Rides 1">
                        </div>
                        <div class="carousel-item">
                            <img src="{{ asset('frontEnd/images/advan-slider-2.svg') }}" class="d-block w-100"
                                alt="ATV Trail Rides 2">
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
                <p class="description">ATV stands for All-Terrain Vehicle — a rugged, 4-wheeled motorbike built for off-road
                    adventures. At Xtreme Adventure Bandarban, our ATVs are single-rider machines designed for one person
                    per vehicle.</p>
                <p class="description">They're fast, nimble, and perfect for those who want full control as they ride
                    through mud, hills, and jungle trails. You'll need balance, coordination, and a sense of adventure — no
                    passengers here, just you and the thrill of the trail!</p>
                <p class="additional-info">Seating Capacity: 1–2 riders per ATV (front and back, like a motorbike)</p>
                <p class="additional-info">Driver Requirement: 18+ with a valid motorcycle license</p>
                <a href="{{ route('frontend.packages.index') }}" class="btn btn-custom mt-3">Book Now</a>
            </div>
        </div>

        <!-- UTV Section -->
        <div class="row section-container g-4 align-items-center">
            <div class="col-md-6">
                <div id="carouselUTV" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner carousel-container">
                        <div class="carousel-item active">
                            <img src="{{ asset('frontEnd/images/advan-slider-2.svg') }}" class="d-block w-100"
                                alt="UTV Trail Rides 1">
                        </div>
                        <div class="carousel-item">
                            <img src="{{ asset('frontEnd/images/advan-slider-1.svg') }}" class="d-block w-100"
                                alt="UTV Trail Rides 2">
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
                <p class="description">A UTV (Utility Terrain Vehicle) is a larger, four-wheeled off-road machine with a
                    side-by-side seating layout — just like a small car. With steering wheels, seat belts, and roll cages,
                    UTVs offer a safer, more stable ride that's perfect for couples or pairs who want to enjoy the trail
                    together.</p>
                <p class="description">It's ideal for those looking for a shared, fun-filled adventure without the intensity
                    of solo riding.</p>
                <p class="additional-info">Seating Capacity: Up to 2 riders per UTV (side-by-side, like a car)</p>
                <p class="additional-info">Driver Requirement: 18+ with a valid driving license</p>
                <a href="{{ route('frontend.packages.index') }}" class="btn btn-custom mt-3">Book Now</a>
            </div>
        </div>
    </div>

    <!-- What to Expect Section -->
    <div class="container adventure-container">
        <h1>What to expect</h1>
        <h2>Xtreme ATV - UTV Trail Riding - Ride Wild in Bandarban!</h2>

        <p>Get ready for mud, speed, and pure excitement at Xtreme Adventure Bandarban! After a quick safety briefing, gear
            up and tear through muddy terrain and rugged Bandarban jungle trails on your own ATV or UTV.</p>
        <p>Whether you're riding solo or with your adventure crew, this off-road thrill ride is the perfect way to crank up
            your trip. Choose from 30-minute to 1-hour ride options — fun guaranteed at every turn.</p>
        <p class="highlight-cta">Book now and make some epic memories in the hills of Bandarban!</p>

        <h3>Experience Highlights</h3>
        <ul>
            <li>Duration: 30-Minute Ride</li>
            <li>Minimum Booking: 1 Person (Single Rider)</li>
            <li>Maximum Capacity: Up to 2 Riders per ATV</li>
            <li>Minimum Age: 18 Years and Above</li>
            <li>License Requirement: Must hold a valid motorcycle driving license</li>
            <li>Safety First: Includes mandatory safety briefing and protective gear (helmet, gloves, etc.)</li>
        </ul>

        <h3>Adventure Route Includes</h3>
        <ul>
            <li>Ride through Forest trail, Off Roading, Mud Riding etc.</li>
            <li>Explore lush Bandarban Forest trail</li>
            <li>Operating Hours</li>
            <li>Daily: 10:00 AM - 6:00 PM</li>
        </ul>

        <h3>Terms & Conditions</h3>
        <ul>
            <li>Ticket Validity: Tickets are valid for 6 months from the date of purchase.</li>
        </ul>

        <div class="policy-title">Booking Policy:</div>
        <ul>
            <li>Advance bookings are highly recommended to secure your preferred time slot.</li>
            <li>Same-day bookings are accepted based on availability.</li>
            <li>Customer Service Hours: Our team is available daily from 10:00 AM - 7:00 PM to assist with bookings and
                inquiries.</li>
            <li>Booking Confirmation: A detailed confirmation message with your ride information will be sent upon
                successful payment.</li>
        </ul>

        <div class="policy-title">Reporting & Arrival Policy:</div>
        <ul>
            <li>Guests are strongly advised to arrive at least 30 minutes before their scheduled ride time for check-in and
                preparation.</li>
            <li>Late arrivals (arriving after the scheduled time) will miss the mandatory safety briefing and will not be
                permitted to ride.</li>
            <li>This will be treated as a last-minute cancellation, and no refunds or rescheduling will be offered.</li>
        </ul>

        <div class="policy-title">Cancellation & Rescheduling Policy:</div>
        <ul>
            <li>Cancellations or schedule changes must be requested at least 48 hours in advance.</li>
            <li>Same-day rescheduling is possible subject to availability and is not guaranteed.</li>
        </ul>

        <div class="policy-title">Weather Policy:</div>
        <ul>
            <li>In case of bad weather, your ride will be rescheduled to the next available suitable time.</li>
            <li>No refunds will be provided due to weather-related changes.</li>
        </ul>
    </div>

    <!-- FAQ Section -->
    <div class="container my-5 faq-section" style="min-height: 80vh !important; padding-top: 10%;">
        <div class="row gx-5 align-items-center">
            <div class="col-lg-6">
                <div class="mb-4">
                    <div class="faq-title">FAQ</div>
                    <h2 class="faq-heading">Frequently Asked Question</h2>
                    <p class="faq-text">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean commodo ligula eget
                        dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur
                        ridiculus mus. Donec quam felis, ultricies nec.</p>
                    <button class="btn btn-accent mt-3">View More <i
                            class="fa-solid fa-arrow-right-long ms-2"></i></button>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="accordion faq-accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                What does adventours include?
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean commodo ligula eget
                                    dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes,
                                    nascetur ridiculus mus.</p>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                Why do adventours activities?
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean commodo ligula eget
                                    dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes,
                                    nascetur ridiculus mus.</p>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                What is adventours advantage?
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean commodo ligula eget
                                    dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes,
                                    nascetur ridiculus mus.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Special Section -->
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

    <!-- Testimonials -->
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

    <!-- Newsletter -->
    <center style="padding: 15% 0%; margin-top: 2%;">
        <h1 style="font-weight: 700;">Join Our Newsletter</h1>
        <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Placeat iste dicta reprehenderit aliquam fugiat quod</p>
        <div class="input-group mb-3" style="width: 40%;">
            <input type="text" class="form-control" placeholder="Enter Your Email Address">
            <span class="input-group-text jatio-bg-color" id="basic-addon2">Sign Up</span>
        </div>
    </center>
@endsection

@push('scripts')
    <script src="{{ asset('frontEnd/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('frontEnd/js/adventure.js') }}"></script>
@endpush
