@extends('layouts.app')

@section('link')
<div class="header-nav">
  <ul class="header-nav__list">
    <li>
      <a class="header-nav__attendance-list" href="/admin/attendance">勤怠一覧</a>
    </li>
    <li>
      <a class="header-nav__attendance" href="/admin/user">スタッフ一覧</a>
    </li>
    <li>
      <a class="header-nav__application" href="/admin/requests">申請一覧</a>
    </li>
    <li>
      <form class="header-nav__logout" action="{{ route('logout.submit') }}" method="post">
        @csrf
          <button class="header-nav__button">ログアウト</button>
      </form>
    </li>
  </ul>
</div>
@endsection