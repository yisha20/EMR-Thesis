@extends('layouts.app')

@section('content')
<div class="card border-info user-table-card">
    <div class="card-header border-info user-table-header">
        <ul class="nav nav-tabs card-header-tabs">
            <li class="nav-item"><a class="nav-link active" href="{{ route('users.index') }}">Users</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('users.create') }}">Add New User</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('users.archive') }}">Archive</a></li>
        </ul>
    </div>
    <div class="card-body">
        <div class="emr-filter-search filter-search emr-local-search">
            <i class="fa fa-search"></i>
            <input type="search" id="userSearch" placeholder="Search by name, username, email, or role" aria-label="Search users">
        </div>
        <div class="table-responsive-shell emr-data-table-wrap">
            <table class="table table-hover user-data-table emr-data-table data-table is-wide">
                <thead>
                    <tr><th>Role</th><th>Username</th><th>Full Name</th><th>Email</th><th>Status</th><th>Last Login</th><th>Date Created</th><th class="text-right">Action</th></tr>
                </thead>
                <tbody id="userTable">
                    @forelse ($users as $user)
                        <tr>
                            <td><span class="role-badge role-{{ strtolower($user->role->name) }}">{{ $user->role->name }}</span></td>
                            <td>{{ $user->username }}</td>
                            <td><strong>{{ $user->fullName() }}</strong></td>
                            <td>{{ $user->email }}</td>
                            <td><span class="emr-status-badge active">Active</span></td>
                            <td>{{ $user->last_login_at ? $user->last_login_at->format('M j, Y g:i A') : 'Never' }}</td>
                            <td>{{ $user->created_at->format('M j, Y') }}</td>
                            <td class="action-cell">
                                <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="table-action-group">
                                    @csrf @method('DELETE')
                                    <a href="{{ route('users.show', $user->id) }}" class="table-action-button" aria-label="View user" title="View user" data-toggle="tooltip"><i class="fa fa-eye"></i></a>
                                    <a href="{{ route('users.edit', $user->id) }}" class="table-action-button" aria-label="Edit user" title="Edit user" data-toggle="tooltip"><i class="fa fa-edit"></i></a>
                                    <button type="submit" class="table-action-button table-action-danger btn" aria-label="Archive user" title="Archive user" data-toggle="tooltip" data-confirm="Archive {{ $user->fullName() }}?" data-confirm-title="Archive user"><i class="fa fa-archive"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8">@include('includes.empty-state', ['title' => 'No users found.', 'icon' => 'fa-users'])</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
document.getElementById('userSearch').addEventListener('input', function () {
    var value = this.value.toLowerCase();
    document.querySelectorAll('#userTable tr').forEach(function (row) {
        row.style.display = row.textContent.toLowerCase().indexOf(value) > -1 ? '' : 'none';
    });
});
</script>
@endpush
