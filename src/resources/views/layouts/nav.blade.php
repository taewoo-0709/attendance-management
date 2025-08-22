@extends('layouts.app')

@section('link')
<div class="header-nav">
  <ul class="header-nav__list">
    @if (Auth::check())
      <li>
        <form class="header-nav__logout" action="{{ route('logout') }}" method="post">
          @csrf
          <button class="header-nav__button">ログアウト</button>
        </form>
      </li>
      <li>
        <a class="header-nav__mypage" href="/mypage">マイページ</a>
      </li>
      <li>
        <a class="header-nav__sell" href="/sell">出品</a>
      </li>
    @else
      <li>
        <a class="header-nav__login" href="/login">ログイン</a>
      </li>
      <li>
        <a class="header-nav__mypage" href="/login">マイページ</a>
      </li>
      <li>
        <a class="header-nav__sell" href="/login">出品</a>
      </li>
    @endif
  </ul>
</div>
@endsection