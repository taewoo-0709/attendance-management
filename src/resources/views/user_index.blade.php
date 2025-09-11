@extends('layouts.admin_nav')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user_index.css') }}?v={{ time() }}">
@endsection

@section('content')
<div class="staff-container">
  <h2 class="staff-title">スタッフ一覧</h2>

  <table class="staff-table">
    <thead>
      <tr>
        <th>名前</th>
        <th>メールアドレス</th>
        <th>月次勤怠</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($staffs as $staff)
        <tr>
          <td>{{ $staff->name }}</td>
          <td>{{ $staff->email }}</td>
          <td>
            @php
              $detailRoute = $isAdmin
                ? 'admin.attendance.staff'
                : 'attendance.detail';
              $params = [
                'id' => $staff->id,
                'date' => $staff->work_date
              ];
            @endphp
            <a class="staff-detail-link" href="{{ route($detailRoute, $params) }}">
              詳細
            </a>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
@endsection