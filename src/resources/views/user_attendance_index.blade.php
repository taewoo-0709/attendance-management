@extends($layout)

@section('css')
<link rel="stylesheet" href="{{ asset('css/user_attendance_index.css') }}?v={{ time() }}">
@endsection

@section('content')
<div class="attendance-date">
  <div class="attendance-date__title">
    <h2 class="title">
      @if(auth()->user()->is_admin)
        {{ $targetUser->name }}さんの勤怠
      @else
        勤怠一覧
      @endif
    </h2>
  </div>

  <div class="attendance-date__content">
    <div class="attendance-table__date">
      @php
        $baseRoute = auth()->user()->is_admin ? 'admin.attendance.staff' : 'attendance.list';
        $routeParams = auth()->user()->is_admin ? ['id' => $targetUser->id] : [];
      @endphp

      <a href="{{ route($baseRoute, array_merge($routeParams, ['date' => $prevDate])) }}">
        <img class="left" src="{{ asset('images/point_icon.svg') }}" alt="左矢印"> 前月
      </a>

      <form method="GET" action="{{ route($baseRoute, $routeParams) }}">
        <img class="calender" src="{{ asset('images/calendar_icon.png') }}" alt="カレンダー">
        <input class="date-select" type="month" name="date" value="{{ $month }}" onchange="this.form.submit()">
      </form>

      <a href="{{ route($baseRoute, array_merge($routeParams, ['date' => $nextDate])) }}">
        翌月 <img class="right" src="{{ asset('images/point_icon.svg') }}" alt="右矢印">
      </a>
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
                @php
                  $detailRoute = auth()->user()->is_admin
                    ? 'admin.attendance.edit'
                    : 'attendance.detail';
                  $params = [
                    'id' => $att?->id ?? 0,
                    'user_id' => $targetUser->id,
                    'date' => $day['date']
                  ];
                @endphp
                <a href="{{ route($detailRoute, $params) }}">詳細</a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    @if(auth()->user()->is_admin)
      <div class="attendance-export">
        <a href="{{ route('admin.attendance.csv', ['id' => $targetUser->id, 'date' => $month]) }}" class="btn btn-primary">
          CSV出力
        </a>
      </div>
    @endif
  </div>
</div>
@endsection