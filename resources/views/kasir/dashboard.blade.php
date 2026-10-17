<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard Kasir | Kantin Baraya</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            color: #333;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 32px;
            background: #1e293b;
            color: white;
        }

        .navbar h2 {
            margin: 0;
            font-size: 20px;
        }

        .logout-button {
            padding: 9px 16px;
            border: none;
            border-radius: 5px;
            background: #dc3545;
            color: white;
            cursor: pointer;
            font-size: 14px;
        }

        .logout-button:hover {
            background: #bb2d3b;
        }

        .container {
            padding: 32px;
        }

        .welcome-card {
            padding: 28px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgb(0 0 0 / 5%);
        }

        .welcome-card h1 {
            margin-top: 0;
            font-size: 26px;
        }

        .welcome-card p {
            color: #666;
            line-height: 1.6;
        }

        .role {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 5px;
            background: #dbeafe;
            color: #1d4ed8;
            font-size: 13px;
            font-weight: bold;
        }

        @media (max-width: 600px) {
            .navbar {
                padding: 16px;
            }

            .container {
                padding: 16px;
            }

            .welcome-card h1 {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>

    <header class="navbar">
        <h2>Kantin Baraya</h2>

        <form action="{{ route('logout') }}" method="POST">
            @csrf

            <button type="submit" class="logout-button">
                Logout
            </button>
        </form>
    </header>

    <main class="container">
        <section class="welcome-card">
            <h1>Dashboard Kasir</h1>

            <p>
                Selamat datang,
                <strong>{{ auth()->user()->name }}</strong>!
            </p>

            <p>
                Anda berhasil masuk ke sistem Kantin Baraya
                sebagai kasir.
            </p>

            <span class="role">
                {{ strtoupper(auth()->user()->role->name) }}
            </span>
        </section>
    </main>

</body>
</html>