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
        <form method="GET" class="account-filter-grid mb-3">
            <select name="role" aria-label="System role"><option value="">All system roles</option>@foreach(\App\Role::orderBy('name')->get() as $role)<option value="{{ $role->name }}" {{ request('role')===$role->name?'selected':'' }}>{{ $role->name }}</option>@endforeach</select>
            <select name="account_type" aria-label="Account type"><option value="">All account types</option>@foreach(['student','faculty','dependent'] as $type)<option value="{{ $type }}" {{ request('account_type')===$type?'selected':'' }}>{{ ucfirst($type) }}</option>@endforeach</select>
            <select name="status" aria-label="Status"><option value="">All statuses</option><option value="Active" {{ request('status')==='Active'?'selected':'' }}>Active</option></select>
            <button class="btn btn-secondary">Filter</button>
        </form>
        <div class="table-responsive-shell emr-data-table-wrap">
            <table class="table table-hover user-data-table emr-data-table data-table is-wide">
                <thead>
                    <tr><th>System Role</th><th>Account Type</th><th>Identifier</th><th>Full Name</th><th>Email</th><th>Status</th><th>Last Login</th><th class="text-right">Actions</th></tr>
                </thead>
                <tbody id="userTable">
                    @forelse ($users as $user)
                        <tr>
                            <td><span class="role-badge">{{ $user->patientAccount ? 'Patient' : $user->role->name }}</span></td>
                            <td><span class="role-badge">{{ $user->patientAccount ? ucfirst($user->patientAccount->patient_type) : 'N/A' }}</span></td>
                            <td>{{ optional($user->patientAccount)->student_id_number ?: (optional($user->patientAccount)->faculty_id_number ?: $user->username) }}</td>
                            <td><strong>{{ $user->fullName() }}</strong></td>
                            <td>{{ $user->email }}</td>
                            <td><span class="emr-status-badge active">Active</span></td>
                            <td>{{ $user->last_login_at ? $user->last_login_at->format('M j, Y g:i A') : 'Never' }}</td>
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
