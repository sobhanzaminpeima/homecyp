<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HomeCyp — Installation Complete</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>body{font-family:'Inter',sans-serif}.font-heading{font-family:'Playfair Display',serif}</style>
</head>
<body class="min-h-screen bg-gray-950 flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-2xl">
        <div class="bg-white rounded-2xl p-8 shadow-2xl text-center">
            <div class="text-6xl mb-4">✅</div>
            <h1 class="font-heading text-3xl font-bold text-gray-900 mb-2">Installation Complete</h1>
            <p class="text-gray-500 mb-6">HomeCyp is ready to use.</p>

            @if(!empty($output))
            <div class="text-left bg-gray-50 rounded-xl p-4 mb-6 space-y-1">
                @foreach($output as $line)
                    <p class="text-sm {{ $line['ok'] ? 'text-green-700' : 'text-amber-600' }}">
                        {{ $line['ok'] ? '✓' : '⚠' }} {{ $line['step'] }}
                    </p>
                @endforeach
            </div>
            @endif

            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6 text-left">
                <p class="text-sm text-amber-800 font-semibold mb-1">⚠ Important — secure your site:</p>
                <ul class="text-sm text-amber-700 list-disc list-inside space-y-1">
                    <li>Log in and change the default admin password immediately.</li>
                    <li>The installer is now locked. Delete <code>storage/installed.lock</code> only if you need to re-run it.</li>
                </ul>
            </div>

            <div class="bg-gray-50 rounded-xl p-4 mb-6 text-left">
                <p class="text-sm text-gray-600 mb-1"><strong>Admin Panel:</strong> <a href="/admin" class="text-yellow-600">/admin</a></p>
                <p class="text-sm text-gray-600 mb-1"><strong>Default email:</strong> admin@homecyp.com</p>
                <p class="text-sm text-gray-600"><strong>Default password:</strong> HomeCyp@2024! <span class="text-red-500">(change this!)</span></p>
            </div>

            <div class="flex gap-3">
                <a href="/" class="flex-1 bg-gray-900 text-white py-3 rounded-xl font-semibold hover:bg-gray-800">Visit Website</a>
                <a href="/admin" class="flex-1 bg-gradient-to-r from-yellow-500 to-yellow-400 text-white py-3 rounded-xl font-semibold hover:shadow-lg">Go to Admin</a>
            </div>
        </div>
    </div>
</body>
</html>
