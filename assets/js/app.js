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
 * Native Multi-Language i18n Dictionary for Vehicle Sampark (All Indian Languages)
 */
const landingPageI18n = {
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
    },
    'gu': {
        announcement: '🚀 ત્વરિત 1-ક્લિક વોટ્સએપ બોટ અને કોલ રિલે • 100% મોબાઇલ નંબર ગોપનીયતા સુરક્ષાની ખાતરી!',
        nav_how: '<i class="fa-solid fa-list-check"></i> આ કેવી રીતે કામ કરે છે',
        nav_hazards: '<i class="fa-solid fa-triangle-exclamation"></i> ઉકેલાયેલ જોખમો',
        nav_why: '<i class="fa-solid fa-shield-halved"></i> શા માટે આપણે',
        nav_faq: '<i class="fa-solid fa-circle-question"></i> વારંવાર પૂછાતા પ્રશ્નો',
        nav_contact: '<i class="fa-solid fa-envelope"></i> સંપર્ક કરો',
        hero_badge: '<span class="live-dot-pulse"></span> <i class="fa-solid fa-shield-halved" style="color: #10b981;"></i> તમારા વાહન અને અંગત ગોપનીયતાનું રક્ષણ કરો',
        hero_sub: 'એક સરળ સ્કેન કોઈપણ વ્યક્તિને તુરંત જ તમારો સંપર્ક કરવા દે છે. રીયલ-ટાઇમ વોટ્સએપ એલર્ટ, 4-વિકલ્પ ઇમરજન્સી બોટ રિપોર્ટ અને સીધા કોલ મેળવો — તમારો ફોન નંબર જાહેર કર્યા વગર.',
        hero_btn_tag: '<i class="fa-solid fa-phone-volume"></i> સ્માર્ટ QR ટેગ મેળવો',
        hero_btn_wa: '<i class="fa-brands fa-whatsapp" style="font-size: 1.3rem;"></i> વોટ્સએપ પર ચેટ કરો',
        trust_priv_val: '100% ખાનગી',
        trust_priv_lbl: 'મોબાઇલ નંબર સુરક્ષિત',
        trust_relay_val: 'તુરંત સંપર્ક',
        trust_relay_lbl: 'કોલ અને વોટ્સએપ બોટ',
        trust_badge_val: 'સ્માર્ટ બેજ',
        trust_badge_lbl: 'સેન્ટર લોગો ટેગ કાર્ડ'
    },
    'mr': {
        announcement: '🚀 त्वरित 1-क्लिक व्हॉट्सॲप बॉट आणि कॉल रिले • 100% मोबाईल नंबर गोपनीयतेची शाश्वती!',
        nav_how: '<i class="fa-solid fa-list-check"></i> हे कसे कार्य करते',
        nav_hazards: '<i class="fa-solid fa-triangle-exclamation"></i> सोडवलेले धोके',
        nav_why: '<i class="fa-solid fa-shield-halved"></i> आम्हीच का',
        nav_faq: '<i class="fa-solid fa-circle-question"></i> विचारले जाणारे प्रश्न',
        nav_contact: '<i class="fa-solid fa-envelope"></i> संपर्क साधा',
        hero_badge: '<span class="live-dot-pulse"></span> <i class="fa-solid fa-shield-halved" style="color: #10b981;"></i> तुमचे वाहन आणि वैयक्तिक गोपनीयतेचे रक्षण करा',
        hero_sub: 'एक सोपा स्कॅन कोणालाही तुमच्याशी त्वरित संपर्क साधू देतो. तुमचा वैयक्तिक फोन नंबर न दाखवता रीअल-टाइम व्हॉट्सॲप इशारे, 4-पर्यायी आपत्कालीन बॉट अहवाल आणि थेट कॉल मिळवा.',
        hero_btn_tag: '<i class="fa-solid fa-phone-volume"></i> स्मार्ट क्यूआर टॅग मिळवा',
        hero_btn_wa: '<i class="fa-brands fa-whatsapp" style="font-size: 1.3rem;"></i> व्हॉट्सॲपवर चॅट करा',
        trust_priv_val: '100% खाजगी',
        trust_priv_lbl: 'फोन नंबर सुरक्षित',
        trust_relay_val: 'त्वरित संपर्क',
        trust_relay_lbl: 'कॉल आणि व्हॉट्सॲप बॉट',
        trust_badge_val: 'स्मार्ट बॅज',
        trust_badge_lbl: 'सेंटर लोगो टॅग कार्ड'
    },
    'bn': {
        announcement: '🚀 তাত্ক্ষণিক ১-ক্লিক হোয়াটসঅ্যাপ বট এবং কল রিলে • ১০০% মোবাইল নম্বর গোপনীয়তা গ্যারান্টিযুক্ত!',
        nav_how: '<i class="fa-solid fa-list-check"></i> এটি কীভাবে কাজ করে',
        nav_hazards: '<i class="fa-solid fa-triangle-exclamation"></i> সমস্যা সমাধান',
        nav_why: '<i class="fa-solid fa-shield-halved"></i> কেন আমরা',
        nav_faq: '<i class="fa-solid fa-circle-question"></i> সাধারণ প্রশ্নাবলী',
        nav_contact: '<i class="fa-solid fa-envelope"></i> যোগাযোগ করুন',
        hero_badge: '<span class="live-dot-pulse"></span> <i class="fa-solid fa-shield-halved" style="color: #10b981;"></i> আপনার যানবাহন এবং ব্যক্তিগত গোপনীয়তা রক্ষা করুন',
        hero_sub: 'একটি সাধারণ স্ক্যান যে কাউকে তাৎক্ষণিকভাবে আপনার সাথে যোগাযোগ করতে দেয়। আপনার ব্যক্তিগত নম্বর প্রকাশ না করে রিয়েল-টাইম হোয়াটসঅ্যাপ সতর্কবার্তা এবং কল পান।',
        hero_btn_tag: '<i class="fa-solid fa-phone-volume"></i> স্মার্ট কিউআর ট্যাগ পান',
        hero_btn_wa: '<i class="fa-brands fa-whatsapp" style="font-size: 1.3rem;"></i> হোয়াটসঅ্যাপে চ্যাট করুন',
        trust_priv_val: '১০০% গোপনীয়',
        trust_priv_lbl: 'নম্বর সুরক্ষিত',
        trust_relay_val: 'তাৎক্ষণিক সংযোগ',
        trust_relay_lbl: 'কল ও হোয়াটসঅ্যাপ বট',
        trust_badge_val: 'স্মার্ট ব্যাজ',
        trust_badge_lbl: 'সেন্টার লোগো ট্যাগ কার্ড'
    },
    'ta': {
        announcement: '🚀 உடனடி 1-கிளிக் வாட்ஸ்அப் போட் & அழைப்பு தொடர்பு • 100% மொபைல் எண் தனியுரிமை பாதுகாப்பு!',
        nav_how: '<i class="fa-solid fa-list-check"></i> இது எப்படி செயல்படுகிறது',
        nav_hazards: '<i class="fa-solid fa-triangle-exclamation"></i> தீர்வு பெற்ற ஆபத்துகள்',
        nav_why: '<i class="fa-solid fa-shield-halved"></i> ஏன் நாங்கள்',
        nav_faq: '<i class="fa-solid fa-circle-question"></i> கேள்விகள்',
        nav_contact: '<i class="fa-solid fa-envelope"></i> தொடர்பு கொள்ளவும்',
        hero_badge: '<span class="live-dot-pulse"></span> <i class="fa-solid fa-shield-halved" style="color: #10b981;"></i> உங்கள் வாகனம் மற்றும் தனிப்பட்ட தனியுரிமையைப் பாதுகாக்கவும்',
        hero_sub: 'ஒரு எளிமையான ஸ்கேன் மூலம் யாராவது உங்களை உடனடியாகத் தொடர்புகொள்ளலாம். உங்கள் தனிப்பட்ட மொபைல் எண்ணை வெளிப்படுத்தாமல் உடனடி வாட்ஸ்அப் எச்சரிக்கைகள் மற்றும் நேரடி அழைப்புகளைப் பெறுங்கள்.',
        hero_btn_tag: '<i class="fa-solid fa-phone-volume"></i> ஸ்மார்ட் QR டேக் பெறவும்',
        hero_btn_wa: '<i class="fa-brands fa-whatsapp" style="font-size: 1.3rem;"></i> வாட்ஸ்அப்பில் சாட் செய்யவும்',
        trust_priv_val: '100% தனியுரிமை',
        trust_priv_lbl: 'எண் பாதுகாப்பானது',
        trust_relay_val: 'உடனடி தொடர்பு',
        trust_relay_lbl: 'அழைப்பு & வாட்ஸ்அப் போட்',
        trust_badge_val: 'ஸ்மார்ட் பேட்ஜ்',
        trust_badge_lbl: 'சென்டர் லୋகோ டேக் கார்டு'
    },
    'te': {
        announcement: '🚀 తక్షణ 1-క్లిక్ వాట్సాప్ బాట్ & కాల్ రిలే • 100% మొబైల్ నంబర్ గోప్యతా రక్షణ నిశ్చయం!',
        nav_how: '<i class="fa-solid fa-list-check"></i> ఇది ఎలా పనిచేస్తుంది',
        nav_hazards: '<i class="fa-solid fa-triangle-exclamation"></i> పరిష్కరించబడిన సమస్యలు',
        nav_why: '<i class="fa-solid fa-shield-halved"></i> ఎందుకు మేము',
        nav_faq: '<i class="fa-solid fa-circle-question"></i> ప్రశ్నలు',
        nav_contact: '<i class="fa-solid fa-envelope"></i> సంప్రదించండి',
        hero_badge: '<span class="live-dot-pulse"></span> <i class="fa-solid fa-shield-halved" style="color: #10b981;"></i> మీ వాహనం మరియు వ్యక్తిగత గోప్యతను రక్షించండి',
        hero_sub: 'ఒక సాధారణ స్కాన్ ఎవరైనా తక్షణమే మిమ్మల్ని సంప్రదించడానికి అనుమతిస్తుంది. మీ వ్యక్తిగత ఫోన్ నంబర్‌ను బయటపెట్టకుండా రియల్ టైమ్ వాట్సాప్ హెచ్చరికలు మరియు ప్రత్యక్ష కాల్‌లను పొందండి.',
        hero_btn_tag: '<i class="fa-solid fa-phone-volume"></i> స్మార్ట్ QR ట్యాగ్ పొందండి',
        hero_btn_wa: '<i class="fa-brands fa-whatsapp" style="font-size: 1.3rem;"></i> వాట్సాప్‌లో చాట్ చేయండి',
        trust_priv_val: '100% వ్యక్తిగతం',
        trust_priv_lbl: 'ఫోన్ నంబర్ సురక్షితం',
        trust_relay_val: 'తక్షణ కనెక్ట్',
        trust_relay_lbl: 'కాల్ & వాట్సాప్ బాట్',
        trust_badge_val: 'స్మార్ట్ బ్యాడ్జ్',
        trust_badge_lbl: 'సెంటర్ లోగో ట్యాగ్ కార్డ్'
    },
    'kn': {
        announcement: '🚀 ತತ್ಕ್ಷಣದ 1-ಕ್ಲಿಕ್ ವಾಟ್ಸಾಪ್ ಬೋಟ್ ಮತ್ತು ಕರೆ ಸಂಪರ್ಕ • 100% ಮೊಬೈಲ್ ಸಂಖ್ಯೆ ಗೌಪ್ಯತೆ ರಕ್ಷಣೆ!',
        nav_how: '<i class="fa-solid fa-list-check"></i> ಇದು ಹೇಗೆ ಕಾರ್ಯನಿರ್ವಹಿಸುತ್ತದೆ',
        nav_hazards: '<i class="fa-solid fa-triangle-exclamation"></i> ಪರಿಹರಿಸಲಾದ ಸಮಸ್ಯೆಗಳು',
        nav_why: '<i class="fa-solid fa-shield-halved"></i> ಏಕೆ ನಾವು',
        nav_faq: '<i class="fa-solid fa-circle-question"></i> ಪ್ರಶ್ನೆಗಳು',
        nav_contact: '<i class="fa-solid fa-envelope"></i> ಸಂಪರ್ಕಿಸಿ',
        hero_badge: '<span class="live-dot-pulse"></span> <i class="fa-solid fa-shield-halved" style="color: #10b981;"></i> ನಿಮ್ಮ ವಾಹನ ಮತ್ತು ವೈಯಕ್ತಿಕ ಗೌಪ್ಯತೆಯನ್ನು ರಕ್ಷಿಸಿ',
        hero_sub: 'ಸರಳವಾದ ಸ್ಕ್ಯಾನ್ ಯಾರಾದರೂ ನಿಮ್ಮನ್ನು ತಕ್ಷಣವೇ ಸಂಪರ್ಕಿಸಲು ಅನುವು ಮಾಡಿಕೊಡುತ್ತದೆ. ನಿಮ್ಮ ವೈಯಕ್ತಿಕ ಫೋನ್ ಸಂಖ್ಯೆಯನ್ನು ಬಹಿರಂಗಪಡಿಸದೆ ನೈಜ-ಸಮಯದ ವಾಟ್ಸಾಪ್ ಎಚ್ಚರಿಕೆಗಳನ್ನು ಪಡೆಯಿರಿ.',
        hero_btn_tag: '<i class="fa-solid fa-phone-volume"></i> ಸ್ಮಾರ್ಟ್ QR ಟ್ಯಾಗ್ ಪಡೆಯಿರಿ',
        hero_btn_wa: '<i class="fa-brands fa-whatsapp" style="font-size: 1.3rem;"></i> ವಾಟ್ಸಾಪ್‌ನಲ್ಲಿ ಚಾಟ್ ಮಾಡಿ',
        trust_priv_val: '100% ಖಾಸಗಿ',
        trust_priv_lbl: 'ಸಂಖ್ಯೆ ಸುರಕ್ಷಿತವಾಗಿದೆ',
        trust_relay_val: 'ತತ್ಕ್ಷಣ ಸಂಪರ್ಕ',
        trust_relay_lbl: 'ಕರೆ & ವಾಟ್ಸಾಪ್ ಬೋಟ್',
        trust_badge_val: 'ಸ್ಮಾರ್ಟ್ ಬ್ಯಾಡ್ಜ್',
        trust_badge_lbl: 'ಸೆಂಟರ್ ಲೋಗೋ ಟ್ಯಾಗ್ ಕಾರ್ಡ್'
    },
    'ml': {
        announcement: '🚀 തൽക്ഷണ 1-ക്ലിക്ക് വാട്ട്‌സ്ആപ്പ് ബോട്ടും കോൾ റിലേയും • 100% മൊബൈൽ നമ്പർ സ്വകാര്യതാ സംരക്ഷണം!',
        nav_how: '<i class="fa-solid fa-list-check"></i> ഇത് എങ്ങനെ പ്രവർത്തിക്കുന്നു',
        nav_hazards: '<i class="fa-solid fa-triangle-exclamation"></i> പരിഹരിച്ച പ്രശ്നങ്ങൾ',
        nav_why: '<i class="fa-solid fa-shield-halved"></i> എന്തുകൊണ്ട് ഞങ്ങൾ',
        nav_faq: '<i class="fa-solid fa-circle-question"></i> ചോദ്യങ്ങൾ',
        nav_contact: '<i class="fa-solid fa-envelope"></i> ബന്ധപ്പെടുക',
        hero_badge: '<span class="live-dot-pulse"></span> <i class="fa-solid fa-shield-halved" style="color: #10b981;"></i> നിങ്ങളുടെ വാഹനവും വ്യക്തിഗത സ്വകാര്യതയും സംരക്ഷിക്കുക',
        hero_sub: 'ഒരു ലളിതമായ സ്കാൻ ഉപയോഗിച്ച് ആർക്കും ഉടനടി നിങ്ങളെ ബന്ധപ്പെടാം. നിങ്ങളുടെ വ്യക്തിഗത ഫോൺ നമ്പർ വെളിപ്പെടുത്താതെ തത്സമയ വാട്ട്‌സ്ആപ്പ് അലേർട്ടുകളും കോളുകളും നേടുക.',
        hero_btn_tag: '<i class="fa-solid fa-phone-volume"></i> സ്മാർട്ട് QR ടാഗ് നേടുക',
        hero_btn_wa: '<i class="fa-brands fa-whatsapp" style="font-size: 1.3rem;"></i> വാട്ട്‌സ്ആപ്പിൽ ചാറ്റ് ചെയ്യുക',
        trust_priv_val: '100% സ്വകാര്യം',
        trust_priv_lbl: 'ഫോൺ നമ്പർ സുരക്ഷിതം',
        trust_relay_val: 'തൽക്ഷണ കണക്റ്റ്',
        trust_relay_lbl: 'കോളും വാട്ട്‌സ്ആപ്പ് ബോട്ടും',
        trust_badge_val: 'സ്മാർട്ട് ബാഡ്ജ്',
        trust_badge_lbl: 'സെന്റർ ലോഗോ കാർഡ്'
    },
    'pa': {
        announcement: '🚀 ਤੁਰੰਤ 1-ਕਲਿੱਕ ਵਟਸਐਪ ਬੋਟ ਅਤੇ ਕਾਲ ਰਿਲੇਅ • 100% ਮੋਬਾਈਲ ਨੰਬਰ ਗੋਪਨੀਯਤਾ ਸੁਰੱਖਿਆ ਦੀ ਗਾਰੰਟੀ!',
        nav_how: '<i class="fa-solid fa-list-check"></i> ਇਹ ਕਿਵੇਂ ਕੰਮ ਕਰਦਾ ਹੈ',
        nav_hazards: '<i class="fa-solid fa-triangle-exclamation"></i> ਹੱਲ ਕੀਤੀਆਂ ਸਮੱਸਿਆਵਾਂ',
        nav_why: '<i class="fa-solid fa-shield-halved"></i> ਅਸੀਂ ਕਿਉਂ',
        nav_faq: '<i class="fa-solid fa-circle-question"></i> ਸਵਾਲ ਜਵਾਬ',
        nav_contact: '<i class="fa-solid fa-envelope"></i> ਸੰਪਰਕ ਕਰੋ',
        hero_badge: '<span class="live-dot-pulse"></span> <i class="fa-solid fa-shield-halved" style="color: #10b981;"></i> ਆਪਣੇ ਵਾਹਨ ਅਤੇ ਨਿੱਜੀ ਗੋਪਨੀਯਤਾ ਦੀ ਰੱਖਿਆ ਕਰੋ',
        hero_sub: 'ਇੱਕ ਸਾਧਾਰਨ ਸਕੈਨ ਨਾਲ ਕੋਈ ਵੀ ਤੁਰੰਤ ਤੁਹਾਡੇ ਨਾਲ ਸੰਪਰਕ ਕਰ ਸਕਦਾ ਹੈ। ਆਪਣਾ ਨਿੱਜੀ ਫੋਨ ਨੰਬਰ ਜ਼ਾਹਰ ਕੀਤੇ ਬਿਨਾਂ ਰੀਅਲ-ਟਾਈਮ ਵਟਸਐਪ ਅਲਰਟ ਅਤੇ ਸਿੱਧੀਆਂ ਕਾਲਾਂ ਪ੍ਰਾਪਤ ਕਰੋ।',
        hero_btn_tag: '<i class="fa-solid fa-phone-volume"></i> ਸਮਾਰਟ QR ਟੈਗ ਪ੍ਰਾਪਤ ਕਰੋ',
        hero_btn_wa: '<i class="fa-brands fa-whatsapp" style="font-size: 1.3rem;"></i> ਵਟਸਐਪ ਤੇ ਚੈਟ ਕਰੋ',
        trust_priv_val: '100% ਨਿੱਜੀ',
        trust_priv_lbl: 'ਨੰਬਰ ਸੁਰੱਖਿਅਤ ਹੈ',
        trust_relay_val: 'ਤੁਰੰਤ ਜੁੜੋ',
        trust_relay_lbl: 'ਕਾਲ ਅਤੇ ਵਟਸਐਪ ਬੋਟ',
        trust_badge_val: 'ਸਮਾਰਟ ਬੈਜ',
        trust_badge_lbl: 'ਸੈਂਟਰ ਲੋਗੋ ਟੈਗ ਕਾਰਡ'
    }
};

/**
 * Multi-Language Selection Engine (All Indian Languages)
 */
function onLangSelectChange(selectElem) {
    if (!selectElem) return;
    const langCode = selectElem.value;

    localStorage.setItem('selectedLangCode', langCode);

    // 1. Native DOM Text Swapping
    applyNativeTranslation(langCode);

    // 2. Set Google Translate Cookie (googtrans=/en/code)
    setGoogleTranslateCookie(langCode);

    // 3. Trigger Google Translate Widget
    applyGoogleTranslate(langCode);
}

function applyNativeTranslation(langCode) {
    const data = landingPageI18n[langCode];
    if (!data) return;

    // Helper function for quick DOM swapping
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


