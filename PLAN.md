# Implementation Plan

High-level roadmap to take the existing design to a production-ready Laravel app without changing the UI. Frontend remains public for guests; admin is authenticated-only.

## Goals
- Preserve current design and component structure
- Public frontend (guest) browsing and booking flow
- Admin CMS features powered by database
- Clean URLs, proper error pages, robust validation, payment via AmarPay

## Assumptions
- Single-tenant app (one brand)
- Email/password auth for admin; no public user accounts initially
- Local dev uses MySQL; production uses MySQL/Postgres

## Architecture Overview
- Laravel 12 (PHP 8.2+)
- Blade views: `resources/views/frontend/*`, `resources/views/admin/*`, layouts kept
- Routes: public frontend; `admin/*` behind `auth` + `admin` middleware
- Domain layers: Controllers → Services → Repos/Models
- Payment: AmarPay service + IPN webhook

## Milestones & Deliverables

### M1: Foundations (Done/Next)
- [x] Blade layouts + clean routes
- [x] Error pages (404/500/generic), fallback
- [x] Convert CTAs to correct routes
- [x] Minimal auth (login/logout), `admin` middleware, protect `admin/*`
- [x] Env + app key setup notes (README)
- [x] Database setup (MySQL), migrations, seeders

### M2: Database Schema
- Core entities & migrations:
  - customers, users (with `is_admin`), vehicle_types, vehicles
  - packages, package_vehicle_types, package_variants, variant_prices
  - schedule_slots, availabilities
  - reservations, reservation_items, payments
  - promo_codes, promo_redemptions
- Seeders: default admin, slots, demo data

### M3: Admin CRUD (wire to existing screens)
- Vehicle Types (Create/Read/Update/Delete)
- Vehicles (Create/Read/Update/Delete, status, images)
- Packages (regular, ATV/UTV), variants, weekday/weekend prices
- Promo Codes (Create/Read/Update/Delete), activation

### M4: Availability & Pricing Calendar
- Package/variant selection → month view
- Day Off toggle, capacity edit, price overrides
- Price badges (regular/premium/discounted)

### M5: Reservations & Payments
- Admin reservation dashboard (filters, paging, export)
- Create reservation flow (admin), status transitions
- Payment records (partial/deposit, refund)

### M6: Frontend Booking Flow (guest)
- Packages → Cart → Payment → Confirmation
- Promo application & validation
- Email confirmations (optional)

### M7: AmarPay Integration
- Config (`config/services.php`) + `.env` keys
- Endpoints:
  - `POST /payments/initiate` → initiate transaction
  - `POST /payment/ipn` → IPN handler (signature verify, idempotent)
  - `GET /payment/success|fail|cancel` → result pages
- Update reservation/payment states accordingly

### M8: QA, Security, Ops
- Validation & policies; CSRF, rate-limit payment endpoints
- Logs/audit trail; error monitoring hooks
- Backups & .env templates; deployment notes

## Database Sketch (summary)
- users(id, name, email, password, is_admin, ...)
- vehicle_types(id, name, description, is_active)
- vehicles(id, vehicle_type_id, name, details, image_path, is_active, op_start_date)
- packages(id, name, subtitle, type, min_participants, max_participants, image_path, is_active)
- package_vehicle_types(id, package_id, vehicle_type_id)
- package_variants(id, package_id, variant_name, capacity, is_active)
- variant_prices(id, package_variant_id, price_type, amount, valid_from, valid_to)
- schedule_slots(id, name, report_time, start_time, end_time, sort_order)
- availabilities(id, date, package_variant_id, schedule_slot_id, capacity_total, capacity_reserved, is_day_off, price_override, price_tag)
- reservations(id, booking_code, customer_id, package_variant_id, schedule_slot_id, date, report_time, party_size, subtotal, discount_amount, tax_amount, total_amount, deposit_amount, balance_amount, booking_status, payment_status, notes)
- reservation_items(id, reservation_id, package_variant_id, qty, unit_price, line_total)
- payments(id, reservation_id, method, amount, currency, transaction_id, paid_at, status, meta)
- promo_codes(id, code, applies_to, package_id, vehicle_type_id, discount_type, discount_value, max_discount, min_spend, usage_limit_total, usage_limit_per_user, starts_at, ends_at, status, remarks)
- promo_redemptions(id, promo_code_id, reservation_id, customer_id, amount_discounted, redeemed_at)

## Routes (public vs protected)
- Public (guest): `/`, `/about`, `/adventure`, `/advanture-2`, `/packeges`, `/archery`, `/cart-2`, `/payment`, `/confirmm`, error pages
- Auth: `/login`, `/logout`
- Admin (auth+admin): `/admin/*` (all existing admin pages)

## AmarPay Config Keys (.env)
- AMARPAY_STORE_ID=...
- AMARPAY_SIGNATURE_KEY=...
- AMARPAY_SANDBOX=true
- AMARPAY_SUCCESS_URL=/payment/success
- AMARPAY_FAIL_URL=/payment/fail
- AMARPAY_CANCEL_URL=/payment/cancel
- AMARPAY_IPN_URL=/payment/ipn

## Definition of Done
- All admin pages backed by DB, validation, and policies
- Full booking + payment flow works with AmarPay and IPN
- Frontend remains public for guests; admin fully protected
- Logs, error pages, and basic monitoring in place


