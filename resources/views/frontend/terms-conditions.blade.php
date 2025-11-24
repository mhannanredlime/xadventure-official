@extends('layouts.frontend')

@section('title', 'Terms & Conditions')

@section('content')
    <section style="background: #ffffff; color: #111827;">
        <div class="py-5 bg-dark"></div>
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-10 col-xl-8">
                    <h1 class="mb-4 fw-bold" style="color:#111;">Xtreme Adventure Bandarban – Terms & Conditions</h1>
                    <p class="text-muted">Effective Date: <span class="fw-semibold">{{ now()->format('F j, Y') }}</span></p>
                    <p class="mb-1"><span class="fw-semibold">Operator:</span> Xtreme Adventure Bandarban</p>
                    <p class="mb-4"><span class="fw-semibold">Location:</span> Tongkaboti, Bandarban, Chattogram Hill
                        Tracts, Bangladesh</p>

                    <div class="card bg-white border-0 shadow-sm mb-4">
                        <div class="card-body p-4 p-md-5">
                            <h5 class="fw-bold mb-3" style="color:#f59e0b;">1. Booking & Confirmation</h5>
                            <ul class="mb-4">
                                <li>Full payment is required at the time of booking.</li>
                                <li>No booking is confirmed until a receipt or booking ID is issued.</li>
                                <li>We reserve the right to cancel any unpaid or improperly submitted bookings.</li>
                                <li>Guests must show valid ID and applicable licenses upon arrival.</li>
                            </ul>

                            <h5 class="fw-bold mb-3" style="color:#f59e0b;">2. Rescheduling & Cancellation Policy</h5>
                            <p class="fw-semibold mb-2">Rescheduling</p>
                            <ul class="mb-3">
                                <li>All activities (except ATV): Free reschedule up to 24 hours before.</li>
                                <li>ATV Trail Rides: Free reschedule up to 72 hours before.</li>
                                <li>One reschedule permitted per booking.</li>
                            </ul>

                            <p class="fw-semibold mb-2">Cancellations & Refunds</p>
                            <div class="table-responsive mb-3">
                                <table class="table table-striped align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Activity Type</th>
                                            <th>Cancellation Window</th>
                                            <th>Refund/Credit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>ATV Trail Ride</td>
                                            <td>
                                                <div class="small">≥72 hrs → Full Refund</div>
                                                <div class="small">48–72 hrs → 50% Refund/Credit</div>
                                                <div class="small">&lt;48 hrs or No-Show</div>
                                            </td>
                                            <td>
                                                <div class="small">✅ Yes</div>
                                                <div class="small">✅ Yes</div>
                                                <div class="small">❌ None</div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>All Other Activities</td>
                                            <td>
                                                <div class="small">≥24 hrs → Full Refund</div>
                                                <div class="small">&lt;24 hrs or No-Show</div>
                                            </td>
                                            <td>
                                                <div class="small">✅ Yes</div>
                                                <div class="small">❌ None</div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <ul class="mb-4">
                                <li>All refunds are subject to deduction of payment processing fees.</li>
                                <li>Refunds processed within 7 working days.</li>
                            </ul>

                            <h5 class="fw-bold mb-3" style="color:#f59e0b;">3. Risk Acknowledgment & Waiver</h5>
                            <ul class="mb-4">
                                <li>Guests must sign a waiver form before participating.</li>
                                <li>All activities involve inherent risk (injury, terrain, collision, height).</li>
                                <li>You participate at your own risk.</li>
                                <li>We are not liable for any personal injury, property damage, or accidents.</li>
                            </ul>

                            <h5 class="fw-bold mb-3" style="color:#f59e0b;">4. Participant Requirements</h5>
                            <ul class="mb-4">
                                <li>ATV Riders: Age 18+, motorcycle license required.</li>
                                <li>UTV Drivers: Age 18+, car license required. Passengers allowed.</li>
                                <li>Climbing, Rope Course, Archery: Fitness required; not suitable for heart conditions,
                                    epilepsy, or recent surgery.</li>
                                <li>Participants must wear safety gear at all times.</li>
                            </ul>

                            <h5 class="fw-bold mb-3" style="color:#f59e0b;">5. General Safety Rules</h5>
                            <ul class="mb-4">
                                <li>Mandatory safety briefing required for all activities.</li>
                                <li>No stunts, reckless behavior, or racing allowed.</li>
                                <li>Stay on designated trails/zones.</li>
                                <li>Listen to instructors and guides at all times.</li>
                                <li>No headphones, phones, or distractions while operating vehicles.</li>
                            </ul>

                            <h5 class="fw-bold mb-3" style="color:#f59e0b;">6. Property Damage & Penalties</h5>
                            <ul class="mb-4">
                                <li>Any damage to vehicles, gear, structures, or landscape due to carelessness will be
                                    charged to the guest.</li>
                                <li>Collisions with other guests or property will result in fines plus cost of repair.</li>
                                <li>You break it—you pay for it.</li>
                            </ul>

                            <h5 class="fw-bold mb-3" style="color:#f59e0b;">7. No Drugs, No Alcohol, No Exceptions</h5>
                            <ul class="mb-4">
                                <li>Zero tolerance for alcohol, narcotics, or controlled substances before or during any
                                    activity.</li>
                                <li>Guests suspected to be under the influence will be denied access without refund.</li>
                            </ul>

                            <h5 class="fw-bold mb-3" style="color:#f59e0b;">8. Children & Supervision</h5>
                            <ul class="mb-4">
                                <li>All children under 16 must be supervised by an adult at all times.</li>
                                <li>Staff are not responsible for babysitting children in Kids Zone or activity areas.</li>
                            </ul>

                            <h5 class="fw-bold mb-3" style="color:#f59e0b;">9. Health & Fitness Requirements</h5>
                            <ul class="mb-4">
                                <li>By booking, you confirm you are physically fit and not under medical restrictions.</li>
                                <li>If unsure, consult a doctor before participating.</li>
                                <li>Guests with chronic illness or disability must inform staff in advance.</li>
                            </ul>

                            <h5 class="fw-bold mb-3" style="color:#f59e0b;">10. Weather & Emergency Disruptions</h5>
                            <ul class="mb-4">
                                <li>Activities may be cancelled/postponed due to rain, storms, mudslides, or equipment
                                    failure.</li>
                            </ul>
                            <p class="mb-4">You will receive: Reschedule, Voucher valid for 3 months, or Refund (minus
                                processing fee).</p>

                            <h5 class="fw-bold mb-3" style="color:#f59e0b;">11. Force Majeure</h5>
                            <ul class="mb-4">
                                <li>We are not liable for cancellations or loss caused by acts of God, natural disasters,
                                    political unrest, pandemics, strikes, or unforeseen legal restrictions.</li>
                            </ul>

                            <h5 class="fw-bold mb-3" style="color:#f59e0b;">12. Photography & Media Release</h5>
                            <ul class="mb-4">
                                <li>We may film or photograph during activities for marketing.</li>
                                <li>By entering, you grant permission for your likeness to be used.</li>
                                <li>If you do not wish to be photographed, please inform staff at check-in.</li>
                            </ul>

                            <h5 class="fw-bold mb-3" style="color:#f59e0b;">13. Pets Policy</h5>
                            <ul class="mb-4">
                                <li>No pets allowed in the trail, climbing, or activity zones.</li>
                            </ul>

                            <h5 class="fw-bold mb-3" style="color:#f59e0b;">14. Dispute Resolution & Jurisdiction</h5>
                            <ul class="mb-4">
                                <li>All disputes must first be addressed with our management.</li>
                                <li>If unresolved, they will be settled under the laws of Bangladesh, in courts of Bandarban
                                    or Chattogram.</li>
                            </ul>

                            <h5 class="fw-bold mb-3" style="color:#f59e0b;">15. Final Legal Disclaimer</h5>
                            <ul class="mb-0">
                                <li>Management reserves the right to refuse service for safety concerns or policy
                                    violations.</li>
                                <li>Management may modify or cancel activities at any time for safety or operational
                                    reasons.</li>
                                <li>Entry and participation imply full acceptance of these terms.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
