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
            <h2 style="margin-bottom: 10px;">Contact Information</h2>
            <p class="contact-section-lead">Reach out to us through multiple channels. We aim to respond to all inquiries within 24 hours.</p>

            <div class="contact-grid">
                {{-- Contact Form --}}
                <form class="contact-form" id="contactForm">
                    @csrf
                    <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 16px;">Send us a Message</h3>
                    
                    <div class="form-group">
                        <label class="form-label">Full Name *</label>
                        <input type="text" class="form-input" name="name" placeholder="Your full name" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email Address *</label>
                        <input type="email" class="form-input" name="email" placeholder="your.email@example.com" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Contact Number</label>
                        <input type="tel" class="form-input" name="phone" placeholder="+63 (XXX) XXX-XXXX">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Subject *</label>
                        <select class="form-input" name="subject" required>
                            <option value="">Select a subject</option>
                            <option value="general">General Inquiry</option>
                            <option value="program">Program Question</option>
                            <option value="technical">Technical Support</option>
                            <option value="registration">Registration Help</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Message *</label>
                        <textarea class="form-textarea" name="message" placeholder="Tell us how we can help..." required></textarea>
                    </div>

                    <button type="submit" class="form-btn">Send Message</button>
                </form>

                {{-- Contact Info Cards --}}
                <div class="contact-info-cards">
                    <div class="contact-info-card">
                        <div class="contact-info-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                        </div>
                        <div class="contact-info-content">
                            <h3>Address</h3>
                            <p>Municipal Hall<br>Santa Cruz, Laguna<br>Philippines 4009</p>
                        </div>
                    </div>

                    <div class="contact-info-card">
                        <div class="contact-info-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 012.84 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 8.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/>
                            </svg>
                        </div>
                        <div class="contact-info-content">
                            <h3>Phone</h3>
                            <p>SK Federation: +63 (49) 501-8400<br>
                            Email Support: sk@santacruz.gov.ph</p>
                        </div>
                    </div>

                    <div class="contact-info-card">
                        <div class="contact-info-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="4" width="20" height="16" rx="2"/>
                                <path d="M22 6L12 13 2 6"/>
                            </svg>
                        </div>
                        <div class="contact-info-content">
                            <h3>Email</h3>
                            <p>General Inquiries:<br>info@skoneportal.ph<br>
                            Support: support@skoneportal.ph</p>
                        </div>
                    </div>

                    <div class="contact-info-card">
                        <div class="contact-info-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                        </div>
                        <div class="contact-info-content">
                            <h3>Office Hours</h3>
                            <p>Monday - Friday<br>8:00 AM - 5:00 PM<br>
                            (Closed on weekends & holidays)</p>
                        </div>
                    </div>

                    <div class="contact-info-card">
                        <div class="contact-info-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z"/>
                                <path d="M12.5 6.5v6l4.2 2.5"/>
                            </svg>
                        </div>
                        <div class="contact-info-content">
                            <h3>Response Time</h3>
                            <p>We aim to respond to<br>all messages within<br>24 hours on business days</p>
                        </div>
                    </div>

                    <div class="contact-info-card">
                        <div class="contact-info-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                            </svg>
                        </div>
                        <div class="contact-info-content">
                            <h3>Follow Us</h3>
                            <p>Facebook: @SKOnePortal<br>
                            Twitter: @SKOnePortal<br>
                            Instagram: @skoneportal_</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ── CTA ── --}}
    <section class="about-cta">
        <div class="about-cta-inner">
            <h2>Have a specific question?</h2>
            <p>Check out our FAQs or contact your barangay SK office for immediate assistance.</p>
            <div class="about-hero-actions">
                <a href="{{ route('faqs') }}" class="btn-hero-primary">View FAQs</a>
                <a href="{{ route('about') }}" class="btn-hero-ghost">About SK</a>
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('contactForm');
        
        if (form) {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                // Collect form data
                const formData = new FormData(form);
                
                try {
                    // In a real implementation, send to backend
                    console.log('Form submitted:', {
                        name: formData.get('name'),
                        email: formData.get('email'),
                        phone: formData.get('phone'),
                        subject: formData.get('subject'),
                        message: formData.get('message')
                    });
                    
                    alert('Thank you for your message! We\'ll get back to you shortly.');
                    form.reset();
                } catch (error) {
                    console.error('Error submitting form:', error);
                    alert('There was an error sending your message. Please try again.');
                }
            });
        }
    });
</script>
@endpush
@endsection
