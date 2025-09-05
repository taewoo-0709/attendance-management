@extends($layout)

@section('css')
<link rel="stylesheet" href="{{ asset('css/application.css') }}?v={{ time() }}">
@endsection

@section('content')
<div class="application-container">
  <h2 class="application-title">申請一覧</h2>

  <ul class="application-tabs">
    <li class="application-tab-item">
      <a class="application-tab-link {{ $status == 0 ? 'active' : '' }}"
        href="{{ route('attendance.request.list', ['status' => 0]) }}">
        承認待ち
      </a>
    </li>
    <li class="application-tab-item">
      <a class="application-tab-link {{ $status == 1 ? 'active' : '' }}"
        href="{{ route('attendance.request.list', ['status' => 1]) }}">
        承認済み
      </a>
    </li>
  </ul>

  <table class="application-table">
    <thead>
      <tr>
        <th>状態</th>
        <th>名前</th>
        <th>対象日時</th>
        <th>申請理由</th>
        <th>申請日時</th>
        <th>詳細</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($requests as $request)
        <tr>
          <td>{{ $request->status == 0 ? '承認待ち' : '承認済み' }}</td>
          <td>{{ $request->attendance->user->name }}</td>
          <td>{{ \Carbon\Carbon::parse($request->attendance->work_date)->format('Y/m/d') }}</td>
          <td>{{ $request->reason }}</td>
          <td>{{ $request->created_at->format('Y/m/d') }}</td>
          <td>
            <a class="application-detail-link" href="{{ $isAdmin
              ? route('admin.attendance.approveview', ['attendance_correct_request' => $request->id])
              : route('attendance.detail', ['id' => $request->attendance_id, 'date' => $request->attendance->work_date]) }}">
                詳細
            </a>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="6" class="application-empty">データがありません</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection