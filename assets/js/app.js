// assets/js/app.js - Vehicle Sampark Smart QR Generator & Modal Engine

document.addEventListener('DOMContentLoaded', function () {
    initGeneratorModal();
    initModalClosers();
});

/**
 * Triggers the Logout Confirmation Modal
 */
function openLogoutModal(event) {
    if (event) event.preventDefault();
    const modal = document.getElementById('logoutModal');
    if (modal) {
        modal.classList.add('show');
    } else {
        window.location.href = 'admin-qr-login?action=logout';
    }
}

/**
 * Toggles Password Input Visibility
 */
function togglePasswordVisibility(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (!input || !icon) return;

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

/**
 * Initializes the QR Generator Modal on both Dashboard and Batches pages
 */
function initGeneratorModal() {
    const btnOpen = document.getElementById('btnOpenGenerator');
    const modal = document.getElementById('generatorModal');
    const btnSubmit = document.getElementById('btnGeneratorSubmit') || document.getElementById('btnWizardSubmit');

    if (btnOpen && modal) {
        btnOpen.addEventListener('click', function () {
            modal.classList.add('show');
        });
    }

    if (btnSubmit) {
        btnSubmit.addEventListener('click', function () {
            submitSingleStepGenerator();
        });
    }
}

/**
 * Submits the single-step QR Generator modal directly
 */
function submitSingleStepGenerator() {
    const btnSubmit = document.getElementById('btnGeneratorSubmit') || document.getElementById('btnWizardSubmit');
    const quantityInput = document.getElementById('qrQuantity');
    const titleInput = document.getElementById('formTitle');
    const descInput = document.getElementById('formDescription');

    const quantity = parseInt(quantityInput.value, 10);
    const formTitle = titleInput.value.trim() || 'Vehicle QR Tag Registration';
    const formDescription = descInput ? descInput.value.trim() : '';

    if (isNaN(quantity) || quantity < 1 || quantity > 500) {
        showToast('Please enter a valid quantity between 1 and 500.', 'error');
        return;
    }

    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Generating QR Codes...';

    // Send payload directly to API
    fetch('admin-qr-api?action=create_batch', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            quantity: quantity,
            form_title: formTitle,
            form_description: formDescription
        })
    })
    .then(res => res.json())
    .then(data => {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles"></i> Generate QR Codes';

        if (data.success) {
            showToast(data.message, 'success');
            document.getElementById('generatorModal').classList.remove('show');
            setTimeout(() => {
                window.location.reload();
            }, 800);
        } else {
            showToast(data.message || 'Failed to generate QR batch.', 'error');
        }
    })
    .catch(err => {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles"></i> Generate QR Codes';
        showToast('Network error while generating QR codes.', 'error');
    });
}

/**
 * Modal Close Buttons Handler
 */
function initModalClosers() {
    document.querySelectorAll('.modal-close').forEach(btn => {
        btn.addEventListener('click', function () {
            const modal = this.closest('.modal-backdrop');
            if (modal) modal.classList.remove('show');
        });
    });

    document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
        backdrop.addEventListener('click', function (e) {
            if (e.target === this) {
                this.classList.remove('show');
            }
        });
    });
}

/**
 * Displays QR Code Tag Modal Preview
 */
function viewQRCodeModal(codeNumber) {
    const modal = document.getElementById('qrViewModal');
    const title = document.getElementById('qrCodeTitle');
    const container = document.getElementById('qrImageContainer');
    const input = document.getElementById('qrScanUrlInput');
    const downloadBtn = document.getElementById('qrDownloadBtn');

    if (!modal) return;

    title.textContent = 'Vehicle Tag - ' + codeNumber;
    container.innerHTML = '<div style="padding: 2rem;"><i class="fa-solid fa-spinner fa-spin fa-2x" style="color: var(--primary);"></i></div>';
    modal.classList.add('show');

    fetch('admin-qr-api?action=get_qrcode&code=' + encodeURIComponent(codeNumber))
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                container.innerHTML = data.data.tag_html;
                input.value = data.data.scan_url;
                downloadBtn.href = data.data.download_url;
            } else {
                container.innerHTML = '<div style="color: var(--accent-rose); padding: 1rem;">' + data.message + '</div>';
            }
        })
        .catch(err => {
            container.innerHTML = '<div style="color: var(--accent-rose); padding: 1rem;">Failed to load tag.</div>';
        });
}

/**
 * Displays Owner Submission Details Modal
 */
function viewSubmissionDetails(codeNumber) {
    const modal = document.getElementById('detailsViewModal');
    const title = document.getElementById('detailsModalTitle');
    const body = document.getElementById('detailsModalBody');

    if (!modal) return;

    title.textContent = 'Registered Owner - ' + codeNumber;
    body.innerHTML = '<div style="text-align: center; padding: 2rem;"><i class="fa-solid fa-spinner fa-spin fa-2x" style="color: var(--primary);"></i></div>';
    modal.classList.add('show');

    fetch('admin-qr-api?action=get_submission&code=' + encodeURIComponent(codeNumber))
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                let html = '<div style="margin-bottom: 1rem;"><span class="badge badge-submitted"><i class="fa-solid fa-check"></i> Registered on ' + data.data.submitted_at + '</span></div>';
                html += '<table class="data-table"><thead><tr><th>Field Label</th><th>Submitted Information</th></tr></thead><tbody>';
                
                for (const [key, val] of Object.entries(data.data.responses)) {
                    html += `<tr><td style="font-weight: 600; width: 40%; color: var(--text-main);">${key}</td><td>${val}</td></tr>`;
                }

                html += '</tbody></table>';
                body.innerHTML = html;
            } else {
                body.innerHTML = '<div style="color: var(--accent-rose); padding: 1rem;">' + data.message + '</div>';
            }
        });
}

function copyScanUrl() {
    const input = document.getElementById('qrScanUrlInput');
    if (!input) return;
    input.select();
    document.execCommand('copy');
    showToast('Public scanner URL copied to clipboard!', 'success');
}

/**
 * Toast Notification System
 */
function showToast(msg, type) {
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = 'toast toast-' + (type || 'success');
    toast.innerHTML = `<i class="fa-solid fa-${type === 'error' ? 'circle-xmark' : 'circle-check'}"></i> <span>${msg}</span>`;

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(10px)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Auto-initialize Hero dynamic text and scanner status loop on DOM load
document.addEventListener('DOMContentLoaded', function () {
    initHeroDynamicWord();
    initHeroStatusToast();
});

/**
 * Dynamic Hero Headline Word Switcher
 */
function initHeroDynamicWord() {
    const wordElem = document.getElementById('heroDynamicWord');
    if (!wordElem) return;

    const phrases = [
        "a Quick QR Scan ⚡",
        "1-Click Call Relay 📞",
        "Instant WhatsApp Bot 💬",
        "Zero Phone Leakage 🛡️"
    ];
    let idx = 0;

    setInterval(() => {
        idx = (idx + 1) % phrases.length;
        wordElem.style.opacity = '0';
        wordElem.style.transform = 'translateY(-12px)';

        setTimeout(() => {
            wordElem.textContent = phrases[idx];
            wordElem.style.opacity = '1';
            wordElem.style.transform = 'translateY(0)';
        }, 300);
    }, 2800);
}

/**
 * Dynamic Live Hero Scan Status Toast Switcher
 */
function initHeroStatusToast() {
    const msgElem = document.getElementById('liveHeroMsg');
    const dotElem = document.getElementById('liveHeroDot');
    if (!msgElem || !dotElem) return;

    const states = [
        { msg: "Scanning Vehicle Tag QRC-SAMPARK-01...", color: "#f97316" },
        { msg: "Tag Recognized! Connecting Owner...", color: "#10b981" },
        { msg: "1-Click Call & WhatsApp Bot Active!", color: "#06b6d4" },
        { msg: "Privacy Protected: Mobile # Shielded!", color: "#8b5cf6" }
    ];
    let step = 0;

    setInterval(() => {
        step = (step + 1) % states.length;
        msgElem.style.opacity = '0';
        setTimeout(() => {
            msgElem.textContent = states[step].msg;
            dotElem.style.background = states[step].color;
            msgElem.style.opacity = '1';
        }, 250);
    }, 3200);
}

/**
 * English (default) and Hindi Native i18n Translation Dictionary for Vehicle Sampark
 */
const landingPageI18n = {
    'en': {
        announcement: '🚀 Instant 1-Click WhatsApp Bot & Call Relay &bull; 100% Mobile Number Privacy Protection Guaranteed!',
        nav_how: '<i class="fa-solid fa-list-check"></i> How It Works',
        nav_hazards: '<i class="fa-solid fa-triangle-exclamation"></i> Hazards Solved',
        nav_why: '<i class="fa-solid fa-shield-halved"></i> Why Us',
        nav_faq: '<i class="fa-solid fa-circle-question"></i> FAQ',
        nav_contact: '<i class="fa-solid fa-envelope"></i> Contact Us',
        hero_badge: '<span class="live-dot-pulse"></span> <i class="fa-solid fa-shield-halved" style="color: #10b981;"></i> Protect Your Vehicle & Personal Privacy',
        hero_sub: 'A simple scan lets anyone contact you instantly. Get real-time WhatsApp alerts, 4-option emergency bot reports, and direct calls if your vehicle needs attention — without revealing your personal phone number.',
        hero_btn_tag: '<i class="fa-solid fa-phone-volume"></i> Get Smart QR Tag Now',
        hero_btn_wa: '<i class="fa-brands fa-whatsapp" style="font-size: 1.3rem;"></i> Chat on WhatsApp',
        trust_priv_val: '100% Private',
        trust_priv_lbl: 'No Phone Exposed',
        trust_relay_val: 'Instant Relay',
        trust_relay_lbl: 'Call & WhatsApp Bot',
        trust_badge_val: 'Smart Badge',
        trust_badge_lbl: 'Center Logo Tag Card'
    },
    'hi': {
        announcement: '🚀 त्वरित 1-क्लिक व्हाट्सएप बॉट और कॉल रिले • 100% मोबाइल नंबर गोपनीयता सुरक्षा की गारंटी!',
        nav_how: '<i class="fa-solid fa-list-check"></i> यह कैसे काम करता है',
        nav_hazards: '<i class="fa-solid fa-triangle-exclamation"></i> सुरक्षा समस्याएं',
        nav_why: '<i class="fa-solid fa-shield-halved"></i> हम क्यों',
        nav_faq: '<i class="fa-solid fa-circle-question"></i> अक्सर पूछे जाने वाले प्रश्न',
        nav_contact: '<i class="fa-solid fa-envelope"></i> संपर्क करें',
        hero_badge: '<span class="live-dot-pulse"></span> <i class="fa-solid fa-shield-halved" style="color: #10b981;"></i> अपने वाहन और व्यक्तिगत गोपनीयता की रक्षा करें',
        hero_sub: 'एक साधारण स्कैन किसी को भी आपसे तुरंत संपर्क करने देता है। वास्तविक समय में व्हाट्सएप अलर्ट, 4-विकल्प आपातकालीन बॉट रिपोर्ट और सीधे कॉल प्राप्त करें — अपना फोन नंबर उजागर किए बिना।',
        hero_btn_tag: '<i class="fa-solid fa-phone-volume"></i> स्मार्ट क्यूआर टैग प्राप्त करें',
        hero_btn_wa: '<i class="fa-brands fa-whatsapp" style="font-size: 1.3rem;"></i> व्हाट्सएप पर चैट करें',
        trust_priv_val: '100% गोपनीय',
        trust_priv_lbl: 'मोबाइल नंबर सुरक्षित',
        trust_relay_val: 'त्वरित संपर्क',
        trust_relay_lbl: 'कॉल और व्हाट्सएप बॉट',
        trust_badge_val: 'स्मार्ट बैज',
        trust_badge_lbl: 'सेंटर लोगो टैग कार्ड'
    }
};

/**
 * Multi-Language Selection Engine (English & Hindi)
 */
function onLangSelectChange(selectElem) {
    if (!selectElem) return;
    const langCode = selectElem.value;

    localStorage.setItem('selectedLangCode', langCode);

    // 1. Native DOM Text Swapping (Instant 0ms translation)
    applyNativeTranslation(langCode);

    // 2. Set Google Translate Cookie
    setGoogleTranslateCookie(langCode);

    // 3. Trigger Google Translate Widget
    applyGoogleTranslate(langCode);
}

function applyNativeTranslation(langCode) {
    const data = landingPageI18n[langCode] || landingPageI18n['en'];
    if (!data) return;

    const setHtml = (selector, html) => {
        const elem = document.querySelector(selector);
        if (elem && html) elem.innerHTML = html;
    };

    setHtml('.announcement-bar', data.announcement);
    setHtml('a[href="#how-it-works"]', data.nav_how);
    setHtml('a[href="#hazards"]', data.nav_hazards);
    setHtml('a[href="#why-us"]', data.nav_why);
    setHtml('a[href="#faq"]', data.nav_faq);
    setHtml('a[href="#contact"]', data.nav_contact);
    setHtml('.hero-badge-animated', data.hero_badge);
    setHtml('.hero-subtitle', data.hero_sub);
    setHtml('a[href="#contact"].btn-primary', data.hero_btn_tag);
    setHtml('a[href*="wa.me"].btn-secondary', data.hero_btn_wa);
    setHtml('.color-emerald', data.trust_priv_val);
    setHtml('.trust-item:nth-child(1) .trust-lbl', data.trust_priv_lbl);
    setHtml('.color-orange', data.trust_relay_val);
    setHtml('.trust-item:nth-child(2) .trust-lbl', data.trust_relay_lbl);
    setHtml('.color-slate', data.trust_badge_val);
    setHtml('.trust-item:nth-child(3) .trust-lbl', data.trust_badge_lbl);
}

function setGoogleTranslateCookie(langCode) {
    const cookieVal = '/en/' + langCode;
    const host = window.location.hostname;
    document.cookie = 'googtrans=' + cookieVal + '; path=/;';
    document.cookie = 'googtrans=' + cookieVal + '; path=/; domain=' + host + ';';
    document.cookie = 'googtrans=' + cookieVal + '; path=/; domain=.' + host + ';';
}

function applyGoogleTranslate(langCode) {
    const selectElem = document.querySelector('.goog-te-combo');
    if (selectElem) {
        selectElem.value = langCode;
        selectElem.dispatchEvent(new Event('change'));
    } else {
        setTimeout(() => {
            const retrySelect = document.querySelector('.goog-te-combo');
            if (retrySelect) {
                retrySelect.value = langCode;
                retrySelect.dispatchEvent(new Event('change'));
            }
        }, 300);
    }
}

// Auto-restore saved language preference on load
document.addEventListener('DOMContentLoaded', function () {
    const savedCode = localStorage.getItem('selectedLangCode');
    if (savedCode) {
        const selectElem = document.getElementById('langSelectBox');
        if (selectElem) {
            selectElem.value = savedCode;
        }
        applyNativeTranslation(savedCode);
        setGoogleTranslateCookie(savedCode);
        setTimeout(() => applyGoogleTranslate(savedCode), 500);
    }
});


