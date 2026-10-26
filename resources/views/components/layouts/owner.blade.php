@props([
    'title' => 'Owner | Kantin Baraya'
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

    <x-owner.sidebar></x-owner.sidebar>

    <div class="min-h-screen lg:ml-72 flex flex-col">
        <x-owner.header :title="$title"></x-owner.header>
        
        {{-- Area Konten Utama: w-full dan text-left memastikan konten nempel di kiri --}}
        <main class="w-full flex-1 p-6 lg:p-8 text-left">
            {{ $slot }}
        </main>
    </div>

</body>
</html>