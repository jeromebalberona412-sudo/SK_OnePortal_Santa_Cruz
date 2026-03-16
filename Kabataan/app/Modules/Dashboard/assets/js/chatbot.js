/* =============================================
   SK OnePortal — Dashboard Chatbot Popover JS
   ============================================= */

// ── Keyword-based reply engine ──────────────────────────────────────────────
const CP_REPLIES = [
    {
        keys: ['program', 'programa', 'programs'],
        reply: 'Ang SK Santa Cruz ay may mga programa sa Education, Sports, Health, Anti-Drugs, Agriculture, Disaster Preparedness, at Gender & Development. Alin ang gusto mong malaman?'
    },
    {
        keys: ['scholarship', 'iskolarship', 'scholar', 'tuition', 'education', 'edukasyon'],
        reply: '🎓 Ang Scholarship Assistance Program ay bukas na! Deadline: March 31, 2026. Pumunta sa Programs sidebar at i-click ang Education para mag-apply.'
    },
    {
        keys: ['event', 'events', 'aktibidad', 'activity', 'activities'],
        reply: '📅 Mga paparating na events:\n• Community Clean-Up Drive — March 15, 2026\n• Youth Leadership Summit — March 20, 2026\nAbangan ang mga announcements sa feed!'
    },
    {
        keys: ['apply', 'mag-apply', 'application', 'pano', 'paano', 'how'],
        reply: '📝 Para mag-apply sa isang programa:\n1. Pumunta sa Programs sidebar\n2. I-click ang kategorya\n3. Basahin ang detalye\n4. I-click ang "Apply Now"\n\nKailangan mo ng valid documents para sa application.'
    },
    {
        keys: ['contact', 'kontak', 'sk', 'tanggapan', 'office', 'numero', 'number'],
        reply: '📞 SK Santa Cruz, Laguna\n📍 Municipal Hall, Santa Cruz, Laguna\n🕐 Lunes–Biyernes, 8AM–5PM\n\nMaaari ka ring mag-message sa aming official Facebook page.'
    },
    {
        keys: ['sports', 'palakasan', 'basketball', 'volleyball', 'sports development'],
        reply: '🏅 Ang Sports Development program ay naglalayong palakasin ang kabataan sa pamamagitan ng iba\'t ibang palakasan. Abangan ang mga susunod na aktibidad!'
    },
    {
        keys: ['health', 'kalusugan', 'medical', 'check-up', 'checkup'],
        reply: '❤️ Ang Health program ay nagbibigay ng libreng medical check-up at health seminars para sa kabataan. Walang aktibong programa ngayon — abangan ang mga updates!'
    },
    {
        keys: ['anti-drug', 'anti drug', 'droga', 'drugs', 'drug'],
        reply: '🚫 Ang Anti-Drugs program ay naglalayong turuan ang kabataan tungkol sa mga panganib ng droga. Walang aktibong programa ngayon — abangan ang mga updates!'
    },
    {
        keys: ['hello', 'hi', 'kumusta', 'hey', 'uy', 'helo', 'magandang'],
        reply: 'Kumusta! 😊 Ako si SK Assistant. Maaari akong tumulong sa impormasyon tungkol sa mga programa, events, at serbisyo ng SK Santa Cruz. Ano ang gusto mong malaman?'
    },
    {
        keys: ['salamat', 'thank', 'thanks', 'maraming salamat'],
        reply: 'Walang anuman! 😊 Kung may iba ka pang katanungan, nandito lang ako. Mabuhay ang kabataan ng Santa Cruz!'
    },
    {
        keys: ['register', 'rehistro', 'sign up', 'signup', 'account'],
        reply: 'Para mag-register sa SK OnePortal, pumunta sa login page at i-click ang "Register". Kailangan mo ng valid email address at personal information.'
    },
    {
        keys: ['profile', 'account', 'impormasyon', 'information', 'edit'],
        reply: 'Maaari mong i-edit ang iyong profile sa pamamagitan ng pag-click sa iyong avatar sa kanang bahagi ng navigation bar, tapos piliin ang "My Profile".'
    },
];

const CP_DEFAULT = 'Pasensya na, hindi ko naintindihan ang iyong tanong. Subukan mong i-click ang isa sa mga quick topics sa itaas, o magtanong tungkol sa mga programa, events, o serbisyo ng SK. 😊';

// ── Helpers ──────────────────────────────────────────────────────────────────
function cpGetTime() {
    const now = new Date();
    return now.toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit' });
}

function cpGetReply(text) {
    const lower = text.toLowerCase();
    for (const item of CP_REPLIES) {
        if (item.keys.some(k => lower.includes(k))) {
            return item.reply;
        }
    }
    return CP_DEFAULT;
}

function cpScrollBottom() {
    const msgs = document.getElementById('cpMessages');
    if (msgs) msgs.scrollTop = msgs.scrollHeight;
}

function cpAppendMessage(text, sender) {
    const msgs = document.getElementById('cpMessages');
    const typing = document.getElementById('cpTyping');

    const row = document.createElement('div');
    row.className = `cp-msg ${sender}`;

    const avatarDiv = document.createElement('div');
    avatarDiv.className = `cp-msg-avatar ${sender}`;

    if (sender === 'bot') {
        avatarDiv.innerHTML = `<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"/></svg>`;
    } else {
        const img = document.createElement('img');
        img.src = document.querySelector('.user-avatar-btn img')?.src || 'https://ui-avatars.com/api/?name=User&background=667eea&color=fff';
        img.alt = 'You';
        avatarDiv.appendChild(img);
    }

    const bodyDiv = document.createElement('div');
    bodyDiv.className = 'cp-msg-body';

    const bubble = document.createElement('div');
    bubble.className = 'cp-bubble';
    bubble.style.whiteSpace = 'pre-line';
    bubble.textContent = text;

    const timeSpan = document.createElement('span');
    timeSpan.className = 'cp-msg-time';
    timeSpan.textContent = cpGetTime();

    bodyDiv.appendChild(bubble);
    bodyDiv.appendChild(timeSpan);
    row.appendChild(avatarDiv);
    row.appendChild(bodyDiv);

    // Insert before typing indicator
    msgs.insertBefore(row, typing);
    cpScrollBottom();
}

// ── Toggle / Close ────────────────────────────────────────────────────────────
// ── Align arrow to button on mobile/tablet ────────────────────────────────────
function cpAlignArrow() {
    const btn = document.getElementById('chatbotNavBtn');
    const popover = document.getElementById('chatbotPopover');
    if (!btn || !popover) return;

    const btnRect = btn.getBoundingClientRect();
    const btnCenter = btnRect.left + btnRect.width / 2;

    // On mobile (fixed positioning): popover right edge = viewport width - 8px (right: 8px)
    // On tablet (absolute, right: 0): popover right edge aligns with wrapper right edge
    // On desktop (absolute positioning): use popover's actual rect
    let popRight;
    if (window.innerWidth <= 480) {
        popRight = window.innerWidth - 8;
    } else {
        // For tablet and desktop, getBoundingClientRect is accurate since popover is visible
        popRight = popover.getBoundingClientRect().right;
    }

    const arrowRight = Math.max(10, Math.round(popRight - btnCenter - 8)); // 8 = half arrow width
    popover.style.setProperty('--cp-arrow-right', arrowRight + 'px');
}

window.toggleChatbotPopover = function toggleChatbotPopover() {
    const popover = document.getElementById('chatbotPopover');
    if (!popover) return;
    const isOpen = popover.classList.contains('open');
    if (isOpen) {
        closeChatbotPopover();
    } else {
        popover.classList.add('open');
        cpAlignArrow();
        document.getElementById('cpInput')?.focus();
        // Close notif popover if open
        document.getElementById('notifPopover')?.classList.remove('open');
        // Remove unread badge
        document.getElementById('chatbotNavBtn')?.classList.remove('has-unread');
    }
}

window.closeChatbotPopover = function closeChatbotPopover() {
    document.getElementById('chatbotPopover')?.classList.remove('open');
}

// ── Send message ──────────────────────────────────────────────────────────────
window.cpHandleSubmit = function cpHandleSubmit(e) {
    e.preventDefault();
    const input = document.getElementById('cpInput');
    const text = input.value.trim();
    if (!text) return;

    cpAppendMessage(text, 'user');
    input.value = '';

    // Show typing indicator
    const typing = document.getElementById('cpTyping');
    if (typing) typing.style.display = 'flex';
    cpScrollBottom();

    // Simulate bot reply delay
    setTimeout(() => {
        if (typing) typing.style.display = 'none';
        cpAppendMessage(cpGetReply(text), 'bot');
    }, 900 + Math.random() * 400);
}

// ── Quick topic button ────────────────────────────────────────────────────────
window.cpSendTopic = function cpSendTopic(topic) {
    const input = document.getElementById('cpInput');
    if (input) input.value = topic;
    cpHandleSubmit(new Event('submit'));
}

// ── Close on outside click ────────────────────────────────────────────────────
document.addEventListener('click', function (e) {
    const popover = document.getElementById('chatbotPopover');
    const btn = document.getElementById('chatbotNavBtn');
    if (!popover || !btn) return;
    if (popover.classList.contains('open') && !popover.contains(e.target) && !btn.contains(e.target)) {
        closeChatbotPopover();
    }
});

// ── Init welcome time ─────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    const el = document.getElementById('cpWelcomeTime');
    if (el) el.textContent = cpGetTime();
});
