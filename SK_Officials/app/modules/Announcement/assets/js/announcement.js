/* SK Officials Community Feed JS - mirrors SK Federations community-feed.js */
'use strict';

const POSTS_PER_PAGE = 5;
let currentFilter = 'all';
let currentPage   = 1;
let editingPostId = null;
let pendingImageDataUrl = null;
let pendingLinkUrl = null;
const likedPosts = new Set();
const commentSections = new Set();

const SK_AVATAR = 'https://ui-avatars.com/api/?name=SK+Officials&background=2c2c3e&color=f5c518&size=80';

const posts = [
    {
        id: 1, type: 'announcement', owned: true,
        author: 'SK Officials - Santa Cruz', avatar: SK_AVATAR,
        time: 'Apr 5, 2026 · 9:00 AM',
        title: 'Quarterly Assembly — April 20, 2026',
        text: 'The SK Officials will hold its quarterly assembly on April 20, 2026 at the Municipal Hall. All SK officials are required to attend. Please confirm your attendance by April 15.',
        image: null, link: null, likes: 18,
        comments: [{ author: 'Maria Santos', avatar: 'https://ui-avatars.com/api/?name=Maria+Santos&background=e6a800&color=fff', text: 'Noted! Will be there.', time: '1 hour ago' }]
    },
    {
        id: 2, type: 'event', owned: false,
        author: 'SK Brgy. San Jose', avatar: 'https://ui-avatars.com/api/?name=SK+San+Jose&background=0450a8&color=fff&size=80',
        time: 'Apr 3, 2026 · 2:30 PM',
        title: 'Youth Leadership Summit — April 15, 2026',
        text: 'Join us for the Youth Leadership Summit on April 15, 2026. Open to all SK officials and youth leaders. Registration is free.',
        image: null, link: null, likes: 12, comments: [],
        details: { date: 'April 15, 2026 | 8:00 AM', location: 'Municipal Hall, Santa Cruz, Laguna' }
    },
    {
        id: 3, type: 'activity', owned: false,
        author: 'SK Brgy. Gatid', avatar: 'https://ui-avatars.com/api/?name=SK+Gatid&background=009688&color=fff&size=80',
        time: 'Mar 28, 2026 · 11:00 AM',
        title: 'Community Clean-Up Drive Completed!',
        text: 'We successfully completed the Community Clean-Up Drive with 80 participants. Thank you to all volunteers who made this possible!',
        image: null, link: null, likes: 35, comments: [],
        details: { date: 'March 28, 2026 | 7:00 AM', location: 'Barangay Gatid Plaza' }
    },
    {
        id: 4, type: 'program', owned: false,
        author: 'SK Brgy. Pagsawitan', avatar: 'https://ui-avatars.com/api/?name=SK+Pagsawitan&background=673AB7&color=fff&size=80',
        time: 'Mar 20, 2026 · 10:00 AM',
        title: 'Scholarship Program Now Open!',
        text: 'Our Education Assistance Program is now accepting applications for the upcoming semester. This program provides financial support to deserving youth.',
        image: null, link: null, likes: 52, comments: [],
        programInfo: { category: 'Education', deadline: 'March 31, 2026' }
    },
    {
        id: 5, type: 'announcement', owned: true,
        author: 'SK Officials - Santa Cruz', avatar: SK_AVATAR,
        time: 'Mar 15, 2026 · 8:00 AM',
        title: 'Q1 2026 Report Submission Deadline',
        text: 'Reminder: Submission of Barangay Program Reports for Q1 2026 is due on March 31. Please coordinate with your respective SK Chairpersons.',
        image: null, link: 'https://skoneportal.gov.ph/reports', likes: 10, comments: []
    },
    {
        id: 6, type: 'activity', owned: false,
        author: 'SK Brgy. Bubukal', avatar: 'https://ui-avatars.com/api/?name=SK+Bubukal&background=9C27B0&color=fff&size=80',
        time: 'Mar 8, 2026 · 3:15 PM',
        title: 'Environmental Drive Completed!',
        text: '95 participants joined the clean-up activity along the river banks of Brgy. Bubukal. Great job, youth!',
        image: null, link: null, likes: 56, comments: []
    },
];

function getFiltered() {
    return currentFilter === 'all' ? posts : posts.filter(function(p) { return p.type === currentFilter; });
}

function renderPosts(reset) {
    var container = document.getElementById('feed-posts');
    if (!container) return;
    if (reset) { container.innerHTML = ''; currentPage = 1; }
    var filtered = getFiltered();
    var slice = filtered.slice((currentPage - 1) * POSTS_PER_PAGE, currentPage * POSTS_PER_PAGE);
    if (!slice.length && currentPage === 1) {
        container.innerHTML = '<div class="post-card" style="text-align:center;color:#999;padding:32px;">No posts found.</div>';
    }
    slice.forEach(function(p) {
        var el = document.createElement('div');
        el.className = 'post-card';
        el.dataset.postId = p.id;
        el.innerHTML = buildPost(p);
        container.appendChild(el);
    });
    var btn = document.getElementById('load-more-btn');
    if (btn) btn.style.display = currentPage * POSTS_PER_PAGE >= filtered.length ? 'none' : 'inline-flex';
}

function buildPost(p) {
    var liked = likedPosts.has(p.id);
    var showComments = commentSections.has(p.id);
    var mediaHtml = '';
    if (p.image) mediaHtml += '<div class="post-image"><img src="' + p.image + '" alt="Post image" loading="lazy"></div>';
    if (p.link) mediaHtml += '<a href="' + p.link + '" target="_blank" rel="noopener" class="post-link-preview"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd"/></svg>' + p.link + '</a>';
    var detailsHtml = '';
    if (p.details) detailsHtml = '<div class="post-details"><div class="detail-item"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/></svg><span>' + p.details.date + '</span></div><div class="detail-item"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg><span>' + p.details.location + '</span></div></div>';
    var programHtml = '';
    if (p.programInfo) programHtml = '<div class="program-info"><div class="info-badge"><svg viewBox="0 0 20 20" fill="currentColor"><path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"/></svg>' + p.programInfo.category + '</div><div class="info-badge"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/></svg>Deadline: ' + p.programInfo.deadline + '</div></div><button class="view-details-btn" onclick="openProgramModal(\'education\')">View Program Details &amp; Apply <svg viewBox="0 0 20 20" fill="currentColor" style="width:16px;height:16px;"><path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></button>';
    var optionsHtml = '';
    if (p.owned) optionsHtml = '<div style="position:relative;"><button class="post-options-btn" onclick="togglePostOptions(' + p.id + ', event)" aria-label="Post options"><svg viewBox="0 0 20 20" fill="currentColor"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/></svg></button><div class="post-options-menu" id="options-menu-' + p.id + '"><button onclick="editPost(' + p.id + ')"><svg viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>Edit</button><button class="danger" onclick="deletePost(' + p.id + ')"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>Delete</button></div></div>';
    var commentsHtml = showComments ? buildComments(p) : '';
    return '<div class="post-header"><img src="' + p.avatar + '" alt="' + p.author + '" class="post-avatar"><div class="post-info"><h3 class="post-author">' + p.author + '</h3><p class="post-meta"><span class="post-type ' + p.type + '">' + p.type + '</span><span class="post-time">' + p.time + '</span></p></div>' + optionsHtml + '</div><div class="post-content">' + (p.title ? '<h2 class="post-title">' + p.title + '</h2>' : '') + '<p class="post-text">' + p.text + '</p>' + mediaHtml + detailsHtml + programHtml + '</div><div class="post-actions"><button class="action-btn' + (liked ? ' liked' : '') + '" onclick="toggleLike(' + p.id + ', this)"><svg viewBox="0 0 20 20" fill="currentColor"><path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"/></svg><span id="like-count-' + p.id + '">Like (' + p.likes + ')</span></button><button class="action-btn comment-btn" onclick="toggleComments(' + p.id + ')"><svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"/></svg><span id="comment-count-' + p.id + '">Comment (' + p.comments.length + ')</span></button></div><div class="comments-section" id="comments-' + p.id + '" style="' + (showComments ? '' : 'display:none;') + '">' + commentsHtml + '</div>';
}

function buildComments(p) {
    var items = p.comments.map(function(c) { return '<div class="comment-item"><img src="' + c.avatar + '" alt="' + c.author + '"><div class="comment-content"><p class="comment-author">' + c.author + '</p><p class="comment-text">' + c.text + '</p><span class="comment-time">' + c.time + '</span></div></div>'; }).join('');
    return items + '<div class="comment-input-wrapper"><img src="' + SK_AVATAR + '" alt="You"><input type="text" class="comment-input" placeholder="Write a comment..." onkeydown="submitComment(event,' + p.id + ',this)"><button class="send-comment-btn" onclick="submitCommentBtn(' + p.id + ',this)"><svg viewBox="0 0 20 20" fill="currentColor"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/></svg></button></div>';
}

function toggleLike(id, btn) { var p = posts.find(function(x){return x.id===id;}); if(!p)return; if(likedPosts.has(id)){likedPosts.delete(id);p.likes--;btn.classList.remove('liked');}else{likedPosts.add(id);p.likes++;btn.classList.add('liked');} var el=document.getElementById('like-count-'+id); if(el)el.textContent='Like ('+p.likes+')'; }
function toggleComments(id) { var section=document.getElementById('comments-'+id); if(!section)return; var p=posts.find(function(x){return x.id===id;}); if(!p)return; if(commentSections.has(id)){commentSections.delete(id);section.style.display='none';}else{commentSections.add(id);section.innerHTML=buildComments(p);section.style.display='block';} }
function submitComment(e,id,input){if(e.key==='Enter')addComment(id,input);}
function submitCommentBtn(id,btn){var input=btn.previousElementSibling;if(input)addComment(id,input);}
function addComment(id,input){var text=input.value.trim();if(!text)return;var p=posts.find(function(x){return x.id===id;});if(!p)return;p.comments.push({author:'You',avatar:SK_AVATAR,text:text,time:'Just now'});input.value='';var section=document.getElementById('comments-'+id);if(section)section.innerHTML=buildComments(p);var countEl=document.getElementById('comment-count-'+id);if(countEl)countEl.textContent='Comment ('+p.comments.length+')';}
function setFeedFilter(btn,filter){currentFilter=filter;document.querySelectorAll('.feed-tab').forEach(function(t){t.classList.remove('active');});btn.classList.add('active');renderPosts(true);}
function loadMorePosts(){currentPage++;renderPosts(false);}
function togglePostOptions(id,e){e.stopPropagation();var menu=document.getElementById('options-menu-'+id);if(!menu)return;var isOpen=menu.classList.contains('open');document.querySelectorAll('.post-options-menu.open').forEach(function(m){m.classList.remove('open');});if(!isOpen)menu.classList.add('open');}
function editPost(id){var p=posts.find(function(x){return x.id===id;});if(!p)return;editingPostId=id;document.getElementById('compose-modal-title').textContent='Edit Post';document.getElementById('edit-post-id').value=id;document.getElementById('compose-content').value=p.text;document.getElementById('compose-type').value=p.type;pendingImageDataUrl=p.image||null;pendingLinkUrl=p.link||null;document.querySelectorAll('.post-options-menu.open').forEach(function(m){m.classList.remove('open');});document.getElementById('composeModal').classList.add('active');}
function deletePost(id){if(!confirm('Delete this post?'))return;var idx=posts.findIndex(function(x){return x.id===id;});if(idx!==-1)posts.splice(idx,1);document.querySelectorAll('.post-options-menu.open').forEach(function(m){m.classList.remove('open');});renderPosts(true);}
function openComposeModal(type){editingPostId=null;pendingImageDataUrl=null;pendingLinkUrl=null;document.getElementById('compose-modal-title').textContent='Create Post';document.getElementById('edit-post-id').value='';document.getElementById('compose-content').value='';document.getElementById('compose-image-preview').style.display='none';document.getElementById('compose-link-input-wrap').style.display='none';document.getElementById('compose-link-input').value='';if(type){var map={announcement:'announcement',event:'event',photo:'activity'};document.getElementById('compose-type').value=map[type]||'update';}document.getElementById('composeModal').classList.add('active');}
function closeComposeModal(){document.getElementById('composeModal').classList.remove('active');editingPostId=null;pendingImageDataUrl=null;pendingLinkUrl=null;}
function previewImage(input){var file=input.files[0];if(!file)return;var reader=new FileReader();reader.onload=function(e){pendingImageDataUrl=e.target.result;document.getElementById('compose-preview-img').src=e.target.result;document.getElementById('compose-image-preview').style.display='block';};reader.readAsDataURL(file);}
function removeImagePreview(){pendingImageDataUrl=null;document.getElementById('compose-image-preview').style.display='none';document.getElementById('compose-image-input').value='';}
function toggleLinkInput(){var wrap=document.getElementById('compose-link-input-wrap');wrap.style.display=wrap.style.display==='none'?'block':'none';}
function submitPost(){var content=document.getElementById('compose-content').value.trim();if(!content){alert('Please write something.');return;}var type=document.getElementById('compose-type').value;var linkVal=document.getElementById('compose-link-input').value.trim();pendingLinkUrl=linkVal||null;if(editingPostId!==null){var p=posts.find(function(x){return x.id===editingPostId;});if(p){p.text=content;p.type=type;p.image=pendingImageDataUrl||p.image;p.link=pendingLinkUrl||p.link;}}else{var newId=posts.length?Math.max.apply(null,posts.map(function(x){return x.id;}))+1:1;posts.unshift({id:newId,type:type,owned:true,author:'SK Officials - Santa Cruz',avatar:SK_AVATAR,time:'Just now',title:'',text:content,image:pendingImageDataUrl,link:pendingLinkUrl,likes:0,comments:[]});}closeComposeModal();renderPosts(true);}

function toggleNotifPopover(e){e.stopPropagation();var pop=document.getElementById('notifPopover');var dd=document.getElementById('profileDropdown');dd&&dd.classList.remove('show');document.querySelector('.profile-btn')&&document.querySelector('.profile-btn').classList.remove('open');pop&&pop.classList.toggle('show');}
function toggleProfileDropdown(e){e.stopPropagation();var dd=document.getElementById('profileDropdown');var pop=document.getElementById('notifPopover');var btn=document.querySelector('.profile-btn');pop&&pop.classList.remove('show');dd&&dd.classList.toggle('show');btn&&btn.classList.toggle('open');}
function toggleSidebar(){var isMobile=window.innerWidth<=1024;if(isMobile){document.body.classList.toggle('sidebar-open');}else{document.body.classList.toggle('sidebar-collapsed');localStorage.setItem('sidebarCollapsed',document.body.classList.contains('sidebar-collapsed'));}}

document.addEventListener('DOMContentLoaded', function() {
    renderPosts(true);
    if(window.innerWidth>1024&&localStorage.getItem('sidebarCollapsed')==='true'){document.body.classList.add('sidebar-collapsed');}
    var searchInput=document.getElementById('feed-search-input');
    if(searchInput){searchInput.addEventListener('input',function(){var q=this.value.toLowerCase().trim();var container=document.getElementById('feed-posts');if(!q){renderPosts(true);return;}container.innerHTML='';var filtered=posts.filter(function(p){return(p.title&&p.title.toLowerCase().includes(q))||p.text.toLowerCase().includes(q)||p.author.toLowerCase().includes(q)||p.type.toLowerCase().includes(q);});if(!filtered.length){container.innerHTML='<div class="post-card" style="text-align:center;color:#999;padding:32px;">No posts found.</div>';return;}filtered.forEach(function(p){var el=document.createElement('div');el.className='post-card';el.dataset.postId=p.id;el.innerHTML=buildPost(p);container.appendChild(el);});var btn=document.getElementById('load-more-btn');if(btn)btn.style.display='none';});}
    document.addEventListener('click',function(){document.querySelectorAll('.post-options-menu.open').forEach(function(m){m.classList.remove('open');});document.getElementById('notifPopover')&&document.getElementById('notifPopover').classList.remove('show');document.getElementById('profileDropdown')&&document.getElementById('profileDropdown').classList.remove('show');document.querySelector('.profile-btn')&&document.querySelector('.profile-btn').classList.remove('open');});
    var fab=document.getElementById('programsFab');var sidebar=document.getElementById('programsSidebar');var backdrop=document.getElementById('programsDrawerBackdrop');
    function openDrawer(){sidebar&&sidebar.classList.add('drawer-open');backdrop&&backdrop.classList.add('active');document.body.style.overflow='hidden';}
    function closeDrawer(){sidebar&&sidebar.classList.remove('drawer-open');backdrop&&backdrop.classList.remove('active');document.body.style.overflow='';}
    fab&&fab.addEventListener('click',function(e){e.stopPropagation();openDrawer();});
    backdrop&&backdrop.addEventListener('click',closeDrawer);
    if(sidebar){var touchStartX=0;sidebar.addEventListener('touchstart',function(e){touchStartX=e.touches[0].clientX;},{passive:true});sidebar.addEventListener('touchend',function(e){var dx=e.changedTouches[0].clientX-touchStartX;if(dx>60)closeDrawer();},{passive:true});}
    window.addEventListener('resize',function(){if(window.innerWidth>1100)closeDrawer();});
});