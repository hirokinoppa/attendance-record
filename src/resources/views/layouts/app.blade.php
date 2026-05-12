<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'COACHTECH 勤怠管理アプリ')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @yield('css')
</head>
<body>
    <header class="header">
        <a href="{{ url('/') }}" class="header__logo">
            <img src="{{ asset('images/coachtech-logo.png') }}" alt="COACHTECHロゴ">
        </a>
    </header>

    <main class="main">
        @yield('content')
    </main>
</body>
</html>