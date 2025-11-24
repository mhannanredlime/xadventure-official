# ATV/UTV Adventure Booking System

A complete Laravel-based adventure booking system for ATV/UTV tours with admin management and payment processing.

## Features

### Frontend (Public)
- **Package Browsing**: View available adventure packages (ATV, UTV, Regular)
- **Dynamic Pricing**: Weekday/weekend pricing with availability checking
- **Shopping Cart**: Add packages to cart with date and slot selection
- **Booking Flow**: Complete checkout process with customer information
- **Payment Integration**: AmarPay payment gateway integration
- **Promo Codes**: Apply discount codes during checkout

### Admin Panel (Protected)
- **Dashboard**: Overview with statistics and quick actions
- **Vehicle Management**: CRUD for vehicle types and individual vehicles
- **Package Management**: Create and manage adventure packages with variants
- **Availability Calendar**: Set capacity, day-offs, and price overrides
- **Reservation Management**: View, filter, and manage all bookings
- **Promo Code Management**: Create and manage discount codes
- **Payment Tracking**: Monitor payment status and transaction history

### Technical Features
- **Database Schema**: Complete relational database with 13 tables
- **Eloquent Models**: Full relationships and business logic
- **API Endpoints**: RESTful APIs for frontend functionality
- **Payment Processing**: Secure payment gateway with IPN handling
- **Authentication**: Admin-only access with middleware protection
- **Validation**: Comprehensive form validation and error handling

## Database Schema

### Core Entities
- **Users**: Admin users with authentication
- **Customers**: Customer information and contact details
- **Vehicle Types**: ATV, UTV, Regular categories
- **Vehicles**: Individual vehicles with details and images
- **Packages**: Adventure packages with variants
- **Package Variants**: Different configurations (single/double rider)
- **Variant Prices**: Weekday/weekend pricing
- **Schedule Slots**: Time slots (morning, afternoon, full day)
- **Availabilities**: Daily capacity and pricing management
- **Reservations**: Booking records with status tracking
- **Reservation Items**: Line items for each booking
- **Payments**: Payment records and transaction history
- **Promo Codes**: Discount codes with validation rules
- **Promo Redemptions**: Usage tracking for promo codes

## Installation

### Prerequisites
- PHP 8.2+
- MySQL 8.0+
- Composer
- Node.js (for asset compilation)

### Setup Steps

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd atvutv
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database configuration**
   ```bash
   # Update .env file with your database credentials
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=atv_utv_db
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. **Run migrations and seeders**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. **Configure AmarPay (optional)**
   ```bash
   # Add to .env file
   AMARPAY_STORE_ID=your_store_id
   AMARPAY_SIGNATURE_KEY=your_signature_key
   AMARPAY_SANDBOX=true
   ```

7. **Start the development server**
   ```bash
   php artisan serve
   ```

## Access Information

### Admin Panel
- **URL**: `http://127.0.0.1:8000/admin`
- **Login**: `admin@example.com`
- **Password**: `password`

### Frontend
- **URL**: `http://127.0.0.1:8000`
- **Public access**: No login required

## API Endpoints

### Frontend APIs
- `GET /api/variants` - Get package variants for a specific date
- `GET /api/availability` - Check availability for a variant/date/slot

### Payment APIs
- `POST /payment/initiate` - Initiate payment with AmarPay
- `POST /payment/ipn` - AmarPay IPN webhook handler
- `GET /payment/success` - Payment success page
- `GET /payment/fail` - Payment failure page
- `GET /payment/cancel` - Payment cancellation page

## Admin Routes

### Resource Routes
- `admin/vehicle-types` - Vehicle type management
- `admin/vehicles` - Vehicle management
- `admin/packages` - Package management
- `admin/promo-codes` - Promo code management
- `admin/reservations` - Reservation management
- `admin/availabilities` - Availability calendar

### Dashboard
- `admin/` - Main dashboard with statistics

## File Structure

```
atvutv/
├── app/
│   ├── Http/Controllers/
│   │   ├── Admin/           # Admin controllers
│   │   ├── Frontend/        # Frontend controllers
│   │   └── Auth/           # Authentication
│   ├── Models/             # Eloquent models
│   └── Services/           # Business logic services
├── database/
│   ├── migrations/         # Database migrations
│   └── seeders/           # Data seeders
├── resources/views/
│   ├── admin/             # Admin views
│   ├── frontend/          # Frontend views
│   └── layouts/           # Layout templates
└── routes/
    └── web.php            # All web routes
```

## Configuration

### AmarPay Settings
The system is configured for AmarPay payment gateway. Update the following in your `.env` file:

```env
AMARPAY_STORE_ID=your_store_id
AMARPAY_SIGNATURE_KEY=your_signature_key
AMARPAY_SANDBOX=true
AMARPAY_SUCCESS_URL=/payment/success
AMARPAY_FAIL_URL=/payment/fail
AMARPAY_CANCEL_URL=/payment/cancel
AMARPAY_IPN_URL=/payment/ipn
```

### Database Configuration
The system uses MySQL by default. Ensure your database is properly configured in the `.env` file.

## Development

### Adding New Features
1. Create migrations for database changes
2. Update models with relationships
3. Create controllers for business logic
4. Add routes in `routes/web.php`
5. Create views for user interface
6. Update tests if applicable

### Code Style
- Follow Laravel conventions
- Use proper validation in controllers
- Implement proper error handling
- Follow PSR-12 coding standards

## Security

- All admin routes are protected with authentication and admin middleware
- Payment endpoints include signature verification
- Form validation prevents malicious input
- CSRF protection on all forms
- SQL injection protection through Eloquent ORM

## Support

For support and questions, please refer to the project documentation or contact the development team.

## License

This project is licensed under the MIT License.
