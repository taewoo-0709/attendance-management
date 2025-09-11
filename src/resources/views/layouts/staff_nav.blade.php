@extends('layouts.app')

@section('link')
<div class="header-nav">
  <ul class="header-nav__list">
    @php
        $isCheckedOut = isset($attendance) && $attendance->check_out_time;
    @endphp

    @if(isset($isDetailPage) && $isDetailPage)
    <li>
      <a class="header-nav__attendance" href="/attendance">勤怠</a>
    </li>
    <li>
      <a class="header-nav__attendance-list" href="/attendance/list">勤怠一覧</a>
    </li>
    <li>
      <a class="header-nav__application" href="{{ route('attendance.request.list') }}">申請</a>
    </li>
    <li>
      <form class="header-nav__logout" action="{{ route('logout') }}" method="post">
        @csrf
        <button class="header-nav__button">ログアウト</button>
      </form>
    </li>
    @else
      @if($isCheckedOut ?? false)
        <li>
          <a class="header-nav__attendance-list" href="/attendance/list">今月の出勤一覧</a>
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
      @else
        <li>
          <a class="header-nav__attendance" href="/attendance">勤怠</a>
        </li>
        <li>
          <a class="header-nav__attendance-list" href="/attendance/list">勤怠一覧</a>
        </li>
        <li>
          <a class="header-nav__application" href="{{ route('attendance.request.list') }}">申請</a>
        </li>
        <li>
          <form class="header-nav__logout" action="{{ route('logout') }}" method="post">
            @csrf
            <button class="header-nav__button">ログアウト</button>
          </form>
        </li>
      @endif
    @endif
  </ul>
</div>
@endsection
