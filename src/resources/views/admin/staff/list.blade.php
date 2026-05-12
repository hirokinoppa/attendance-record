@extends('layouts.app3')

@section('title', 'スタッフ一覧')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin-staff-list.css') }}">
@endsection

@section('content')
    <div class="admin-staff">
        <div class="admin-staff__inner">
            <h1 class="admin-staff__heading">スタッフ一覧</h1>

            <div class="admin-staff__table-wrapper">
                <table class="admin-staff__table">
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
                                    <a
                                        href="{{ route('admin.attendance.staff', ['id' => $staff->id]) }}"
                                        class="admin-staff__detail-link"
                                    >
                                        詳細
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