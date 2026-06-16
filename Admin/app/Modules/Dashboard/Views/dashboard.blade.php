@extends('layouts.app')

@section('title', 'Dashboard')

@section('head')
@vite(['app/Modules/Dashboard/assets/css/dashboard.css'])
@vite(['app/Modules/Dashboard/assets/js/dashboard.js'])
@endsection

@section('content')
@include('layout::header')
@include('layout::sidebar')

@php
	$topRowCards = [
		['label' => 'Total Users', 'metricKey' => 'totalUsers', 'value' => $accountMetrics['totalUsers'], 'tone' => 'azure', 'icon' => 'users', 'route' => null],
		['label' => 'Total SK Federations', 'metricKey' => 'federationAccounts', 'value' => $accountMetrics['federationAccounts'], 'tone' => 'teal', 'icon' => 'federation', 'route' => null],
		['label' => 'Total SK Officials', 'metricKey' => 'officialAccounts', 'value' => $accountMetrics['officialAccounts'], 'tone' => 'violet', 'icon' => 'officials', 'route' => null],
		['label' => 'Total Kabataan', 'metricKey' => 'kabataanAccounts', 'value' => $accountMetrics['kabataanAccounts'], 'tone' => 'cyan', 'icon' => 'kabataan', 'route' => 'manage-kabataan.index'],
	];

	$bottomRowCards = [
		['label' => 'Deleted SK Federation', 'metricKey' => 'deletedSkFederation', 'value' => $accountMetrics['deletedSkFederation'], 'tone' => 'red', 'icon' => 'trash', 'route' => null],
		['label' => 'Deleted SK Officials', 'metricKey' => 'deletedSkOfficials', 'value' => $accountMetrics['deletedSkOfficials'], 'tone' => 'red', 'icon' => 'trash', 'route' => null],
		['label' => 'SK Federation Records', 'metricKey' => 'skFederationRecords', 'value' => $accountMetrics['skFederationRecords'], 'tone' => 'red', 'icon' => 'federation', 'route' => null],
		['label' => 'SK Officials Records', 'metricKey' => 'skOfficialsRecords', 'value' => $accountMetrics['skOfficialsRecords'], 'tone' => 'red', 'icon' => 'officials', 'route' => null],
	];
@endphp

<script>
    window.__DASHBOARD_DATA_URL__ = @json(route('dashboard.data'));
    window.__DASHBOARD_TERM_FILTERS__ = @json($termFilters ?? ['years' => [], 'terms' => []]);
</script>
<div id="mainContent" class="gov-dashboard dashboard-shell container-fluid" x-data="dashboardConsole()" aria-label="Dashboard content">
	<!-- ══ Page Header ══════════════════════════════════════ -->
	<div class="dash-page-header">
		<div class="dash-page-header-left">
			<h1 class="dash-page-title">Dashboard</h1>
			<p class="dash-page-welcome">Welcome back Admin</p>
		</div>
		<div class="dash-header-filters">
			<div class="dash-year-filter">
				<label for="yearSelect" class="dash-year-label">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
						<line x1="3" y1="10" x2="21" y2="10"></line>
						<line x1="8" y1="2" x2="8" y2="6"></line>
						<line x1="16" y1="2" x2="16" y2="6"></line>
					</svg>
					Year
				</label>
				<select id="yearSelect" class="dash-year-select">
					<option value="all">All Years</option>
				</select>
			</div>
			<div class="dash-term-filter">
				<label for="termSelect" class="dash-term-label">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<rect x="2" y="7" width="20" height="14" rx="2"></rect>
						<path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
					</svg>
					Term
				</label>
				<select id="termSelect" class="dash-term-select">
					<option value="all">All Terms</option>
				</select>
			</div>
		</div>
	</div>

	<!-- ══ Stat Cards — Top Row ═══════════════════════════ -->
	<section class="stats-grid stats-grid--top" aria-label="Top statistics">
		@foreach ($topRowCards as $card)
			@include('dashboard::components.statcard', $card)
		@endforeach
	</section>

	<!-- ══ Stat Cards — Bottom Row ═══════════════════════════ -->
	<section class="stats-grid stats-grid--bottom" aria-label="Archive statistics">
		@foreach ($bottomRowCards as $card)
			@include('dashboard::components.statcard', $card)
		@endforeach
	</section>

	<!-- ══ Quick Actions ═════════════════════════════════════ -->
	<div class="dash-section-card">
		<div class="dash-section-header">
			<div>
				<h2 class="dash-section-title">Quick Actions</h2>
			</div>
		</div>
		<div class="quick-actions-scroll" aria-label="Quick actions">
			<a href="{{ route('profile') }}" class="qa-btn qa-blue">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
						<circle cx="12" cy="7" r="4"></circle>
						<path d="M5.5 21a6.5 6.5 0 0 1 13 0"></path>
					</svg>
					My Profile
			</a>
			<a href="{{ route('manage-kabataan.index') }}" class="qa-btn qa-blue">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
						<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
						<circle cx="9" cy="7" r="4"></circle>
						<path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
						<path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
					</svg>
					Kabataan
			</a>
		</div>
	</div>

	<section class="distribution-analytics-grid" aria-label="Analytics panels">
		@include('dashboard::components.piechart')
		@include('dashboard::components.kkprofilingchart')
	</section>

	<section class="dash-panels-grid" aria-label="Operations panels">
		@include('dashboard::components.audittable', ['recentAuditActivity' => $recentAuditActivity])
		@include('dashboard::components.barangaydistribution', ['barangayDistribution' => $barangayDistribution])
	</section>
</div>
@endsection