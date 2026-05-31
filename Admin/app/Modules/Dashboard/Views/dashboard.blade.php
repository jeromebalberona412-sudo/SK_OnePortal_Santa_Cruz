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
	$statCards = [
		['label' => 'Total Users', 'metricKey' => 'totalUsers', 'tone' => 'azure', 'icon' => 'users', 'route' => null],
		['label' => 'Federation Accounts Count', 'metricKey' => 'federationAccounts', 'tone' => 'teal', 'icon' => 'federation', 'route' => 'accounts.federation.index'],
		['label' => 'Official Accounts Count', 'metricKey' => 'officialAccounts', 'tone' => 'violet', 'icon' => 'officials', 'route' => 'accounts.officials.index'],
		['label' => 'Kabataan Accounts Count', 'metricKey' => 'kabataanAccounts', 'tone' => 'cyan', 'icon' => 'kabataan', 'route' => 'manage-kabataan.index'],
		['label' => 'Deleted SK Federation', 'metricKey' => 'deletedSkFederation', 'tone' => 'red', 'icon' => 'trash', 'route' => 'archived.deleted-sk-federation'],
		['label' => 'Deleted SK Officials', 'metricKey' => 'deletedSkOfficials', 'tone' => 'red', 'icon' => 'trash', 'route' => 'archived.deleted-sk-officials'],
		['label' => 'SK Federation Records', 'metricKey' => 'skFederationRecords', 'tone' => 'red', 'icon' => 'federation', 'route' => 'archived.sk-federation-records'],
		['label' => 'SK Officials Records', 'metricKey' => 'skOfficialsRecords', 'tone' => 'red', 'icon' => 'officials', 'route' => 'archived.sk-officials-records'],
	];
@endphp

<div id="mainContent" class="gov-dashboard dashboard-shell" x-data="dashboardConsole()" aria-label="Dashboard content">
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
					<option value="2023">2023</option>
					<option value="2024">2024</option>
					<option value="2025">2025</option>
					<option value="2026" selected>2026</option>
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
					<option value="2023-2026">2023 - 2026</option>
					<option value="2024-2027">2024 - 2027</option>
					<option value="2025-2028">2025 - 2028</option>
				</select>
			</div>
		</div>
	</div>

	<!-- ══ Stat Cards — 2-row grid ═══════════════════════════ -->
	<section class="stats-2row-grid">
		@foreach ($statCards as $card)
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
		<div class="quick-actions-scroll">
			<a href="{{ route('profile') }}" class="qa-btn qa-blue">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
					<circle cx="12" cy="7" r="4"></circle>
					<path d="M5.5 21a6.5 6.5 0 0 1 13 0"></path>
				</svg>
				My Profile
			</a>
			<a href="{{ route('accounts.federation.index') }}" class="qa-btn qa-green">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
					<rect x="3" y="4" width="7" height="7" rx="1.4" />
					<rect x="14" y="4" width="7" height="7" rx="1.4" />
					<rect x="8.5" y="14" width="7" height="7" rx="1.4" />
					<path d="M10 7.5h4" />
					<path d="M12 11v3" />
				</svg>
				SK Federation
			</a>
			<a href="{{ route('accounts.officials.index') }}" class="qa-btn qa-green">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
					<path d="M12 4l8 4v2H4V8l8-4z" />
					<path d="M5 10v7" />
					<path d="M9 10v7" />
					<path d="M15 10v7" />
					<path d="M19 10v7" />
					<path d="M3 19h18" />
				</svg>
				SK Officials
			</a>
			<a href="{{ route('manage-kabataan.index') }}" class="qa-btn qa-purple">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
					<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
					<circle cx="9" cy="7" r="4"></circle>
					<line x1="19" y1="8" x2="19" y2="14"></line>
					<line x1="22" y1="11" x2="16" y2="11"></line>
				</svg>
				Kabataan SK
			</a>
			<a href="{{ route('barangay-logos.index') }}" class="qa-btn qa-purple">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
					<rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
					<circle cx="8.5" cy="8.5" r="1.5"></circle>
					<polyline points="21 15 16 10 5 21"></polyline>
				</svg>
				Barangay Logos
			</a>
			<a href="{{ route('contact.manage') }}" class="qa-btn qa-yellow">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
					<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
				</svg>
				Manage Contacts
			</a>
			<a href="{{ route('auditlogs.index') }}" class="qa-btn qa-yellow">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
					<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
					<polyline points="14,2 14,8 20,8"></polyline>
					<line x1="16" y1="13" x2="8" y2="13"></line>
					<line x1="16" y1="17" x2="8" y2="17"></line>
				</svg>
				Audit Log
			</a>
			<a href="{{ route('archived.deleted-sk-federation') }}" class="qa-btn qa-red">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
					<polyline points="3 6 5 6 21 6"></polyline>
					<path d="M19 6l-1 14H6L5 6"></path>
					<path d="M10 11v6"></path><path d="M14 11v6"></path>
					<path d="M9 6V4h6v2"></path>
				</svg>
				Deleted SK Federation
			</a>
			<a href="{{ route('archived.deleted-sk-officials') }}" class="qa-btn qa-red">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
					<polyline points="3 6 5 6 21 6"></polyline>
					<path d="M19 6l-1 14H6L5 6"></path>
					<path d="M10 11v6"></path><path d="M14 11v6"></path>
					<path d="M9 6V4h6v2"></path>
				</svg>
				Deleted SK Officials
			</a>
		</div>
	</div>

	<section class="dashboard-row dashboard-row--analytics">
		@include('dashboard::components.piechart')
		@include('dashboard::components.platformhealth')
	</section>

	<section class="dashboard-row dashboard-row--operations">
		@include('dashboard::components.audittable')
		@include('dashboard::components.barangaydistribution')
	</section>
</div>
@endsection