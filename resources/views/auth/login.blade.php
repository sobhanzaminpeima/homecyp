<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - HomeCyp</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-950 flex items-center justify-center px-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <a href="{{ route('home') }}">
                <img src="{{ asset('images/logo-white.svg') }}" alt="HomeCyp" class="h-12 mx-auto">
            </a>
        </div>
        <div class="bg-white rounded-2xl p-8 shadow-2xl">
            <h1 class="font-heading text-2xl font-bold text-gray-900 mb-6 text-center">Sign In</h1>

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-4 text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-yellow-300 focus:border-yellow-400 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" name="password" required
                           class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-yellow-300 focus:border-yellow-400 outline-none">
                </div>
                <div class="flex items-center justify-between">
                    <label class="flex items-center space-x-2 text-sm text-gray-600">
                        <input type="checkbox" name="remember" class="rounded">
                        <span>Remember me</span>
                    </label>
                    <a href="{{ route('password.request') }}" class="text-sm text-yellow-600 hover:underline">Forgot password?</a>
                </div>
                <button type="submit" class="btn-gold w-full py-3 rounded-xl font-semibold">Sign In</button>
            </form>

            <p class="text-center text-sm text-gray-500 mt-4">
                <a href="{{ route('home') }}" class="text-yellow-600 hover:underline">← Back to HomeCyp</a>
            </p>
        </div>
    </div>
</body>
</html>
