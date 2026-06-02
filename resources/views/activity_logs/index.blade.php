@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h3 class="mb-3">Activity History</h3>
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Date</th>
                <th>User</th>
                <th>Action</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                <tr>
                    <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                    <td>{{ $log->user ? $log->user->name : 'Unknown User' }}</td>
                    <td>{{ $log->action }}</td>
                    <td>{{ $log->description }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">No activity yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
