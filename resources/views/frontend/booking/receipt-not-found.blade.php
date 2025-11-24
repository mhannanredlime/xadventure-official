<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Not Found</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-2xl">
        <div class="bg-white rounded-lg shadow-lg p-8 text-center">
            <div class="text-6xl text-red-500 mb-4">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Booking Not Found</h1>
            <p class="text-gray-600 mb-6">
                We couldn't find a booking with the code: <strong>{{ $bookingCode }}</strong>
            </p>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-yellow-800 mb-2">Possible reasons:</h3>
                <ul class="text-yellow-700 text-sm space-y-1">
                    <li>• The booking code may be incorrect</li>
                    <li>• The booking may have been cancelled</li>
                    <li>• The link may have expired</li>
                </ul>
            </div>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('home') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium">
                    <i class="fas fa-home mr-2"></i>
                    Back to Home
                </a>
                <a href="tel:+8801712345678" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium">
                    <i class="fas fa-phone mr-2"></i>
                    Contact Support
                </a>
            </div>
        </div>
    </div>
</body>
</html>
