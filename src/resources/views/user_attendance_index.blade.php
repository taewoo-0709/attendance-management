@extends($layout)

@section('css')
<link rel="stylesheet" href="{{ asset('css/user_attendance_index.css') }}?v={{ time() }}">
@endsection

@section('content')
<div class="attendance-date">
  <div class="attendance-date__title">
    @if(auth()->user()->is_admin)
      <h2 class="title">{{ $targetUser->name }}さんの勤怠</h2>
    @else
      <h2 class="title">勤怠一覧</h2>
    @endif
  </div>

  <div class="attendance-date__content">
    <div class="attendance-table__date">
      @if(auth()->user()->is_admin)
        <a href="{{ route('admin.attendance.staff', ['id' => $targetUser->id, 'date' => $prevDate]) }}">
          <img class="left" src="{{ asset('images/point_icon.svg') }}" alt="左矢印">
          前月
        </a>
        <form method="GET" action="{{ route('admin.attendance.staff', ['id' => $targetUser->id]) }}">
          <img class="calender" src="{{ asset('images/calendar_icon.png') }}" alt="カレンダー">
          <input class="date-select" type="month" name="date" value="{{ $month }}" onchange="this.form.submit()">
        </form>
        <a href="{{ route('admin.attendance.staff', ['id' => $targetUser->id, 'date' => $nextDate]) }}">
          翌月
          <img class="right" src="{{ asset('images/point_icon.svg') }}" alt="右矢印">
        </a>
      @else
        <a href="{{ route('attendance.list', ['date' => $prevDate]) }}">
          <img class="left" src="{{ asset('images/point_icon.svg') }}" alt="左矢印">
          前月
        </a>
        <form method="GET" action="{{ route('attendance.list') }}">
          <img class="calender" src="{{ asset('images/calendar_icon.png') }}" alt="カレンダー">
          <input class="date-select" type="month" name="date" value="{{ $month }}" onchange="this.form.submit()">
        </form>
        <a href="{{ route('attendance.list', ['date' => $nextDate]) }}">
          翌月
          <img class="right" src="{{ asset('images/point_icon.svg') }}" alt="右矢印">
        </a>
      @endif
    </div>

    <div class="attendance-table__items">
      <table>
        <thead>
          <tr>
            <th>日付</th>
            <th>出勤</th>
            <th>退勤</th>
            <th>休憩</th>
            <th>合計</th>
            <th>詳細</th>
          </tr>
        </thead>
        <tbody>
          @foreach($days as $day)
            @php $att = $day['attendance']; @endphp
            <tr>
              <td>{{ \Carbon\Carbon::parse($day['date'])->format('Y/m/d') }}</td>
              <td>{{ $att?->check_in_time?->format('H:i') ?? '' }}</td>
              <td>{{ $att?->check_out_time?->format('H:i') ?? '' }}</td>
              <td>{{ $att?->total_break_time ?? '' }}</td>
              <td>{{ $att?->actual_work_time ?? '' }}</td>
              <td>
                @if(auth()->user()->is_admin)
                  <a href="{{ route('admin.attendance.edit', [
                    'id' => $att?->id ?? 0,
                    'user_id' => $targetUser->id,
                    'date' => $day['date']
                  ]) }}">
                    詳細
                  </a>
                @else
                  <a href="{{ route('attendance.detail', [
                    'id' => $att?->id ?? 0,
                    'user_id' => $targetUser->id,
                    'date' => $day['date']
                  ]) }}">
                    詳細
                  </a>
                @endif
              </td>
            </tr>
        @endforeach
        </tbody>
      </table>
    </div>
  </div>
  </div>
</div>
@endsection