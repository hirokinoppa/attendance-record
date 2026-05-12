@extends('layouts.app')

@section('title', 'メール認証')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/verify-email.css') }}">
@endsection

@section('content')
    <div class="verify-email">
        <div class="verify-email__inner">

            <p class="verify-email__message">
                登録していただいたメールアドレスに認証メールを送付しました。<br>
                メール認証を完了してください。
            </p>

            <div class="verify-email__button-wrapper">
                <a href="http://localhost:8025" target="_blank" class="verify-email__button">
                    認証はこちらから
                </a>
            </div>

            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="verify-email__resend">
                    認証メールを再送する
                </button>
            </form>

        </div>
    </div>
@endsection