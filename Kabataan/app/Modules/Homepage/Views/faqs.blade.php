@extends('homepage::layout')

@section('title', 'FAQs - SK OnePortal Kabataan')

@section('content')
<div class="faq-main">
    {{-- ── HERO SECTION ── --}}
    <section class="faq-hero">
        <div class="faq-hero-inner">
            <h1>Frequently Asked Questions</h1>
            <p>Find answers to common questions about SK OnePortal, registration, programs, and more.</p>
        </div>
    </section>

    {{-- ── FAQ SECTION ── --}}
    <section class="about-section">
        <div class="about-section-inner">
            {{-- Search Bar --}}
            <div class="faq-search">
                <svg class="faq-search-icon" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
                </svg>
                <input type="text" class="faq-search-input" id="faqSearch" placeholder="Search FAQs...">
            </div>

            {{-- Category Filters --}}
            <div class="faq-categories">
                <button class="faq-category-btn active" data-category="">All</button>
                <button class="faq-category-btn" data-category="account">Account & Registration</button>
                <button class="faq-category-btn" data-category="programs">Programs</button>
                <button class="faq-category-btn" data-category="technical">Technical Support</button>
                <button class="faq-category-btn" data-category="privacy">Privacy & Security</button>
            </div>

            {{-- FAQ Accordion --}}
            <div class="faq-accordion">
                @php
                $faqs = [
                    [
                        'id' => 1,
                        'category' => 'account',
                        'question' => 'Who can join SK OnePortal Kabataan?',
                        'answer' => 'Youth aged 13-30 residing in Santa Cruz, Laguna can join SK OnePortal Kabataan. You\'ll need a valid email address and contact number to register. Residents from any of the 26 barangays are welcome to participate.'
                    ],
                    [
                        'id' => 2,
                        'category' => 'account',
                        'question' => 'Is registration free?',
                        'answer' => 'Yes! Registration and all participation in SK OnePortal Kabataan is completely free. There are no hidden fees or charges. We believe in making community engagement accessible to all youth.'
                    ],
                    [
                        'id' => 3,
                        'category' => 'account',
                        'question' => 'How do I reset my password?',
                        'answer' => 'Click on the "Forgot Password" link on the login page. Enter your registered email address, and we\'ll send you a password reset link. Follow the instructions in the email to create a new password. The link expires in 1 hour for security.'
                    ],
                    [
                        'id' => 4,
                        'category' => 'account',
                        'question' => 'Can I delete my account?',
                        'answer' => 'You can request account deletion by going to Account Settings > Privacy. Your data will be securely deleted within 30 days. Some anonymized data may be retained for program statistics.'
                    ],
                    [
                        'id' => 5,
                        'category' => 'programs',
                        'question' => 'How do I find programs in my barangay?',
                        'answer' => 'Visit the Programs page and use the barangay filter. You can also click on your barangay in the About section to see specific SK officials and their programs. Programs are updated regularly, so check back often for new opportunities.'
                    ],
                    [
                        'id' => 6,
                        'category' => 'programs',
                        'question' => 'Can I apply for programs from other barangays?',
                        'answer' => 'Yes! While each barangay has its own SK office, many programs are open to youth from other barangays. Check the program details to see if cross-barangay participation is allowed. Some programs may have residency requirements.'
                    ],
                    [
                        'id' => 7,
                        'category' => 'programs',
                        'question' => 'How do I track my program applications?',
                        'answer' => 'Once you\'re logged in, go to your Dashboard to see all your applications and their statuses. You\'ll also receive email notifications when SK officials respond to your application or when the program status changes.'
                    ],
                    [
                        'id' => 8,
                        'category' => 'programs',
                        'question' => 'What does the progress bar in programs mean?',
                        'answer' => 'The progress bar shows how many youth have joined relative to the program capacity. For example, 45/100 means 45 youth have registered out of 100 slots. When a program reaches capacity, it may close to new registrations.'
                    ],
                    [
                        'id' => 9,
                        'category' => 'technical',
                        'question' => 'I\'m having trouble logging in. What should I do?',
                        'answer' => 'First, double-check your email and password. If you\'re still having issues, try clearing your browser cache and cookies. If the problem persists, use the "Forgot Password" feature. For additional support, contact us via the Contact page.'
                    ],
                    [
                        'id' => 10,
                        'category' => 'technical',
                        'question' => 'What browsers are supported?',
                        'answer' => 'SK OnePortal works best on modern browsers including Chrome, Firefox, Safari, and Edge (latest 2 versions). For mobile, use the latest versions of Chrome or Safari on your phone. Some older browsers may have compatibility issues.'
                    ],
                    [
                        'id' => 11,
                        'category' => 'technical',
                        'question' => 'Why am I being logged out frequently?',
                        'answer' => 'For security, SK OnePortal logs you out after 30 minutes of inactivity. If you\'re being logged out too frequently, check if cookies are enabled in your browser. You can also enable "Remember Me" for longer sessions on trusted devices.'
                    ],
                    [
                        'id' => 12,
                        'category' => 'privacy',
                        'question' => 'Is my personal data safe?',
                        'answer' => 'Yes, we take data security seriously. All data is encrypted during transmission and at rest. We comply with data protection laws and regularly audit our security measures. We never sell or share your personal data with third parties.'
                    ],
                    [
                        'id' => 13,
                        'category' => 'privacy',
                        'question' => 'What information is visible to SK officials?',
                        'answer' => 'SK officials can see your name, barangay, contact information, and program application history. Your password is never visible to officials. Program-specific information may be shared with officials overseeing programs you\'ve joined.'
                    ],
                    [
                        'id' => 14,
                        'category' => 'privacy',
                        'question' => 'Can I control my profile visibility?',
                        'answer' => 'Yes! In Settings > Privacy, you can control who sees your profile information. You can choose between public (all users), barangay only (users in your barangay), or private (only you and SK officials). Your email is never publicly displayed.'
                    ],
                    [
                        'id' => 15,
                        'category' => 'privacy',
                        'question' => 'How do I report inappropriate content?',
                        'answer' => 'Click the report button (three dots) on any post or comment. Select a reason and provide details. Our moderation team will review reports within 24 hours. Serious violations may result in account suspension.'
                    ],
                ];
                @endphp

                @foreach($faqs as $faq)
                <div class="faq-item" data-category="{{ $faq['category'] }}" data-question="{{ strtolower($faq['question']) }}">
                    <div class="faq-item-header">
                        <span class="faq-item-question">{{ $faq['question'] }}</span>
                        <div class="faq-item-toggle">+</div>
                    </div>
                    <div class="faq-item-answer">
                        <div class="faq-item-answer-content">
                            {{ $faq['answer'] }}
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
            <h2>Still have questions?</h2>
            <p>Reach out to us through our contact page or visit your barangay SK office for more information.</p>
            <div class="about-hero-actions">
                <a href="{{ route('contact') }}" class="btn-hero-primary">Contact Us</a>
                <a href="{{ route('about') }}" class="btn-hero-ghost">Learn More</a>
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const faqSearch = document.getElementById('faqSearch');
        const categoryBtns = document.querySelectorAll('.faq-category-btn');
        const faqItems = document.querySelectorAll('.faq-item');
        const faqHeaders = document.querySelectorAll('.faq-item-header');

        // Accordion toggle
        faqHeaders.forEach(header => {
            header.addEventListener('click', () => {
                const item = header.parentElement;
                const isActive = item.classList.contains('active');
                
                // Close all items
                faqItems.forEach(i => i.classList.remove('active'));
                
                // Open clicked item if it wasn't open
                if (!isActive) {
                    item.classList.add('active');
                }
            });
        });

        // Category filter
        categoryBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const category = btn.dataset.category;
                categoryBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                
                faqItems.forEach(item => {
                    if (!category || item.dataset.category === category) {
                        item.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });

        // Search functionality
        faqSearch?.addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase();
            
            faqItems.forEach(item => {
                const question = item.dataset.question;
                if (searchTerm === '' || question.includes(searchTerm)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
</script>
@endpush
@endsection
