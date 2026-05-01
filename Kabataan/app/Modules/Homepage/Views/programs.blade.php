@extends('homepage::layout')

@section('title', 'Programs - SK OnePortal Kabataan')

@section('content')
<div class="programs-main">
    {{-- ── HERO SECTION ── --}}
    <section class="programs-hero">
        <div class="programs-hero-inner">
            <h1>SK Programs & Opportunities</h1>
            <p>Discover education, health, sports, agriculture, and community programs happening across all barangays in Santa Cruz, Laguna.</p>
            <div class="about-hero-actions">
                <a href="#programs-grid" class="btn-hero-primary">Explore Programs</a>
                <a href="{{ route('about') }}" class="btn-hero-ghost">Learn More</a>
            </div>
        </div>
    </section>

    {{-- ── PROGRAMS SECTION ── --}}
    <section class="about-section">
        <div class="about-section-inner">
            {{-- Filter Bar --}}
            <div class="programs-filter-bar">
                <span class="programs-filter-label">Filter by:</span>
                <select class="programs-filter-select" id="categoryFilter">
                    <option value="">All Categories</option>
                    <option value="education">Education</option>
                    <option value="health">Health</option>
                    <option value="sports">Sports</option>
                    <option value="agriculture">Agriculture</option>
                    <option value="livelihood">Livelihood</option>
                </select>
                <select class="programs-filter-select" id="barangayFilter">
                    <option value="">All Barangays</option>
                    <option value="alipit">Alipit</option>
                    <option value="bagumbayan">Bagumbayan</option>
                    <option value="pagsawitan">Pagsawitan</option>
                    <option value="labuin">Labuin</option>
                </select>
                <select class="programs-filter-select" id="statusFilter">
                    <option value="">All Status</option>
                    <option value="open">Open for Registration</option>
                    <option value="ongoing">Ongoing</option>
                    <option value="completed">Completed</option>
                </select>
            </div>

            {{-- Programs Grid --}}
            <div class="programs-grid" id="programs-grid">
                @php
                $programs = [
                    [
                        'id' => 1,
                        'title' => 'Career Readiness Session for Senior High',
                        'category' => 'education',
                        'barangay' => 'Pagsawitan',
                        'description' => 'A practical CV and interview workshop for youth preparing for college and employment pathways.',
                        'participants' => '45 / 100',
                        'budget' => '₱50,000',
                        'progress' => 45,
                        'status' => 'open'
                    ],
                    [
                        'id' => 2,
                        'title' => 'Weekend Basketball Skills Clinic',
                        'category' => 'sports',
                        'barangay' => 'Labuin',
                        'description' => 'Open clinic focused on fundamentals, teamwork, and youth fitness monitoring for all skill levels.',
                        'participants' => '32 / 60',
                        'budget' => '₱35,000',
                        'progress' => 53,
                        'status' => 'open'
                    ],
                    [
                        'id' => 3,
                        'title' => 'Mental Health Peer Support Circle',
                        'category' => 'health',
                        'barangay' => 'Alipit',
                        'description' => 'A guided peer support activity and stress management session for kabataan volunteers.',
                        'participants' => '28 / 50',
                        'budget' => '₱20,000',
                        'progress' => 56,
                        'status' => 'ongoing'
                    ],
                    [
                        'id' => 4,
                        'title' => 'Youth Digital Freelancing Orientation',
                        'category' => 'livelihood',
                        'barangay' => 'Santisima Cruz',
                        'description' => 'Introductory orientation on online freelancing, portfolio basics, and responsible digital work habits.',
                        'participants' => '18 / 40',
                        'budget' => '₱15,000',
                        'progress' => 45,
                        'status' => 'open'
                    ],
                    [
                        'id' => 5,
                        'title' => 'Sustainable Farming Workshop',
                        'category' => 'agriculture',
                        'barangay' => 'San Jose',
                        'description' => 'Learn modern sustainable farming techniques suitable for urban and rural agricultural projects.',
                        'participants' => '22 / 40',
                        'budget' => '₱25,000',
                        'progress' => 55,
                        'status' => 'open'
                    ],
                    [
                        'id' => 6,
                        'title' => 'Basic Computer Literacy for Seniors & Youth',
                        'category' => 'education',
                        'barangay' => 'Barangay I (Poblacion I)',
                        'description' => 'Comprehensive computer skills training covering MS Office, internet safety, and basic coding.',
                        'participants' => '38 / 50',
                        'budget' => '₱40,000',
                        'progress' => 76,
                        'status' => 'ongoing'
                    ],
                ];
                @endphp

                @foreach($programs as $program)
                <div class="program-card" data-category="{{ $program['category'] }}" data-barangay="{{ strtolower(str_replace(' ', '-', $program['barangay'])) }}" data-status="{{ $program['status'] }}">
                    <div class="program-card-image">
                        <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center;">
                            <svg viewBox="0 0 100 100" style="width: 50%; height: 50%; opacity: 0.3;" fill="white">
                                <rect x="10" y="30" width="80" height="50" rx="4"/>
                                <line x1="10" y1="50" x2="90" y2="50" stroke="white" stroke-width="2"/>
                                <line x1="10" y1="60" x2="90" y2="60" stroke="white" stroke-width="2"/>
                            </svg>
                        </div>
                        <span class="program-category-badge {{ $program['category'] }}">{{ ucfirst($program['category']) }}</span>
                    </div>
                    <div class="program-card-body">
                        <div class="program-card-header">
                            <div class="program-barangay">📍 {{ $program['barangay'] }}</div>
                            <h3 class="program-title">{{ $program['title'] }}</h3>
                        </div>
                        <p class="program-description">{{ $program['description'] }}</p>
                        
                        <div class="program-progress">
                            <div class="program-progress-bar" style="width: {{ $program['progress'] }}%"></div>
                        </div>

                        <div class="program-stats">
                            <span class="program-stat-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                                {{ $program['participants'] }}
                            </span>
                            <span class="program-stat-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                                {{ $program['budget'] }}
                            </span>
                            <span class="program-stat-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ ucfirst($program['status']) }}
                            </span>
                        </div>

                        <div class="program-card-footer">
                            <button class="program-btn-join">Join Program</button>
                            <button class="program-btn-info">ℹ️</button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── CTA ── --}}
    <section class="about-cta">
        <div class="about-cta-inner">
            <h2>Ready to join a program?</h2>
            <p>Register now and start your journey as an active member of your barangay community.</p>
            <div class="about-hero-actions">
                <a href="#" class="btn-hero-primary">Sign Up</a>
                <a href="{{ route('about') }}" class="btn-hero-ghost">Learn More</a>
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const categoryFilter = document.getElementById('categoryFilter');
        const barangayFilter = document.getElementById('barangayFilter');
        const statusFilter = document.getElementById('statusFilter');
        const programCards = document.querySelectorAll('.program-card');

        function filterPrograms() {
            const category = categoryFilter?.value || '';
            const barangay = barangayFilter?.value || '';
            const status = statusFilter?.value || '';

            programCards.forEach(card => {
                let show = true;
                if (category && card.dataset.category !== category) show = false;
                if (barangay && card.dataset.barangay !== barangay) show = false;
                if (status && card.dataset.status !== status) show = false;
                
                card.style.display = show ? '' : 'none';
            });
        }

        categoryFilter?.addEventListener('change', filterPrograms);
        barangayFilter?.addEventListener('change', filterPrograms);
        statusFilter?.addEventListener('change', filterPrograms);
    });
</script>
@endpush
@endsection
