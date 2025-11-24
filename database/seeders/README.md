# Database Seeders Documentation

This document explains the improved database seeding system for the ATV/UTV booking application.

## Overview

The seeding system has been completely restructured to provide:
- **Proper order dependency** - Seeders run in the correct sequence
- **Comprehensive error handling** - Detailed logging and error reporting
- **Realistic test data** - Data that mirrors real-world scenarios
- **Non-seasonal slot system** - Removed seasonal variations as requested
- **Proper relationships** - All model relationships are correctly established

## Seeder Order

The seeders run in the following order to ensure proper data relationships:

### 1. Core Data (Step 2)
- `VehicleTypeSeeder` - Creates ATV, UTV, and Regular vehicle types
- `VehicleTypeImageSeeder` - Adds images for vehicle types
- `VehicleSeeder` - Creates individual vehicles with proper relationships
- `CreateSampleVehiclesSeeder` - Additional vehicle samples

### 2. Package System (Step 3)
- `PackageSeeder` - Creates packages with variants and pricing
- `UpdatePackageVehicleTypesSeeder` - Links packages to vehicle types
- `RegularPackageSeeder` - Additional package configurations

### 3. Scheduling System (Step 4)
- `CleanupScheduleSlotsSeeder` - Removes seasonal slots, creates 10 continuous slots (9 AM - 7 PM)
- `AvailabilitySeeder` - Creates 30 days of availability data

### 4. Customer & Promotional Data (Step 5)
- `PromoCodeSeeder` - Creates various promo codes with different discount types
- `CreateSamplePromoCodesSeeder` - Additional promotional offers
- `CustomerSeeder` - Creates customers with associated user accounts
- `CustomerAuthSeeder` - Additional customer authentication data

### 5. Reservations (Step 6)
- `ReservationSeeder` - Creates realistic reservation data with payments

## Key Improvements

### Non-Seasonal Slot System
- **Removed**: Winter, Summer, Spring, Fall, Autumn seasonal slots
- **Added**: 10 continuous hourly slots from 9 AM to 7 PM
- **Benefits**: Simplified booking process, consistent availability

### Comprehensive Availability
- **Duration**: 30 days of availability data
- **Business Rules**: Mondays marked as day off, holidays excluded
- **Capacity Management**: Different capacities for weekdays vs weekends
- **Real-time Updates**: Capacity tracking for reservations

### Realistic Test Data
- **Customers**: 15 customers with complete profiles and user accounts
- **Packages**: 3 package types with multiple variants and pricing
- **Promo Codes**: 8 different promotional offers with various discount types
- **Reservations**: 13 reservations with different statuses and payment records

### Error Handling
- **Detailed Logging**: All errors are logged with file and line information
- **Graceful Failures**: Seeders continue even if some data is missing
- **Progress Reporting**: Real-time feedback on seeding progress
- **Dependency Checks**: Validates required data before proceeding

## Usage

### Run All Seeders
```bash
php artisan db:seed
```

### Run Individual Seeders
```bash
php artisan db:seed --class=PackageSeeder
php artisan db:seed --class=CustomerSeeder
php artisan db:seed --class=AvailabilitySeeder
```

### Fresh Database with Seeding
```bash
php artisan migrate:fresh --seed
```

## Test Data Details

### Admin User
- **Email**: admin@example.com
- **Password**: password
- **Role**: Administrator

### Sample Customers
- **John Doe** (john.doe@example.com) - password
- **Sarah Johnson** (sarah.johnson@example.com) - password
- **Michael Brown** (michael.brown@example.com) - password
- Plus 12 additional random customers

### Package Types
1. **ATV/UTV Trail Rides** - Adventure packages with single/double rider options
2. **Regular Package** - Standard adventure tours
3. **Premium ATV Experience** - Exclusive premium packages

### Promo Codes
- **WELCOME2024** - 15% off for new customers
- **SUMMER25** - 25% summer discount
- **WEEKDAY50** - ৳50 off weekday bookings
- **FIRSTTIME** - 20% first-time customer discount
- **GROUP10** - 10% group booking discount
- **FLASH30** - 30% flash sale (limited time)
- **LOYALTY15** - 15% loyalty program discount
- **EXPIRED_TEST** - Expired code for testing

### Schedule Slots
- 9 AM - 10 AM
- 10 AM - 11 AM
- 11 AM - 12 PM
- 12 PM - 1 PM
- 1 PM - 2 PM
- 2 PM - 3 PM
- 3 PM - 4 PM
- 4 PM - 5 PM
- 5 PM - 6 PM
- 6 PM - 7 PM

## Business Rules Implemented

### Availability Rules
- **Day Off**: Every Monday is marked as a day off
- **Holidays**: Christmas, New Year, Independence Day are holidays
- **Capacity**: Weekends have slightly higher capacity than weekdays
- **Pricing**: Different pricing for weekdays vs weekends

### Reservation Rules
- **Status Flow**: pending → confirmed → completed/cancelled
- **Payment Status**: Automatically set based on reservation status
- **Capacity Tracking**: Reservations update availability capacity
- **Promo Codes**: Applied based on business rules

### Customer Rules
- **User Accounts**: Each customer gets an associated user account
- **License Requirements**: All customers have valid driver's licenses
- **Emergency Contacts**: Required emergency contact information
- **Age Verification**: All customers are 18+ years old

## Troubleshooting

### Common Issues

1. **Missing Dependencies**
   ```
   Error: Vehicle types not found. Please run VehicleTypeSeeder first.
   ```
   **Solution**: Run seeders in the correct order or use `php artisan db:seed`

2. **Database Connection Issues**
   ```
   Error: Could not connect to database
   ```
   **Solution**: Check database configuration in `.env` file

3. **Permission Issues**
   ```
   Error: Permission denied for storage directory
   ```
   **Solution**: Run `php artisan storage:link` and check file permissions

### Debug Mode
To see detailed seeding information, run:
```bash
php artisan db:seed -v
```

## Maintenance

### Adding New Seeders
1. Create the seeder file in `database/seeders/`
2. Add proper error handling and logging
3. Update `DatabaseSeeder.php` with correct order
4. Test with `php artisan db:seed --class=NewSeeder`

### Updating Existing Data
- Use `updateOrCreate()` methods to avoid duplicates
- Include proper unique constraints
- Test with fresh database to ensure consistency

### Data Validation
- All seeders include validation for required relationships
- Missing data is logged with warnings
- Seeders can run independently or as part of the full sequence
