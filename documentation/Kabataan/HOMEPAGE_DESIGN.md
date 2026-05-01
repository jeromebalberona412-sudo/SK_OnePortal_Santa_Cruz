# Kabataan Public Homepage (Pre-Auth) - UI/UX Design

**Last Updated:** May 1, 2026

---

## **1. Navigation Bar** (Sticky/Fixed)

### Structure
```
Logo (Kabataan) | About | Programs | Announcements | FAQs | Contact
                                              [Login] [Sign Up] ➜
```

### Design Details
- **Fixed header** across all screen sizes
- Left-aligned logo with app branding
- Center navigation links with hover effects
- Right-aligned authentication CTAs
- **Mobile:** Hamburger menu (responsive collapse at ~768px)
- **Background:** Clean white or subtle gradient
- **Font:** Modern sans-serif (Poppins, Inter)
- **Height:** ~70px desktop, ~60px mobile

### Interactions
- Hover underline on nav links
- Color transition on CTA buttons
- Active page indicator on current section

---

## **2. Hero Section** (Full-Width)

### Headline & Subheading
**Primary:**
```
"Your Voice. Your Programs. Your Barangay."
```

**Secondary:**
```
"Join 10,000+ young leaders transforming Santa Cruz"
```

### Visual Composition
- **Full-width background** (100vh or min-height: 80vh)
- **Background options:**
  - Option A: Vibrant color gradient (Teal → Purple)
  - Option B: Hero image carousel (diverse youth in community activities)
  - Option C: Animated background pattern
- **Overlay:** Subtle semi-transparent dark overlay (0.2-0.3 opacity)
- **Central image/collage:** Diverse youth, real community photos (right side or background)

### Call-to-Action Buttons
```
[Get Started] [Explore Programs]
```
- **Primary (Get Started):** Vibrant button (Teal/Orange)
- **Secondary (Explore):** Outlined/Ghost button
- **Button size:** ~50px height, ~200px width
- **Hover effects:** Shadow, scale, color transition

### Typography
- **Headline:** 48-64px (desktop), 32-40px (mobile)
- **Subheading:** 18-24px (desktop), 14-18px (mobile)
- **Color:** White or high-contrast text

### Mobile Optimization
- Single-column centered layout
- Buttons stacked vertically
- Hero height reduced to ~60vh

---

## **3. Value Proposition Section** (3-4 Cards)

### Grid Layout
- **Desktop:** 2x2 grid (4 cards)
- **Mobile:** 1 column, stacked
- **Tablet:** 2x2 or 1x4 flexible

### Card Structure
Each card contains:
1. **Icon** (2-3 lines of margin-top)
2. **Title** (bold, 20px+)
3. **Description** (body text, 16px, subtle gray)

### Cards Content

| # | Icon | Title | Description |
|---|------|-------|-------------|
| 1 | 🎯 | **Discover Opportunities** | "Browse education, health, sports, and agricultural programs happening in your barangay" |
| 2 | 🤝 | **Connect & Participate** | "Join with friends, comment, react, and share your experiences" |
| 3 | 💰 | **See Where Money Goes** | "Transparent budgets & real impact metrics—know exactly how programs are funded" |
| 4 | 🏆 | **Build Your Profile** | "Earn badges, track accomplishments, grow your leadership journey" |

### Design Details
- **Background:** White or light gray card background
- **Border:** Subtle shadow (0 2px 8px rgba(0,0,0,0.1))
- **Padding:** 30px inside each card
- **Border-radius:** 12px
- **Hover effect:** Lift effect (+2px shadow), slight scale
- **Icon size:** 48x48px

### Spacing
- Gap between cards: 20-30px
- Section padding: 60px top/bottom, 20px sides (mobile)

---

## **4. Featured Programs Section**

### Section Header
```
"What's Happening This Month?"
```

### Layout
- **Desktop:** 3-4 cards per row, horizontal scroll OR fixed grid
- **Mobile:** Single column, swipeable carousel
- **Tablet:** 2 cards per row

### Program Card Template
```
┌──────────────────────────────────────┐
│  [Program Image - Full Width]        │
│                                      │
│  🎓 Education Program (Category)     │
│  "STEM Workshop Series"              │
│                                      │
│  📍 [Barangay Name]                  │
│  👥 45/100 joined                    │
│  💰 ₱50,000 allocated [Budget bar]   │
│                                      │
│  [Learn More →]                      │
└──────────────────────────────────────┘
```

### Card Details
- **Image:** Min-height 200px, cover object-fit
- **Category badge:** Color-coded by sector
  - 🎓 Education: Blue
  - 🌾 Agriculture: Green
  - 🏥 Health: Red
  - ⚽ Sports: Orange
  - ♀️ GAD: Purple
  - 🚫 Anti-Drugs: Dark Gray
  - 🌊 Disaster: Cyan
- **Progress bar:** Shows participant progress (45/100)
- **Budget display:** Clear typography, currency symbol included
- **CTA button:** Subtle underline link style

### Carousel Controls (Mobile)
- Swipe gestures
- Manual next/prev buttons
- Dot indicators (optional)

### Section Spacing
- Top padding: 60px
- Bottom padding: 60px
- Container max-width: 1200px

---

## **5. Explore Other Barangays Section** (Cross-Barangay Discovery)

### Section Header
```
"Discover What's Happening Across Santa Cruz"
```

### Barangay Selector/Navigation

#### Option A: Barangay Tab Navigation
```
🔵 All Barangays | 📍 Barangay A | 📍 Barangay B | 📍 Barangay C ... [See All]
```
- Horizontal scrollable tabs
- Active tab highlighted in accent color
- "All Barangays" shows combined feed
- "See All" opens modal with 26 barangays

#### Option B: Dropdown Filter
```
[📍 Select Barangay ▼] [🎯 Filter by Type ▼] [📅 Filter by Date ▼]
```
- Barangay dropdown: Shows all 26 barangays + "All Barangays"
- Type filter: Activities, Events, Programs
- Date filter: This Week, This Month, All Time

#### Option C: Search + Sidebar Filter
```
[🔍 Search barangays/activities...]
Sidebar:
  📍 My Barangay (highlighted)
  📍 Nearby Barangays (3-5 closest)
  📍 All Barangays (26 total)
```

### Cards Layout - Barangay Activity/Event/Program Cards

```
┌────────────────────────────────────────────┐
│ 📍 BARANGAY NAME                           │
│ Activity Type Badge (Event | Program |     │
│ Activity) | Category (Education, etc.)     │
│                                            │
│ [Thumbnail Image]                          │
│                                            │
│ "Activity/Event Title"                     │
│ Short description (1-2 lines)              │
│                                            │
│ 📅 May 30-June 5, 2026                    │
│ 👥 156 interested / 43 participating       │
│ 💰 ₱75,000 budget (if applicable)          │
│                                            │
│ [View Details →] [Save] [Share]            │
└────────────────────────────────────────────┘
```

### Card Details

#### Header Section
- **Barangay name:** Bold, accent color
- **Type badge:** 
  - 🎯 Activity (Light gray)
  - 📅 Event (Orange)
  - 📚 Program (Blue)
- **Category:** Small tag (Education, Health, Sports, etc.)

#### Image Section
- **Min-height:** 180px
- **Cover object-fit**
- **Overlay date/status** (optional top-right corner)

#### Content Section
- **Title:** Bold, 18-20px
- **Description:** 14px gray, max 2 lines (truncated with "...")
- **Metadata row:**
  - Date range/schedule
  - Participation metrics
  - Budget (if applicable)
- **Action buttons:**
  - Primary: "View Details"
  - Secondary: "Save for later" (heart icon)
  - Share: Social share icon

#### Visual Indicators
- **Barangay color coding:** Each barangay can have subtle accent color
- **Participation progress:** Small bar indicator (if applicable)
- **Distance indicator:** "12 km away" (optional, if geolocation available)

### Grid Layout
- **Desktop:** 3-4 cards per row
- **Tablet:** 2 cards per row
- **Mobile:** 1 card per row (swipeable carousel)

### Load More / Pagination
- **Show:** 12 cards initially
- **Load more button:** "See 24 more activities" OR
- **Infinite scroll:** Auto-load as user scrolls
- **Total count display:** "Showing 12 of 312 activities across 26 barangays"

### Empty States
**When no activities in selected barangay:**
```
"No activities yet in [Barangay Name]"
🎯 Suggestions:
• Explore other barangays
• Browse by category
• Check back soon
[View All Barangays →]
```

### Mobile Optimization
- **Selector:** Horizontal scroll for tabs OR dropdown
- **Cards:** Single column, full-width
- **Gestures:** Swipe right to see next barangay
- **Bottom navigation:** Easy barangay switching

### Section Spacing
- **Top padding:** 60px
- **Bottom padding:** 60px
- **Container max-width:** 1200px
- **Gap between cards:** 20px

### Interactive Features

#### Barangay Information Modal/Popup
Click barangay name to see:
```
📍 [Barangay Name]
━━━━━━━━━━━━━━━━
📊 Quick Stats:
  • 1,234 Kabataan members
  • 18 Active programs
  • 42 Upcoming events
  • ₱500,000 invested this year

🏢 SK Officials:
  • [Official Name 1] - Chairperson
  • [Official Name 2] - Secretary
  • Contact: +63...

📢 Latest Announcement: [Title] [Read →]

[Close]
```

#### Wishlist/Save Functionality
- Heart icon saves activity
- Saves appear in user's profile (after login)
- Creates personalized feed recommendations
- Notification when saved item is happening

#### Share Features
- Share to messaging apps
- Copy link
- Email invite (for events)
- QR code to event

### Filters & Search (Detailed)

#### Search Bar
```
[🔍 Search barangays, activities, events... ×]
```
- Real-time search as user types
- Autocomplete suggestions:
  - Recent searches
  - Popular barangays
  - Trending activities
- Search results page (if needed)

#### Filter Sidebar (Desktop)
```
━━━━━━━━━━━━━━━━━━
📍 BARANGAY (26)
━━━━━━━━━━━━━━━━━━
☐ All Barangays
☐ My Barangay (highlighted)
☐ Barangay A
☐ Barangay B
☐ ... [Show more]

🎯 TYPE
━━━━━━━━━━━━━━━━━━
☑ Activities
☑ Events
☑ Programs

📂 CATEGORY
━━━━━━━━━━━━━━━━━━
☐ Education
☐ Health
☐ Agriculture
☐ Sports
☐ GAD
☐ Anti-Drugs
☐ Disaster
☐ Others

📅 DATE
━━━━━━━━━━━━━━━━━━
○ This Week
○ This Month
○ Next 3 Months
○ All Time

[Clear Filters] [Apply]
```

#### Filter Tags (Mobile)
- Horizontal scrollable filter chips
- Selected filters shown as tags
- "X" to remove individual filters
- "Clear all" option

### Sort Options
```
Sort by: [Newest ▼] | [Most Popular ▼] | [Closest (if geo) ▼]
```
- Newest first
- Most interested/participating
- Closest barangay (if location services enabled)
- Most budget allocated
- Ending soon

### Barangay Spotlight (Optional Weekly Feature)
```
✨ FEATURED BARANGAY THIS WEEK ✨

📍 [Barangay Name Highlighted]
┌──────────────────────┐
│ [Hero Image]         │
└──────────────────────┘

"This week, Barangay [X] is launching..."

🎯 5 Highlight Activities/Events
[1. ...] [2. ...] [3. ...] [4. ...] [5. ...]

[Explore All from This Barangay →]
```

---

## **6. Why Join Kabataan Section**

### Two-Column Layout
- **Left (60%):** Text content
- **Right (40%):** Image/Video

### Left Side Content
```
"Designed for Young Leaders Like You"

✓ Real opportunities in your barangay
✓ Direct feedback from officials
✓ Transparent governance
✓ Build community, earn recognition
✓ Voice that matters
```

### Typography
- **Title:** 36-48px, bold
- **Body text:** 16-18px, line-height 1.6
- **Checkmarks:** Use icons, 20px size
- **List items:** Margin 12px bottom

### Right Side
- **Option A:** Testimonial video (auto-muted, looped)
- **Option B:** Hero image (diverse youth group)
- **Option C:** Animated illustration

### Responsive
- **Tablet:** Stack vertically (100% width each)
- **Mobile:** Full-width stacked

---

## **7. Testimonials/Social Proof Section**

### Layout
- **Desktop:** 3-4 cards visible in carousel OR grid
- **Mobile:** 1 card visible, swipeable
- **Carousel:** Auto-play (5-second interval), manual controls

### Testimonial Card Structure
```
⭐⭐⭐⭐⭐
"I joined the education program and got a scholarship. 
The transparency about budget helped me understand 
what the government invested in us."

— Maria Santos, Kabataan Member
```

### Card Details
- **Stars:** 5 yellow stars, 20px size
- **Quote:** Italicized, 16px, dark gray
- **Author:** Bold, 14px, barangay optional
- **Background:** White or light gray
- **Border-left:** 4px solid accent color
- **Padding:** 30px
- **Margin-bottom:** 20px

### Statistics Below Testimonials
```
10,000+ Active Members | 50+ Active Programs | ₱2.5M Invested in Youth
```
- **Stats layout:** Flex row, space-around
- **Numbers:** 32px bold
- **Labels:** 14px gray
- **Dividers:** Subtle borders between stats

### Responsive
- **Mobile:** Full-width cards, stacked below stats

---

## **8. How It Works Section** (4-Step Flow)

### Visual Timeline
```
1️⃣ SIGN UP           2️⃣ EXPLORE           3️⃣ JOIN            4️⃣ PARTICIPATE
   [Create             [Browse             [Register for      [Comment, React,
    Account]           Programs &          Your Chosen        Track Progress,
                       Announcements]      Program]            Earn Badges]
```

### Step Cards
- **Number circle:** 48x48px, bold white text on accent background
- **Title:** Bold, 18-20px
- **Description:** 14-16px gray text
- **Icon below:** Optional visual representation

### Connection Lines
- Horizontal line connecting steps
- **Desktop:** Visible lines
- **Mobile:** Lines hidden, steps stack vertically

### Spacing
- Gap between steps: 40px (desktop), 20px (mobile)
- Section padding: 60px top/bottom

---

## **9. Latest Updates/Announcements Section**

### Section Header
```
"Latest Updates from SK Officials"
```

### Announcement Card Template
```
┌────────────────────────────────────┐
│ 📢 Important Announcement          │
│                                    │
│ "Summer Programs Registration      │
│  Opens June 1st"                  │
│                                    │
│ Posted by: SK Officials            │
│ 🏘️ [Barangay Name]               │
│ 📅 May 28, 2026                   │
│                                    │
│ [Read Full Announcement →]         │
└────────────────────────────────────┘
```

### Card Details
- **Icon:** 24px
- **Title:** Bold, 18-20px
- **Metadata:** 12px gray (posted by, barangay, date)
- **CTA:** Underline link, hover color change
- **Background:** Light blue or subtle background
- **Border-radius:** 8px

### Layout
- **Desktop:** 2-3 cards per row OR carousel
- **Mobile:** Single column, swipeable carousel
- **Show:** Latest 3-6 announcements

### Spacing
- Section padding: 60px top/bottom
- Gap between cards: 20px

---

## **10. Feature Highlights Section** (3-Column)

### Columns
1. **📊 Transparent Budgets**
2. **💬 Community Engagement**
3. **🎖️ Recognition System**

### Column Content Structure
- **Icon:** 48x48px
- **Title:** Bold, 20-24px
- **Bullet points:** 
  - Each point on own line
  - 14-16px text
  - Margin 8px bottom

### Feature Details

#### Column 1: Transparent Budgets
```
Every program shows:
• Total budget allocated
• How money is spent
• Real-time progress
• Completed outcomes
```

#### Column 2: Community Engagement
```
Interact authentically:
• Comment on programs
• React with emojis
• Share experiences
• View official responses
```

#### Column 3: Recognition System
```
Earn badges for:
• Program participation
• Community contributions
• Leadership roles
• Social impact
```

### Design
- **Background:** White cards OR light gray section background
- **Border:** Subtle bottom border (accent color) for visual interest
- **Padding:** 30px per column
- **Responsive:** Stack on mobile

---

## **11. FAQ Section** (Expandable Accordion)

### Structure
Each item: Question header + Answer content (collapsed by default)

### FAQ Items

| # | Question | Answer |
|---|----------|--------|
| 1 | Who can join Kabataan? | Youth aged 13-30 in Santa Cruz barangays |
| 2 | Is it free? | Yes! Registration and participation are completely free |
| 3 | How do I see program budgets? | Click any program to view full budget breakdown and impact metrics |
| 4 | Can I really interact with officials? | Yes! Comment on programs and announcements—officials respond |
| 5 | What happens with my personal data? | [Link to Privacy Policy] Your data is secure and encrypted |

### Accordion Design
- **Header clickable:** Full row clickable, not just text
- **Icon:** Chevron right (rotates down on expand)
- **Background:** Light gray or white
- **Border-bottom:** Subtle divider line
- **Padding:** 16px header, 16px answer
- **Animation:** Smooth expand/collapse (300ms)
- **Answer text:** 14-16px, line-height 1.6

### Responsive
- **Mobile:** Full-width, easier tap targets (48px min-height)

---

## **12. Final Call-to-Action Section**

### Centered Block
```
"Ready to Make a Difference?"

[Sign Up Now] (Primary - Vibrant)
[Already have an account? Login] (Secondary - Link)

"Join your peers in shaping your barangay's future."
```

### Design Details
- **Main heading:** 36-48px, bold
- **Primary button:** Large (50px height, 280px width)
- **Secondary link:** Subtle hover underline
- **Subtext:** 16px, gray
- **Background:** Light or gradient
- **Padding:** 80px top/bottom, 40px sides
- **Text-align:** Center

### Button Styling
- **Primary:** Vibrant accent color, white text, shadow, hover scale
- **Secondary:** Text link, no background, hover underline

---

## **13. Footer**

### 4-Column Layout

| Column 1 | Column 2 | Column 3 | Column 4 |
|----------|----------|----------|----------|
| **About** | **Resources** | **Community** | **Contact** |
| • About Kabataan | • Programs | • FAQ | • Email: info@... |
| • Our Mission | • Guides | • Community Guidelines | • Phone: +63... |
| • Team | • Help Center | • Stories | • Social: @kabataan |
| • Careers | • Blog | • Feedback | • Report Issue |

### Footer Details
- **Background:** Dark gray or navy
- **Text color:** White or light gray
- **Column title:** Bold, 14px
- **Links:** 12-14px, hover color change
- **Link spacing:** 8px margin-bottom

### Bottom Footer
```
© 2026 SK_ONEPORTAL - Kabataan Platform
[Privacy Policy] | [Terms of Service] | [Accessibility]
```

### Responsive
- **Mobile:** Single column, full-width links
- **Tablet:** 2x2 grid

---

## **14. Color Palette & Design System**

### Primary Colors
- **Primary Accent:** Teal (#1BA098 or similar)
- **Secondary Accent:** Orange (#FF8C42 or similar)
- **Tertiary:** Purple (#7C3AED or similar)

### Neutral Colors
- **Dark Text:** #1F2937 (Dark Gray)
- **Light Text:** #6B7280 (Medium Gray)
- **Light Background:** #F9FAFB (Off White)
- **Card Background:** #FFFFFF (White)
- **Border:** #E5E7EB (Light Gray)

### Status Colors
- **Success:** #10B981 (Green) - Budget transparency, completed items
- **Alert:** #EF4444 (Red) - Urgent announcements
- **Info:** #3B82F6 (Blue) - Information
- **Warning:** #F59E0B (Amber) - Warnings

### Typography System
- **Headings:** Poppins or Inter Bold
  - H1: 48-64px
  - H2: 36-48px
  - H3: 24-32px
  - H4: 20-24px
- **Body:** Open Sans or Roboto
  - Large: 18px
  - Regular: 16px
  - Small: 14px
  - Extra Small: 12px

### Spacing Scale
- **xs:** 4px
- **sm:** 8px
- **md:** 16px
- **lg:** 24px
- **xl:** 32px
- **2xl:** 48px
- **3xl:** 60px
- **4xl:** 80px

### Border Radius
- **Buttons:** 6-8px
- **Cards:** 12px
- **Inputs:** 6px
- **Large sections:** 16px

---

## **15. Interactive Elements & Animations**

### Hover Effects
- **Cards:** Shadow lift (+4px), subtle scale (1.02)
- **Buttons:** Color darken, shadow increase
- **Links:** Color change, underline appear

### Animations
- **Section scroll-in:** Fade + slide up on viewport entrance
- **Carousel auto-play:** 5-second interval, fade transition
- **FAQ expand:** Smooth 300ms height animation
- **CTA pulse:** Subtle opacity pulse (optional)
- **Page load:** Staggered entrance of hero elements

### Scroll Behavior
- **Smooth scrolling:** CSS `scroll-behavior: smooth`
- **Sticky header:** Stays fixed at top
- **Lazy loading:** Images load on viewport intersection

---

## **16. Mobile Optimization Checklist**

### Responsive Breakpoints
- **Mobile:** < 640px
- **Tablet:** 640px - 1024px
- **Desktop:** > 1024px

### Mobile Adjustments
- Navigation: Hamburger menu
- Hero: Reduced height (60vh), large touch targets
- Cards: Full-width, single column
- Buttons: Larger height (48px min-touch-target)
- Font sizes: Scale appropriately
- Spacing: Reduced gaps
- Carousels: Swipe-enabled
- Forms: Full-width inputs, large labels

### Touch Targets
- Minimum 48px x 48px
- 8px padding around interactive elements
- Clear visual feedback on press

### Performance
- Image optimization (webp, srcset)
- Lazy loading for below-fold content
- Minified CSS/JS
- Critical rendering path optimization

---

## **17. Trust & Safety Section** (Subtle Callouts)

### Placement
- Scattered throughout page or footer area

### Messaging
```
🔒 Secure Sign-Up (SSL encrypted)
✅ Official Government Platform
🎯 Real Impact, Real Data
👥 Transparent, Youth-Focused
```

### Design
- **Icon + text:** 12-14px
- **Placement:** Footer area or scattered callouts
- **Color:** Subtle gray, not prominent

---

## **18. Page Load & Section Order**

**Top to Bottom:**
1. Navigation (sticky)
2. Hero Section
3. Value Proposition Cards
4. Featured Programs
5. Explore Other Barangays (Cross-Barangay Discovery)
6. Why Join Kabataan
7. Testimonials/Social Proof
8. How It Works (4-step)
9. Latest Announcements
10. Feature Highlights
11. FAQ
12. Final CTA
13. Footer

---

## **19. Accessibility Considerations**

### WCAG 2.1 AA Compliance
- Color contrast ratios ≥ 4.5:1 for text
- Semantic HTML (h1, h2, button, etc.)
- Alt text on all images
- ARIA labels where needed
- Keyboard navigation support (Tab, Enter, Escape)
- Focus indicators visible
- Skip navigation link at top

### Form Accessibility
- Label associated with input (for/id)
- Error messages linked to inputs
- Required field indicators
- Placeholder ≠ label

---

## **20. Performance Targets**

- **First Contentful Paint:** < 1.5s
- **Largest Contentful Paint:** < 2.5s
- **Cumulative Layout Shift:** < 0.1
- **Page Size:** < 3MB (uncompressed)
- **Lighthouse Score:** 90+

---

## **21. Browser Support**

- Chrome/Edge (latest 2 versions)
- Firefox (latest 2 versions)
- Safari (latest 2 versions)
- Mobile browsers (Safari iOS, Chrome Android)

---

## **Next Steps for Implementation**

1. **Create Blade template** with section components
2. **Style with Tailwind CSS** for rapid development
3. **Implement carousels** (Alpine.js or Swiper.js)
4. **Add form validation** for Sign Up CTA
5. **Setup image optimization** (Cloudinary or Supabase Storage)
6. **Test on mobile** (responsive design, touch interactions)
7. **Implement lazy loading** for below-fold content
8. **Add analytics tracking** (page sections, CTAs)
9. **SEO optimization** (meta tags, structured data)
10. **Accessibility audit** before launch
