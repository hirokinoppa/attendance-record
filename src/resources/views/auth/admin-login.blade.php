@extends('layouts.app')

@section('title', '管理者ログイン')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')
    <div class="login">
        <div class="login__inner">
            <h1 class="login__heading">管理者ログイン</h1>

            @if ($errors->has('login'))
                <p class="login__error">{{ $errors->first('login') }}</p>
            @endif

            <form action="{{ route('admin.login.store') }}" method="POST" class="login__form">
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
                    @error('email')
                        <p class="login__error">{{ $message }}</p>
                    @enderror
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
                    @error('password')
                        <p class="login__error">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="login__button">管理者ログインする</button>
            </form>
        </div>
    </div>
@endsection