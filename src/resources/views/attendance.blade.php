@extends('layouts.staff_nav')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}?v={{ time() }}">
@endsection

@section('content')
<div class="attendance">
  @if(!$attendance || !$attendance->check_in_time)
    <div class="attendance-form">
      <div class="work-situation">
        <p class="before-work">勤務外</p>
      </div>
      <p class="date">{{ $dateStr }}</p>
      <p class="time time-display">{{ $timeStr }}</p>
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
      <p class="date">{{ $dateStr }}</p>
      <p class="time time-display">{{ $timeStr }}</p>
      <div class="btn__group">
        <form action="{{ route('attendance.update') }}" method="POST">
          @csrf
          <button class="work__button btn" type="submit" name="action" value="check_out">退勤</button>
        </form>
        <form action="{{ route('attendance.update') }}" method="POST">
          @csrf
          <button class="work__button btn-break" type="submit" name="action" value="break_start">休憩入</button>
        </form>
      </div>
    </div>

  @elseif($onBreak)
    <div class="attendance-form">
      <div class="work-situation">
        <p class="at-break">休憩中</p>
      </div>
      <p class="date">{{ $dateStr }}</p>
      <p class="time time-display">{{ $timeStr }}</p>
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
      <p class="date">{{ $dateStr }}</p>
      <p class="time time-display">{{ $timeStr }}</p>
      <p class="leaving-work__message">お疲れ様でした。</p>
    </div>
  @endif
</div>

<script>
    function updateClock() {
    const now = new Date();
    let hours = String(now.getHours()).padStart(2, '0');
    let minutes = String(now.getMinutes()).padStart(2, '0');

    document.querySelectorAll('.time-display').forEach(el => {
        el.textContent = `${hours}:${minutes}`;
    });
  }

  updateClock();
  setInterval(updateClock, 1000);
</script>
@endsection