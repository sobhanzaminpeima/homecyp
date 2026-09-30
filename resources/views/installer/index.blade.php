<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HomeCyp Installer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>body{font-family:'Inter',sans-serif}.font-heading{font-family:'Playfair Display',serif}</style>
</head>
<body class="min-h-screen bg-gray-950 flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-2xl">
        <div class="text-center mb-8">
            <h1 class="font-heading text-4xl font-bold text-white">HomeCyp</h1>
            <p class="text-gray-400 mt-2">Web Installer</p>
        </div>

        <div class="bg-white rounded-2xl p-8 shadow-2xl">
            @isset($errors)
                @if(is_array($errors))
                <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
                    <h3 class="font-semibold text-red-800 mb-2">Installation failed</h3>
                    @foreach($errors as $line)
                        <p class="text-sm {{ $line['ok'] ? 'text-green-700' : 'text-red-700' }}">
                            {{ $line['ok'] ? '✓' : '✗' }} {{ $line['step'] }}: {{ $line['msg'] }}
                        </p>
                    @endforeach
                </div>
                @endif
            @endisset

            <h2 class="font-heading text-2xl font-bold text-gray-900 mb-1">Server Requirements</h2>
            <p class="text-gray-500 text-sm mb-6">All checks must pass before installing.</p>

            <div class="space-y-2 mb-8">
                @php $allOk = true; @endphp
                @foreach($checks as $check)
                    @php $allOk = $allOk && $check['ok']; @endphp
                    <div class="flex items-center justify-between py-2 px-4 rounded-lg {{ $check['ok'] ? 'bg-green-50' : 'bg-red-50' }}">
                        <span class="text-sm font-medium text-gray-700">{{ $check['name'] }}</span>
                        <span class="text-sm font-semibold {{ $check['ok'] ? 'text-green-600' : 'text-red-600' }}">
                            {{ $check['ok'] ? '✓ OK' : '✗ Missing' }}
                        </span>
                    </div>
                @endforeach
            </div>

            @if($allOk)
                <form method="POST" action="{{ route('installer.run') }}">
                    @csrf
                    <button type="submit"
                            class="w-full bg-gradient-to-r from-yellow-500 to-yellow-400 text-white py-4 rounded-xl font-semibold hover:shadow-lg transition-all">
                        Install HomeCyp Now
                    </button>
                </form>
                <p class="text-xs text-gray-400 text-center mt-3">
                    This will create database tables, seed initial data, and configure the application.
                </p>
            @else
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-800">
                    Please enable the missing PHP extensions in cPanel (MultiPHP INI Editor / Select PHP Version → Extensions) and ensure folders are writable, then refresh this page.
                </div>
            @endif
        </div>

        <p class="text-center text-gray-600 text-xs mt-6">
            Make sure your <code class="text-gray-400">.env</code> database credentials are set before installing.
        </p>
    </div>
</body>
</html>
