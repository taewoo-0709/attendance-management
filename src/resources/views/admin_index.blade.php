@extends('layouts.nav')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}?v={{ time() }}">
@endsection

@section('content')
<div class="attendance-date">
  <div class="attendance-date__title">
    <h2 class="title">{{ $date }}の勤怠</h2>
  </div>

  <div class="attendance-date__content">
    <div class="attendance-table__date">
      <a href="{{ route('admin.attendances.index', ['date' => $prevDate]) }}">
        <p class="before-date">←前日</p>
      </a>
      <form method="GET" action="{{ route('admin.attendances.index') }}" style="display:inline;">
        <input class="date-select" type="date" name="date" value="{{ $date }}" onchange="this.form.submit()">
      </form>
      <a href="{{ route('admin.attendances.index', ['date' => $nextDate]) }}">
        <p class="after-date">翌日→</p>
      </a>
    </div>
    <div class="attendance-table__items">
      <table>
        <thead>
          <tr>
            <th>名前</th>
            <th>出勤</th>
            <th>退勤</th>
            <th>休憩</th>
            <th>合計</th>
            <th>詳細</th>
          </tr>
        </thead>
        <tbody>
          @foreach($attendances as $attendance)
            <tr>
              <td>{{ $attendance->user->name }}</td>
              <td>{{ $attendance->check_in_time ? $attendance->check_in_time->format('H:i') : '-' }}</td>
              <td>{{ $attendance->check_out_time ? $attendance->check_out_time->format('H:i') : '-' }}</td>
              <td>{{ $attendance->break_time }}</td>
              <td>{{ $attendance->total_time }}</td>
              <td><a href="{{ route('admin.attendances.show', $attendance->id) }}">詳細</a></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  </div>
</div>
@endsection