@props([
    'title' => 'Pelanggan',
])
<!DOCTYPE html>
<html lang="id" class="h-full bg-stone-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/home/card.js'])
</head>

<body class="h-full bg-stone-50 text-stone-900 antialiased">

    {{-- SIDEBAR CUSTOMER (Fixed di desktop w-72 / 18rem) --}}
    <x-customer.sidebar />

    {{-- KONTEN SEBELAH KANAN SIDEBAR --}}
    <div class="flex min-h-screen flex-col lg:pl-72">
        {{-- HEADER CUSTOMER --}}
        <x-customer.header :title="$title" />

        {{-- AREA KONTEN UTAMA --}}
        <main class="w-full flex-1 p-4 sm:p-6 lg:p-8">
            {{ $slot }}
        </main>
        @stack('scripts')
    </div>

</body>

</html>
