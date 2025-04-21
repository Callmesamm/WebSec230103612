@extends('layouts.master')
@section('title', auth()->user()->hasRole('Admin') ? 'Users' : 'Customers')
@section('content')
<div class="row mt-2">
    <div class="col col-10">
        <h1>{{ auth()->user()->hasRole('Admin') ? 'Users' : 'Customers' }}</h1>
    </div>
</div>

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

<form>
    <div class="row">
        <div class="col col-sm-2">
            <input name="keywords" type="text" class="form-control" placeholder="Search Keywords" value="{{ request()->keywords }}" />
        </div>
        <div class="col col-sm-1">
            <button type="submit" class="btn btn-primary">Submit</button>
        </div>
        <div class="col col-sm-1">
            <button type="reset" class="btn btn-danger">Reset</button>
        </div>
    </div>
</form>

@if(auth()->user()->hasRole('Admin'))
    <div class="row mt-2">
        <div class="col col-12">
            <h2>Add Employee</h2>
            <form action="{{route('users.create_employee')}}" method="POST">
                @csrf
                <div class="row">
                    <div class="col col-sm-3">
                        <input name="name" type="text" class="form-control" placeholder="Name" required />
                    </div>
                    <div class="col col-sm-3">
                        <input name="email" type="email" class="form-control" placeholder="Email" required />
                    </div>
                    <div class="col col-sm-3">
                        <input name="password" type="password" class="form-control" placeholder="Password" required />
                    </div>
                    <div class="col col-sm-3">
                        <button type="submit" class="btn btn-primary">Add Employee</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endif

<div class="card mt-2">
    <div class="card-body">
        @if(isset($users) && $users->count() > 0)
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Credit</th>
                        <th scope="col">Roles</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td scope="col">{{ $user->id }}</td>
                            <td scope="col">{{ $user->name }}</td>
                            <td scope="col">{{ $user->email }}</td>
                            <td scope="col">{{ $user->credit }}</td>
                            <td scope="col">
                                @foreach($user->roles as $role)
                                    <span class="badge bg-primary">{{ $role->name }}</span>
                                @endforeach
                            </td>
                            <td scope="col">
                                @can('edit_users')
                                    <a class="btn btn-primary" href="{{ route('users_edit', [$user->id]) }}">Edit</a>
                                @endcan
                                @if(auth()->user()->hasRole('Employee') && $user->hasRole('Customer'))
                                    <form action="{{ route('users.add_credit', $user->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <input type="number" name="credit" min="1"  required class="form-control d-inline" style="width: 100px;" placeholder="Add Credit">
                                        <button type="submit" class="btn btn-success">Add</button>
                                    </form>
                                @endif
                               
                            
                                @can('admin_users')
                                    <a class="btn btn-primary" href="{{ route('edit_password', [$user->id]) }}">Change Password</a>
                                @endcan
                                @can('delete_users')
                                    <a class="btn btn-danger" href="{{ route('users_delete', [$user->id]) }}">Delete</a>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No {{ auth()->user()->hasRole('Admin') ? 'users' : 'customers' }} found.</p>
        @endif
    </div>
</div>
@endsection