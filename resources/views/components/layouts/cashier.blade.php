@props([
    'title' => 'Kasir'
])
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-surface-bg text-typography-heading antialiased">

    <x-cashier.sidebar></x-cashier.sidebar>

    <div class="min-h-screen lg:ml-72 flex flex-col">
        <x-cashier.header :title="$title"></x-cashier.header>
        
        {{-- Area Konten Utama: w-full dan text-left memastikan konten nempel di kiri --}}
        <main class="w-full flex-1 p-6 lg:p-8 text-left">
            {{ $slot }}
        </main>
    </div>

</body>
</html>