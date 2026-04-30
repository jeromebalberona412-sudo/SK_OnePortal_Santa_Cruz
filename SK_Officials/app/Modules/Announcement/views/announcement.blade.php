
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Community Feed - SK Officials</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite([
        'app/Modules/layout/css/header.css',
        'app/Modules/layout/css/sidebar.css',
        'app/Modules/Announcement/assets/css/announcement.css',
    ])
</head>
<body>

@include('layout::header')
@include('layout::sidebar')

<main class="main-content ann-main">
    <div class="ann-container">

        {{-- CENTER: Feed --}}
        <div class="feed-section">

            {{-- SK Officials Info Card --}}
            <div class="sk-fed-card" style="cursor:pointer;" onclick="window.location.href='{{ route('sk-officials.barangay-profile', ['slug' => 'san-jose']) }}'">
                <div class="sk-fed-card-banner">
                    <img src="{{ asset('images/logo.png') }}" alt="SK Officials Logo" class="sk-fed-card-logo">
                    <div class="sk-fed-card-info">
                        <h2 class="sk-fed-card-name">SK OnePortal</h2>
                        <p class="sk-fed-card-sub">SK Officials Portal · Santa Cruz, Laguna</p>
                        <p style="font-size:11px;color:rgba(255,255,255,0.85);font-weight:600;margin-top:4px;">View Your Barangay Profile →</p>
                    </div>
                </div>
            </div>

            {{-- Compose Post --}}
            <div class="post-card compose-card">
                <div class="compose-row">
                    <div class="compose-avatar-sm-placeholder"><i class="fas fa-user"></i></div>
                    <button class="compose-trigger" onclick="openComposeModal()">What's happening in your barangay?</button>
                </div>
                <div class="compose-actions">
                    <button class="compose-action-btn" onclick="openComposeModal('announcement')">
                        <svg viewBox="0 0 20 20" fill="currentColor"><path d="M18 3a1 1 0 00-1.447-.894L8.763 6H5a3 3 0 000 6h.28l1.771 5.316A1 1 0 008 18h1a1 1 0 001-1v-4.382l6.553 3.276A1 1 0 0018 15V3z"/></svg>
                        Announcement
                    </button>
                    <button class="compose-action-btn" onclick="openComposeModal('event')">
                        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/></svg>
                        Event
                    </button>
                    <button class="compose-action-btn" onclick="openComposeModal('photo')">
                        <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/></svg>
                        Photo
                    </button>
                </div>
            </div>

            {{-- Filter Tabs --}}
            <div class="feed-filter-bar">
                <button class="feed-tab active" data-filter="all" onclick="setFeedFilter(this,'all')">All</button>
                <button class="feed-tab" data-filter="announcement" onclick="setFeedFilter(this,'announcement')">Announcements</button>
                <button class="feed-tab" data-filter="event" onclick="setFeedFilter(this,'event')">Events</button>
                <button class="feed-tab" data-filter="activity" onclick="setFeedFilter(this,'activity')">Activities</button>
                <button class="feed-tab" data-filter="program" onclick="setFeedFilter(this,'program')">Programs</button>
            </div>

            <div id="feed-posts"></div>

            <div style="text-align:center;padding:8px 0 16px;">
                <button class="load-more-btn" id="load-more-btn" onclick="loadMorePosts()">
                    <svg viewBox="0 0 20 20" fill="currentColor" style="width:16px;height:16px;"><path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/></svg>
                    Load More
                </button>
            </div>
        </div>

        {{-- RIGHT: Programs Sidebar --}}
        <aside class="programs-sidebar" id="programsSidebar">
            <div class="sidebar-card">
                <h2 class="sidebar-title">Programs in Your Barangay</h2>
                <p class="sidebar-subtitle">Available programs for SK officials</p>
                <div class="program-categories">
                    <div class="program-category" onclick="openProgramModal('education')">
                        <div class="category-icon education"><svg viewBox="0 0 20 20" fill="currentColor"><path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3z"/><path d="M3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/></svg></div>
                        <div class="category-content"><h3>Education</h3><p>1 active program</p></div>
                        <svg class="chevron" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                    </div>
                    <div class="program-category" onclick="openProgramModal('anti-drugs')">
                        <div class="category-icon anti-drugs"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"/></svg></div>
                        <div class="category-content"><h3>Anti-Drugs</h3><p>0 active programs</p></div>
                        <svg class="chevron" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                    </div>
                    <div class="program-category" onclick="openProgramModal('sports')">
                        <div class="category-icon sports"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/></svg></div>
                        <div class="category-content"><h3>Sports Development</h3><p>0 active programs</p></div>
                        <svg class="chevron" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                    </div>
                    <div class="program-category" onclick="openProgramModal('health')">
                        <div class="category-icon health"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/></svg></div>
                        <div class="category-content"><h3>Health</h3><p>0 active programs</p></div>
                        <svg class="chevron" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                    </div>
                    <div class="program-category" onclick="openProgramModal('disaster')">
                        <div class="category-icon disaster"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 6a3 3 0 013-3h10a1 1 0 01.8 1.6L14.25 8l2.55 3.4A1 1 0 0116 13H6a1 1 0 00-1 1v3a1 1 0 11-2 0V6z" clip-rule="evenodd"/></svg></div>
                        <div class="category-content"><h3>Disaster Preparedness</h3><p>0 active programs</p></div>
                        <svg class="chevron" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                    </div>
                    <div class="program-category" onclick="openProgramModal('others')">
                        <div class="category-icon others"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/></svg></div>
                        <div class="category-content"><h3>Others</h3><p>0 active programs</p></div>
                        <svg class="chevron" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                    </div>
                </div>
            </div>

            <div class="sidebar-card" style="margin-top:16px;">
                <h2 class="sidebar-title">Barangay SK Profiles</h2>
                <p class="sidebar-subtitle">Browse SK officials from each barangay.</p>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    @php
                    $brgyList = [
                        ['name'=>'Alipit',        'slug'=>'alipit',        'color'=>'#4CAF50'],
                        ['name'=>'Bagumbayan',    'slug'=>'bagumbayan',    'color'=>'#2196F3'],
                        ['name'=>'Bubukal',       'slug'=>'bubukal',       'color'=>'#9C27B0'],
                        ['name'=>'Duhat',         'slug'=>'duhat',         'color'=>'#FF9800'],
                        ['name'=>'Gatid',         'slug'=>'gatid',         'color'=>'#009688'],
                        ['name'=>'Labuin',        'slug'=>'labuin',        'color'=>'#f44336'],
                        ['name'=>'Pagsawitan',    'slug'=>'pagsawitan',    'color'=>'#673AB7'],
                        ['name'=>'San Jose',      'slug'=>'san-jose',      'color'=>'#0450a8'],
                        ['name'=>'Santisima Cruz','slug'=>'santisima-cruz','color'=>'#FF5722'],
                    ];
                    @endphp
                    @foreach($brgyList as $brgy)
                    <a href="{{ route('sk-officials.barangay-profile', ['slug' => $brgy['slug']]) }}" class="brgy-link-item">
                        <div class="brgy-link-dot" style="background:{{ $brgy['color'] }};">{{ strtoupper(substr($brgy['name'], 0, 2)) }}</div>
                        <div class="brgy-link-info">
                            <p class="brgy-link-name">Brgy. {{ $brgy['name'] }}</p>
                            <p class="brgy-link-sub">SK Officials</p>
                        </div>
                        <svg style="width:14px;height:14px;color:#bbb;flex-shrink:0;" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                    </a>
                    @endforeach
                </div>
            </div>
        </aside>
    </div>
</main>

<div class="programs-drawer-backdrop" id="programsDrawerBackdrop"></div>
<button class="programs-fab" id="programsFab" aria-label="View Programs"><i class="fas fa-th-list"></i></button>

{{-- Compose Modal --}}
<div id="composeModal" class="program-modal">
    <div class="modal-overlay" onclick="closeComposeModal()"></div>
    <div class="modal-container" style="max-width:560px;">
        <div class="modal-header">
            <h2 id="compose-modal-title">Create Post</h2>
            <button class="modal-close" onclick="closeComposeModal()"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="edit-post-id" value="">
            <div class="compose-type-row">
                <label class="compose-type-label">Post Type:</label>
                <select class="compose-type-select" id="compose-type">
                    <option value="update">Update</option>
                    <option value="announcement">Announcement</option>
                    <option value="event">Event</option>
                    <option value="activity">Activity</option>
                    <option value="program">Youth Program</option>
                </select>
            </div>
            <textarea class="compose-textarea" id="compose-content" placeholder="Write something..." rows="4"></textarea>
            <div class="compose-attach-row">
                <label class="compose-attach-btn" for="compose-image-input"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/></svg> Photo</label>
                <input type="file" id="compose-image-input" accept="image/*" style="display:none;" onchange="previewImage(this)">
                <label class="compose-attach-btn" onclick="toggleLinkInput()"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd"/></svg> Link</label>
            </div>
            <div id="compose-image-preview" style="display:none;margin-top:10px;position:relative;">
                <img id="compose-preview-img" src="" alt="Preview" style="width:100%;border-radius:10px;max-height:220px;object-fit:cover;">
                <button onclick="removeImagePreview()" style="position:absolute;top:6px;right:6px;background:rgba(0,0,0,0.55);border:none;border-radius:50%;width:28px;height:28px;color:#fff;cursor:pointer;">✕</button>
            </div>
            <div id="compose-link-input-wrap" style="display:none;margin-top:10px;">
                <input type="url" id="compose-link-input" class="compose-link-field" placeholder="Paste a link (https://...)">
            </div>
        </div>
        <div class="modal-footer-btns">
            <button class="btn-secondary" onclick="closeComposeModal()">Cancel</button>
            <button class="btn-primary" onclick="submitPost()"><svg viewBox="0 0 20 20" fill="currentColor" style="width:16px;height:16px;"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/></svg> Post</button>
        </div>
    </div>
</div>

{{-- Education Program Modal --}}
<div id="educationModal" class="program-modal">
    <div class="modal-overlay"></div>
    <div class="modal-container">
        <div class="modal-header"><h2>Education Programs</h2><button class="modal-close" onclick="closeProgramModal('educationModal')"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></button></div>
        <div class="modal-body">
            <div class="program-item">
                <div class="program-header"><h3>🎓 Scholarship Assistance Program</h3><span class="program-status active">Active</span></div>
                <p class="program-description">Financial assistance for deserving students pursuing higher education.</p>
                <div class="program-meta"><span>📅 Deadline: March 31, 2026</span><span>👥 50 slots available</span></div>
                <button class="apply-btn">Apply Now</button>
            </div>
        </div>
    </div>
</div>

{{-- No Program Modal --}}
<div id="noProgramModal" class="program-modal">
    <div class="modal-overlay"></div>
    <div class="modal-container">
        <div class="modal-header"><h2 id="noProgramModalTitle">Programs</h2><button class="modal-close" onclick="closeProgramModal('noProgramModal')"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></button></div>
        <div class="modal-body" style="text-align:center;padding:40px 20px;">
            <i class="fas fa-inbox" style="font-size:48px;color:#ccc;display:block;margin-bottom:16px;"></i>
            <h3 style="font-size:18px;color:#333;margin-bottom:8px;">No Programs Available</h3>
            <p style="color:#999;font-size:14px;line-height:1.6;">There are currently no active programs in this category.</p>
        </div>
    </div>
</div>

@vite([
    'app/Modules/layout/js/header.js',
    'app/Modules/layout/js/sidebar.js',
    'app/Modules/Announcement/assets/js/announcement.js',
])

<script>
function openProgramModal(cat) {
    if (cat === 'education') { document.getElementById('educationModal').classList.add('active'); }
    else { var t={'anti-drugs':'Anti-Drugs Programs','sports':'Sports Development','health':'Health Programs','disaster':'Disaster Preparedness','others':'Other Programs'}; document.getElementById('noProgramModalTitle').textContent=t[cat]||'Programs'; document.getElementById('noProgramModal').classList.add('active'); }
}
function closeProgramModal(id) { document.getElementById(id).classList.remove('active'); }
</script>
</body>
</html>
