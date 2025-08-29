@extends('layouts.staff_nav')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}?v={{ time() }}">
@endsection

@section('content')
@if(session('success'))
  <div class="alert-message">
    {{ session('success') }}
  </div>
@endif

<div class="attendance-detail">
  <h2 class="title">勤怠詳細</h2>

  <form action="/attendance/detail/{{ $attendance->id }}" method="post">
    @csrf
    <table class="attendance-detail__table">
      <tr>
        <th>名前</th>
        <td>{{ $attendance->user->name }}</td>
      </tr>
      <tr>
        <th>日付</th>
        <td>
          {{ $attendance->work_date->format('Y年 m月d日') }}
        </td>
      </tr>
      <tr>
        <th>出勤・退勤</th>
        <td>
          <input type="time" name="check_in_time"
            value="{{ $attendance->check_in_time?->format('H:i') }}">
          ～
          <input type="time" name="check_out_time"
            value="{{ $attendance->check_out_time?->format('H:i') }}">
        </td>
      </tr>

      {{-- 休憩時間 --}}
      @php
          $breakCount = $attendance->breaks->count();
      @endphp
      @for ($i = 0; $i < $breakCount + 1; $i++)
        @php
            $break = $attendance->breaks[$i] ?? null;
        @endphp
        <tr>
          <th>休憩{{ $i+1 }}</th>
          <td>
            <input type="time" name="breaks[{{ $i }}][start]"
              value="{{ $break?->break_start_time?->format('H:i') }}">
            ～
            <input type="time" name="breaks[{{ $i }}][end]"
              value="{{ $break?->break_end_time?->format('H:i') }}">
          </td>
        </tr>
      @endfor

      <tr>
        <th>備考</th>
        <td>
          <textarea name="remarks" cols="40" rows="2">{{ $attendance->remarks }}</textarea>
        </td>
      </tr>
    </table>

    {{-- ボタン部分 --}}
    <div class="attendance-detail__form-btn">
      @if (auth()->user()->is_admin)
          <button type="submit" class="btn btn-primary">修正</button>
      @else
          @if ($attendance->edits()->where('status', \App\Models\AttendanceEdit::STATUS_PENDING)->exists())
              <p class="text-muted">承認待ちのため修正はできません</p>
          @else
              <button type="submit" class="btn btn-primary">修正</button>
          @endif
      @endif
    </div>
  </form>
</div>
@endsection