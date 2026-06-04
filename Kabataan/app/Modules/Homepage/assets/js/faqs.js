document.addEventListener('DOMContentLoaded', () => {
    const faqRoot = document.getElementById('faq');
    const faqList = document.getElementById('faqList');
    const faqSearch = document.getElementById('faqSearch');

    if (!faqRoot || !faqList) {
        return;
    }

    const fallbackFaqs = [
        {
            question: 'Who can register on SK OnePortal Kabataan?',
            answer: 'Katipunan ng Kabataan members aged 15 to 30 who live in Santa Cruz, Laguna may register using a valid email address and mobile number.',
        },
        {
            question: 'What programs can I find on the portal?',
            answer: 'You can discover scholarships, sports activities, health programs, livelihood trainings, and other barangay SK initiatives posted by officials.',
        },
        {
            question: 'How do I apply for a program?',
            answer: 'Sign in, open the program details, and complete the application form. Requirements and deadlines are listed on each program page.',
        },
        {
            question: 'Can I join programs outside my barangay?',
            answer: 'Some programs accept youth from other barangays. Check the eligibility notes on each listing before you apply.',
        },
        {
            question: 'Is my personal information protected?',
            answer: 'Yes. The platform uses secure sign-in, role-based access, and privacy controls so only authorized SK officials can view application data.',
        },
    ];

    let sourceFaqs = [];

    try {
        sourceFaqs = JSON.parse(faqRoot.dataset.faqs || '[]');
    } catch {
        sourceFaqs = [];
    }

    const faqsToRender = Array.isArray(sourceFaqs) && sourceFaqs.length
        ? sourceFaqs.map((item) => ({
            question: item.question || '',
            answer: item.answer || '',
        }))
        : fallbackFaqs;

    const renderFaqs = (query) => {
        const normalizedQuery = (query || '').trim().toLowerCase();
        const filteredFaqs = faqsToRender.filter((item) => {
            const question = (item.question || '').toLowerCase();
            const answer = (item.answer || '').toLowerCase();
            return !normalizedQuery || question.includes(normalizedQuery) || answer.includes(normalizedQuery);
        });

        faqList.innerHTML = '';

        if (!filteredFaqs.length) {
            const empty = document.createElement('p');
            empty.className = 'faq-empty text-center text-muted py-4 mb-0';
            empty.textContent = 'No matching questions. Try keywords like registration, programs, or privacy.';
            faqList.appendChild(empty);
            return;
        }

        filteredFaqs.forEach((item, index) => {
            const article = document.createElement('article');
            article.className = 'faq-item';

            const answerId = `faq-answer-${index}`;
            const toggle = document.createElement('button');
            toggle.type = 'button';
            toggle.className = 'faq-toggle';
            toggle.setAttribute('aria-expanded', 'false');
            toggle.setAttribute('aria-controls', answerId);

            const questionSpan = document.createElement('span');
            questionSpan.textContent = item.question;
            toggle.appendChild(questionSpan);

            const answer = document.createElement('div');
            answer.className = 'faq-answer';
            answer.id = answerId;
            answer.hidden = true;

            const answerContent = document.createElement('div');
            answerContent.className = 'faq-answer-content';
            answerContent.textContent = item.answer;
            answer.appendChild(answerContent);

            toggle.addEventListener('click', () => {
                const expanded = toggle.getAttribute('aria-expanded') === 'true';
                toggle.setAttribute('aria-expanded', expanded ? 'false' : 'true');
                answer.hidden = expanded;
            });

            article.appendChild(toggle);
            article.appendChild(answer);
            faqList.appendChild(article);
        });
    };

    renderFaqs('');
    faqSearch?.addEventListener('input', () => renderFaqs(faqSearch.value));
});
