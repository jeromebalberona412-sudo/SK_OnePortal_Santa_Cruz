# About Kabataan — SK_ONEPORTAL

**Last Updated:** May 2, 2026

---

## Overview

Kabataan is the youth-facing web application within the SK_ONEPORTAL ecosystem for Santa Cruz. It connects young leaders (ages 13–30) with programs, events, announcements, and SK officials across 26 barangays. Kabataan is built to be a transparent, community-focused platform that helps youth discover opportunities, participate in local programs, and track real impact in their barangays.

## Mission

To empower youth across Santa Cruz by making local programs discoverable, participation easy, and program funding transparent — so young people can lead, learn, and influence local governance.

## Vision

An engaged, informed, and empowered generation of barangay youth who shape their communities through participation, accountability, and service.

## Who Can Join

- Youth residents of Santa Cruz aged 13–30.
- SK volunteers, trainees, and community partners (view or limited-contribute roles may apply).

## Key Features

- Program & Event Discovery — browse education, health, sports, agriculture, GAD, disaster, and other programs by barangay.
- Social Participation — comment, react, save, and share activities to build community engagement.
- Transparent Budgets — view allocated budgets, progress metrics, and outcomes for posted programs.
- Profile & Recognition — earn badges, track accomplishments, and build a leadership profile.
- Cross-barangay Discovery — explore activities happening in other barangays, find nearby opportunities, and follow featured barangays.

## How Kabataan Works (Summary)

1. Sign up with verified contact and basic profile details.
2. Explore programs and events on the public feed or by barangay filters.
3. Register or express interest in activities; saved items appear in your personal feed.
4. Participate, provide feedback, and earn recognition through badges and activity history.

## Governance & Safety

Kabataan is a role-scoped module inside SK_ONEPORTAL. Posts and announcements originate from SK Officials (per barangay role) or approved community partners. The platform enforces tenant and role boundaries to ensure data and action scope is limited to the appropriate barangay contexts.

Safety & trust features include:

- Secure sign-up and transport-level encryption (SSL/TLS).
- Content moderation and official response mechanisms.
- Privacy-first defaults and limited personal data collection (see Privacy section).

## Privacy & Data

We minimize stored personal data and never store uploaded IDs on the application server disk. Uploaded assets and documents are stored in external object storage (Supabase Storage preferred). Users control saved items and can request account data exports or deletion according to platform policies.

## Impact & Early Targets

- Target users across Santa Cruz: 10,000+ youth
- Early program goals: 50+ active programs platform-wide
- Reporting goal: Clear, exportable program outcome reports and budget breakdowns for public review

## Get Involved

Want to contribute or partner?

- SK Officials: Use your SK_Officials app to publish barangay announcements and programs.
- Community Partners: Reach out to the Admin team to become a verified contributor.
- Volunteers: Sign up, save activities, and invite friends to join local programs.

For partnership and onboarding, contact: info@sk_oneportal.example or visit the Admin portal.

## Implementation Notes (for developers)

- Blade template: create `kabataan.about` Blade view and reuse shared header/footer components.
- Styling: prefer Tailwind CSS for rapid implementation and consistent design tokens (see HOMEPAGE_DESIGN.md for palette).
- Routes: public `/kabataan/about` route in the Kabataan app; cache basic stats for performance.
- Assets: keep photos in Supabase Storage with signed URLs and `srcset` for responsive images.
- Accessibility: follow WCAG 2.1 AA (ARIA labels, keyboard navigation, contrast ratios).

## Next Steps

1. Draft `about` Blade view using content here.
2. Add route and controller action that returns cached platform stats.
3. Create responsive imagery and optimize for Lighthouse targets.
4. Run accessibility audit and content review with product owners before publish.

---

This page is part of the Kabataan documentation for SK_ONEPORTAL. For design guidance see HOMEPAGE_DESIGN.md.
