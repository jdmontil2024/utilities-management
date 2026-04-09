@extends('layouts.app')
@section('title', 'Page Not Found')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center py-5">
            <h1 class="display-1 text-muted">404</h1>
            <h2>Page Not Found</h2>
            <p>The page you are looking for could not be found.</p>
            <a href="{{ url('/dashboard') }}" class="btn btn-primary">Go to Dashboard</a>
        </div>
    </div>
</div>
@endsection
