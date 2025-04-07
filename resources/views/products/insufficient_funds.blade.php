@extends('layouts.master')
@section('title', 'Insufficient Funds')
@section('content')
<div class="row">
    <div class="m-4 col-sm-8">
        <h1>Insufficient Funds</h1>
        <p>You do not have enough credit to purchase <strong>{{ $product->name }}</strong>.</p>
        <p>Price: {{ $product->price }}</p>
        <p>Your Credit: {{ auth()->user()->credit }}</p>
        <p>Please contact an employee to add more credit to your account.</p>
        <a href="{{ route('products_list') }}" class="btn btn-primary">Back to Products</a>
    </div>
</div>
@endsection