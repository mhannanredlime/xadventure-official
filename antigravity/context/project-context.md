# 🗺️ ATV/UTV Booking Platform Backend Context

This document outlines the core models, relationships, and business logic governing the ATV/UTV booking platform backend, implemented using Laravel 12, Services, and the Request class based validation, jquery, bootstrap 5.

## 👥 User Roles (Spatie Permission)

| Role | Permissions |
| :--- | :--- |
| **Admin** | Full access. Manages all CRUD operations (Vehicles,VehicleType, Packages, PackagePrice, PackageRiderTypePrice, Cart, CartItem, ScheduleSlot, Reservation, ReservationItem, Order, OrderPromoMap, Current PromoCode, ScheduleSlot, PromoCodeUser, User, Role, Permission and refactor current acl/rbac). |
| **Staff** | Manages daily operations (e.g., Schedule Slots, basic Reservation viewing/check-in). |
| **Customer** | Manages Carts, creates Reservations, views booking history. |

---

## 🔗 Key Relationships

| Model A | Relationship | Model B | Notes |
| :--- | :--- | :--- | :--- |
| `users` | Many-to-Many | `roles` | Spatie Permission implementation. |
| `vehicles` | Belongs To | `vehicle_types` | Defines the category of a vehicle (e.g., ATV). |
| `packages` | Has Many | `package_day_prices` | For date/day-specific price overrides. |
| `packages` | Has Many | `package_rider_type_prices` | For rider count/type-specific price overrides. |
| `carts` | Has Many | `cart_items` | Standard one-to-many shopping cart structure. |
| `reservations` | Has Many | `cart_items` | Items are converted/copied from `cart_items` upon checkout. |
| `reservations` | Belongs To | `users` | Links booking to the customer. |
| `promo_codes` | Belongs To Many | `users` (via `promo_code_user`) | For user-specific promotion tracking. |

---

## ⚙️ Core Business Logic

### 1. Pricing Engine (`PricingService`)
* **Method:** `get_package_price($package, $day, $riderCount)`
* **Priority:** Checks in order:
    1.  `package_rider_type_prices` (Highest priority)
    2.  `package_day_prices`
    3.  Base price defined in the `packages` model.

### 2. Vehicle Availability Engine (`AvailabilityService`)
* **Method:** `checkAvailability($packageId, $date, $slotId, $riderCount)`
* **Logic:**
    1.  Determine the **Required Vehicle Type/Capacity** based on the `$packageId` and `$riderCount`.
    2.  Calculate **Reserved Vehicles** for the given `$date` and `$slotId`.
    3.  Compare reserved count against total **Available Vehicles** of the required type.
    4.  Return boolean availability status.

### 3. Booking Workflow
* **Cart Service:** Handles `add`, `update`, `remove`, `calculateTotals`, and `applyPromoCode`.
* **Checkout:** Converts the `carts` state (including `cart_items` and calculated totals) into a permanent `reservations` record, generates a unique booking code, and updates vehicle availability counts.



┌────────────┐       ┌──────────────┐
│  packages  │1─────∞│ package_prices│
└────────────┘       └──────────────┘
       │                     │
       │                     │
       ▼                     ▼
┌────────────┐       ┌────────────┐
│ price_types│       │ rider_types│
└────────────┘       └────────────┘

┌───────────────────┐
│ package_vehicle_mapping │
└───────────────────┘
         │
         ▼
┌────────────┐       ┌──────────────┐
│ vehicles   │1─────∞│ vehicle_types│
└────────────┘       └──────────────┘

┌────────────┐
│ customers is user type  │1─────∞│ reservations │
└────────────┘       └────────────┘
                          │
                          ▼
                 ┌────────────────────┐
                 │ reservation_items  │
                 └────────────────────┘
                          │
                          ▼
                     ┌────────────┐
                     │ orders/pay │
                     └────────────┘
                          │
                          ▼
                     ┌────────────┐
                     │ promo_codes│
                     └────────────┘
                          │
                          ▼
                  ┌────────────────┐
                  │ order_promo_map│
                  └────────────────┘
