@extends('layouts.frontend')

@section('title', 'Contact Us - Adventure Tours')

@section('content')
    <!-- Contact Hero Section -->
    <section class="contact-hero-section">
        <div class="container">
            <div class="row justify-content-center text-center">
                <div class="col-lg-8">
                    <h1 class="text-white display-4 fw-bold mb-4">Get In Touch</h1>
                    <p class="text-white-50 lead mb-0">Have questions about our adventures? We'd love to hear from you!</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Information Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <i class="bi  bi-map-marker-alt fa-2x text-orange"></i>
                            </div>
                            <h5 class="card-title">Our Location</h5>
                            <p class="card-text text-muted">Xtreme Adventure Bandarban<br>Babunagarpara, Ward No.
                                3,<br>Tongkaboti, Bandarban</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <i class="bi  bi-envelope fa-2x text-orange"></i>
                            </div>
                            <h5 class="card-title">Email Us</h5>
                            <p class="card-text text-muted">info@xadventurebandarban.com</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <i class="bi  bi-phone-alt fa-2x text-orange"></i>
                            </div>
                            <h5 class="card-title">Call Us</h5>
                            <p class="card-text text-muted">01893583010<br>01893585377</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Form Section -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card border-0 shadow">
                        <div class="card-body p-5">
                            <h2 class="text-center mb-4">Send Us a Message</h2>

                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            <form action="{{ route('contact.store') }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">Full Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                            id="name" name="name" value="{{ old('name') }}">
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email Address <span
                                                class="text-danger">*</span></label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                            id="email" name="email" value="{{ old('email') }}">
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="subject" class="form-label">Subject <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('subject') is-invalid @enderror"
                                        id="subject" name="subject" value="{{ old('subject') }}">
                                    @error('subject')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-4">
                                    <label for="message" class="form-label">Message <span
                                            class="text-danger">*</span></label>
                                    <textarea class="form-control @error('message') is-invalid @enderror" id="message" name="message" rows="5">{{ old('message') }}</textarea>
                                    @error('message')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <button type="submit" class="btn btn-orange jatio-bg-color btn-lg px-5">
                                    Send Message
                                </button>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card border-0 shadow">
                        <div class="card-body p-0">
                            <div class="ratio ratio-16x9">
                                <iframe
                                    src="https://www.google.com/maps?q=Xtreme%20Adventure%20Bandarban,%20Babunagarpara,%20Ward%20No.%203,%20Tongkaboti,%20Bandarban&hl=en&z=15&output=embed"
                                    style="border:0; width:100%; height:100%;" allowfullscreen loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade"></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center mb-5">
                    <h2>Frequently Asked Questions</h2>
                    <p class="text-muted">Find answers to common questions about our adventure tours</p>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq1">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapse1">
                                    What is the difference between an ATV and a UTV?
                                </button>
                            </h2>
                            <div id="collapse1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <strong>ATV (All-Terrain Vehicle):</strong> Four-wheeled off-road bike with
                                    front-and-back seating for up to 2 riders, agile for solo or duo thrill-seekers.<br>
                                    <strong>UTV (Utility Terrain Vehicle):</strong> Side-by-side off-road vehicle with
                                    car-like controls and seating for 2 people, offering a stable and comfortable ride.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq2">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapse2">
                                    Do I need to book in advance?
                                </button>
                            </h2>
                            <div id="collapse2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Advance booking is highly recommended to secure your preferred time slot. Same-day
                                    bookings are allowed, but only based on availability.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq3">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapse3">
                                    How early should I arrive before my ride?
                                </button>
                            </h2>
                            <div id="collapse3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Please arrive at least 30 minutes before your scheduled ride time for check-in and
                                    safety preparations. Late arrivals may miss the mandatory safety briefing and will not
                                    be allowed to ride.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq4">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapse4">
                                    Is safety gear provided?
                                </button>
                            </h2>
                            <div id="collapse4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Yes. All riders receive a mandatory safety briefing, and we provide helmets, gloves, and
                                    required protective gear.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
        .text-orange {
            color: #ff6b35 !important;
        }

        .btn-orange {
            background-color: #ff6b35;
            border-color: #ff6b35;
            color: white;
        }

        .btn-orange:hover {
            background-color: #e55a2b;
            border-color: #e55a2b;
            color: white;
        }

        .contact-hero-section {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('/frontEnd/images/nav-1.webp') center/cover;
            padding: 120px 0 80px;
        }

        .accordion-button:not(.collapsed) {
            background-color: #fff3e0;
            color: #ff6b35;
        }

        .accordion-button:focus {
            box-shadow: 0 0 0 0.25rem rgba(255, 107, 53, 0.25);
        }

        /* Ensure navbar is properly visible */
        .navbar {
            background-color: rgba(0, 0, 0, 0.8) !important;
            backdrop-filter: blur(10px);
            position: fixed !important;
            top: 0;
            width: 100%;
            z-index: 1000;
        }

        .navbar-nav .nav-link {
            color: white !important;
            font-weight: 500;
        }

        .navbar-nav .nav-link:hover {
            color: #ff6b35 !important;
        }

        .navbar-nav .nav-link.active {
            color: #ff6b35 !important;
        }

        /* Adjust hero section to account for fixed navbar */
        .contact-hero-section {
            padding-top: 120px !important;
        }
    </style>
@endsection
