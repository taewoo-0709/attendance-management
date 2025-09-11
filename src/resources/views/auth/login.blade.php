@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css') }}?v={{ time() }}">
@endsection

@section('content')
<div class="login-form">
  <h2 class="login-form__heading">{{ $role === 'admin' ? '管理者ログイン' : 'ログイン' }}</h2>
  <div class="login-form__inner">
    <form class="login-form__form" action="{{ $role === 'admin' ? route('admin.login.submit') : route('login') }}" method="post">
      @csrf
      <input type="hidden" name="role" value="{{ $role }}">
      <div class="login-form__group">
        <label class="login-form__label" for="email">メールアドレス</label>
        <input class="login-form__input" type="text" name="email" id="email" value="{{ old('email') }}">
        @error('email')
          <p class="login-form__error-message">
            {{ $message }}
          </p>
        @enderror
      </div>
      <div class="login-form__group">
        <label class="login-form__label" for="password">パスワード</label>
        <input class="login-form__input" type="password" name="password" id="password">
        @error('password')
          <p class="login-form__error-message">
            {{ $message }}
          </p>
        @enderror
      </div>

      <input class="login-form__btn" type="submit" value="{{ $role === 'admin' ? '管理者ログインする' : 'ログインする' }}">
      @if($role === 'staff')
        <a class="login-form__link" href="{{ route('register') }}">会員登録はこちら</a>
      @endif
    </form>
  </div>
</div>
@endsection