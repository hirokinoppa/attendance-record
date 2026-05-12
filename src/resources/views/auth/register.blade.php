@extends('layouts.app')

@section('title', '会員登録')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
@endsection

@section('content')
    <div class="register">
        <div class="register__inner">
            <h1 class="register__heading">会員登録</h1>

            <form action="{{ route('register') }}" method="POST" class="register__form">
                @csrf

                <div class="register__group">
                    <label for="name" class="register__label">名前</label>
                    <input
                        id="name"
                        type="text"
                        name="name"
                        class="register__input"
                        value="{{ old('name') }}"
                        autocomplete="name"
                    >
                    <p class="register__error-item">
                        @error('name')
                            {{ $message }}
                        @enderror
                    </p>
                </div>

                <div class="register__group">
                    <label for="email" class="register__label">メールアドレス</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        class="register__input"
                        value="{{ old('email') }}"
                        autocomplete="email"
                    >
                    <p class="register__error-item">
                        @error('email')
                            {{ $message }}
                        @enderror
                    </p>
                </div>

                <div class="register__group">
                    <label for="password" class="register__label">パスワード</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        class="register__input"
                        autocomplete="new-password"
                    >
                    <p class="register__error-item">
                        @error('password')
                            {{ $message }}
                        @enderror
                    </p>
                </div>

                <div class="register__group">
                    <label for="password_confirmation" class="register__label">パスワード確認</label>
                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        class="register__input"
                        autocomplete="new-password"
                    >
                    <p class="register__error-item">
                        @error('password_confirmation')
                            {{ $message }}
                        @enderror
                    </p>
                </div>

                <button type="submit" class="register__button">登録する</button>
            </form>

            <div class="register__link-wrapper">
                <a href="{{ route('login') }}" class="register__link">ログインはこちら</a>
            </div>
        </div>
    </div>
@endsection