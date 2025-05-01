@extends('layouts.master')
@section('title', 'Reset Password')
@section('content')
<div class="d-flex justify-content-center">
    <div class="card m-4 col-sm-6">
        <div class="card-body">
            <h2>Reset Password</h2>
            <form action="{{ route('password.update') }}" method="post">
                {{ csrf_field() }}
                <input type="hidden" name="token" value="{{ $token }}">
                <div class="form-group">
                    @foreach($errors->all() as $error)
                        <div class="alert alert-danger">
                            <strong>Error!</strong> {{ $error }}
                        </div>
                    @endforeach
                </div>
                <div class="form-group mb-2">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" class="form-control" placeholder="email" name="email" value="{{ $email ?? old('email') }}" required>
                </div>
                <div class="form-group mb-2">
                    <label for="password" class="form-label">Password:</label>
                    <input type="password" class="form-control" placeholder="password" name="password" required>
                </div>
                <div class="form-group mb-2">
                    <label for="password_confirmation" class="form-label">Confirm Password:</label>
                    <input type="password" class="form-control" placeholder="confirm password" name="password_confirmation" required>
                </div>
                <div class="form-group mb-2">
                    <button type="submit" class="btn btn-primary">Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection