@extends('layouts.frontend')

@section('title', 'About')

@section('content')
    <section class="hero-section-about">
        <div>
            <h1>About Us</h1>
            <p class="breadcrumb-custom">Home / About Us</p>
        </div>
    </section>

    <section class="py-5 animation-img" style="min-height: 100vh;">
        <div class="container">
            <div class="row align-items-center g-4">
                <div class="col-lg-6">
                    <img src="{{ asset('frontEnd/images/atv4.jpg') }}" alt="" class="img-fluid">
                </div>
                <div class="col-lg-6">
                    <p class="text-orange fw-bold mt-5">The Ultimate Adventure Park in Bangladesh</p>
                    <h2 class="fw-bold">Welcome to Xtreme Adventure Bandarban</h2>
                    <p class="text-muted">The most thrilling outdoor adventure park in Bangladesh, nestled in the stunning
                        Bandarban Hills!</p>
                    <p class="text-muted">Experience heart-racing ATV & UTV off-road rides, daring treetop challenges, and
                        exciting team games surrounded by breathtaking nattire. Whether you're an adrenaline junkie, family
                        explorer, or corporate team courses are built to make you feel alive. our trails and courses are
                        built to make you feel alive</p>
                    <p class="text-muted">Every activity is quided by trained professionals, using top Ter safety gear,
                        ensuring you can enjoy the adventure worry-free. From roaring engines to sky-high climbs Xtreme
                        Adventure delivers the perfect mix of fun, thrill, and nature.</p>
                    <a href="#" class="btn btn-orange mt-3 jatio-bg-color">About Us →</a>
                </div>

            </div>
        </div>
    </section>
    <section class="py-5 animation-img" style="min-height: 100vh;">
        <div class="container">
            <div class="row align-items-center g-4">
                <div class="col-lg-6">
                    <img src="{{ asset('frontEnd/images/atv7.jpg') }}" alt="" class="img-fluid">
                </div>
                <div class="col-lg-6">
                    <p class="text-orange fw-bold">Xtreme Adventure Bandarban</p>
                    <h2 class="fw-bold">Off-Road, Treetop & Outdoor Fun</h2>
                    <p class="text-muted">Xtreme Adventure Bandarban | Off-Road, Treetop & Outdoor Fun-Experience the thrill
                        of ATV and UTV rides. treetop adventures, hiking, and climbing at Bangladesh's premier adventure
                        resort. Nestled in the scenic hills of Bandarban, Xtreme Adventure offers the perfect blend of
                        adrenaline, nature, and relaxation. Whether you're an adventure seeker or simply want to escape the
                        city and explore the great outdoors, this resort promises unforgettable excitement and breathtaking
                        views at every turn</p>

                </div>

                <div class="col-lg-6">

                    <p class="text-muted">Experience thrilling ATV nides, treetop challenges, and outdoor adventures at
                        Xtreme Adventure Bandartian-Bangladesh's ultimate adventure part</p>
                    <p class="text-muted">Nestled in the breathtaking hills of Bandarban, Xtreme Adventure Resort offers a
                        one-of-a-kind outdoor experience for thrill seekers and nature lovers alike. Ride through rugged
                        mountain trails on powerful ATVs and UT, test your endurance With hiking and climbing challenges,
                        and enjoy panoramic views that capture the wild beauty of the Chittagong Hill Tracts</p>
                    <p class="text-muted">Whether you're looking for an adrenaline pumping escape or a refreshing getaway
                        surrounded by nattire, rette Adventore Bandarban has something for everyone With expert quides,
                        modern safety measures, and a serene resort anmosphere, its the pertva nesmanion for femies, bimde
                        and tren Come and explore Basadenitis premier adventure resortere exces tranquillly in the heart of
                        nature</p>


                </div>
                <div class="col-lg-6">
                    <img src="{{ asset('frontEnd/images/atv5.jpg') }}" alt="" class="img-fluid">
                </div>
            </div>
        </div>
    </section>
    <!-- CTA -->
    <section>
        <div class="container my-5 ">
            <div class="hero-section-down shadow">
                <div>

                    <p>Discover the heart-pounding excitement of Xivreme Adventure Bandarban, the top adventure park in
                        Bangladesh! Enjoy epic ATV & UTV off-mod rides, treetop challenges, archery, team-building games,
                        and more in the stunning Bandarban Hills. Safe, eco-friendly, and unforgettable - it's the perfect
                        destination for the seekers, families, and corporate groups.</p>

                </div>
            </div>
        </div>
    </section>
    <section style="min-height: 100vh;">
        <div class="container" style="margin-left: 15%;">
            <div class=" rounded-3 d-flex flex-column flex-md-row gap-3 p-3" style="color: black;">

                <div class="card-body jatio-bg-color p-4 rounded-3" style="color: white; width: 100%; max-width: 27rem;">
                    <h5 class="card-title">Our Mission</h5><br>
                    <p class="card-text">To make Bandarban the adventure capital of Bangladesh, inspiring locals and
                        travelers to reconnect with nature through thrill, teamwork, and discovery.</p>
                    <p>We believe adventure isn't just an activity - it's a mindset.</p>
                    <p>Every ride, climb, or swing is a reminder that life's greatest moments happen when you step outside
                        your comfort zone.</p>
                </div>
                <div class="card-body bg-white shadow-sm p-4 rounded-3 border" style="width: 100%; max-width: 27rem;">
                    <h5 class="card-title jatio-color">Our Values</h5><br>
                    <ul class="values-list">
                        <li class="mb-3">
                            <strong>Safety First</strong> – Professionally guided, fully equipped, always secure.
                        </li>
                        <li class="mb-3">
                            <strong>Adventure Always</strong> – Every experience designed for maximum thrill.
                        </li>
                        <li class="mb-3">
                            <strong>Team Spirit</strong> – Built for bonding, laughter, and shared memories.
                        </li>
                        <li>
                            <strong>Eco-Respect</strong> – Nature is our playground, and we protect it fiercely.
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
    <!-- CTA -->
    <section>
        <div class="container">
            <div class="hero-section-down shadow">
                <div>
                    <h1>Feel The Adventure Experience With Us,<br>Don't Hesitate To <a href="{{ route('contact.index') }}"
                            style="color: #ff6b35; text-decoration: none;">Contact Us</a> !</h1>
                    <p>Our team is ready to help you plan the perfect day —</p>
                    <p>whether it’s a solo ride, family outing, or group event.</p>
                    <p>Let’s make your adventure unforgettable!</p>
                    <a href="#" class="btn btn-orange">Get Appointment →</a>
                </div>
            </div>
        </div>
    </section>
@endsection
