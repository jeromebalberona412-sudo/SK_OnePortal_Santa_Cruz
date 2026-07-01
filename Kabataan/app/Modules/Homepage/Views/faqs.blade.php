<div
    class="faq-page kabataan-page-section"
    id="faq"
    data-faqs="@json($faqs ?? [])"
>
    <section class="faq-hero">
        <div class="container">
            <div class="row g-4 align-items-stretch">
                <div class="col-lg-6">
                    <div class="faq-hero-copy h-100" aria-labelledby="faqHeading">
                        <span class="faq-eyebrow">Help Center</span>
                        <h1 id="faqHeading">Frequently Asked Questions</h1>
                        <p>Find clear answers about SKonePortal — registration, sign-in, available services, and who can use the platform.</p>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="faq-hero-search h-100">
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
                </div>
            </div>
        </div>
    </section>

    <section class="faq-layout" aria-label="FAQ content">
        <div class="container">
            <div class="faq-content">
                <div id="faqList" class="faq-list" role="region" aria-label="FAQ items"></div>
            </div>
        </div>
    </section>
</div>
