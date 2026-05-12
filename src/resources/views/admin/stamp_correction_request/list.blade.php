@extends('layouts.app3')

@section('title', '申請一覧')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin-request-list.css') }}">
@endsection

@section('content')
    <div class="admin-request-list">
        <div class="admin-request-list__inner">
            <h1 class="admin-request-list__heading">申請一覧</h1>

            <div class="admin-request-list__tabs">
                <a
                    href="{{ route('admin.stamp_correction_request.list', ['status' => \App\Models\AttendanceCorrectionRequest::STATUS_PENDING]) }}"
                    class="admin-request-list__tab {{ $currentStatus === \App\Models\AttendanceCorrectionRequest::STATUS_PENDING ? 'is-active' : '' }}"
                >
                    承認待ち
                </a>

                <a
                    href="{{ route('admin.stamp_correction_request.list', ['status' => \App\Models\AttendanceCorrectionRequest::STATUS_APPROVED]) }}"
                    class="admin-request-list__tab {{ $currentStatus === \App\Models\AttendanceCorrectionRequest::STATUS_APPROVED ? 'is-active' : '' }}"
                >
                    承認済み
                </a>
            </div>

            <div class="admin-request-list__table-wrapper">
                <table class="admin-request-list__table">
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
                                <td>{{ $request['status'] }}</td>
                                <td>{{ $request['user_name'] }}</td>
                                <td>{{ $request['target_date'] }}</td>
                                <td>{{ $request['reason'] }}</td>
                                <td>{{ $request['requested_at'] }}</td>
                                <td>
                                    <a
                                        href="{{ route('admin.stamp_correction_request.approve', ['id' => $request['id']]) }}"
                                        class="admin-request-list__detail-link"
                                    >
                                        詳細
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="admin-request-list__empty">
                                    申請はありません
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection