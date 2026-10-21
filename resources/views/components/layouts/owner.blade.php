<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Owner | Kantin Baraya')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-surface-bg text-typography-heading antialiased">

    <x-owner.sidebar />

    <div class="min-h-screen lg:ml-72">
        <x-owner.header />
        {{ $slot }}
    </div>

</body>
</html>
