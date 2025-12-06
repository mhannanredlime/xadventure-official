<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ATV/UTV Adventure Packages</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .package-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.2s;
        }

        .package-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .package-image {
            height: 180px;
            object-fit: cover;
            width: 100%;
        }

        .package-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .package-price {
            font-weight: 500;
            color: #333;
            margin-bottom: 1rem;
        }

        .btn-add-cart {
            border-radius: 25px;
            padding: 0.5rem 1.2rem;
            font-weight: 500;
        }

        .booking-summary {
            max-height: 400px;
            overflow-y: auto;
        }

        .remove-item {
            cursor: pointer;
            color: #dc3545;
        }

        @media (max-width: 576px) {
            .package-card {
                margin-bottom: 20px;
            }
        }

        /* Hide the template card */
        #package-template {
            display: none;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div class="container py-5">
        <h2 class="mb-4">ATV/UTV Adventure Packages</h2>

        <!-- Package container -->
        <div class="row g-4" id="packageContainer">

            <!-- Template card (hidden) -->
            <div class="col-md-6 col-lg-4" id="package-template">
                <div class="package-card bg-white p-3">
                    <img src="https://via.placeholder.com/400x180" alt="Package Image" class="package-image mb-3">
                    <div class="package-title">Package Title</div>
                    <div class="package-description mb-1">Description for 1–3 Riders</div>
                    <div class="package-price mb-2">Starting from BDT 0</div>

                    <label for="riders_TPL" class="form-label">Select Riders:</label>
                    <select class="form-select mb-2" id="riders_TPL">
                        <option value="1">1 Rider</option>
                        <option value="2">2 Riders</option>
                        <option value="3">3 Riders</option>
                    </select>

                    <label for="date_TPL" class="form-label">Select Date:</label>
                    <input type="date" class="form-control mb-2" id="date_TPL" min="{{ date('Y-m-d') }}">

                    <label for="time_TPL" class="form-label">Select Time Slot:</label>
                    <select class="form-select mb-3" id="time_TPL">
                        <option value="09:00 AM">09:00 AM</option>
                        <option value="12:00 PM">12:00 PM</option>
                        <option value="03:00 PM">03:00 PM</option>
                        <option value="06:00 PM">06:00 PM</option>
                    </select>

                    <button class="btn btn-outline-danger w-100 btn-add-cart">Add to Cart</button>
                </div>
            </div>

        </div>

        <!-- Booking Summary -->
        <div class="mt-5">
            <h3>Booking Summary</h3>
            <div class="booking-summary list-group mb-3" id="bookingSummary">
                <div class="list-group-item">No packages added yet.</div>
            </div>
            <button class="btn btn-success w-100" id="checkoutBtn" onclick="checkout()" disabled>Proceed to
                Checkout</button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            const packages = [{
                    title: 'Explorer Adventure',
                    price: 13900,
                    img: 'https://media.gettyimages.com/id/1183836478/photo/quad-bike-fun.jpg?s=612x612&w=gi&k=20&c=TgZg32ljA_3ePFRuGKjcnzFLnaFzvnVIJuGVCb4VRqg='
                },
                {
                    title: 'Kids Zone',
                    price: 650,
                    img: 'https://media.gettyimages.com/id/1183836478/photo/quad-bike-fun.jpg?s=612x612&w=gi&k=20&c=TgZg32ljA_3ePFRuGKjcnzFLnaFzvnVIJuGVCb4VRqg='
                },
                {
                    title: 'Ground Activity Challenge',
                    price: 400,
                    img: 'https://media.gettyimages.com/id/1183836478/photo/quad-bike-fun.jpg?s=612x612&w=gi&k=20&c=TgZg32ljA_3ePFRuGKjcnzFLnaFzvnVIJuGVCb4VRqg='
                },
                {
                    title: 'Sunset ATV Ride',
                    price: 15000,
                    img: 'https://media.gettyimages.com/id/1183836478/photo/quad-bike-fun.jpg?s=612x612&w=gi&k=20&c=TgZg32ljA_3ePFRuGKjcnzFLnaFzvnVIJuGVCb4VRqg='
                },
                {
                    title: 'Mountain UTV Adventure',
                    price: 22000,
                    img: 'https://media.gettyimages.com/id/1183836478/photo/quad-bike-fun.jpg?s=612x612&w=gi&k=20&c=TgZg32ljA_3ePFRuGKjcnzFLnaFzvnVIJuGVCb4VRqg='
                }
            ];

            const $template = $('#package-template');
            const $container = $('#packageContainer');

            packages.forEach((pkg, index) => {
                const $card = $template.clone().removeAttr('id').show();

                // Update IDs
                const idSuffix = index + 1;
                $card.find('#riders_TPL').attr('id', 'riders' + idSuffix);
                $card.find('#date_TPL').attr('id', 'date' + idSuffix);
                $card.find('#time_TPL').attr('id', 'time' + idSuffix);

                $card.find('label[for="riders_TPL"]').attr('for', 'riders' + idSuffix);
                $card.find('label[for="date_TPL"]').attr('for', 'date' + idSuffix);
                $card.find('label[for="time_TPL"]').attr('for', 'time' + idSuffix);

                // Update content
                $card.find('.package-title').text(pkg.title);
                $card.find('.package-price').text(`Starting from BDT ${pkg.price.toLocaleString()}`);
                $card.find('.package-image').attr('src', pkg.img);

                // Update button onclick
                $card.find('.btn-add-cart').attr('onclick',
                    `addToCart('${pkg.title}', ${pkg.price}, 'riders${idSuffix}','date${idSuffix}','time${idSuffix}')`
                );

                // Append card
                $container.append($card);
            });
        });

        // Cart functionality
        const cart = [];

        function addToCart(packageName, basePrice, ridersId, dateId, timeId) {
            const riders = $('#' + ridersId).val();
            const date = $('#' + dateId).val();
            const time = $('#' + timeId).val();

            if (!date) {
                alert('Please select a date!');
                return;
            }

            cart.push({
                packageName,
                basePrice,
                riders,
                date,
                time
            });
            renderCart();
        }

        function removeFromCart(index) {
            cart.splice(index, 1);
            renderCart();
        }

        function renderCart() {
            const $summary = $('#bookingSummary');
            $summary.empty();

            if (cart.length === 0) {
                $summary.html('<div class="list-group-item">No packages added yet.</div>');
                $('#checkoutBtn').prop('disabled', true);
                return;
            }

            cart.forEach((item, index) => {
                const price = item.basePrice * item.riders;
                const elHtml = `
                <div class="list-group-item d-flex justify-content-between align-items-center">
                  <div>
                    <strong>${item.packageName}</strong><br>
                    ${item.riders} Rider(s), ${item.date} at ${item.time} <br>
                    Price: BDT ${price.toLocaleString()}
                  </div>
                  <div class="remove-item" onclick="removeFromCart(${index})">&times;</div>
                </div>`;
                $summary.append(elHtml);
            });

            $('#checkoutBtn').prop('disabled', false);
        }

        function checkout() {
            let message = 'You have booked the following packages:\n\n';
            cart.forEach(item => {
                const price = item.basePrice * item.riders;
                message +=
                    `${item.packageName} - ${item.riders} Rider(s) - ${item.date} at ${item.time} - BDT ${price.toLocaleString()}\n`;
            });
            alert(message + '\nThank you for your booking!');
        }
    </script>
</body>

</html>
