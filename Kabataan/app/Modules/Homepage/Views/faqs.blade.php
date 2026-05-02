@extends('homepage::layout')

@section('title', 'FAQs - SK OnePortal Kabataan')

@section('content')
<main class="faq-page">
    <section class="faq-hero kabataan-shell" aria-labelledby="faqHeading">
        <div class="faq-hero-copy">
            <span class="faq-eyebrow">Help Center</span>
            <h1 id="faqHeading">Frequently Asked Questions</h1>
            <p>Find quick answers about the Kabataan platform, youth programs, participation rules, and data privacy.</p>
        </div>

        <div class="faq-hero-search">
            <label for="faqSearch" class="sr-only">Search FAQs</label>
            <div class="faq-search-control">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
                    <path d="m21 21-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <input id="faqSearch" type="search" placeholder="Search by question, keyword, or topic" aria-label="Search frequently asked questions"/>
            </div>
            <p class="faq-hint">Try: registration, programs, privacy, login</p>
        </div>
    </section>

    <section class="faq-layout kabataan-shell" aria-label="FAQ content">
        <section class="faq-content">
            <div id="faqList" role="region" aria-label="FAQ items"></div>
        </section>
    </section>
</main>
@endsection
