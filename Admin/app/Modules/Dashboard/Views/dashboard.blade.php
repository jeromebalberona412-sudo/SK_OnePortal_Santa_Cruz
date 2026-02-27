@extends('layouts.app')

@section('title', 'Dashboard')

@section('head')
@endsection

@section('content')
<!-- Include Header -->
@include('layout::layouts.header')

<!-- Include Sidebar -->
@include('layout::layouts.sidebar')

<div class="main-content-modern" id="mainContent">
    <div style="padding: 20px;">
        <h1>Hello World</h1>
    </div>
</div>
@endsection