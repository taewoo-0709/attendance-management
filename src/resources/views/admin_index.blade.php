@extends('layouts.admin_nav')

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
      <a href="{{ route('admin.attendance.list', ['date' => $prevDate]) }}"> <img class="left" src="{{ asset('images/point_icon.svg') }}" alt="左矢印"> 前日</a>
      <form method="GET" action="{{ route('admin.attendance.list') }}" style="display:inline;">
        <img class="calender" src="{{ asset('images/calendar_icon.png') }}" alt="カレンダー">
        <input class="date-select" type="date" name="date" value="{{ $date }}" onchange="this.form.submit()">
      </form>
      <a href="{{ route('admin.attendance.list', ['date' => $nextDate]) }}">翌日 <img class="right" src="{{ asset('images/point_icon.svg') }}" alt="右矢印"></a>
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
            <td>{{ $attendance->check_in_time ? $attendance->check_in_time->format('H:i') : '' }}</td>
            <td>{{ $attendance->check_out_time ? $attendance->check_out_time->format('H:i') : '' }}</td>
            <td>{{ $attendance->total_break_time ?? '' }}</td>
            <td>{{ $attendance->actual_work_time ?? '' }}</td>
            <td>
              <a href="{{ route('admin.attendance.edit', [
                'id' => $attendance->id ?? 0,
                'user_id' => $attendance->user->id,
                'date' => $date
              ]) }}">詳細
              </a>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection