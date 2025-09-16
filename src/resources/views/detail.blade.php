@extends($layout ?? (auth()->user()->is_admin ? 'layouts.admin_nav' : 'layouts.staff_nav'))

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}?v={{ time() }}">
@endsection

@section('content')
@if(session('success'))
  <div class="alert-message">{{ session('success') }}</div>
@endif

<div class="attendance-detail">
  <h2 class="title">勤怠詳細</h2>

  @php
    $displayCheckIn = old(
        'check_in_time',
        $pendingEdit?->after_check_in
            ? \Carbon\Carbon::parse($pendingEdit->after_check_in)->format('H:i')
            : ($attendance?->check_in_time
                ? \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i')
                : null)
    );

    $displayCheckOut = old(
        'check_out_time',
        $pendingEdit?->after_check_out
            ? \Carbon\Carbon::parse($pendingEdit->after_check_out)->format('H:i')
            : ($attendance?->check_out_time
                ? \Carbon\Carbon::parse($attendance->check_out_time)->format('H:i')
                : null)
    );

    $editBreaks = $pendingEdit?->editBreaks ?? $attendance->breaks;
    $isPending = $pendingEdit !== null;
    $totalBreaks = $isPending ? $editBreaks->count() : $editBreaks->count() + 1;
    $displayReason = optional($pendingEdit)->reason ?? $attendance->remarks ?? '';
    $canEdit = auth()->user()->is_admin || !$isPending || ($pendingEdit->status ?? 1) !== 0;
  @endphp

  @if(!($isApprovalMode ?? false))
  <form action="
    @if(auth()->user()->is_admin)
      {{ $attendance->exists ? route('admin.attendance.update', $attendance->id) : route('admin.attendance.store') }}
    @else
      {{ route('attendance.requestEdit', $attendance->id ?? 0) }}
    @endif
    " method="post">
    @csrf
    @if(auth()->user()->is_admin && $attendance->exists)
      @method('PUT')
    @endif

    <input type="hidden" name="work_date" value="{{ $date ?? ($attendance->work_date ?? now()->format('Y-m-d')) }}">
    <input type="hidden" name="user_id" value="{{ $attendance->user->id ?? request('user_id') }}">
  @endif

  <table class="attendance-detail__table">
    <tr>
      <th>名前</th>
      <td>{{ $attendance->user->name ?? '－' }}</td>
    </tr>
    <tr>
      <th>日付</th>
      <td>
        @if($attendance->work_date)
          <span>{{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年') }}</span>
          <span class="date-space"></span>
          <span>{{ \Carbon\Carbon::parse($attendance->work_date)->format('m月d日') }}</span>
        @endif
      </td>
    </tr>
    <tr>
      <th>出勤・退勤</th>
      <td>
        @if($pendingEdit)
          <p>{{ $displayCheckIn ?? '' }} <span class="date-space__time--edit"></span> ～ <span class="date-space__time--edit"></span> {{ $displayCheckOut ?? ''}}</p>
        @else
          <input type="time" name="check_in_time" value="{{ $displayCheckIn ?? '' }}">
          <span class="date-space__time"></span> ～ <span class="date-space__time"></span>
          <input type="time" name="check_out_time" value="{{ $displayCheckOut ?? '' }}"><br>
          @if($errors->has('check_in_time') || $errors->has('check_out_time'))
            <p class="detail-form__error-message">{{ $errors->first('check_in_time') ?: $errors->first('check_out_time') }}</p>
          @endif
        @endif
      </td>
    </tr>

    @for ($i = 0; $i < $totalBreaks; $i++)
      @php
        $break = $editBreaks[$i] ?? null;
        $breakStart = old("breaks.$i.start", $break ? (\Carbon\Carbon::parse($break->after_break_start_time ?? $break->break_start_time)->format('H:i')) : null);
        $breakEnd   = old("breaks.$i.end", $break ? (\Carbon\Carbon::parse($break->after_break_end_time ?? $break->break_end_time)->format('H:i')) : null);
        if ($isPending && !$breakStart && !$breakEnd) continue;
      @endphp
      <tr>
        <th>休憩{{ $i + 1 }}</th>
        <td>
          @if($isPending)
            <p>{{ $breakStart ?? '' }} <span class="date-space__time--edit"></span> ～ <span class="date-space__time--edit"></span> {{ $breakEnd ?? '' }}</p>
          @else
            <input type="time" name="breaks[{{ $i }}][start]" value="{{ $breakStart ?? '' }}">
            <span class="date-space__time"></span> ～ <span class="date-space__time"></span>
            <input type="time" name="breaks[{{ $i }}][end]" value="{{ $breakEnd ?? '' }}"><br>
            @error("breaks.$i")
              <p class="detail-form__error-message">{{ $message }}</p>
            @enderror
          @endif
        </td>
      </tr>
    @endfor

    <tr>
      <th>備考</th>
      <td>
        @if($pendingEdit)
          <p>{{ $displayReason }}</p>
        @else
          <textarea name="reason" cols="40" rows="2">{{ old('reason', $attendance->reason ?? '' ) }}</textarea><br>
          @error('reason')
            <p class="detail-form__error-message">{{ $message }}</p>
          @enderror
        @endif
      </td>
    </tr>
  </table>

  <div class="attendance-detail__form-btn">
    @if($isApprovalMode ?? false)
      @if(!empty($pendingEdit) && $pendingEdit->status === 0)
        <form action="{{ route('admin.attendance.approve', ['attendance_correct_request' => $pendingEdit->id]) }}" method="post">
          @csrf
          <button type="submit" class="btn btn-success">承認</button>
        </form>
      @else
        <button type="button" class="btn btn-secondary" disabled>承認済み</button>
      @endif
    @elseif($canEdit)
      <button type="submit" class="btn btn-primary">修正</button>
    @else
      <p class="text-muted">＊承認待ちのため修正はできません</p>
    @endif
  </div>

  @if(!($isApprovalMode ?? false))
  </form>
  @endif
</div>
@endsection