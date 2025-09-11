@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/register.css') }}?v={{ time() }}">
@endsection

@section('content')
<div class="register-form">
  <h2 class="register-form__heading content__heading">会員登録</h2>

  <div class="register-form__inner">
    <form class="register-form__form" action="{{ route('register') }}" method="post">
      @csrf

      <div class="register-form__group">
        <label class="register-form__label" for="name">ユーザー名</label>
        <input class="register-form__input" type="text" name="name" id="name" value="{{ old('name') }}">
        @error('name')
          <p class="register-form__error-message">{{ $message }}</p>
        @enderror
      </div>

      <div class="register-form__group">
        <label class="register-form__label" for="email">メールアドレス</label>
        <input class="register-form__input" type="text" name="email" id="email" value="{{ old('email') }}">
        @error('email')
          <p class="register-form__error-message">{{ $message }}</p>
        @enderror
      </div>

      <div class="register-form__group">
        <label class="register-form__label" for="password">パスワード</label>
        <input class="register-form__input" type="password" name="password" id="password">
        @error('password')
          <p class="register-form__error-message">{{ $message }}</p>
        @enderror
      </div>

      <div class="register-form__group">
        <label class="register-form__label" for="password_confirmation">確認パスワード</label>
        <input class="register-form__input" type="password" name="password_confirmation" id="password_confirmation">
        @error('password_confirmation')
          <p class="register-form__error-message">{{ $message }}</p>
        @enderror
      </div>

      <input class="register-form__btn" type="submit" value="登録する">
      <a class="register-form__link" href="{{ route('login') }}">ログインはこちら</a>
    </form>
  </div>
</div>
@endsection