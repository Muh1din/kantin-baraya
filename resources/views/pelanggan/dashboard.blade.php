<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard Pelanggan | Kantin Baraya</title>

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
        }

        .role {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 5px;
            background: #fef3c7;
            color: #92400e;
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
            <h1>Dashboard Pelanggan</h1>

            <p>
                Selamat datang,
                <strong>{{ auth()->user()->name }}</strong>!
            </p>

            <p>
                Selamat datang di sistem Kantin Baraya.
            </p>

            <span class="role">
                {{ strtoupper(auth()->user()->role->name) }}
            </span>
        </section>
    </main>

</body>
</html>