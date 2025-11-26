<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error Loading Receipt</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-2xl">
        <div class="bg-white rounded-lg shadow-lg p-8 text-center">
            <div class="text-6xl text-orange-500 mb-4">
                <i class="bi  bi-exclamation-circle"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-800 mb-4">Error Loading Receipt</h1>
            <p class="text-gray-600 mb-6">
                We encountered an error while trying to load your booking receipt.
            </p>
            <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-orange-800 mb-2">What you can do:</h3>
                <ul class="text-orange-700 text-sm space-y-1">
                    <li>• Try refreshing the page</li>
                    <li>• Check your internet connection</li>
                    <li>• Contact our support team</li>
                </ul>
            </div>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <button onclick="window.location.reload()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium">
                    <i class="bi  bi-redo mr-2"></i>
                    Refresh Page
                </button>
                <a href="{{ route('home') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-medium">
                    <i class="bi  bi-home mr-2"></i>
                    Back to Home
                </a>
                <a href="tel:+8801712345678" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium">
                    <i class="bi  bi-phone mr-2"></i>
                    Contact Support
                </a>
            </div>
        </div>
    </div>
</body>
</html>
