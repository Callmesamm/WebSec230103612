@extends('layouts.master')
@section('title', 'User Profile')
@section('content')
<div class="row">
    <div class="m-4 col-sm-8">
        <h1>Welcome, {{ $user->name }}</h1>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <table class="table table-striped">
            <tr>
                <th>Name</th>
                <td>{{ $user->name }}</td>
            </tr>
            <tr>
                <th>Email</th>
                <td>{{ $user->email }}</td>
            </tr>
            <tr>
                <th>Credit</th>
                <td>{{ $user->credit }}</td>
            </tr>
            <tr>
                <th>Roles</th>
                <td>
                    @foreach($user->roles as $role)
                        <span class="badge bg-primary">{{ $role->name }}</span>
                    @endforeach
                </td>
            </tr>
            <tr>
                <th>Permissions</th>
                <td>
                    @foreach($permissions as $permission)
                        <span class="badge bg-success">{{ $permission->display_name }}</span>
                    @endforeach
                </td>
            </tr>
            <tr>
                <th>Purchases</th>
                <td>
                    @if(isset($purchases) && $purchases->count() > 0)
                        <ul>
                            @foreach($purchases as $purchase)
                                <li>{{ $purchase->product->name }} (Purchased on {{ $purchase->created_at->format('Y-m-d H:i:s') }})</li>
                            @endforeach
                        </ul>
                    @else
                        <span>No purchases yet.</span>
                    @endif
                </td>
            </tr>
        </table>

        <div class="row">
            <div class="col col-6"></div>
            @if(auth()->user()->hasPermissionTo('admin_users') || auth()->id() == $user->id)
                <div class="col col-4">
                    <a class="btn btn-primary" href="{{ route('edit_password', $user->id) }}">Change Password</a>
                </div>
            @else
                <div class="col col-4"></div>
            @endif
            @if(auth()->user()->hasPermissionTo('edit_users') || auth()->id() == $user->id)
                <div class="col col-2">
                    <a href="{{ route('users_edit', $user->id) }}" class="btn btn-success form-control">Edit</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection