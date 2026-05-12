@extends('layouts.app')

@section('title', 'ログイン')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')
    <div class="login">
        <div class="login__inner">
            <h1 class="login__heading">ログイン</h1>

            <form action="{{ route('login') }}" method="POST" class="login__form">
                @csrf

                <div class="login__group">
                    <label for="email" class="login__label">メールアドレス</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        class="login__input"
                        value="{{ old('email') }}"
                        autocomplete="email"
                    >
                    <p class="login__error-item">
                        @error('email')
                            {{ $message }}
                        @enderror
                    </p>
                </div>

                <div class="login__group">
                    <label for="password" class="login__label">パスワード</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        class="login__input"
                        autocomplete="current-password"
                    >
                    <p class="login__error-item">
                        @error('password')
                            {{ $message }}
                        @enderror
                    </p>
                </div>

                @if (session('status'))
                    <p class="login__status-message">{{ session('status') }}</p>
                @endif

                <button type="submit" class="login__button">ログインする</button>
            </form>

            <div class="login__link-wrapper">
                <a href="{{ route('register') }}" class="login__link">会員登録はこちら</a>
            </div>
        </div>
    </div>
@endsection