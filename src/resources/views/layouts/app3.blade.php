<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'COACHTECH 勤怠管理アプリ')</title>

    <link rel="stylesheet" href="{{ asset('css/app3.css') }}">
    @yield('css')
</head>
<body>
    <header class="header">
        <div class="header__inner">
            <div class="header__logo">
                <a href="{{ route('admin.attendance.list') }}">
                    <img src="{{ asset('images/coachtech-logo.png') }}" alt="COACHTECHロゴ">
                </a>
            </div>

            <nav class="header__nav">
                <ul class="header__nav-list">
                    <li class="header__nav-item">
                        <a href="{{ route('admin.attendance.list') }}" class="header__nav-link">勤怠一覧</a>
                    </li>
                    <li class="header__nav-item">
                        <a href="{{ route('admin.staff.list') }}" class="header__nav-link">スタッフ一覧</a>
                    </li>
                    <li class="header__nav-item">
                        <a href="{{ route('admin.stamp_correction_request.list') }}" class="header__nav-link">申請一覧</a>
                    </li>
                    <li class="header__nav-item">
                        <form action="{{ route('admin.logout') }}" method="POST" class="header__logout-form">
                            @csrf
                            <button type="submit" class="header__logout-button">ログアウト</button>
                        </form>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="main">
        @yield('content')
    </main>
</body>
</html>