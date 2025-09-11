@extends('layouts.app')

@section('link')
<div class="header-nav">
  <ul class="header-nav__list">
    <li>
      <a class="header-nav__attendance-list" href="/admin/attendance/list">勤怠一覧</a>
    </li>
    <li>
      <a class="header-nav__attendance" href="/admin/staff/list">スタッフ一覧</a>
    </li>
    <li>
      <a class="header-nav__application" href="{{ route('attendance.request.list') }}">申請一覧</a>
    </li>
    <li>
      <form class="header-nav__logout" action="{{ route('logout') }}" method="post">
        @csrf
        <button class="header-nav__button">ログアウト</button>
      </form>
    </li>
  </ul>
</div>
@endsection