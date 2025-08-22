@extends('layouts.nav')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}?v={{ time() }}">
@endsection

@section('content')
<div class="attendance">
  @php
    $now = now();
    $dateStr = $now->format('Y-m-d (D)');
    $timeStr = $now->format('H:i:s');

    $latestBreak = $attendance->breaks()->latest()->first();
    $onBreak = $latestBreak && !$latestBreak->break_end_time;
  @endphp

  @if(!$attendance->check_in_time)
    <div class="attendance-form">
      <div class="work-situation">
        <p class="before-work">勤務外</p>
      </div>
      <p class="before-work__date">{{ $dateStr }}</p>
      <p class="before-work__time">{{ $timeStr }}</p>
      <form action="{{ route('attendance.update') }}" method="POST">
        @csrf
          <button class="work__button btn" type="submit" name="action" value="check_in">出勤</button>
      </form>
    </div>

  @elseif($attendance->check_in_time && !$attendance->check_out_time && !$onBreak)
    <div class="attendance-form">
      <div class="work-situation">
        <p class="at-work">出勤中</p>
      </div>
      <p class="at-work__date">{{ $dateStr }}</p>
      <p class="at-work__time">{{ $timeStr }}</p>
      <form action="{{ route('attendance.update') }}" method="POST">
      @csrf
        <button class="work__button btn" type="submit" name="action" value="check_out">退勤</button>
      </form>
      <form action="{{ route('attendance.update') }}" method="POST">
      @csrf
        <button class="work__button btn-break" type="submit" name="action" value="break_start">休憩入</button>
      </form>
    </div>

  @elseif($onBreak)
    <div class="attendance-form">
      <div class="work-situation">
        <p class="at-break">休憩中</p>
      </div>
      <p class="at-break__date">{{ $dateStr }}</p>
      <p class="at-break__time">{{ $timeStr }}</p>
      <form action="{{ route('attendance.update') }}" method="POST">
      @csrf
        <button class="work__button btn-break" type="submit" name="action" value="break_end">休憩戻</button>
      </form>
    </div>

  @elseif($attendance->check_out_time)
    <div class="attendance-form">
      <div class="work-situation">
        <p class="leaving-work">退勤済</p>
      </div>
      <p class="leaving-work__date">{{ $dateStr }}</p>
      <p class="leaving-work__time">{{ $timeStr }}</p>
      <p class="leaving-work__message">お疲れ様でした。</p>
    </div>
  @endif
</div>
@endsection