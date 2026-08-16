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
        hero_title: 'Let Anyone Reach You With <br class="mobile-hide"><span id="heroDynamicWord" class="gradient-text hero-dynamic-word">a Quick QR Scan ⚡</span>',
        hero_sub: 'A simple scan lets anyone contact you instantly. Get real-time WhatsApp alerts, 4-option emergency bot reports, and direct calls if your vehicle needs attention — without revealing your personal phone number.',
        hero_btn_tag: '<i class="fa-solid fa-phone-volume"></i> Get Smart QR Tag Now',
        hero_btn_wa: '<i class="fa-brands fa-whatsapp" style="font-size: 1.3rem;"></i> Chat on WhatsApp',
        trust_priv_val: '100% Private',
        trust_priv_lbl: 'No Phone Exposed',
        trust_relay_val: 'Instant Relay',
        trust_relay_lbl: 'Call & WhatsApp Bot',
        trust_badge_val: 'Smart Badge',
        trust_badge_lbl: 'Center Logo Tag Card',
        float_left: '<i class="fa-solid fa-lock" style="color: #10b981;"></i> 100% Phone Privacy',
        float_right: '<i class="fa-solid fa-bolt" style="color: #f97316;"></i> 3-Sec Connect',

        how_tag: 'Scan To Call & WhatsApp',
        how_title: 'How Vehicle Sampark Protects You in 3 Easy Steps',
        step1_title: '1. Scan the QR Code',
        step1_desc: 'Anyone can scan the Vehicle Sampark sticker on your windshield using any smartphone camera — zero app download required.',
        step2_title: '2. Connect Instantly',
        step2_desc: 'Call the car owner directly or select WhatsApp emergency bot options without exposing personal phone numbers.',
        step3_title: '3. Resolve the Issue',
        step3_desc: 'Coordinate moving the car, wrong parking, towing, or emergency situations quickly, safely, and peacefully.',

        story_tag: 'Zero Personal Number Shared',
        story_title: 'How Vehicle Sampark Connects You Safely',
        story_sub: 'Bystanders connect with vehicle owners through our official company WhatsApp gateway — keeping your private phone number 100% hidden!',
        s1_title: 'Vehicle Wrongly Parked',
        s1_desc: 'A car blocks a driveway, gate, or another vehicle in a crowded parking area. The driver is away.',
        s2_title: 'Bystander Scans QR Tag',
        s2_desc: 'The bystander scans the Vehicle Sampark sticker on the car windshield using any phone camera.',
        s3_title: 'Company WhatsApp Masking',
        s3_desc: 'Connected via Vehicle Sampark\'s official WhatsApp Bot line. No personal phone number is ever exposed to the bystander.',
        s4_title: 'Anonymous Alert & Solved!',
        s4_desc: 'The owner receives an urgent WhatsApp alert, moves their vehicle peacefully, and both parties stay 100% private & safe!',

        hazards_tag: 'Real-World Protection',
        hazards_title: 'Common Vehicle Emergencies & Services Solved',
        h1_title: 'Prevent Scratching & Vandalism',
        h1_desc: 'Blocked someone in a tight parking spot? They can WhatsApp or call you instantly instead of scratching your car.',
        h2_title: 'Avoid Towing & Expensive Fines',
        h2_desc: 'Traffic police or neighbors can scan your sticker to warn you to move your car before calling a tow truck.',
        h3_title: 'Doorstep Cleaning Service',
        h3_desc: 'Request professional doorstep car washing, eco waterless cleaning, interior detailing right at your parking spot.',
        h4_title: 'Garage & Mechanic Solution',
        h4_desc: 'Instant access to nearby verified garages, roadside mechanics, flat tyre puncture repair, and battery jumpstart.',
        h5_title: 'Battery Drain & Headlights Left ON',
        h5_desc: 'Left your headlights or cabin lights ON? Good Samaritans can warn you before your battery completely dies.',
        h6_title: 'Windows Left Open & Theft Prevention',
        h6_desc: 'Left your window down before rain or theft? Neighbors can scan your tag to alert you before rain enters.',
        h7_title: 'Critical Emergencies & Accidents',
        h7_desc: 'In urgent crash situations, bystanders scan the tag to instantly alert your family with emergency reports.',
        h8_title: 'Fluid Leaks & Animal Safety',
        h8_desc: 'Oil leaks, smoke, or a stray animal trapped under your car engine can be reported immediately by bystanders.',

        why_tag: 'Privacy Protection Comparison',
        why_title: 'Vehicle Sampark vs Traditional Paper Stickers',
        old_title: 'Paper Mobile Number Sticker',
        old_f1: 'Personal phone number exposed to every stranger & spammer.',
        old_f2: 'No automated WhatsApp bot alerts or emergency classifications.',
        old_f3: 'Cannot update phone number without buying a new sticker.',
        old_f4: 'Paper fades, tears, or peels off easily in rain.',
        new_title: 'Smart QR Privacy Tag',
        new_f1: '100% Phone Number Privacy — Zero stranger exposure.',
        new_f2: '4-Option Emergency WhatsApp Bot Relay & Direct Call line.',
        new_f3: 'Instant online phone number update anytime via website.',
        new_f4: 'Durable, UV-resistant tag card pasted inside windshield.',

        faq_tag: 'Got Questions?',
        faq_title: 'Frequently Asked Questions',
        faq1_q: 'How does Vehicle Sampark protect my personal phone number?',
        faq1_a: 'When someone scans your Vehicle Sampark tag, they see direct "Call Owner" or "Chat on WhatsApp" buttons. The call and WhatsApp messages are routed through our secure platform, so your real phone number and identity are never exposed on the physical sticker.',
        faq2_q: 'Does a person scanning my QR sticker need to download an app?',
        faq2_a: 'No! Anyone with a regular smartphone camera, Google Lens, or default QR scanner can scan your tag and connect instantly. Zero app downloads required.',
        faq3_q: 'How do Doorstep Cleaning and Garage Solutions work?',
        faq3_a: 'Vehicle Sampark links your vehicle tag with verified doorstep car cleaning partners and nearby emergency garage/breakdown mechanic services, giving you instant access to maintenance and roadside support.',
        faq4_q: 'Can I change my registered phone number or vehicle details later?',
        faq4_a: 'Yes! You can update your mobile number, WhatsApp number, or car details anytime without replacing your physical tag sticker.',
        faq5_q: 'How does the 4-Option Emergency WhatsApp Bot work?',
        faq5_a: 'When a bystander clicks "Chat on WhatsApp", our bot asks them to confirm the vehicle number and select from 4 emergency issues (Wrong Parked, Accident, Lights On/Window Open, Towing Notice). Once selected, an urgent WhatsApp alert is relayed directly to you!',

        contact_tag: 'Get In Touch',
        contact_title: 'Order Smart QR Tags & Inquiries',
        contact_sub: 'No online payment required. Send us an inquiry or contact us directly via Call or WhatsApp!',
        channels_title: '<span style="width: 42px; height: 42px; border-radius: 12px; background: rgba(16, 185, 129, 0.12); color: #10b981; display: inline-flex; align-items: center; justify-content: center; font-size: 1.25rem;"><i class="fa-solid fa-headset"></i></span> Direct Contact Channels',
        call_title: '<i class="fa-solid fa-bolt" style="color: #fef08a;"></i> Instant Call Line',
        wa_title: '<i class="fa-solid fa-comments" style="color: #ffedd5;"></i> 1-Click WhatsApp Bot',
        wa_btn: 'Chat on WhatsApp',
        email_title: 'Email Support Line',
        form_title: '<span style="width: 42px; height: 42px; border-radius: 12px; background: rgba(249, 115, 22, 0.12); color: #f97316; display: inline-flex; align-items: center; justify-content: center; font-size: 1.25rem;"><i class="fa-solid fa-paper-plane"></i></span> Send Us an Inquiry',
        form_name: 'Full Name <span class="required" style="color: #f43f5e;">*</span>',
        form_phone: 'Mobile / WhatsApp Number <span class="required" style="color: #f43f5e;">*</span>',
        form_vehicle: 'Vehicle Registration Number <span style="font-weight: 500; color: #94a3b8; font-size: 0.78rem;">(Optional)</span>',
        form_city: 'City & State <span class="required" style="color: #f43f5e;">*</span>',
        form_qty: 'Tag Quantity Needed',
        form_msg: 'Message / Additional Instructions',
        form_btn: '<i class="fa-solid fa-paper-plane"></i> Submit Inquiry Now'
    },
    'hi': {
        announcement: '🚀 त्वरित 1-क्लिक व्हाट्सएप बॉट और कॉल रिले • 100% मोबाइल नंबर गोपनीयता सुरक्षा की गारंटी!',
        nav_how: '<i class="fa-solid fa-list-check"></i> यह कैसे काम करता है',
        nav_hazards: '<i class="fa-solid fa-triangle-exclamation"></i> सुरक्षा समस्याएं',
        nav_why: '<i class="fa-solid fa-shield-halved"></i> हम क्यों',
        nav_faq: '<i class="fa-solid fa-circle-question"></i> अक्सर पूछे जाने वाले प्रश्न',
        nav_contact: '<i class="fa-solid fa-envelope"></i> संपर्क करें',
        hero_badge: '<span class="live-dot-pulse"></span> <i class="fa-solid fa-shield-halved" style="color: #10b981;"></i> अपने वाहन और व्यक्तिगत गोपनीयता की रक्षा करें',
        hero_title: 'स्मार्ट QR स्कैन से <br class="mobile-hide"><span id="heroDynamicWord" class="gradient-text hero-dynamic-word">कोई भी आपसे तुरंत संपर्क कर सकता है ⚡</span>',
        hero_sub: 'एक साधारण स्कैन किसी को भी आपसे तुरंत संपर्क करने देता है। वास्तविक समय में व्हाट्सएप अलर्ट, 4-विकल्प आपातकालीन बॉट रिपोर्ट और सीधे कॉल प्राप्त करें — अपना फोन नंबर उजागर किए बिना।',
        hero_btn_tag: '<i class="fa-solid fa-phone-volume"></i> स्मार्ट क्यूआर टैग प्राप्त करें',
        hero_btn_wa: '<i class="fa-brands fa-whatsapp" style="font-size: 1.3rem;"></i> व्हाट्सएप पर चैट करें',
        trust_priv_val: '100% गोपनीय',
        trust_priv_lbl: 'मोबाइल नंबर सुरक्षित',
        trust_relay_val: 'त्वरित संपर्क',
        trust_relay_lbl: 'कॉल और व्हाट्सएप बॉट',
        trust_badge_val: 'स्मार्ट बैज',
        trust_badge_lbl: 'सेंटर लोगो टैग कार्ड',
        float_left: '<i class="fa-solid fa-lock" style="color: #10b981;"></i> 100% फ़ोन गोपनीयता',
        float_right: '<i class="fa-solid fa-bolt" style="color: #f97316;"></i> 3-सेकंड कनेक्ट',

        how_tag: 'कॉल और व्हाट्सएप के लिए स्कैन करें',
        how_title: 'वाहन संपर्क 3 आसान चरणों में आपकी रक्षा करता है',
        step1_title: '1. QR कोड स्कैन करें',
        step1_desc: 'कोई भी व्यक्ति किसी भी स्मार्टफोन कैमरे से आपकी कार पर लगे क्यूआर स्टिकर को स्कैन कर सकता है — कोई ऐप डाउनलोड करने की आवश्यकता नहीं है।',
        step2_title: '2. तुरंत संपर्क करें',
        step2_desc: 'अपना फोन नंबर दिखाए बिना वाहन मालिक को सीधे कॉल करें या व्हाट्सएप आपातकालीन बॉट विकल्प चुनें।',
        step3_title: '3. समस्या का समाधान करें',
        step3_desc: 'गलत पार्किंग, कार हटाने, टोइंग या आपातकालीन स्थितियों में जल्दी और सुरक्षित रूप से संपर्क करें।',

        story_tag: 'जीरो पर्सनल नंबर शेयरिंग',
        story_title: 'वाहन संपर्क आपको सुरक्षित रूप से कैसे जोड़ता है',
        story_sub: 'राहगीर हमारे आधिकारिक व्हाट्सएप गेटवे के माध्यम से वाहन मालिकों से जुड़ते हैं — आपका व्यक्तिगत नंबर 100% छिपा रहता है!',
        s1_title: 'वाहन गलत तरीके से पार्क किया गया',
        s1_desc: 'भीड़भाड़ वाले पार्किंग क्षेत्र में एक कार रास्ते, गेट या किसी अन्य वाहन को अवरुद्ध करती है। ड्राइवर दूर है।',
        s2_title: 'राहगीर QR टैग स्कैन करता है',
        s2_desc: 'राहगीर किसी भी फोन कैमरे का उपयोग करके कार की विंडशील्ड पर लगे वाहन संपर्क स्टिकर को स्कैन करता है।',
        s3_title: 'कंपनी व्हाट्सएप मास्किंग गेटवे',
        s3_desc: 'वाहन संपर्क के आधिकारिक व्हाट्सएप बॉट लाइन के माध्यम से जुड़ा। राहगीर को कभी भी व्यक्तिगत फोन नंबर नहीं दिखता।',
        s4_title: 'अनाम अलर्ट और समाधान!',
        s4_desc: 'मालिक को एक जरूरी व्हाट्सएप अलर्ट मिलता है, वे अपने वाहन को शांति से हटाते हैं, और दोनों पक्ष 100% निजी और सुरक्षित रहते हैं!',

        hazards_tag: 'वास्तविक दुनिया सुरक्षा',
        hazards_title: 'वाहन संपर्क द्वारा हल की गई गंभीर समस्याएं और सेवाएं',
        h1_title: 'स्क्रैच और तोड़फोड़ से बचाएं',
        h1_desc: 'तंग पार्किंग में कार खड़ी की है? आपकी कार स्क्रैच करने के बजाय राहगीर आपको तुरंत व्हाट्सएप या कॉल कर सकते हैं।',
        h2_title: 'टोइंग और महंगे जुर्माने से बचें',
        h2_desc: 'ट्रैफिक पुलिस या पड़ोसी टो ट्रक बुलाने से पहले कार हटाने के लिए आपका स्टिकर स्कैन करके चेतावनी दे सकते हैं।',
        h3_title: 'डोरस्टेप कार सफाई सेवा',
        h3_desc: 'अपनी पार्किंग स्थल पर ही पेशेवर कार धोने, इको वाटरलेस क्लीनिंग, और इंटीरियर पॉलिशिंग का अनुरोध करें।',
        h4_title: 'गैरेज और मैकेनिक समाधान',
        h4_desc: 'नजदीकी सत्यापित गैरेज, सड़क किनारे मैकेनिक, फ्लैट टायर पंचर मरम्मत, और बैटरी जंपस्टार्ट तक तुरंत पहुंच।',
        h5_title: 'बैटरी ड्रेन और हेडलाइट्स ऑन रहना',
        h5_desc: 'अपनी हेडलाइट्स चालू छोड़ दीं? आपकी बैटरी पूरी तरह से खत्म होने से पहले लोग आपको सचेत कर सकते हैं।',
        h6_title: 'खिड़कियां खुली रहना और चोरी से बचाव',
        h6_desc: 'बारिश या चोरी से पहले खिड़की खुली छोड़ दी? बारिश का पानी अंदर जाने से पहले पड़ोसी आपको अलर्ट कर सकते हैं।',
        h7_title: 'गंभीर आपात स्थिति और दुर्घटनाएं',
        h7_desc: 'आपातकालीन दुर्घटना स्थितियों में, जब आप बोल नहीं सकते, तो राहगीर आपके परिवार को तुरंत रिपोर्ट भेज सकते हैं।',
        h8_title: 'द्रव लीक और पशु सुरक्षा',
        h8_desc: 'ऑयल लीक, धुआं, या कार के इंजन के नीचे फंसे आवारा जानवर की तुरंत राहगीरों द्वारा रिपोर्ट की जा सकती है।',

        why_tag: 'गोपनीयता सुरक्षा तुलना',
        why_title: 'वाहन संपर्क बनाम पारंपरिक कागज स्टिकर',
        old_title: 'कागज का मोबाइल नंबर स्टिकर',
        old_f1: 'व्यक्तिगत फोन नंबर हर अनजान व्यक्ति और स्पैमर के सामने उजागर होता है।',
        old_f2: 'कोई स्वचालित व्हाट्सएप बॉट अलर्ट या आपातकालीन वर्गीकरण नहीं।',
        old_f3: 'नया स्टिकर खरीदे बिना फोन नंबर अपडेट नहीं कर सकते।',
        old_f4: 'बारिश में कागज आसानी से धुंधला हो जाता है या फट जाता है।',
        new_title: 'स्मार्ट QR गोपनीयता टैग',
        new_f1: '100% फोन नंबर गोपनीयता — शून्य अजनबी प्रदर्शन।',
        new_f2: '4-विकल्प आपातकालीन व्हाट्सएप बॉट रिले और सीधे कॉल लाइन।',
        new_f3: 'वेबसाइट के माध्यम से किसी भी समय तुरंत ऑनलाइन फोन नंबर अपडेट करें।',
        new_f4: 'विंडशील्ड के अंदर चिपकाया जाने वाला टिकाऊ, यूवी-प्रतिरोधी टैग कार्ड।',

        faq_tag: 'कोई प्रश्न हैं?',
        faq_title: 'अक्सर पूछे जाने वाले प्रश्न',
        faq1_q: 'वाहन संपर्क मेरे व्यक्तिगत फोन नंबर की सुरक्षा कैसे करता है?',
        faq1_a: 'जब कोई आपके वाहन संपर्क टैग को स्कैन करता है, तो उन्हें सीधे "कॉल ऑनर" या "व्हाट्सएप पर चैट करें" बटन दिखाई देते हैं। कॉल और व्हाट्सएप संदेश हमारे सुरक्षित प्लेटफॉर्म के माध्यम से भेजे जाते हैं, इसलिए आपका वास्तविक नंबर कभी उजागर नहीं होता।',
        faq2_q: 'क्या QR स्टिकर स्कैन करने वाले व्यक्ति को ऐप डाउनलोड करने की आवश्यकता है?',
        faq2_a: 'नहीं! सामान्य स्मार्टफोन कैमरे, गूगल लेंस या डिफॉल्ट क्यूआर स्कैनर वाला कोई भी व्यक्ति आपके टैग को स्कैन करके तुरंत जुड़ सकता है। कोई ऐप डाउनलोड आवश्यक नहीं है।',
        faq3_q: 'डोरस्टेप सफाई और गैरेज समाधान कैसे काम करते हैं?',
        faq3_a: 'वाहन संपर्क आपके वाहन टैग को सत्यापित कार सफाई भागीदारों और नजदीकी आपातकालीन गैरेज/मैकेनिक सेवाओं से जोड़ता है, जिससे आपको तुरंत सहायता मिलती है।',
        faq4_q: 'क्या मैं बाद में अपना पंजीकृत फोन नंबर बदल सकता हूं?',
        faq4_a: 'हां! आप अपने भौतिक टैग स्टिकर को बदले बिना किसी भी समय अपना मोबाइल नंबर या व्हाट्सएप नंबर अपडेट कर सकते हैं।',
        faq5_q: '4-विकल्प आपातकालीन व्हाट्सएप बॉट कैसे काम करता है?',
        faq5_a: 'जब कोई राहगीर "व्हाट्सएप पर चैट करें" पर क्लिक करता है, तो हमारा बॉट 4 आपातकालीन समस्याओं (गलत पार्क, दुर्घटना, लाइट चालू/खिड़की खुली, टोइंग नोटिस) में से चुनने के लिए कहता है। चयन करने पर, आपको तुरंत अलर्ट भेजा जाता है!',

        contact_tag: 'संपर्क करें',
        contact_title: 'स्मार्ट QR टैग ऑर्डर करें और पूछताछ करें',
        contact_sub: 'किसी ऑनलाइन भुगतान की आवश्यकता नहीं है। हमें पूछताछ भेजें या कॉल या व्हाट्सएप के माध्यम से सीधे संपर्क करें!',
        channels_title: '<span style="width: 42px; height: 42px; border-radius: 12px; background: rgba(16, 185, 129, 0.12); color: #10b981; display: inline-flex; align-items: center; justify-content: center; font-size: 1.25rem;"><i class="fa-solid fa-headset"></i></span> सीधे संपर्क चैनल',
        call_title: '<i class="fa-solid fa-bolt" style="color: #fef08a;"></i> त्वरित कॉल हेल्पलाइन',
        wa_title: '<i class="fa-solid fa-comments" style="color: #ffedd5;"></i> 1-क्लिक व्हाट्सएप बॉट',
        wa_btn: 'व्हाट्सएप पर चैट करें',
        email_title: 'ईमेल सहायता लाइन',
        form_title: '<span style="width: 42px; height: 42px; border-radius: 12px; background: rgba(249, 115, 22, 0.12); color: #f97316; display: inline-flex; align-items: center; justify-content: center; font-size: 1.25rem;"><i class="fa-solid fa-paper-plane"></i></span> हमें पूछताछ भेजें',
        form_name: 'पूरा नाम <span class="required" style="color: #f43f5e;">*</span>',
        form_phone: 'मोबाइल / व्हाट्सएप नंबर <span class="required" style="color: #f43f5e;">*</span>',
        form_vehicle: 'वाहन पंजीकरण संख्या <span style="font-weight: 500; color: #94a3b8; font-size: 0.78rem;">(वैकल्पिक)</span>',
        form_city: 'शहर और राज्य <span class="required" style="color: #f43f5e;">*</span>',
        form_qty: 'आवश्यक टैग संख्या',
        form_msg: 'संदेश / अतिरिक्त निर्देश',
        form_btn: '<i class="fa-solid fa-paper-plane"></i> अब पूछताछ जमा करें'
    }
};

/**
 * Multi-Language Selection Engine (English & Hindi)
 */
function onLangSelectChange(selectElem) {
    if (!selectElem) return;
    const langCode = selectElem.value;

    localStorage.setItem('selectedLangCode', langCode);

    // 1. Native DOM Text Swapping (Instant 0ms translation for key elements)
    applyNativeTranslation(langCode);

    // 2. Set Google Translate Cookie across hostinger domain
    setGoogleTranslateCookie(langCode);

    // 3. Trigger Google Translate Widget if available (smooth, no page reload)
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
    setHtml('.hero-title', data.hero_title);
    setHtml('.hero-subtitle', data.hero_sub);
    setHtml('a[href="#contact"].btn-primary', data.hero_btn_tag);
    setHtml('a[href*="wa.me"].btn-secondary', data.hero_btn_wa);
    setHtml('.color-emerald', data.trust_priv_val);
    setHtml('.trust-item:nth-child(1) .trust-lbl', data.trust_priv_lbl);
    setHtml('.color-orange', data.trust_relay_val);
    setHtml('.trust-item:nth-child(2) .trust-lbl', data.trust_relay_lbl);
    setHtml('.color-slate', data.trust_badge_val);
    setHtml('.trust-item:nth-child(3) .trust-lbl', data.trust_badge_lbl);
    setHtml('.float-top-left', data.float_left);
    setHtml('.float-top-right', data.float_right);

    // How It Works Section
    setHtml('#how-it-works .section-tag', data.how_tag);
    setHtml('#how-it-works .section-title', data.how_title);
    setHtml('.step-card:nth-child(1) h3', data.step1_title);
    setHtml('.step-card:nth-child(1) p', data.step1_desc);
    setHtml('.step-card:nth-child(2) h3', data.step2_title);
    setHtml('.step-card:nth-child(2) p', data.step2_desc);
    setHtml('.step-card:nth-child(3) h3', data.step3_title);
    setHtml('.step-card:nth-child(3) p', data.step3_desc);

    // Story Section
    setHtml('.story-section .section-tag', data.story_tag);
    setHtml('.story-section .section-title', data.story_title);
    setHtml('.story-section .section-header p', data.story_sub);
    setHtml('.story-card:nth-child(1) h3', data.s1_title);
    setHtml('.story-card:nth-child(1) p', data.s1_desc);
    setHtml('.story-card:nth-child(2) h3', data.s2_title);
    setHtml('.story-card:nth-child(2) p', data.s2_desc);
    setHtml('.story-card:nth-child(3) h3', data.s3_title);
    setHtml('.story-card:nth-child(3) p', data.s3_desc);
    setHtml('.story-card:nth-child(4) h3', data.s4_title);
    setHtml('.story-card:nth-child(4) p', data.s4_desc);

    // Hazards Solved Section
    setHtml('#hazards .section-tag', data.hazards_tag);
    setHtml('#hazards .section-title', data.hazards_title);
    setHtml('.hazard-card:nth-child(1) h4', data.h1_title);
    setHtml('.hazard-card:nth-child(1) p', data.h1_desc);
    setHtml('.hazard-card:nth-child(2) h4', data.h2_title);
    setHtml('.hazard-card:nth-child(2) p', data.h2_desc);
    setHtml('.hazard-card:nth-child(3) h4', data.h3_title);
    setHtml('.hazard-card:nth-child(3) p', data.h3_desc);
    setHtml('.hazard-card:nth-child(4) h4', data.h4_title);
    setHtml('.hazard-card:nth-child(4) p', data.h4_desc);
    setHtml('.hazard-card:nth-child(5) h4', data.h5_title);
    setHtml('.hazard-card:nth-child(5) p', data.h5_desc);
    setHtml('.hazard-card:nth-child(6) h4', data.h6_title);
    setHtml('.hazard-card:nth-child(6) p', data.h6_desc);
    setHtml('.hazard-card:nth-child(7) h4', data.h7_title);
    setHtml('.hazard-card:nth-child(7) p', data.h7_desc);
    setHtml('.hazard-card:nth-child(8) h4', data.h8_title);
    setHtml('.hazard-card:nth-child(8) p', data.h8_desc);

    // Why Us Comparison Section
    setHtml('#why-us .section-tag', data.why_tag);
    setHtml('#why-us .section-title', data.why_title);
    setHtml('.compare-box-old h3', data.old_title);
    setHtml('.compare-box-old li:nth-child(1) span', data.old_f1);
    setHtml('.compare-box-old li:nth-child(2) span', data.old_f2);
    setHtml('.compare-box-old li:nth-child(3) span', data.old_f3);
    setHtml('.compare-box-old li:nth-child(4) span', data.old_f4);
    setHtml('.compare-box-new h3', data.new_title);
    setHtml('.compare-box-new li:nth-child(1) span', data.new_f1);
    setHtml('.compare-box-new li:nth-child(2) span', data.new_f2);
    setHtml('.compare-box-new li:nth-child(3) span', data.new_f3);
    setHtml('.compare-box-new li:nth-child(4) span', data.new_f4);

    // FAQ Section
    setHtml('#faq .section-tag', data.faq_tag);
    setHtml('#faq .section-title', data.faq_title);
    setHtml('.faq-item:nth-child(1) .faq-question span', data.faq1_q);
    setHtml('.faq-item:nth-child(1) .faq-answer', data.faq1_a);
    setHtml('.faq-item:nth-child(2) .faq-question span', data.faq2_q);
    setHtml('.faq-item:nth-child(2) .faq-answer', data.faq2_a);
    setHtml('.faq-item:nth-child(3) .faq-question span', data.faq3_q);
    setHtml('.faq-item:nth-child(3) .faq-answer', data.faq3_a);
    setHtml('.faq-item:nth-child(4) .faq-question span', data.faq4_q);
    setHtml('.faq-item:nth-child(4) .faq-answer', data.faq4_a);
    setHtml('.faq-item:nth-child(5) .faq-question span', data.faq5_q);
    setHtml('.faq-item:nth-child(5) .faq-answer', data.faq5_a);

    // Contact & Inquiry Section
    setHtml('#contact .section-tag', data.contact_tag);
    setHtml('#contact .section-title', data.contact_title);
    setHtml('#contact .section-header p', data.contact_sub);
    setHtml('.content-card:nth-child(1) h3', data.channels_title);
    setHtml('.contact-channel-box:nth-child(1) div div div:first-child', data.call_title);
    setHtml('.contact-channel-box:nth-child(1) div div div:nth-child(2)', data.wa_title);
    setHtml('.contact-channel-box:nth-child(1) div div div:last-child', data.wa_btn);
    setHtml('.email-support-card div div div:first-child', data.email_title);
    setHtml('#landingContactForm div:nth-child(1) label', data.form_name);
    setHtml('#landingContactForm div:nth-child(2) label', data.form_phone);
    setHtml('#landingContactForm div:nth-child(3) label', data.form_vehicle);
    setHtml('#landingContactForm div:nth-child(4) label', data.form_city);
    setHtml('#landingContactForm div:nth-child(5) label', data.form_qty);
    setHtml('#landingContactForm div:nth-child(6) label', data.form_msg);
    setHtml('#landingContactForm button[type="submit"]', data.form_btn);
}

function setGoogleTranslateCookie(langCode) {
    const cookieVal = (langCode === 'en') ? '' : '/en/' + langCode;
    const host = window.location.hostname;
    
    // Delete old cookie if switching to English
    const expires = (langCode === 'en') ? '; expires=Thu, 01 Jan 1970 00:00:00 UTC' : '';

    document.cookie = 'googtrans=' + cookieVal + '; path=/' + expires + ';';
    document.cookie = 'googtrans=' + cookieVal + '; path=/; domain=' + host + expires + ';';
    
    // Extract root domain (e.g. hostingersite.com)
    const domainParts = host.split('.');
    if (domainParts.length >= 2) {
        const rootDomain = domainParts.slice(-2).join('.');
        document.cookie = 'googtrans=' + cookieVal + '; path=/; domain=.' + rootDomain + expires + ';';
        document.cookie = 'googtrans=' + cookieVal + '; path=/; domain=' + rootDomain + expires + ';';
    }
}

function applyGoogleTranslate(langCode) {
    const selectElem = document.querySelector('.goog-te-combo');
    if (selectElem) {
        selectElem.value = langCode;
        selectElem.dispatchEvent(new Event('change'));
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
        if (savedCode !== 'en') {
            setGoogleTranslateCookie(savedCode);
            setTimeout(() => applyGoogleTranslate(savedCode), 500);
        }
    }
});


