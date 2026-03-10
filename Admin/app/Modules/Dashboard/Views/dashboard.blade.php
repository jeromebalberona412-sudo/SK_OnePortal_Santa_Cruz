@extends('layouts.app')

@section('title', 'Dashboard')

@section('head')
@vite(['app/Modules/Dashboard/assets/css/dashboard.css'])
@vite(['app/Modules/Dashboard/assets/js/dashboard.js'])
<style>
	.dashboard-bg-layer,
	.dashboard-bg-tint,
	.dashboard-bg-vignette,
	.dashboard-bg-scanline {
		position: fixed;
		inset: 0;
		pointer-events: none;
	}

	.dashboard-bg-layer {
		z-index: 1;
		background-image: url("{{ Vite::asset('app/Modules/Authentication/assets/Flag_of_Santa_Cruz__Laguna-removebg-preview.png') }}");
		background-position: center;
		background-repeat: no-repeat;
		background-size: cover;
		opacity: 0.2;
		filter: grayscale(0.98) brightness(0.2) saturate(0.35) contrast(1.05);
	}

	.dashboard-bg-tint {
		z-index: 2;
		background:
			linear-gradient(180deg, rgba(1, 7, 17, 0.74) 0%, rgba(2, 11, 24, 0.9) 52%, rgba(1, 5, 12, 0.96) 100%),
			linear-gradient(120deg, rgba(9, 29, 61, 0.42) 0%, rgba(2, 8, 18, 0.1) 50%, rgba(6, 22, 46, 0.36) 100%);
	}

	.dashboard-bg-vignette {
		z-index: 2;
		box-shadow: inset 0 0 190px rgba(0, 0, 0, 0.72);
	}

	.dashboard-bg-scanline {
		z-index: 3;
		background: repeating-linear-gradient(
			to bottom,
			rgba(126, 175, 255, 0.05) 0,
			rgba(126, 175, 255, 0.05) 1px,
			transparent 1px,
			transparent 4px
		);
		opacity: 0.2;
	}

	.dashboard-bg-scanline::before {
		content: '';
		position: absolute;
		inset-inline: 0;
		top: -130px;
		height: 110px;
		background: linear-gradient(
			to bottom,
			transparent,
			rgba(149, 197, 255, 0.14),
			transparent
		);
		animation: dashboard-scanline-shift 7s linear infinite;
	}

	#mainContent {
		position: relative;
		z-index: 4;
	}

	@keyframes dashboard-scanline-shift {
		0% {
			transform: translateY(-140px);
		}
		100% {
			transform: translateY(calc(100vh + 140px));
		}
	}
</style>
@endsection

@section('content')
@include('layout::layouts.header')
@include('layout::layouts.sidebar')

@php
	$statCards = [
		['label' => 'Total managed users', 'metricKey' => 'totalManagedUsers', 'tone' => 'healthy'],
		['label' => 'SKFederation accounts', 'metricKey' => 'skFederationAccounts', 'tone' => 'healthy'],
		['label' => 'SKOfficial accounts', 'metricKey' => 'skOfficialAccounts', 'tone' => 'healthy'],
		['label' => 'Active accounts', 'metricKey' => 'activeAccounts', 'tone' => 'healthy'],
		['label' => 'Inactive accounts', 'metricKey' => 'inactiveAccounts', 'tone' => 'warning'],
		['label' => 'Pending accounts', 'metricKey' => 'pendingAccounts', 'tone' => 'warning'],
		['label' => 'Suspended accounts', 'metricKey' => 'suspendedAccounts', 'tone' => 'critical'],
		['label' => 'Unassigned accounts', 'metricKey' => 'unassignedAccounts', 'tone' => 'critical'],
	];
@endphp

<div class="dashboard-bg-layer" aria-hidden="true"></div>
<div class="dashboard-bg-tint" aria-hidden="true"></div>
<div class="dashboard-bg-vignette" aria-hidden="true"></div>
<div class="dashboard-bg-scanline" aria-hidden="true"></div>

<div id="mainContent" class="gov-dashboard dashboard-shell" x-data="dashboardConsole()" aria-label="Dashboard content">
	<section class="dashboard-row dashboard-row--stats">
		@foreach ($statCards as $card)
			@include('dashboard::components.statcard', $card)
		@endforeach
	</section>

	<section class="dashboard-row dashboard-row--governance">
		@include('dashboard::components.securitymetrics')
		@include('dashboard::components.governancealerts')
	</section>

	<section class="dashboard-row dashboard-row--operations">
		@include('dashboard::components.audittable')
		@include('dashboard::components.quickactions')
		@include('dashboard::components.platformhealth')
	</section>

	<section class="dashboard-row dashboard-row--analytics">
		@include('dashboard::components.trendchart')
		@include('dashboard::components.barangaydistribution')
	</section>
</div>
@endsection