@extends('homepage::layout')

@section('title', 'Contact Us - SK OnePortal Kabataan')

@section('content')
<div class="contact-main">
    {{-- ── HERO SECTION ── --}}
    <section class="contact-hero">
        <div class="contact-hero-inner">
            <h1>Get in Touch</h1>
            <p>Have questions about SK OnePortal or want to know more about SK programs in Santa Cruz, Laguna? We're here to help!</p>
        </div>
    </section>

    {{-- ── CONTACT SECTION ── --}}
    <section class="contact-section">
        <div class="contact-section-inner">
            <div class="contact-grid">
                <div class="kabataan-contact-form-col">
                    <h2>Contact Inquiry</h2>
                    <p class="contact-section-lead">Send us your questions, concerns, or suggestions. We are happy to assist you.</p>
                    @include('homepage::partials.contact-inquiry-form')
                </div>

                <div class="kabataan-contact-info-col">
                    <h2>Contact Information</h2>
                    <p class="contact-section-lead">Reach out to us through multiple channels. We aim to respond to all inquiries within 24 hours.</p>
                    @include('homepage::partials.contact-info-panel')
                </div>
            </div>
        </div>
    </section>

</div>
@endsection
