@extends('layouts.master')
@section('title', 'Insufficient Credit')
@section('content')
    <h1>Insufficient Credit</h1>
    <p>You don’t have enough credit to purchase {{$product->name}} (Price: {{$product->price}}).</p>
    <p>Your current credit: {{auth()->user()->credit}}</p>
    <a href="{{route('profile')}}" class="btn btn-primary">Go to Profile</a>
@endsection