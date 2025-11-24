<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Receipt - {{ $reservation->booking_code }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; padding: 20px; }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Booking Receipt</h1>
                    <p class="text-gray-600 mt-2">Thank you for your booking!</p>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-green-600">{{ $reservation->booking_code }}</div>
                    <div class="text-sm text-gray-500">Booking Code</div>
                </div>
            </div>
        </div>

        <!-- Booking Details -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Customer Information -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-user text-blue-500 mr-2"></i>
                    Customer Information
                </h2>
                <div class="space-y-3">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Name</label>
                        <p class="text-gray-800">{{ $reservation->customer->name }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Phone</label>
                        <p class="text-gray-800">{{ $reservation->customer->phone }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Email</label>
                        <p class="text-gray-800">{{ $reservation->customer->email }}</p>
                    </div>
                </div>
            </div>

            <!-- Booking Information -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-calendar-alt text-green-500 mr-2"></i>
                    Booking Information
                </h2>
                <div class="space-y-3">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Packages</label>
                        @if(isset($allReservations) && $allReservations->count() > 1)
                            <p class="text-gray-800">{{ $allReservations->count() }} packages booked</p>
                        @else
                            <p class="text-gray-800">{{ $reservation->packageVariant->package->name ?? 'Adventure Package' }}</p>
                        @endif
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Date</label>
                        <p class="text-gray-800">{{ $reservation->date->format('F j, Y') }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Time</label>
                        <p class="text-gray-800">{{ $reservation->report_time->format('g:i A') }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Party Size</label>
                        <p class="text-gray-800">{{ isset($totalPartySize) ? $totalPartySize : $reservation->party_size }} person(s)</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Package Details -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-map-marked-alt text-purple-500 mr-2"></i>
                Package Details
            </h2>
            
            @if(isset($allReservations) && $allReservations->count() > 1)
                <!-- Multiple Packages -->
                <div class="space-y-4">
                    @foreach($allReservations as $index => $res)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-800 mb-2">
                                    Package {{ $index + 1 }}: {{ $res->packageVariant->package->name ?? 'Adventure Package' }}
                                </h3>
                                <p class="text-gray-600 text-sm mb-2">{{ $res->packageVariant->package->description ?? 'Experience the thrill of adventure with our premium package.' }}</p>
                                
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="text-gray-500">Party Size:</span>
                                        <span class="text-gray-800">{{ $res->party_size }} person(s)</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Amount:</span>
                                        <span class="text-gray-800 font-semibold">৳{{ number_format($res->total_amount, 2) }}</span>
                                    </div>
                                </div>
                                
                                @if($res->scheduleSlot)
                                <div class="bg-gray-50 rounded-lg p-3 mt-3">
                                    <h4 class="font-medium text-gray-800 mb-1">Schedule Details</h4>
                                    <p class="text-sm text-gray-600">
                                        <i class="fas fa-clock text-blue-500 mr-1"></i>
                                        {{ $res->scheduleSlot->start_time->format('g:i A') }} - {{ $res->scheduleSlot->end_time->format('g:i A') }}
                                    </p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                    
                    <!-- Total Amount -->
                    <div class="border-t border-gray-200 pt-4">
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-semibold text-gray-800">Total Amount:</span>
                            <div class="text-3xl font-bold text-green-600">
                                ৳{{ number_format($totalAmount, 2) }}
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- Single Package -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="font-semibold text-gray-800 mb-2">{{ $reservation->packageVariant->package->name ?? 'Adventure Package' }}</h3>
                        <p class="text-gray-600 text-sm mb-4">{{ $reservation->packageVariant->package->description ?? 'Experience the thrill of adventure with our premium package.' }}</p>
                        
                        @if($reservation->scheduleSlot)
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="font-medium text-gray-800 mb-2">Schedule Details</h4>
                            <p class="text-sm text-gray-600">
                                <i class="fas fa-clock text-blue-500 mr-1"></i>
                                {{ $reservation->scheduleSlot->start_time->format('g:i A') }} - {{ $reservation->scheduleSlot->end_time->format('g:i A') }}
                            </p>
                        </div>
                        @endif
                    </div>
                    
                    <div class="text-right">
                        <div class="text-3xl font-bold text-green-600">
                            ৳{{ number_format(isset($totalAmount) ? $totalAmount : $reservation->total_amount, 2) }}
                        </div>
                        <p class="text-gray-500 text-sm">Total Amount</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Payment Information -->
        @if($reservation->payments->count() > 0)
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-credit-card text-orange-500 mr-2"></i>
                Payment Information
            </h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-2 text-sm font-medium text-gray-500">Payment ID</th>
                            <th class="text-left py-2 text-sm font-medium text-gray-500">Method</th>
                            <th class="text-left py-2 text-sm font-medium text-gray-500">Amount</th>
                            <th class="text-left py-2 text-sm font-medium text-gray-500">Status</th>
                            <th class="text-left py-2 text-sm font-medium text-gray-500">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reservation->payments as $payment)
                        <tr class="border-b">
                            <td class="py-3 text-sm text-gray-800">{{ $payment->id }}</td>
                            <td class="py-3 text-sm text-gray-800">{{ ucfirst($payment->method) }}</td>
                            <td class="py-3 text-sm text-gray-800">৳{{ number_format($payment->amount, 2) }}</td>
                            <td class="py-3">
                                <span class="px-2 py-1 text-xs rounded-full 
                                    @if($payment->status === 'completed') bg-green-100 text-green-800
                                    @elseif($payment->status === 'pending') bg-yellow-100 text-yellow-800
                                    @else bg-red-100 text-red-800
                                    @endif">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                            <td class="py-3 text-sm text-gray-800">{{ $payment->created_at->format('M j, Y g:i A') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Booking Status -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                Booking Status
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="text-sm font-medium text-gray-500">Booking Status</label>
                    <div class="mt-1">
                        <span class="px-3 py-1 text-sm rounded-full 
                            @if($reservation->booking_status === 'confirmed') bg-green-100 text-green-800
                            @elseif($reservation->booking_status === 'pending') bg-yellow-100 text-yellow-800
                            @elseif($reservation->booking_status === 'cancelled') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ ucfirst($reservation->booking_status) }}
                        </span>
                    </div>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Payment Status</label>
                    <div class="mt-1">
                        <span class="px-3 py-1 text-sm rounded-full 
                            @if($reservation->payment_status === 'paid') bg-green-100 text-green-800
                            @elseif($reservation->payment_status === 'pending') bg-yellow-100 text-yellow-800
                            @elseif($reservation->payment_status === 'failed') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ ucfirst($reservation->payment_status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Important Notes -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold text-blue-800 mb-4 flex items-center">
                <i class="fas fa-exclamation-triangle text-blue-600 mr-2"></i>
                Important Information
            </h2>
            <ul class="space-y-2 text-blue-700">
                <li class="flex items-start">
                    <i class="fas fa-check-circle text-blue-500 mr-2 mt-1"></i>
                    Please arrive 15 minutes before your scheduled time
                </li>
                <li class="flex items-start">
                    <i class="fas fa-check-circle text-blue-500 mr-2 mt-1"></i>
                    Bring this receipt or your booking code for verification
                </li>
                <li class="flex items-start">
                    <i class="fas fa-check-circle text-blue-500 mr-2 mt-1"></i>
                    Wear comfortable clothing and closed-toe shoes
                </li>
                <li class="flex items-start">
                    <i class="fas fa-check-circle text-blue-500 mr-2 mt-1"></i>
                    For any questions, contact us at +880 1712 345678
                </li>
            </ul>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center no-print">
            <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium flex items-center justify-center">
                <i class="fas fa-print mr-2"></i>
                Print Receipt
            </button>
            <a href="{{ route('home') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-medium flex items-center justify-center">
                <i class="fas fa-home mr-2"></i>
                Back to Home
            </a>
        </div>

        <!-- Footer -->
        <div class="text-center text-gray-500 text-sm mt-8 no-print">
            <p>This receipt was generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
            <p class="mt-2">© {{ date('Y') }} Adventure Zone. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
