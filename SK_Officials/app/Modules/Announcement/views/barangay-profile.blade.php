<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SK Barangay {{ $name }} - SK Officials</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite([
        'app/Modules/layout/css/header.css',
        'app/Modules/layout/css/sidebar.css',
        'app/Modules/Announcement/assets/css/announcement.css',
    ])
    <style>
        .bfp-wrap { padding: 24px; }
        .bfp-back { display:inline-flex;align-items:center;gap:6px;color:#b88600;font-size:13px;font-weight:600;text-decoration:none;margin-bottom:16px;transition:color .2s; }
        .bfp-back:hover { color:#f5c518; }
        .bfp-header-card { background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 2px 12px rgba(44,44,62,.10);margin-bottom:24px; }
        .bfp-cover { height:160px;background:linear-gradient(135deg,#2c2c3e 0%,#3a3a4a 100%);position:relative;overflow:hidden;border-bottom:3px solid #f5c518; }
        .bfp-cover::after { content:'';position:absolute;inset:0;background-image:url('/images/Background.png');background-size:cover;background-position:center;opacity:.08; }
        .bfp-info-row { padding:0 28px 22px;display:flex;align-items:flex-end;gap:20px;flex-wrap:wrap; }
        .bfp-avatar-wrap { margin-top:-50px;flex-shrink:0; }
        .bfp-avatar { width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,#2c2c3e,#3a3a4a);border:4px solid #f5c518;display:flex;align-items:center;justify-content:center;font-size:32px;font-weight:900;color:#f5c518;box-shadow:0 4px 16px rgba(0,0,0,.2); }
        .bfp-meta { flex:1;padding-top:12px; }
        .bfp-badge { display:inline-flex;align-items:center;gap:5px;background:rgba(245,197,24,.12);color:#b88600;font-size:10px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;padding:3px 10px;border-radius:999px;margin-bottom:4px;border:1px solid rgba(245,197,24,.25); }
        .bfp-name { font-size:20px;font-weight:800;color:#1a1a2e;margin-bottom:2px; }
        .bfp-loc  { font-size:13px;color:#64748b;margin-bottom:8px; }
        .bfp-stats { display:flex;gap:20px;flex-wrap:wrap; }
        .bfp-stat strong { display:block;font-size:18px;font-weight:800;color:#2c2c3e; }
        .bfp-stat span   { font-size:11px;color:#94a3b8; }
        .bfp-edit-btn { padding:9px 18px;background:linear-gradient(135deg,#f5c518,#e6a800);color:#1a1a2e;border:none;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;align-self:flex-end;margin-bottom:4px;display:inline-flex;align-items:center;gap:7px;transition:box-shadow .2s; }
        .bfp-edit-btn:hover { box-shadow:0 4px 14px rgba(245,197,24,.45); }
        .bfp-grid { display:grid;grid-template-columns:300px 1fr;gap:20px;align-items:start; }
        .bfp-left,.bfp-right { display:flex;flex-direction:column;gap:16px; }
        .bfp-card { background:#fff;border-radius:14px;padding:20px 22px;box-shadow:0 2px 8px rgba(44,44,62,.07); }
        .bfp-card-title { font-size:14px;font-weight:700;color:#1a1a2e;margin-bottom:14px;display:flex;align-items:center;gap:8px; }
        .bfp-card-title i { color:#b88600; }
        .bfp-officer-item { display:flex;align-items:center;gap:10px;padding:9px 0;border-bottom:1px solid #f1f5f9; }
        .bfp-officer-item:last-child { border-bottom:none; }
        .bfp-officer-dot { width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#2c2c3e,#3a3a4a);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;color:#f5c518;flex-shrink:0; }
        .bfp-officer-name { font-size:13px;font-weight:700;color:#1e293b; }
        .bfp-officer-role { font-size:11px;color:#94a3b8; }
        .bfp-councilor-grid { display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:12px; }
        .bfp-councilor-chip { display:flex;align-items:center;gap:8px;background:#f5f7fa;border-radius:10px;padding:8px 10px; }
        .bfp-councilor-dot { width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,#2c2c3e,#3a3a4a);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;color:#f5c518;flex-shrink:0; }
        .bfp-councilor-name { font-size:11px;font-weight:600;color:#444; }
    </style>
</head>
    <style>
        .bfp-contact-row { display:flex;align-items:center;gap:10px;padding:9px 0;border-bottom:1px solid #f1f5f9; }
        .bfp-contact-row:last-child { border-bottom:none; }
        .bfp-contact-icon { width:30px;height:30px;border-radius:8px;background:rgba(245,197,24,.12);display:flex;align-items:center;justify-content:center;flex-shrink:0; }
        .bfp-contact-icon i { font-size:12px;color:#b88600; }
        .bfp-contact-label { font-size:11px;color:#94a3b8; }
        .bfp-contact-value { font-size:13px;font-weight:600;color:#1e293b; }
        .bfp-feed-tabs { display:flex;gap:4px;margin-bottom:16px;border-bottom:2px solid #e2e8f0; }
        .bfp-tab { padding:9px 16px;border:none;background:none;font-size:13px;font-weight:600;color:#64748b;cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-2px;transition:all .2s;font-family:inherit; }
        .bfp-tab.active { color:#b88600;border-bottom-color:#f5c518; }
        .bfp-tab:hover  { color:#b88600; }
        .bfp-post { border:1px solid #e2e8f0;border-radius:12px;padding:16px;margin-bottom:14px;transition:box-shadow .2s; }
        .bfp-post:hover { box-shadow:0 4px 16px rgba(44,44,62,.08); }
        .bfp-post-header { display:flex;align-items:center;gap:10px;margin-bottom:10px; }
        .bfp-post-avatar { width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#2c2c3e,#3a3a4a);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;color:#f5c518;flex-shrink:0; }
        .bfp-post-author { font-size:13px;font-weight:700;color:#1a1a2e; }
        .bfp-post-meta   { font-size:11px;color:#94a3b8;margin-top:2px; }
        .bfp-post-type { display:inline-block;padding:2px 7px;border-radius:8px;font-size:10px;font-weight:700;text-transform:uppercase;margin-right:4px; }
        .bfp-post-type.event        { background:#fef3c7;color:#92400e; }
        .bfp-post-type.announcement { background:rgba(245,197,24,.15);color:#b88600; }
        .bfp-post-type.activity     { background:#dcfce7;color:#15803d; }
        .bfp-post-title { font-size:15px;font-weight:700;color:#1a1a2e;margin-bottom:6px; }
        .bfp-post-text  { font-size:13px;color:#475569;line-height:1.6;margin-bottom:10px; }
        .bfp-post-detail { display:flex;align-items:center;gap:6px;font-size:12px;color:#64748b;margin-bottom:4px; }
        .bfp-post-detail i { color:#b88600;font-size:11px; }
        .bfp-post-actions { display:flex;gap:8px;margin-top:12px;padding-top:10px;border-top:1px solid #f1f5f9; }
        .bfp-action-btn { flex:1;display:flex;align-items:center;justify-content:center;gap:6px;padding:7px;border:none;background:none;border-radius:8px;font-size:12px;color:#64748b;cursor:pointer;transition:background .2s;font-family:inherit; }
        .bfp-action-btn:hover { background:#f1f5f9;color:#b88600; }
        /* Shared modal base */
        .bfp-modal { display:none;position:fixed;inset:0;z-index:2000;align-items:center;justify-content:center; }
        .bfp-modal.active { display:flex; }
        .bfp-modal-overlay { position:absolute;inset:0;background:rgba(0,0,0,.55);backdrop-filter:blur(4px); }
        .bfp-modal-box { position:relative;background:#fff;border-radius:20px;width:90%;max-height:90vh;overflow:hidden;display:flex;flex-direction:column;animation:bfpModalIn .3s ease; }
        @keyframes bfpModalIn { from{opacity:0;transform:scale(.93) translateY(14px)} to{opacity:1;transform:scale(1) translateY(0)} }
        .bfp-modal-header { display:flex;align-items:center;justify-content:space-between;padding:18px 24px;background:linear-gradient(135deg,#2c2c3e,#3a3a4a);border-bottom:3px solid #f5c518; }
        .bfp-modal-header h2 { font-size:17px;font-weight:700;color:#f5c518;display:flex;align-items:center;gap:9px; }
        .bfp-modal-close { width:32px;height:32px;border:none;background:rgba(255,255,255,.1);border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#fff;font-size:16px;transition:background .2s; }
        .bfp-modal-close:hover { background:rgba(245,197,24,.3);color:#f5c518; }
        .bfp-modal-body { padding:24px;overflow-y:auto; }
        .bfp-modal-footer { display:flex;gap:10px;justify-content:flex-end;padding:14px 24px;border-top:1px solid #e2e8f0;background:#fafafa; }
        /* Edit modal */
        .bfp-modal-box.edit-modal { max-width:600px; }
        .edit-field-group { display:grid;grid-template-columns:1fr 1fr;gap:16px; }
        .edit-field-wrap label { display:block;font-size:12px;font-weight:600;color:#64748b;margin-bottom:5px; }
        .edit-field { width:100%;padding:9px 12px;border:2px solid #e2e8f0;border-radius:8px;font-size:13px;font-family:inherit;transition:border-color .2s;box-sizing:border-box; }
        .edit-field:focus { outline:none;border-color:#f5c518; }
        /* Compose modal */
        .bfp-modal-box.compose-modal { max-width:560px; }
        .bfp-compose-trigger { flex:1;background:#f5f7fa;border:2px solid #e8ecf0;border-radius:24px;padding:10px 18px;text-align:left;color:#999;font-size:14px;font-family:inherit;cursor:pointer;transition:all .2s; }
        .bfp-compose-trigger:hover { border-color:#f5c518;color:#666; }
        /* Shared buttons */
        .bfp-btn-primary { padding:9px 20px;background:linear-gradient(135deg,#f5c518,#e6a800);color:#1a1a2e;border:none;border-radius:8px;font-size:13px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:7px;transition:box-shadow .2s;font-family:inherit; }
        .bfp-btn-primary:hover { box-shadow:0 4px 14px rgba(245,197,24,.45); }
        .bfp-btn-secondary { padding:9px 18px;background:#f5f7fa;color:#555;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;transition:background .2s;font-family:inherit; }
        .bfp-btn-secondary:hover { background:#e8ecf0; }
        @media (max-width:1024px) { .bfp-grid { grid-template-columns:1fr; } }
        @media (max-width:640px) { .bfp-wrap{padding:16px;} .bfp-info-row{padding:0 16px 18px;} .bfp-councilor-grid{grid-template-columns:1fr;} .edit-field-group{grid-template-columns:1fr;} }
    </style>
<body>

@include('layout::header')
@include('layout::sidebar')

<main class="main-content">
    <div class="bfp-wrap">
        <a href="{{ route('announcements') }}" class="bfp-back">
            <i class="fas fa-arrow-left"></i> Back to Community Feed
        </a>

        {{-- Header Card --}}
        <div class="bfp-header-card">
            <div class="bfp-cover"></div>
            <div class="bfp-info-row">
                <div class="bfp-avatar-wrap">
                    <div class="bfp-avatar">{{ strtoupper(substr($name, 0, 2)) }}</div>
                </div>
                <div class="bfp-meta">
                    <div class="bfp-badge"><i class="fas fa-check-circle" style="font-size:10px;"></i> Sangguniang Kabataan</div>
                    <h1 class="bfp-name">SK Barangay {{ $name }}</h1>
                    <p class="bfp-loc"><i class="fas fa-map-marker-alt" style="color:#b88600;margin-right:4px;"></i>Barangay {{ $name }}, Santa Cruz, Laguna</p>
                    <div class="bfp-stats">
                        <div class="bfp-stat"><strong id="post-count">3</strong><span>Posts</span></div>
                        <div class="bfp-stat"><strong>12</strong><span>Officers</span></div>
                        <div class="bfp-stat"><strong>2023–2026</strong><span>SK Term</span></div>
                    </div>
                </div>
                <button class="bfp-edit-btn" onclick="openEditModal()">
                    <i class="fas fa-edit"></i> Edit Profile
                </button>
            </div>
        </div>

        <div class="bfp-grid">
            {{-- LEFT --}}
            <div class="bfp-left">
                <div class="bfp-card">
                    <div class="bfp-card-title"><i class="fas fa-users"></i> SK Officers</div>
                    @php
                    $officerList = [
                        ['name'=>'[SK Chairman]',   'role'=>'SK Chairman'],
                        ['name'=>'[Vice Chairman]', 'role'=>'Vice Chairman'],
                        ['name'=>'[Secretary]',     'role'=>'Secretary'],
                        ['name'=>'[Treasurer]',     'role'=>'Treasurer'],
                        ['name'=>'[Auditor]',       'role'=>'Auditor'],
                        ['name'=>'[PRO]',           'role'=>'Public Relations Officer'],
                    ];
                    $councilors = ['[Councilor 1]','[Councilor 2]','[Councilor 3]','[Councilor 4]','[Councilor 5]','[Councilor 6]'];
                    @endphp
                    @foreach($officerList as $o)
                    <div class="bfp-officer-item">
                        <div class="bfp-officer-dot">{{ strtoupper(substr(trim($o['name'],'[]'),0,2)) }}</div>
                        <div><p class="bfp-officer-name">{{ $o['name'] }}</p><p class="bfp-officer-role">{{ $o['role'] }}</p></div>
                    </div>
                    @endforeach
                    <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#94a3b8;margin:14px 0 8px;">SK Councilors</p>
                    <div class="bfp-councilor-grid">
                        @foreach($councilors as $c)
                        <div class="bfp-councilor-chip">
                            <div class="bfp-councilor-dot">{{ strtoupper(substr(trim($c,'[]'),0,2)) }}</div>
                            <span class="bfp-councilor-name">{{ $c }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="bfp-card">
                    <div class="bfp-card-title"><i class="fas fa-info-circle"></i> Barangay Information</div>
                    @foreach([['Barangay',$name],['Municipality','Santa Cruz'],['Province','Laguna'],['Region','Region IV-A (CALABARZON)'],['SK Term','2023 – 2026'],['Total Officers','12']] as $row)
                    <div class="bfp-contact-row"><div><p class="bfp-contact-label">{{ $row[0] }}</p><p class="bfp-contact-value">{{ $row[1] }}</p></div></div>
                    @endforeach
                </div>

                <div class="bfp-card">
                    <div class="bfp-card-title"><i class="fas fa-phone"></i> Contact Information</div>
                    @foreach([['fas fa-phone','Phone','[SK Contact Number]'],['fas fa-envelope','Email','[SK Email Address]'],['fas fa-map-marker-alt','Office Address','Barangay '.$name.' Hall, Santa Cruz, Laguna'],['fas fa-clock','Office Hours','Mon–Fri, 8:00 AM – 5:00 PM']] as $row)
                    <div class="bfp-contact-row">
                        <div class="bfp-contact-icon"><i class="{{ $row[0] }}"></i></div>
                        <div><p class="bfp-contact-label">{{ $row[1] }}</p><p class="bfp-contact-value">{{ $row[2] }}</p></div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- RIGHT --}}
            <div class="bfp-right">
                <div class="bfp-card">
                    <div class="bfp-card-title"><i class="fas fa-plus-circle"></i> Create a Post</div>
                    <div style="display:flex;align-items:center;gap:12px;">
                        <div class="bfp-post-avatar" style="flex-shrink:0;">{{ strtoupper(substr($name,0,2)) }}</div>
                        <button class="bfp-compose-trigger" onclick="openBfpCompose()">
                            Share an update for Barangay {{ $name }}...
                        </button>
                    </div>
                </div>

                <div class="bfp-card">
                    <div class="bfp-card-title"><i class="fas fa-newspaper"></i> Posts from Barangay {{ $name }}</div>
                    <div class="bfp-feed-tabs">
                        <button class="bfp-tab active" data-tab="all">All</button>
                        <button class="bfp-tab" data-tab="event">Events</button>
                        <button class="bfp-tab" data-tab="announcement">Announcements</button>
                        <button class="bfp-tab" data-tab="activity">Activities</button>
                    </div>
                    <div id="bfpFeed">
                        @php
                        $samplePosts = [
                            ['type'=>'Announcement','type_class'=>'announcement','posted_at'=>'Apr 5, 2026','title'=>'Quarterly Assembly','text'=>'The SK Barangay '.$name.' will hold its quarterly assembly. All SK officials are required to attend.','date'=>'April 20, 2026 | 9:00 AM','venue'=>'Barangay '.$name.' Hall','audience'=>'All SK Officials'],
                            ['type'=>'Event','type_class'=>'event','posted_at'=>'Mar 28, 2026','title'=>'Youth Leadership Training','text'=>'Join us for a youth leadership training program open to all SK officials and youth leaders.','date'=>'April 10, 2026 | 8:00 AM','venue'=>'Municipal Hall, Santa Cruz','audience'=>'SK Officials & Youth Leaders'],
                            ['type'=>'Activity','type_class'=>'activity','posted_at'=>'Mar 15, 2026','title'=>'Community Clean-Up Drive','text'=>'We successfully completed the community clean-up drive with 60 participants. Thank you to all volunteers!','date'=>'March 15, 2026 | 7:00 AM','venue'=>'Barangay '.$name.' Plaza','audience'=>'Community Members'],
                        ];
                        @endphp
                        @foreach($samplePosts as $post)
                        <div class="bfp-post" data-post-type="{{ $post['type_class'] }}">
                            <div class="bfp-post-header">
                                <div class="bfp-post-avatar">{{ strtoupper(substr($name,0,2)) }}</div>
                                <div>
                                    <p class="bfp-post-author">SK Brgy. {{ $name }}</p>
                                    <p class="bfp-post-meta"><span class="bfp-post-type {{ $post['type_class'] }}">{{ $post['type'] }}</span>{{ $post['posted_at'] }}</p>
                                </div>
                            </div>
                            <h3 class="bfp-post-title">{{ $post['title'] }}</h3>
                            <p class="bfp-post-text">{{ $post['text'] }}</p>
                            <div class="bfp-post-detail"><i class="fas fa-calendar-alt"></i> {{ $post['date'] }}</div>
                            <div class="bfp-post-detail"><i class="fas fa-map-marker-alt"></i> {{ $post['venue'] }}</div>
                            <div class="bfp-post-detail"><i class="fas fa-users"></i> {{ $post['audience'] }}</div>
                            <div class="bfp-post-actions">
                                <button class="bfp-action-btn"><i class="fas fa-thumbs-up"></i> Like</button>
                                <button class="bfp-action-btn"><i class="fas fa-comment"></i> Comment</button>
                                <button class="bfp-action-btn"><i class="fas fa-share"></i> Share</button>
                            </div>
                        </div>
                        @endforeach
                        <div id="bfp-new-posts"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

{{-- ══ EDIT PROFILE MODAL ══ --}}
<div id="editProfileModal" class="bfp-modal">
    <div class="bfp-modal-overlay" onclick="closeEditModal()"></div>
    <div class="bfp-modal-box edit-modal">
        <div class="bfp-modal-header">
            <h2><i class="fas fa-edit"></i> Edit Barangay Profile</h2>
            <button class="bfp-modal-close" onclick="closeEditModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="bfp-modal-body">
            <div class="edit-field-group">
                <div class="edit-field-wrap">
                    <label>SK Chairman Name</label>
                    <input type="text" class="edit-field" id="edit-chairman" placeholder="[SK Chairman Name]">
                </div>
                <div class="edit-field-wrap">
                    <label>Vice Chairman</label>
                    <input type="text" class="edit-field" id="edit-vice" placeholder="[Vice Chairman Name]">
                </div>
                <div class="edit-field-wrap">
                    <label>Secretary</label>
                    <input type="text" class="edit-field" id="edit-secretary" placeholder="[Secretary Name]">
                </div>
                <div class="edit-field-wrap">
                    <label>Treasurer</label>
                    <input type="text" class="edit-field" id="edit-treasurer" placeholder="[Treasurer Name]">
                </div>
                <div class="edit-field-wrap">
                    <label>Contact Number</label>
                    <input type="text" class="edit-field" id="edit-contact" placeholder="[Contact Number]">
                </div>
                <div class="edit-field-wrap">
                    <label>Email Address</label>
                    <input type="email" class="edit-field" id="edit-email" placeholder="[Email Address]">
                </div>
            </div>
        </div>
        <div class="bfp-modal-footer">
            <button class="bfp-btn-secondary" onclick="closeEditModal()">Cancel</button>
            <button class="bfp-btn-primary" onclick="saveProfile()"><i class="fas fa-save"></i> Save Changes</button>
        </div>
    </div>
</div>

{{-- ══ COMPOSE POST MODAL ══ --}}
<div id="bfpComposeModal" class="bfp-modal">
    <div class="bfp-modal-overlay" onclick="closeBfpCompose()"></div>
    <div class="bfp-modal-box compose-modal">
        <div class="bfp-modal-header">
            <h2><i class="fas fa-pen"></i> Create Post — Brgy. {{ $name }}</h2>
            <button class="bfp-modal-close" onclick="closeBfpCompose()"><i class="fas fa-times"></i></button>
        </div>
        <div class="bfp-modal-body">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
                <div class="bfp-post-avatar">{{ strtoupper(substr($name,0,2)) }}</div>
                <div>
                    <div style="font-weight:700;font-size:14px;color:#1a1a2e;">SK Brgy. {{ $name }}</div>
                    <select style="font-size:12px;border:1px solid #e0e0e0;border-radius:6px;padding:3px 8px;color:#555;font-family:inherit;margin-top:3px;" id="bfp-post-type">
                        <option value="update">Update</option>
                        <option value="announcement">Announcement</option>
                        <option value="event">Event</option>
                        <option value="activity">Activity</option>
                    </select>
                </div>
            </div>
            <textarea id="bfp-post-content" class="edit-field" placeholder="Write something for your barangay..." rows="4" style="resize:vertical;"></textarea>
        </div>
        <div class="bfp-modal-footer">
            <button class="bfp-btn-secondary" onclick="closeBfpCompose()">Cancel</button>
            <button class="bfp-btn-primary" onclick="submitBfpPost()"><i class="fas fa-paper-plane"></i> Post</button>
        </div>
    </div>
</div>

@vite([
    'app/Modules/layout/js/header.js',
    'app/Modules/layout/js/sidebar.js',
])

<script>
// Tab filtering
document.querySelectorAll('.bfp-tab').forEach(function(tab) {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.bfp-tab').forEach(function(t) { t.classList.remove('active'); });
        tab.classList.add('active');
        var filter = tab.dataset.tab;
        document.querySelectorAll('#bfpFeed .bfp-post').forEach(function(post) {
            post.style.display = (filter === 'all' || post.dataset.postType === filter) ? 'block' : 'none';
        });
    });
});

// Edit Profile Modal
function openEditModal()  { document.getElementById('editProfileModal').classList.add('active'); }
function closeEditModal() { document.getElementById('editProfileModal').classList.remove('active'); }
function saveProfile() {
    alert('Profile saved! (In production this would save to the database.)');
    closeEditModal();
}

// Compose Post Modal
function openBfpCompose()  { document.getElementById('bfpComposeModal').classList.add('active'); }
function closeBfpCompose() {
    document.getElementById('bfpComposeModal').classList.remove('active');
    document.getElementById('bfp-post-content').value = '';
}
function submitBfpPost() {
    var content = document.getElementById('bfp-post-content').value.trim();
    var type    = document.getElementById('bfp-post-type').value;
    if (!content) { alert('Please write something.'); return; }
    var feed = document.getElementById('bfp-new-posts');
    var div  = document.createElement('div');
    div.className = 'bfp-post';
    div.dataset.postType = type;
    div.innerHTML = '<div class="bfp-post-header"><div class="bfp-post-avatar">{{ strtoupper(substr($name,0,2)) }}</div><div><p class="bfp-post-author">SK Brgy. {{ $name }}</p><p class="bfp-post-meta"><span class="bfp-post-type '+type+'">'+type+'</span> Just now</p></div></div><p class="bfp-post-text">'+content+'</p><div class="bfp-post-actions"><button class="bfp-action-btn"><i class="fas fa-thumbs-up"></i> Like</button><button class="bfp-action-btn"><i class="fas fa-comment"></i> Comment</button><button class="bfp-action-btn"><i class="fas fa-share"></i> Share</button></div>';
    feed.insertBefore(div, feed.firstChild);
    var count = document.getElementById('post-count');
    if (count) count.textContent = parseInt(count.textContent) + 1;
    closeBfpCompose();
}
</script>
</body>
</html>
