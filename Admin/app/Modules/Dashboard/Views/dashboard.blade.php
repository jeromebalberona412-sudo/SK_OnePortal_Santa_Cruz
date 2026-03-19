@extends('layouts.app')

@section('title', 'Dashboard')

@section('head')
@vite(['app/Modules/Dashboard/assets/css/dashboard.css'])
@vite(['app/Modules/Dashboard/assets/js/dashboard.js'])
@endsection

@section('content')
@include('layout::layouts.header')
@include('layout::layouts.sidebar')

@php
	$statCards = [
		['label' => 'Total Users', 'metricKey' => 'totalUsers', 'tone' => 'azure', 'icon' => 'users'],
		['label' => 'Federation Accounts Count', 'metricKey' => 'federationAccounts', 'tone' => 'teal', 'icon' => 'federation'],
		['label' => 'Official Accounts Count', 'metricKey' => 'officialAccounts', 'tone' => 'violet', 'icon' => 'officials'],
		['label' => 'Current Active Accounts', 'metricKey' => 'currentActiveAccounts', 'tone' => 'indigo', 'icon' => 'activity'],
	];
@endphp

<div id="mainContent" class="gov-dashboard dashboard-shell" x-data="dashboardConsole()" aria-label="Dashboard content">
	<section class="dashboard-row dashboard-row--stats">
		@foreach ($statCards as $card)
			@include('dashboard::components.statcard', $card)
		@endforeach
	</section>

	<section class="dashboard-row dashboard-row--analytics">
		@include('dashboard::components.trendchart')
		@include('dashboard::components.platformhealth')
	</section>

	<section class="dashboard-row dashboard-row--operations">
		@include('dashboard::components.audittable')
		@include('dashboard::components.barangaydistribution')
	</section>
</div>
@endsection