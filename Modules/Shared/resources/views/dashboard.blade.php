@extends('shared::layouts.app')

@section('content')

    <h1>ERP Dashboard</h1>

    <p>
        Welcome to your ERP SaaS Dashboard
    </p>

    <p>
        Tenant:
        {{ tenant()->id }}
    </p>

@endsection