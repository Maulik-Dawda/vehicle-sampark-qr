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
        hero_title: '<span id="heroTitleMain">Let Anyone Reach You With </span><span id="heroDynamicWord" class="gradient-text hero-dynamic-word">a Quick QR Scan ⚡</span>',
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
        hero_call_pill: '<i class="fa-solid fa-phone-volume"></i> Call Owner',
        hero_wa_pill: '<i class="fa-brands fa-whatsapp"></i> WhatsApp Bot',
        live_support: '<span style="width: 8px; height: 8px; border-radius: 50%; background: #10b981; display: inline-block; animation: pulseDot 1.5s infinite;"></span> Live 24/7 Support',

        ticker1: '100% Privacy Guarded - No Mobile Numbers Revealed',
        ticker2: 'Instant Call & 4-Option Emergency WhatsApp Bot Relay',
        ticker3: 'Avoid Scratching, Vandalism & Unexpected Towing Fines',
        ticker4: 'Integrated Doorstep Car Cleaning & Nearby Garage Support',
        ticker5: 'Zero App Download - Scans with Any Phone Camera or Google Lens',

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
        scene1_badge: 'SCENE 1',
        scene2_badge: 'SCENE 2',
        scene3_badge: 'SCENE 3',
        scene4_badge: 'SCENE 4',
        scene1_alert: '🚫 Blocked Exit!',
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
        old_method_lbl: '❌ Traditional Method',
        old_title: 'Paper Mobile Number Sticker',
        old_f1: 'Personal phone number exposed to every stranger & spammer.',
        old_f2: 'No automated WhatsApp bot alerts or emergency classifications.',
        old_f3: 'Cannot update phone number without buying a new sticker.',
        old_f4: 'Paper fades, tears, or peels off easily in rain.',
        new_method_lbl: '✅ Vehicle Sampark',
        rec_badge: 'RECOMMENDED',
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
        form_opt1: '🚗 1 Tag (Personal Car/Bike)',
        form_opt2: '👨‍👩‍👧‍👦 5 Tags (Family Fleet)',
        form_opt3: '🏢 10 Tags (Small Fleet)',
        form_opt4: '🚛 50+ Tags (Commercial Fleet / Business)',
        form_msg: 'Message / Additional Instructions',
        form_btn: '<i class="fa-solid fa-paper-plane"></i> Submit Inquiry Now',
        form_safe_notice: '<i class="fa-solid fa-shield-check" style="color: #10b981;"></i> 100% Safe & Private. No spam, guaranteed.',
        wa_widget_title: '<i class="fa-brands fa-whatsapp"></i> Instant Connect',
        wa_widget_msg: 'Hi! Have a question or want to order smart vehicle tags? Chat with us directly on WhatsApp!',
        footer_p: 'Smart Emergency Vehicle Safety & Privacy Tag System &bull; Built with ❤️ for Indian Roads &bull; &copy; 2026 Vehicle Sampark.',
        footer_l1: 'How It Works',
        footer_l2: 'Emergency Cases',
        footer_l3: 'Why Vehicle Sampark',
        footer_l4: 'FAQ',
        footer_l5: 'Contact Us'
    },
    'hi': {
        announcement: '🚀 त्वरित 1-क्लिक व्हाट्सएप बॉट और कॉल रिले • 100% मोबाइल नंबर गोपनीयता सुरक्षा की गारंटी!',
        nav_how: '<i class="fa-solid fa-list-check"></i> यह कैसे काम करता है',
        nav_hazards: '<i class="fa-solid fa-triangle-exclamation"></i> सुरक्षा समस्याएं',
        nav_why: '<i class="fa-solid fa-shield-halved"></i> हम क्यों',
        nav_faq: '<i class="fa-solid fa-circle-question"></i> अक्सर पूछे जाने वाले प्रश्न',
        nav_contact: '<i class="fa-solid fa-envelope"></i> संपर्क करें',
        hero_badge: '<span class="live-dot-pulse"></span> <i class="fa-solid fa-shield-halved" style="color: #10b981;"></i> अपने वाहन और व्यक्तिगत गोपनीयता की रक्षा करें',
        hero_title: '<span id="heroTitleMain">स्मार्ट QR स्कैन से </span><span id="heroDynamicWord" class="gradient-text hero-dynamic-word">कोई भी आपसे तुरंत संपर्क कर सकता है ⚡</span>',
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
    },
    'gu': {
        announcement: '🚀 ત્વરિત 1-ક્લિક વોટ્સએપ બોટ અને કોલ રિલે • 100% મોબાઇલ નંબર ગોપનીયતા સુરક્ષાની ખાતરી!',
        nav_how: '<i class="fa-solid fa-list-check"></i> આ કેવી રીતે કામ કરે છે',
        nav_hazards: '<i class="fa-solid fa-triangle-exclamation"></i> ઉકેલાયેલ જોખમો',
        nav_why: '<i class="fa-solid fa-shield-halved"></i> શા માટે આપણે',
        nav_faq: '<i class="fa-solid fa-circle-question"></i> વારંવાર પૂછાતા પ્રશ્નો',
        nav_contact: '<i class="fa-solid fa-envelope"></i> સંપર્ક કરો',
        hero_badge: '<span class="live-dot-pulse"></span> <i class="fa-solid fa-shield-halved" style="color: #10b981;"></i> તમારા વાહન અને અંગત ગોપનીયતાનું રક્ષણ કરો',
        hero_title: '<span id="heroTitleMain">સ્માર્ટ QR સ્કેન વડે </span><span id="heroDynamicWord" class="gradient-text hero-dynamic-word">કોઈપણ વ્યક્તિ તમારો તુરંત સંપર્ક કરી શકે છે ⚡</span>',
        hero_sub: 'એક સરળ સ્કેન કોઈપણ વ્યક્તિને તુરંત જ તમારો સંપર્ક કરવા દે છે. રીયલ-ટાઇમ વોટ્સએપ એલર્ટ, 4-વિકલ્પ ઇમરજન્સી બોટ રિપોર્ટ અને સીધા કોલ મેળવો — તમારો ફોન નંબર જાહેર કર્યા વગર.',
        hero_btn_tag: '<i class="fa-solid fa-phone-volume"></i> સ્માર્ટ QR ટેગ મેળવો',
        hero_btn_wa: '<i class="fa-brands fa-whatsapp" style="font-size: 1.3rem;"></i> વોટ્સએપ પર ચેટ કરો',
        trust_priv_val: '100% ખાનગી',
        trust_priv_lbl: 'મોબાઇલ નંબર સુરક્ષિત',
        trust_relay_val: 'તુરંત સંપર્ક',
        trust_relay_lbl: 'કોલ અને વોટ્સએપ બોટ',
        trust_badge_val: 'સ્માર્ટ બેજ',
        trust_badge_lbl: 'સેન્ટર લોગો ટેગ કાર્ડ',
        float_left: '<i class="fa-solid fa-lock" style="color: #10b981;"></i> 100% ફોન ગોપનીયતા',
        float_right: '<i class="fa-solid fa-bolt" style="color: #f97316;"></i> 3-સેકન્ડ કનેક્ટ',

        how_tag: 'કોલ અને વોટ્સએપ માટે સ્કેન કરો',
        how_title: 'વાહન સંપર્ક 3 સરળ પગલાંમાં તમારી રક્ષા કરે છે',
        step1_title: '1. QR કોડ સ્કેન કરો',
        step1_desc: 'કોઈપણ વ્યક્તિ કોઈપણ સ્માર્ટફોન કેમેરાથી તમારી કાર પરના QR સ્ટીકરને સ્કેન કરી શકે છે — કોઈ એપ ડાઉનલોડ જરૂરી નથી.',
        step2_title: '2. તુરંત સંપર્ક કરો',
        step2_desc: 'તમારો ફોન નંબર દર્શાવ્યા વિના વાહન માલિકને સીધો કોલ કરો અથવા વોટ્સએપ બોટ વિકલ્પ પસંદ કરો.',
        step3_title: '3. સમસ્યાનું નિવારણ કરો',
        step3_desc: 'ખોટી પાર્કિંગ, કાર ખસેડવા, ટોઇંગ અથવા ઇમરજન્સી પરિસ્થિતિઓમાં ઝડપથી અને સુરક્ષિત રીતે સંપર્ક કરો.',

        story_tag: 'ઝીરો પર્સનલ નંબર શેરિંગ',
        story_title: 'વાહન સંપર્ક તમને સુરક્ષિત રીતે કેવી રીતે જોડે છે',
        story_sub: 'રસ્તા પરના લોકો અમારા સત્તાવાર વોટ્સએપ ગેટવે દ્વારા વાહન માલિકો સાથે જોડાય છે — તમારો અંગત નંબર 100% છુપાયેલો રહે છે!',
        s1_title: 'વાહન ખોટી રીતે પાર્ક થયેલ છે',
        s1_desc: 'ભીડવાળા પાર્કિંગ વિસ્તારમાં કાર ગેટ અથવા અન્ય વાહનને અવરોધે છે. ડ્રાઇવર દૂર છે.',
        s2_title: 'રાહદારી QR ટેગ સ્કેન કરે છે',
        s2_desc: 'રાહદારી કોઈપણ ફોન કેમેરાનો ઉપયોગ કરીને કારની વિન્ડશિલ્ડ પર રહેલ વાહન સંપર્ક સ્ટીકર સ્કેન કરે છે.',
        s3_title: 'કંપની વોટ્સએપ માસ્કીંગ ગેટવે',
        s3_desc: 'વાહન સંપર્કના સત્તાવાર વોટ્સએપ બોટ દ્વારા કનેક્ટેડ. રહાદારીને ક્યારેય અંગત નંબર દેખાતો નથી.',
        s4_title: 'અનામી અલર્ટ અને ઉકેલ!',
        s4_desc: 'માલિકને તાત્કાલિક વોટ્સએપ અલર્ટ મળે છે, તેઓ વાહન ખસેડે છે અને બંને પક્ષો 100% સુરક્ષિત રહે છે!',

        hazards_tag: 'વાસ્તવિક દુનિયાની સુરક્ષા',
        hazards_title: 'વાહન સંપર્ક દ્વારા ઉકેલાયેલ ગંભીર સમસ્યાઓ અને સેવાઓ',
        h1_title: 'સ્ક્રેચ અને નુકસાનથી બચાવો',
        h1_desc: 'તંગ પાર્કિંગમાં કાર પાર્ક કરી છે? કાર પર સ્ક્રેચ કરવાને બદલે લોકો તમને તુરંત વોટ્સએપ અથવા કોલ કરી શકે છે.',
        h2_title: 'ટોઇંગ અને મોંઘા દંડથી બચો',
        h2_desc: 'ટ્રાફિક પોલીસ અથવા પડોશીઓ ટો ટ્રક બોલાવતા પહેલા કાર ખસેડવા માટે સ્ટીકર સ્કેન કરીને ચેતવણી આપી શકે છે.',
        h3_title: 'ડોરસ્ટેપ કાર વોશ સેવા',
        h3_desc: 'તમારી પાર્કિંગ જગ્યા પર જ વ્યાવસાયિક કાર વોશ, ઇકો વોટરલેસ ક્લીનિંગ અને ઇન્ટિરિયર પોલિશિંગ મેળવો.',
        h4_title: 'ગેરેજ અને મિકેનિક સોલ્યુશન',
        h4_desc: 'નજીકના ચકાસાયેલ ગેરેજ, પંચર રિપેર, બેટરી જમ્પસ્ટાર્ટ અને ઇમરજન્સી મિકેનિક સુધી ત્વરિત પહોંચ.',
        h5_title: 'બેટરી ડ્રેઇન અને હેડલાઇટ ચાલુ રહેવી',
        h5_desc: 'હેડલાઇટ ચાલુ રહી ગઈ છે? બેટરી પૂરી થાય તે પહેલા લોકો તમને સચેત કરી શકે છે.',
        h6_title: 'કાચ ખુલ્લા રહેવા અને ચોરી નિવારણ',
        h6_desc: 'વરસાદ અથવા ચોરી પહેલાં બારી ખુલ્લી રહી ગઈ? વરસાદ પાણી અંદર જાય તે પહેલા પડોશીઓ અલર્ટ કરી શકે છે.',
        h7_title: 'ગંભીર અકસ્માત અને ઇમરજન્સી',
        h7_desc: 'અકસ્માતની સ્થિતિમાં, જ્યારે તમે બોલી શકતા નથી, ત્યારે રહાદારી તમારા પરિવારને તુરંત અલર્ટ મોકલી શકે છે.',
        h8_title: 'ઓઇલ લીક અને પ્રાણી સુરક્ષા',
        h8_desc: 'ઓઇલ લીક, ધુમાડો અથવા કારના એન્જિન નીચે ફસાયેલા પ્રાણીની તુરંત રિપોર્ટ કરી શકાય છે.',

        why_tag: 'ગોપનીયતા સુરક્ષા સરખામણી',
        why_title: 'વાહન સંપર્ક વિરુદ્ધ પરંપરાગત કાગળ સ્ટીકર',
        old_title: 'કાગળનો મોબાઇલ નંબર સ્ટીકર',
        old_f1: 'અંગત ફોન નંબર દરેક અજાણ્યા વ્યક્તિ અને સ્પેમર સામે ખુલ્લો રહે છે.',
        old_f2: 'કોઈ સ્વચાલિત વોટ્સએપ બોટ અલર્ટ કે ઈમરજન્સી વર્ગીકરણ નથી.',
        old_f3: 'નવું સ્ટીકર ખરીદ્યા વગર ફોન નંબર અપડેટ કરી શકાતો નથી.',
        old_f4: 'વરસાદમાં કાગળ સહેલાઈથી ઝાંખો થઈ જાય છે કે ફાટી જાય છે.',
        new_title: 'સ્માર્ટ QR ગોપનીયતા ટેગ',
        new_f1: '100% ફોન નંબર ગોપનીયતા — ઝીરો અજાણ્યા પ્રદર્શન.',
        new_f2: '4-વિકલ્પ ઇમરજન્સી વોટ્સએપ બોટ રિલે અને ડાયરેક્ટ કોલ લાઇન.',
        new_f3: 'વેબસાઇટ દ્વારા કોઈપણ સમયે તુરંત ઓનલાઇન ફોન નંબર અપડેટ કરો.',
        new_f4: 'વિન્ડશિલ્ડની અંદર ચોંટાડાતો ટકાઉ, યુવી-પ્રતિરોધી ટેગ કાર્ડ.',

        faq_tag: 'કોઈ પ્રશ્નો છે?',
        faq_title: 'વારંવાર પૂછાતા પ્રશ્નો',
        faq1_q: 'વાહન સંપર્ક મારા અંગત ફોન નંબરનું રક્ષણ કેવી રીતે કરે છે?',
        faq1_a: 'જ્યારે કોઈ તમારો વાહન સંપર્ક ટેગ સ્કેન કરે છે, ત્યારે તેમને "કોલ ઓનર" અથવા "વોટ્સએપ પર ચેટ કરો" બટન દેખાય છે. કોલ અને વોટ્સએપ મેસેજ અમારા સુરક્ષિત પ્લેટફોર્મ દ્વારા રૂટ થાય છે, તેથી તમારો વાસ્તવિક નંબર ક્યારેય જાહેર થતો નથી.',
        faq2_q: 'શું QR સ્ટીકર સ્કેન કરનાર વ્યક્તિએ એપ ડાઉનલોડ કરવી જરૂરી છે?',
        faq2_a: 'ના! સામાન્ય સ્માર્ટફોન કેમેરા, ગૂગલ લેન્સ અથવા QR સ્કેનર ધરાવતી કોઈપણ વ્યક્તિ તુરંત કનેક્ટ થઈ શકે છે. કોઈ એપ ડાઉનલોડ જરૂરી નથી.',
        faq3_q: 'ડોરસ્ટેપ ક્લીનિંગ અને ગેરેજ સોલ્યુશન કેવી રીતે કામ કરે છે?',
        faq3_a: 'વાહન સંપર્ક તમારા વાહન ટેગને ચકાસાયેલ કાર ક્લીનિંગ પાર્ટનર્સ અને નજીકના ઇમરજન્સી ગેરેજ/મિકેનિક સેવાઓ સાથે જોડે છે.',
        faq4_q: 'શું હું પાછળથી મારો મોબાઈલ નંબર બદલી શકું છું?',
        faq4_a: 'હા! તમે ભૌતિક ટેગ સ્ટીકર બદલ્યા વિના કોઈપણ સમયે તમારો મોબાઇલ નંબર અથવા વોટ્સએપ નંબર અપડેટ કરી શકો છો.',
        faq5_q: '4-વિકલ્પ ઇમરજન્સી વોટ્સએપ બોટ કેવી રીતે કામ કરે છે?',
        faq5_a: 'જ્યારે કોઈ "વોટ્સએપ પર ચેટ કરો" પર ક્લિક કરે છે, ત્યારે અમારો બોટ 4 ઈમરજન્સી વિકલ્પોમાંથી પસંદ કરવા કહે છે. પસંદ કર્યા પછી તમને તુરંત જ અલર્ટ મોકલવામાં આવે છે!',

        contact_tag: 'સંપર્ક કરો',
        contact_title: 'સ્માર્ટ QR ટેગ ઓર્ડર કરો અને પૂછપરછ કરો',
        contact_sub: 'કોઈ ઓનલાઇન ચુકવણી જરૂરી નથી. અમને પૂછપરછ મોકલો અથવા કોલ/વોટ્સએપ દ્વારા સીધો સંપર્ક કરો!',
        channels_title: '<span style="width: 42px; height: 42px; border-radius: 12px; background: rgba(16, 185, 129, 0.12); color: #10b981; display: inline-flex; align-items: center; justify-content: center; font-size: 1.25rem;"><i class="fa-solid fa-headset"></i></span> સીધા સંપર્ક ચેનલો',
        call_title: '<i class="fa-solid fa-bolt" style="color: #fef08a;"></i> ત્વરિત કોલ હેલ્પલાઇન',
        wa_title: '<i class="fa-solid fa-comments" style="color: #ffedd5;"></i> 1-ક્લિક વોટ્સએપ બોટ',
        wa_btn: 'વોટ્સએપ પર ચેટ કરો',
        email_title: 'ઇમેઇલ સપોર્ટ લાઇન',
        form_title: '<span style="width: 42px; height: 42px; border-radius: 12px; background: rgba(249, 115, 22, 0.12); color: #f97316; display: inline-flex; align-items: center; justify-content: center; font-size: 1.25rem;"><i class="fa-solid fa-paper-plane"></i></span> અમને પૂછપરછ મોકલો',
        form_name: 'પૂરું નામ <span class="required" style="color: #f43f5e;">*</span>',
        form_phone: 'મોબાઇલ / વોટ્સએપ નંબર <span class="required" style="color: #f43f5e;">*</span>',
        form_vehicle: 'વાહન નોંધણી નંબર <span style="font-weight: 500; color: #94a3b8; font-size: 0.78rem;">(વૈકલ્પિક)</span>',
        form_city: 'શહેર અને રાજ્ય <span class="required" style="color: #f43f5e;">*</span>',
        form_qty: 'જરૂરી ટેગ સંખ્યા',
        form_msg: 'સંદેશ / વધારાની સૂચનાઓ',
        form_btn: '<i class="fa-solid fa-paper-plane"></i> હવે પૂછપરછ જમા કરો'
    },
    'mr': {
        announcement: '🚀 त्वरित 1-क्लिक व्हॉट्सॲप बॉट आणि कॉल रिले • 100% मोबाईल नंबर गोपनीयतेची शाश्वती!',
        nav_how: '<i class="fa-solid fa-list-check"></i> हे कसे कार्य करते',
        nav_hazards: '<i class="fa-solid fa-triangle-exclamation"></i> सोडवलेले धोके',
        nav_why: '<i class="fa-solid fa-shield-halved"></i> आम्हीच का',
        nav_faq: '<i class="fa-solid fa-circle-question"></i> विचारले जाणारे प्रश्न',
        nav_contact: '<i class="fa-solid fa-envelope"></i> संपर्क साधा',
        hero_badge: '<span class="live-dot-pulse"></span> <i class="fa-solid fa-shield-halved" style="color: #10b981;"></i> तुमचे वाहन आणि वैयक्तिक गोपनीयतेचे रक्षण करा',
        hero_title: '<span id="heroTitleMain">स्मार्ट QR स्कॅनद्वारे </span><span id="heroDynamicWord" class="gradient-text hero-dynamic-word">कोणीही तुमच्याशी त्वरित संपर्क साधू शकते ⚡</span>',
        hero_sub: 'एक सोपा स्कॅन कोणालाही तुमच्याशी त्वरित संपर्क साधू देतो. तुमचा वैयक्तिक फोन नंबर न दाखवता रीअल-टाइम व्हॉट्सॲप इशारे, 4-पर्यायी आपत्कालीन बॉट अहवाल आणि थेट कॉल मिळवा.',
        hero_btn_tag: '<i class="fa-solid fa-phone-volume"></i> स्मार्ट क्यूआर टॅग मिळवा',
        hero_btn_wa: '<i class="fa-brands fa-whatsapp" style="font-size: 1.3rem;"></i> व्हॉट्सॲपवर चॅट करा',
        trust_priv_val: '100% खाजगी',
        trust_priv_lbl: 'फोन नंबर सुरक्षित',
        trust_relay_val: 'त्वरित संपर्क',
        trust_relay_lbl: 'कॉल आणि व्हॉट्सॲप बॉट',
        trust_badge_val: 'स्मार्ट बॅज',
        trust_badge_lbl: 'सेंटर लोगो टॅग कार्ड',
        float_left: '<i class="fa-solid fa-lock" style="color: #10b981;"></i> 100% फोन गोपनीयता',
        float_right: '<i class="fa-solid fa-bolt" style="color: #f97316;"></i> 3-सेकंद कनेक्ट',

        how_tag: 'कॉल आणि व्हॉट्सॲपसाठी स्कॅन करा',
        how_title: 'वाहन संपर्क 3 सोप्या टप्प्यात तुमचे रक्षण करतो',
        step1_title: '1. QR कोड स्कॅन करा',
        step1_desc: 'कोणीही कोणत्याही स्मार्टफोन कॅमेऱ्याने तुमच्या कारवरील QR स्टिकर स्कॅन करू शकते — कोणतेही ॲप डाउनलोड करण्याची गरज नाही.',
        step2_title: '2. त्वरित संपर्क साधा',
        step2_desc: 'तुमचा नंबर न दाखवता वाहन मालकाला थेट कॉल करा किंवा व्हॉट्सॲप बॉट पर्याय निवडा.',
        step3_title: '3. समस्येचे निवारण करा',
        step3_desc: 'चुकीचे पार्किंग, कार बाजूला करणे, टोईंग किंवा आपत्कालीन परिस्थितीत लवकर आणि सुरक्षितपणे संपर्क साधा.',

        story_tag: 'झिरो पर्सनल नंबर शेअरिंग',
        story_title: 'वाहन संपर्क तुम्हाला सुरक्षितपणे कसा जोडतो',
        story_sub: 'रस्त्यावरील नागरिक आमच्या अधिकृत व्हॉट्सॲप गेटवेद्वारे वाहन मालकांशी जोडले जातात — तुमचा नंबर 100% लपलेला राहतो!',
        s1_title: 'वाहन चुकीच्या पद्धतीने पार्क केले',
        s1_desc: 'गर्दीच्या पार्किंग क्षेत्रात कार रस्ता किंवा इतर वाहनाचा मार्ग अडवते. ड्रायव्हर बाजूला आहे.',
        s2_title: 'नागरिक QR टॅग स्कॅन करतो',
        s2_desc: 'नागरिक कोणत्याही फोन कॅमेऱ्याने कारच्या विंडशील्डवरील वाहन संपर्क स्टिकर स्कॅन करतो.',
        s3_title: 'कंपनी व्हॉट्सॲप मास्किंग गेटवे',
        s3_desc: 'वाहन संपर्कच्या अधिकृत व्हॉट्सॲप बॉटद्वारे जोडलेले. नागरिकाला वैयक्तिक नंबर कधीही दिसत नाही.',
        s4_title: 'अनामिक अलर्ट आणि निवारण!',
        s4_desc: 'मालकाला तातडीचा व्हॉट्सॲप अलर्ट मिळतो, ते वाहन हलवतात आणि दोन्ही बाजू 100% सुरक्षित राहतात!',

        hazards_tag: 'वास्तविक जगातील संरक्षण',
        hazards_title: 'वाहन संपर्कद्वारे सोडवलेल्या गंभीर समस्या आणि सेवा',
        h1_title: 'स्कॅच आणि नुकसानापासून वाचवा',
        h1_desc: 'कार अडचणीत पार्क केली आहे? गाडीचे नुकसान करण्याऐवजी लोक तुम्हाला लगेच व्हॉट्सॲप किंवा कॉल करू शकतात.',
        h2_title: 'टोईंग आणि दंडापासून वाचा',
        h2_desc: 'ट्रॅफिक पोलिस किंवा शेजारी टो ट्रक बोलावण्यापूर्वी कार हलवण्यासाठी स्टिकर स्कॅन करून सूचना देऊ शकतात.',
        h3_title: 'डोअरस्टेप कार वॉशिंग सेवा',
        h3_desc: 'तुमच्या पार्किंगच्या ठिकाणीच व्यावसायिक कार वॉश, इको वॉटरलेस क्लीनिंग आणि इंटीरियर पॉलिशिंगचा लाभ घ्या.',
        h4_title: 'गॅरेज आणि मेकॅनिक उपाय',
        h4_desc: 'जवळचे सत्यापित गॅरेज, रस्ता दुरुस्ती, पंक्चर दुरुस्ती, आणि बॅटरी जंपस्टार्टपर्यंत त्वरित पोहोच.',
        h5_title: 'बॅटरी ड्रेन आणि हेडलाइट चालू राहणे',
        h5_desc: 'हेडलाइट चालू राहिली आहे? बॅटरी संपण्यापूर्वी नागरिक तुम्हाला अलर्ट करू शकतात.',
        h6_title: 'खिडक्या उघड्या राहणे व चोरी रोखणे',
        h6_desc: 'पावसापूर्वी खिडकी उघडी राहिली? पावसाचे पाणी आत जाण्यापूर्वी शेजारी तुम्हाला अलर्ट करू शकतात.',
        h7_title: 'गंभीर आपत्कालीन परिस्थिती व अपघात',
        h7_desc: 'अपघाताच्या प्रसंगी, जेव्हा तुम्ही बोलू शकत नाही, तेव्हा नागरिक तुमच्या कुटुंबाला त्वरित अलर्ट पाठवू शकतात.',
        h8_title: 'ऑइल गळती व प्राणी सुरक्षितता',
        h8_desc: 'ऑइल गळती, धूर किंवा कारखाली अडकलेल्या प्राण्याची नागरिकांद्वारे त्वरित तक्रार केली जाऊ शकते.',

        why_tag: 'गोपनीयता संरक्षण तुलना',
        why_title: 'वाहन संपर्क विरुद्ध पारंपारिक कागदी स्टिकर',
        old_title: 'कागदी मोबाईल नंबर स्टिकर',
        old_f1: 'वैयक्तिक फोन नंबर प्रत्येक अनोळखी व्यक्ती आणि स्पॅमरसमोर उघडा पडतो.',
        old_f2: 'कोणताही स्वयंचलित व्हॉट्सॲप बॉट अलर्ट किंवा आपत्कालीन वर्गीकरण नाही.',
        old_f3: 'नवीन स्टिकर खरेदी केल्याशिवाय नंबर अपडेट करता येत नाही.',
        old_f4: 'पावसात कागद सहज अस्पष्ट होतो किंवा फाटतो.',
        new_title: 'स्मार्ट QR गोपनीयता टॅग',
        new_f1: '100% फोन नंबर गोपनीयता — शून्य अनोळखी प्रदर्शन.',
        new_f2: '4-पर्यायी आपत्कालीन व्हॉट्सॲप बॉट रिले आणि थेट कॉल लाइन.',
        new_f3: 'वेबसाइटवरून कोणत्याही वेळी त्वरित नंबर अपडेट करा.',
        new_f4: 'विंडशील्डच्या आत चिकटवला जाणारा टिकाऊ, यूव्ही-प्रतिबंधक टॅग कार्ड.',

        faq_tag: 'काही प्रश्न आहेत?',
        faq_title: 'वारंवार विचारले जाणारे प्रश्न',
        faq1_q: 'वाहन संपर्क माझ्या वैयक्तिक नंबरचे रक्षण कसे करतो?',
        faq1_a: 'जेव्हा कोणी तुमचा टॅग स्कॅन करतो, तेव्हा त्यांना "कॉल मालक" किंवा "व्हॉट्सॲपवर चॅट करा" बटणे दिसतात. कॉल आणि संदेश आमच्या सुरक्षित प्लॅटफॉर्मवरून जातात, त्यामुळे तुमचा खरा नंबर कधीही उघड होत नाही.',
        faq2_q: 'स्कॅन करणाऱ्या व्यक्तीने ॲप डाउनलोड करणे आवश्यक आहे का?',
        faq2_a: 'नाही! कोणत्याही स्मार्टफोन कॅमेऱ्याने किंवा QR स्कॅनरने कोणीही स्कॅन करून जोडले जाऊ शकते. ॲप डाउनलोड आवश्यक नाही.',
        faq3_q: 'डोअरस्टेप कार वॉश आणि गॅरेज सेवा कशी काम करते?',
        faq3_a: 'वाहन संपर्क तुमचा टॅग सत्यापित कार वॉश भागीदार आणि जवळील आपत्कालीन गॅरेज मेकॅनिक सेवांशी जोडतो.',
        faq4_q: 'मी नंतर माझा मोबाईल नंबर बदलू शकतो का?',
        faq4_a: 'होय! तुम्ही भौतिक स्टिकर न बदलता कोणत्याही वेळी तुमचा नंबर अपडेट करू शकता.',
        faq5_q: '4-पर्यायी आपत्कालीन व्हॉट्सॲप बॉट कसा काम करतो?',
        faq5_a: 'जेव्हा कोणी "व्हॉट्सॲपवर चॅट करा" वर क्लिक करते, तेव्हा आमचा बॉट 4 आपत्कालीन पर्यायांपैकी निवडण्यास सांगतो. निवड केल्यावर तुम्हाला लगेच अलर्ट पाठवला जातो!',

        contact_tag: 'संपर्क साधा',
        contact_title: 'स्मार्ट QR टॅग ऑर्डर करा आणि चौकशी करा',
        contact_sub: 'कोणत्याही ऑनलाइन पेमेंटची गरज नाही. आम्हाला चौकशी पाठवा किंवा कॉल/व्हॉट्सॲपद्वारे थेट संपर्क साधा!',
        channels_title: '<span style="width: 42px; height: 42px; border-radius: 12px; background: rgba(16, 185, 129, 0.12); color: #10b981; display: inline-flex; align-items: center; justify-content: center; font-size: 1.25rem;"><i class="fa-solid fa-headset"></i></span> थेट संपर्क चॅनेल',
        call_title: '<i class="fa-solid fa-bolt" style="color: #fef08a;"></i> त्वरित कॉल हेल्पलाइन',
        wa_title: '<i class="fa-solid fa-comments" style="color: #ffedd5;"></i> 1-क्लिक व्हॉट्सॲप बॉट',
        wa_btn: 'व्हॉट्सॲपवर चॅट करा',
        email_title: 'ईमेल मदत लाइन',
        form_title: '<span style="width: 42px; height: 42px; border-radius: 12px; background: rgba(249, 115, 22, 0.12); color: #f97316; display: inline-flex; align-items: center; justify-content: center; font-size: 1.25rem;"><i class="fa-solid fa-paper-plane"></i></span> आम्हाला चौकशी पाठवा',
        form_name: 'पूर्ण नाव <span class="required" style="color: #f43f5e;">*</span>',
        form_phone: 'मोबाईल / व्हॉट्सॲप नंबर <span class="required" style="color: #f43f5e;">*</span>',
        form_vehicle: 'वाहन नोंदणी क्रमांक <span style="font-weight: 500; color: #94a3b8; font-size: 0.78rem;">(ऐच्छिक)</span>',
        form_city: 'शहर व राज्य <span class="required" style="color: #f43f5e;">*</span>',
        form_qty: 'आवश्यक टॅग संख्या',
        form_msg: 'संदेश / अतिरिक्त सूचना',
        form_btn: '<i class="fa-solid fa-paper-plane"></i> आता चौकशी सादर करा'
    },
    'ta': {
        announcement: '🚀 உடனடி 1-கிளிக் வாட்ஸ்அப் பாட் & கால் ரிலே • 100% மொபைல் எண் தனியுரிமை பாதுகாப்பு உத்திரவாதம்!',
        nav_how: '<i class="fa-solid fa-list-check"></i> இது எப்படி செயல்படுகிறது',
        nav_hazards: '<i class="fa-solid fa-triangle-exclamation"></i> தீர்க்கப்பட்ட சிக்கல்கள்',
        nav_why: '<i class="fa-solid fa-shield-halved"></i> ஏன் நாங்கள்',
        nav_faq: '<i class="fa-solid fa-circle-question"></i> கேள்விகள்',
        nav_contact: '<i class="fa-solid fa-envelope"></i> தொடர்பு கொள்ள',
        hero_badge: '<span class="live-dot-pulse"></span> <i class="fa-solid fa-shield-halved" style="color: #10b981;"></i> உங்கள் வாகனம் மற்றும் தனிப்பட்ட தனியுரிமையைப் பாதுகாக்கவும்',
        hero_title: '<span id="heroTitleMain">ஸ்மார்ட் QR ஸ்கேன் மூலம் </span><span id="heroDynamicWord" class="gradient-text hero-dynamic-word">யாரும் உங்களை உடனடியாகத் தொடர்புகொள்ளலாம் ⚡</span>',
        hero_sub: 'ஒரு எளிய ஸ்கேன் மூலம் எவரும் உங்களை உடனடியாகத் தொடர்புகொள்ளலாம். உங்கள் தனிப்பட்ட போன் எண்ணை வெளிப்படுத்தாமல் நிகழ்நேர வாட்ஸ்அப் விழிப்பூட்டல்கள் மற்றும் நேரடி அழைப்புகளைப் பெறுங்கள்.',
        hero_btn_tag: '<i class="fa-solid fa-phone-volume"></i> ஸ்மார்ட் QR டேக் பெறவும்',
        hero_btn_wa: '<i class="fa-brands fa-whatsapp" style="font-size: 1.3rem;"></i> வாட்ஸ்அப்பில் அரட்டையடிக்கவும்',
        trust_priv_val: '100% ரகசியம்',
        trust_priv_lbl: 'போன் எண் பாதுகாப்பானது',
        trust_relay_val: 'உடனடி தொடர்பு',
        trust_relay_lbl: 'அழைப்பு & வாட்ஸ்அப் பாட்',
        trust_badge_val: 'ஸ்மார்ட் பேட்ஜ்',
        trust_badge_lbl: 'சென்டர் லோகோ டேக் கார்டு',
        float_left: '<i class="fa-solid fa-lock" style="color: #10b981;"></i> 100% போன் தனியுரிமை',
        float_right: '<i class="fa-solid fa-bolt" style="color: #f97316;"></i> 3-வினாடி இணைப்பு',
        hero_call_pill: '<i class="fa-solid fa-phone-volume"></i> உரிமையாளரை அழைக்கவும்',
        hero_wa_pill: '<i class="fa-brands fa-whatsapp"></i> வாட்ஸ்அப் பாட்',
        live_support: '<span style="width: 8px; height: 8px; border-radius: 50%; background: #10b981; display: inline-block; animation: pulseDot 1.5s infinite;"></span> நேரலை 24/7 உதவி',

        ticker1: '100% தனியுரிமை பாதுகாப்பு - மொபைல் எண்கள் காட்டப்படாது',
        ticker2: 'உடனடி அழைப்பு & 4-விருப்ப அவசர வாட்ஸ்அப் பாட் ரிலே',
        ticker3: 'கீறல்கள், சேதங்கள் மற்றும் திடீர் டோயிங் அபராதங்களைத் தவிர்க்கவும்',
        ticker4: 'வீட்டு வாசலில் கார் சுத்தம் மற்றும் அருகில் உள்ள கேரேஜ் உதவி',
        ticker5: 'ஆப் பதிவிறக்கம் தேவையில்லை - எந்த போன் கேமராவிலும் ஸ்கேன் செய்யலாம்',

        how_tag: 'அழைக்க & வாட்ஸ்அப்பிற்கு ஸ்கேன் செய்யவும்',
        how_title: 'வாகன் சம்பார்க் 3 எளிய படிகளில் உங்களைப் பாதுகாக்கிறது',
        step1_title: '1. QR குறியீட்டை ஸ்கேன் செய்யவும்',
        step1_desc: 'உங்கள் காரின் விண்ட்ஷீல்டில் உள்ள QR ஸ்டிக்கரை எவரும் எந்த ஸ்மார்ட்போன் கேமரா மூலமாகவும் ஸ்கேன் செய்யலாம்.',
        step2_title: '2. உடனடியாக இணையுங்கள்',
        step2_desc: 'தனிப்பட்ட போன் எண்ணைக் காட்டாமல் வாகன உரிமையாளரை நேரடியாக அழைக்கவும் அல்லது வாட்ஸ்அப் பாட் விருப்பத்தைத் தேர்ந்தெடுக்கவும்.',
        step3_title: '3. பிரச்சனையைத் தீர்க்கவும்',
        step3_desc: 'தவறான பார்க்கிங், காரை நகர்த்துவது அல்லது அவசர சூழ்நிலைகளை விரைவாகவும் பாதுகாப்பாகவும் தீர்க்கவும்.',

        story_tag: 'ஜீரோ தனிப்பட்ட எண் பகிர்வு',
        story_title: 'வாகன் சம்பார்க் உங்களை எவ்வாறு பாதுகாப்பாக இணைக்கிறது',
        story_sub: 'எங்கள் அதிகாரப்பூர்வ வாட்ஸ்அப் நுழைவாயில் மூலம் பொதுமக்கள் வாகன உரிமையாளர்களுடன் இணைக்கப்படுகிறார்கள்!',
        scene1_badge: 'காட்சி 1',
        scene2_badge: 'காட்சி 2',
        scene3_badge: 'காட்சி 3',
        scene4_badge: 'காட்சி 4',
        scene1_alert: '🚫 வழி அடைக்கப்பட்டுள்ளது!',
        s1_title: 'வாகனம் தவறாக நிறுத்தப்பட்டுள்ளது',
        s1_desc: 'கார்ப்பார்க்கிங்கில் ஒரு கார் வழியை அடைக்கிறது. டிரைவர் அருகில் இல்லை.',
        s2_title: 'பொதுமக்கள் QR டாக்கை ஸ்கேன் செய்கிறார்கள்',
        s2_desc: 'காரின் விண்ட்ஷீல்டில் உள்ள வாகன் சம்பார்க் ஸ்டிக்கரை போன் கேமரா மூலம் ஸ்கேன் செய்கிறார்கள்.',
        s3_title: 'நிறுவன வாட்ஸ்அப் மாஸ்கிங்',
        s3_desc: 'அதிகாரப்பூர்வ வாட்ஸ்அப் பாட் மூலம் இணைக்கப்பட்டுள்ளது. தனிப்பட்ட எண் யாருக்கும் தெரியாது.',
        s4_title: 'ரகசிய எச்சரிக்கை & பிரச்சனை தீர்ந்தது!',
        s4_desc: 'உரிமையாளருக்கு வாட்ஸ்அப் எச்சரிக்கை வருகிறது, அவர்கள் காரை நகர்த்துகிறார்கள்.',

        hazards_tag: 'உண்மை உலக பாதுகாப்பு',
        hazards_title: 'வாகன் சம்பார்க் மூலம் தீர்க்கப்படும் அவசர பிரச்சனைகள்',
        h1_title: 'கீறல்கள் மற்றும் சேதங்களைத் தடுத்தல்',
        h1_desc: 'காரில் கீறல் போடுவதற்குப் பதிலாக பொதுமக்கள் உங்களுக்கு உடனடியாக வாட்ஸ்அப் அல்லது கால் செய்யலாம்.',
        h2_title: 'டோயிங் மற்றும் அபராதங்களைத் தடுத்தல்',
        h2_desc: 'ட்ராஃபிக் போலீஸ் அல்லது அண்டை வீட்டார் டோ ட்ரக்கை அழைப்பதற்கு முன் உங்களை எச்சரிக்கலாம்.',
        h3_title: 'வீட்டு வாசலில் கார் கழுவும் சேவை',
        h3_desc: 'உங்கள் பார்க்கிங் இடத்திலேயே தொழில்முறை கார் வாஷ் மற்றும் பாலிஷிங் பெறலாம்.',
        h4_title: 'கேரேஜ் மற்றும் மெக்கானிக் தீர்வு',
        h4_desc: 'அருகிலுள்ள கேரேஜ், பஞ்சர் பழுது மற்றும் பேட்டரி ஜம்ப்ஸ்டார்ட் வசதி.',
        h5_title: 'பேட்டரி வடிகால் மற்றும் ஹெட்லைட் ஆன்',
        h5_desc: 'ஹெட்லைட் ஆன் செய்து வைக்கப்பட்டிருந்தால் பேட்டரி தீரும் முன் எச்சரிக்கலாம்.',
        h6_title: 'ஜன்னல் திறந்திருப்பது & திருட்டு தடுப்பு',
        h6_desc: 'மழை பெய்யும் முன் ஜன்னல் திறந்திருந்தால் அண்டை வீட்டார் எச்சரிக்கலாம்.',
        h7_title: 'அவசர விபத்து சூழ்நிலைகள்',
        h7_desc: 'விபத்து நேரத்தில் பொதுமக்கள் உங்கள் குடும்பத்திற்கு உடனடியாக எச்சரிக்கை அனுப்பலாம்.',
        h8_title: 'ஆயில் கசிவு & விலங்குகள் பாதுகாப்பு',
        h8_desc: 'ஆயில் கசிவு அல்லது காரின் அடியில் விலங்குகள் சிக்கியிருந்தால் உடனடியாக புகாரளிக்கலாம்.',

        why_tag: 'தனியுரிமை ஒப்பீடு',
        why_title: 'வாகன் சம்பார்க் எதிராக சாதாரண பேப்பர் ஸ்டிக்கர்',
        old_method_lbl: '❌ பழைய முறை',
        old_title: 'பேப்பர் மொபைல் எண் ஸ்டிக்கர்',
        old_f1: 'தனிப்பட்ட போன் எண் அனைவருக்கும் வெளிப்படும்.',
        old_f2: 'தானியங்கி வாட்ஸ்அப் பாட் எச்சரிக்கைகள் இல்லை.',
        old_f3: 'புதிய ஸ்டிக்கர் வாங்காமல் எண்ணை மாற்ற முடியாது.',
        old_f4: 'மழையில் காகிதம் எளிதில் கிழிந்துவிடும்.',
        new_method_lbl: '✅ வாகன் சம்பார்க்',
        rec_badge: 'பரிந்துரைக்கப்பட்டது',
        new_title: 'ஸ்மார்ட் QR தனியுரிமை டேக்',
        new_f1: '100% போன் எண் தனியுரிமை.',
        new_f2: '4-விருப்ப அவசர வாட்ஸ்அப் பாட் & நேரடி அழைப்பு.',
        new_f3: 'இணையதளம் மூலம் எப்போது வேண்டுமானாலும் எண்ணை மாற்றலாம்.',
        new_f4: 'நீடித்த, மழைக்கு தாங்கும் தரமான டேக் கார்டு.',

        faq_tag: 'கேள்விகள் உள்ளதா?',
        faq_title: 'அடிக்கடி கேட்கப்படும் கேள்விகள்',
        faq1_q: 'வாகன் சம்பார்க் எனது போன் எண்ணை எவ்வாறு பாதுகாக்கிறது?',
        faq1_a: 'யாராவது ஸ்கேன் செய்யும்போது, ​​அழைப்பு மற்றும் வாட்ஸ்அப் செய்திகள் எங்கள் பாதுகாப்பான தளம் மூலம் அனுப்பப்படுகின்றன, எனவே உங்கள் உண்மையான எண் வெளிப்படாது.',
        faq2_q: 'ஸ்கேன் செய்பவர் ஆப் பதிவிறக்க வேண்டுமா?',
        faq2_a: 'இல்லை! சாதாரண போன் கேமரா அல்லது QR ஸ்கேனர் போதுமானது.',
        faq3_q: 'கார் வாஷ் மற்றும் கேரேஜ் சேவைகள் எவ்வாறு செயல்படுகின்றன?',
        faq3_a: 'வாகன் சம்பார்க் உங்கள் டேக்கை சரிபார்க்கப்பட்ட கார் வாஷ் மற்றும் கேரேஜ் சேவைகளுடன் இணைக்கிறது.',
        faq4_q: 'எனது போன் எண்ணை பின்னர் மாற்ற முடியுமா?',
        faq4_a: 'ஆம்! ஸ்டிக்கரை மாற்றாமல் எப்போது வேண்டுமானாலும் உங்கள் எண்ணை ஆன்லைனில் புதுப்பிக்கலாம்.',
        faq5_q: '4-விருப்ப அவசர வாட்ஸ்அப் பாட் எவ்வாறு செயல்படுகிறது?',
        faq5_a: 'வாட்ஸ்அப்பில் அரட்டையடிக்கும் போது, ​​பாட் 4 அவசர விருப்பங்களை வழங்கி உடனடியாக உங்களுக்கு எச்சரிக்கை அனுப்புகிறது!',

        contact_tag: 'தொடர்பு கொள்ள',
        contact_title: 'ஸ்மார்ட் QR டேக் ஆர்டர் செய்ய & விசாரணைகளுக்கு',
        contact_sub: 'ஆன்லைன் கட்டணம் தேவையில்லை. எங்களை நேரடியாக அழைக்கவும் அல்லது வாட்ஸ்அப் செய்யவும்!',
        channels_title: '<span style="width: 42px; height: 42px; border-radius: 12px; background: rgba(16, 185, 129, 0.12); color: #10b981; display: inline-flex; align-items: center; justify-content: center; font-size: 1.25rem;"><i class="fa-solid fa-headset"></i></span> நேரடி தொடர்பு வழிகள்',
        call_title: '<i class="fa-solid fa-bolt" style="color: #fef08a;"></i> உடனடி அழைப்பு உதவி எண்',
        wa_title: '<i class="fa-solid fa-comments" style="color: #ffedd5;"></i> 1-கிளிக் வாட்ஸ்அப் பாட்',
        wa_btn: 'வாட்ஸ்அப்பில் அரட்டையடிக்கவும்',
        email_title: 'மின்னஞ்சல் உதவி எண்',
        form_title: '<span style="width: 42px; height: 42px; border-radius: 12px; background: rgba(249, 115, 22, 0.12); color: #f97316; display: inline-flex; align-items: center; justify-content: center; font-size: 1.25rem;"><i class="fa-solid fa-paper-plane"></i></span> விசாரணையை அனுப்பவும்',
        form_name: 'முழு பெயர் <span class="required" style="color: #f43f5e;">*</span>',
        form_phone: 'மொபைல் / வாட்ஸ்அப் எண் <span class="required" style="color: #f43f5e;">*</span>',
        form_vehicle: 'வாகன பதிவு எண் <span style="font-weight: 500; color: #94a3b8; font-size: 0.78rem;">(விருப்பத்தேர்வு)</span>',
        form_city: 'நகரம் & மாநிலம் <span class="required" style="color: #f43f5e;">*</span>',
        form_qty: 'தேவையான டேகுகளின் எண்ணிக்கை',
        form_opt1: '🚗 1 டேக் (சொந்த கார்/பைக்)',
        form_opt2: '👨‍👩‍👧‍👦 5 டேகுகள் (குடும்ப வாகனங்கள்)',
        form_opt3: '🏢 10 டேகுகள் (சிறிய கடற்படை)',
        form_opt4: '🚛 50+ டேகுகள் (வணிக வாகனங்கள்)',
        form_msg: 'செய்தி / கூடுதல் விவரங்கள்',
        form_btn: '<i class="fa-solid fa-paper-plane"></i> இப்போது சமர்ப்பிக்கவும்',
        form_safe_notice: '<i class="fa-solid fa-shield-check" style="color: #10b981;"></i> 100% பாதுகாப்பானது & ரகசியமானது.',
        wa_widget_title: '<i class="fa-brands fa-whatsapp"></i> உடனடி இணைப்பு',
        wa_widget_msg: 'வணக்கம்! ஏதேனும் கேள்வி உள்ளதா அல்லது ஸ்மார்ட் டேக் ஆர்டர் செய்ய வேண்டுமா? வாட்ஸ்அப்பில் தொடர்பு கொள்ளவும்!',
        footer_p: 'ஸ்மார்ட் அவசர வாகன பாதுகாப்பு மற்றும் தனியுரிமை டேக் அமைப்பு • © 2026 Vehicle Sampark.',
        footer_l1: 'இது எப்படி செயல்படுகிறது',
        footer_l2: 'அவசர வழக்குகள்',
        footer_l3: 'ஏன் வாகன் சம்பார்க்',
        footer_l4: 'கேள்விகள்',
        footer_l5: 'தொடர்பு கொள்ள'
    },
    'bn': {
        announcement: '🚀 তাত্ক্ষণিক ১-ক্লিক হোয়াটসঅ্যাপ বট এবং কল রিলে • ১০০% মোবাইল নম্বর গোপনীয়তা সুরক্ষার নিশ্চয়তা!',
        nav_how: '<i class="fa-solid fa-list-check"></i> এটি কীভাবে কাজ করে',
        nav_hazards: '<i class="fa-solid fa-triangle-exclamation"></i> সমাধানকৃত সমস্যা',
        nav_why: '<i class="fa-solid fa-shield-halved"></i> কেন আমরা',
        nav_faq: '<i class="fa-solid fa-circle-question"></i> সাধারণ জিজ্ঞাসা',
        nav_contact: '<i class="fa-solid fa-envelope"></i> যোগাযোগ করুন',
        hero_badge: '<span class="live-dot-pulse"></span> <i class="fa-solid fa-shield-halved" style="color: #10b981;"></i> আপনার গাড়ি এবং ব্যক্তিগত গোপনীয়তা রক্ষা করুন',
        hero_title: '<span id="heroTitleMain">স্মার্ট QR স্ক্যান দিয়ে </span><span id="heroDynamicWord" class="gradient-text hero-dynamic-word">যে কেউ অবিলম্বে আপনার সাথে যোগাযোগ করতে পারে ⚡</span>',
        hero_sub: 'একটি সাধারণ স্ক্যান করে যে কেউ অবিলম্বে আপনার সাথে যোগাযোগ করতে পারে। আপনার নম্বর গোপন রেখে রিয়েল-টাইম হোয়াটসঅ্যাপ অ্যালার্ট এবং সরাসরি কল পান।',
        hero_btn_tag: '<i class="fa-solid fa-phone-volume"></i> স্মার্ট QR ট্যাগ পান',
        hero_btn_wa: '<i class="fa-brands fa-whatsapp" style="font-size: 1.3rem;"></i> হোয়াটসঅ্যাপে চ্যাট করুন',
        trust_priv_val: '১০০% গোপনীয়',
        trust_priv_lbl: 'ফোন নম্বর সুরক্ষিত',
        trust_relay_val: 'তাত্ক্ষণিক সংযোগ',
        trust_relay_lbl: 'কল ও হোয়াটসঅ্যাপ বট',
        trust_badge_val: 'স্মার্ট ব্যাজ',
        trust_badge_lbl: 'সেন্টার লোগো ট্যাগ কার্ড',
        float_left: '<i class="fa-solid fa-lock" style="color: #10b981;"></i> ১০০% ফোন গোপনীয়তা',
        float_right: '<i class="fa-solid fa-bolt" style="color: #f97316;"></i> ৩-সেকেন্ড সংযোগ',
        hero_call_pill: '<i class="fa-solid fa-phone-volume"></i> মালিককে কল করুন',
        hero_wa_pill: '<i class="fa-brands fa-whatsapp"></i> হোয়াটসঅ্যাপ বট',
        live_support: '<span style="width: 8px; height: 8px; border-radius: 50%; background: #10b981; display: inline-block; animation: pulseDot 1.5s infinite;"></span> লাইভ ২৪/৭ সহায়তা',

        ticker1: '১০০% গোপনীয়তা সুরক্ষিত - কোনো ফোন নম্বর প্রকাশ পায় না',
        ticker2: 'তাত্ক্ষণিক কল এবং ৪-অপশন জরুরি হোয়াটসঅ্যাপ বট রিলে',
        ticker3: 'গাড়িতে স্ক্র্যাচ, ভাঙচুর এবং হঠাৎ টোয়িং জরিমানা এড়ান',
        ticker4: 'ডোরস্টেপ কার ওয়াশ এবং কাছাকাছি গ্যারেজ সহায়তা',
        ticker5: 'কোনো অ্যাপ ডাউনলোড লাগবে না - যে কোনো ফোন ক্যামেরা দিয়ে স্ক্যান করুন',

        how_tag: 'কল এবং হোয়াটসঅ্যাপের জন্য স্ক্যান করুন',
        how_title: 'বাহন সম্পর্ক ৩টি সহজ ধাপে আপনাকে রক্ষা করে',
        step1_title: '১. QR কোড স্ক্যান করুন',
        step1_desc: 'যে কেউ আপনার গাড়ির উইন্ডশীল্ডের QR স্টিকার স্ক্যান করতে পারে — কোনো অ্যাপের প্রয়োজন নেই।',
        step2_title: '২. অবিলম্বে যুক্ত হন',
        step2_desc: 'ব্যক্তিগত নম্বর প্রকাশ না করে গাড়ির মালিককে কল করুন বা হোয়াটসঅ্যাপ বিকল্প বেছে নিন।',
        step3_title: '৩. সমস্যার সমাধান করুন',
        step3_desc: 'ভুল পার্কিং, গাড়ি সরানো বা জরুরি পরিস্থিতিতে দ্রুত ও নিরাপদে যোগাযোগ করুন।',

        story_tag: 'জিরো ব্যক্তিগত নম্বর শেয়ারিং',
        story_title: 'বাহন সম্পর্ক কীভাবে আপনাকে নিরাপদে যুক্ত করে',
        story_sub: 'আমাদের অফিসিয়াল হোয়াটসঅ্যাপ গেটওয়ের মাধ্যমে পথচারীরা গাড়ি মালিকদের সাথে যুক্ত হন!',
        scene1_badge: 'দৃশ্য ১',
        scene2_badge: 'দৃশ্য ২',
        scene3_badge: 'দৃশ্য ৩',
        scene4_badge: 'দৃশ্য ৪',
        scene1_alert: '🚫 রাস্তা বন্ধ!',
        s1_title: 'গাড়ি ভুলভাবে পার্ক করা হয়েছে',
        s1_desc: 'একটি গাড়ি পার্কিংয়ে অন্য গাড়ির পথ আটকে রেখেছে। ডাইভার দূরে আছেন।',
        s2_title: 'পথচারী QR ট্যাগ স্ক্যান করছেন',
        s2_desc: 'পথচারী ফোনের ক্যামেরা দিয়ে গাড়ির উইন্ডশীল্ডের স্টিকার স্ক্যান করছেন।',
        s3_title: 'কোম্পানি হোয়াটসঅ্যাপ মাস্কিং',
        s3_desc: 'অফিসিয়াল হোয়াটসঅ্যাপ বটের মাধ্যমে যুক্ত। ব্যক্তিগত নম্বর কখনো প্রকাশ পায় না।',
        s4_title: 'বেনামী অ্যালার্ট এবং সমাধান!',
        s4_desc: 'মালিক একটি জরুরি হোয়াটসঅ্যাপ অ্যালার্ট পান এবং গাড়িটি সরিয়ে নেন।',

        hazards_tag: 'বাস্তব জীবনের সুরক্ষা',
        hazards_title: 'বাহন সম্পর্ক দ্বারা সমাধানকৃত জরুরি সমস্যাসমূহ',
        h1_title: 'গাড়িতে স্ক্র্যাচ ও ক্ষতি রোধ',
        h1_desc: 'গাড়িতে দাগ ফেলার পরিবর্তে পথচারীরা আপনাকে অবিলম্বে হোয়াটসঅ্যাপ বা কল করতে পারেন।',
        h2_title: 'টোয়িং এবং জরিমানা এড়ান',
        h2_desc: 'ট্রাফিক পুলিশ বা প্রতিবেশীরা গাড়ি সরানোর জন্য স্টিকার স্ক্যান করে সতর্ক করতে পারেন।',
        h3_title: 'ডোরস্টেপ কার ওয়াশ সেবা',
        h3_desc: 'আপনার পার্কিংয়েই পেশাদার কার ওয়াশিং এবং পলিশিং পান।',
        h4_title: 'গ্যারেজ এবং মেকানিক সমাধান',
        h4_desc: 'নিকটস্থ গ্যারেজ, পাংচার মেরামত এবং ব্যাটারি জাম্পস্টার্টের সুবিধা।',
        h5_title: 'ব্যাটারি ড্রেন এবং হেডলাইট অন',
        h5_desc: 'লাইটিং অন থাকলে ব্যাটারি শেষ হওয়ার আগেই প্রতিবেশীরা সতর্ক করতে পারেন।',
        h6_title: 'জানালা খোলা থাকা ও চুরি রোধ',
        h6_desc: 'বৃষ্টির আগে জানালা খোলা থাকলে প্রতিবেশীরা সতর্ক করতে পারেন।',
        h7_title: 'জরুরি দুর্ঘটনা পরিস্থিতি',
        h7_desc: 'দুর্ঘটনার সময় পথচারীরা আপনার পরিবারকে অবিলম্বে অ্যালার্ট পাঠাতে পারেন।',
        h8_title: 'তেল লিক এবং পশুদের নিরাপত্তা',
        h8_desc: 'ইঞ্জিনের নিচে পশু আটকে থাকলে বা তেল লিক হলে তাৎক্ষণিক রিপোর্ট করা যায়।',

        why_tag: 'গোপনীয়তা তুলনা',
        why_title: 'বাহন সম্পর্ক বনাম সাধারণ কাগজের স্টিকার',
        old_method_lbl: '❌ পুরনো পদ্ধতি',
        old_title: 'কাগজের মোবাইল নম্বর স্টিকার',
        old_f1: 'ব্যক্তিগত নম্বর সবার কাছে প্রকাশ পায়।',
        old_f2: 'কোনো স্বয়ংক্রিয় হোয়াটসঅ্যাপ অ্যালার্ট নেই।',
        old_f3: 'নতুন স্টিকার না কিনে নম্বর পরিবর্তন করা যায় না।',
        old_f4: 'বৃষ্টিতে কাগজ সহজে ছিঁড়ে যায়।',
        new_method_lbl: '✅ বাহন সম্পর্ক',
        rec_badge: 'সুপারিশকৃত',
        new_title: 'স্মার্ট QR গোপনীয়তা ট্যাগ',
        new_f1: '১০০% ফোন নম্বর গোপনীয়তা।',
        new_f2: '৪-অপশন জরুরি হোয়াটসঅ্যাপ বট এবং কল।',
        new_f3: 'ওয়েবসাইট থেকে যেকোনো সময় নম্বর আপডেট করুন।',
        new_f4: 'স্থায়ী, ওয়াটারপ্রুফ টেকসই ট্যাগ কার্ড।',

        faq_tag: 'জিজ্ঞাসা আছে?',
        faq_title: 'সাধারণ জিজ্ঞাসা',
        faq1_q: 'বাহন সম্পর্ক কীভাবে আমার নম্বর সুরক্ষিত রাখে?',
        faq1_a: 'যখন কেউ স্ক্যান করে, তখন আমাদের সুরক্ষিত প্ল্যাটফর্মের মাধ্যমে কল ও মেসেজ পাঠানো হয়, ফলে আসল নম্বর প্রকাশ পায় না।',
        faq2_q: 'স্ক্যানকারীকে কি অ্যাপ ডাউনলোড করতে হবে?',
        faq2_a: 'না! যে কোনো সাধারণ ফোন ক্যামেরা দিয়েই স্ক্যান করা যায়।',
        faq3_q: 'কার ওয়াশ এবং গ্যারেজ সেবা কীভাবে কাজ করে?',
        faq3_a: 'বাহন সম্পর্ক আপনার ট্যাগকে যাচাইকৃত কার ওয়াশ এবং মেকানিক সেবার সাথে যুক্ত করে।',
        faq4_q: 'আমি কি পরে নম্বর পরিবর্তন করতে পারি?',
        faq4_a: 'হ্যাঁ! স্টিকার না বদলে যেকোনো সময় অনলাইন নম্বর আপডেট করা যায়।',
        faq5_q: '৪-অপশন জরুরি হোয়াটসঅ্যাপ বট কীভাবে কাজ করে?',
        faq5_a: 'হোয়াটসঅ্যাপে বট ৪টি জরুরি অপশন দেয় এবং নির্বাচন করলে আপনাকে অবিলম্বে মেসেজ পাঠায়!',

        contact_tag: 'যোগাযোগ করুন',
        contact_title: 'স্মার্ট QR ট্যাগ অর্ডার ও জিজ্ঞাসার জন্য',
        contact_sub: 'কোনো অনলাইন পেমেন্টের প্রয়োজন নেই। সরাসরি কল বা হোয়াটসঅ্যাপ করুন!',
        channels_title: '<span style="width: 42px; height: 42px; border-radius: 12px; background: rgba(16, 185, 129, 0.12); color: #10b981; display: inline-flex; align-items: center; justify-content: center; font-size: 1.25rem;"><i class="fa-solid fa-headset"></i></span> সরাসরি যোগাযোগের মাধ্যম',
        call_title: '<i class="fa-solid fa-bolt" style="color: #fef08a;"></i> তাত্ক্ষণিক কল হেল্পলাইন',
        wa_title: '<i class="fa-solid fa-comments" style="color: #ffedd5;"></i> ১-ক্লিক হোয়াটসঅ্যাপ বট',
        wa_btn: 'হোয়াটসঅ্যাপে চ্যাট করুন',
        email_title: 'ইমেল সহায়তা লাইন',
        form_title: '<span style="width: 42px; height: 42px; border-radius: 12px; background: rgba(249, 115, 22, 0.12); color: #f97316; display: inline-flex; align-items: center; justify-content: center; font-size: 1.25rem;"><i class="fa-solid fa-paper-plane"></i></span> অনুসন্ধান পাঠান',
        form_name: 'পূর্ণ নাম <span class="required" style="color: #f43f5e;">*</span>',
        form_phone: 'মোবাইল / হোয়াটসঅ্যাপ নম্বর <span class="required" style="color: #f43f5e;">*</span>',
        form_vehicle: 'গাড়ির নম্বর <span style="font-weight: 500; color: #94a3b8; font-size: 0.78rem;">(ঐচ্ছিক)</span>',
        form_city: 'শহর ও রাজ্য <span class="required" style="color: #f43f5e;">*</span>',
        form_qty: 'প্রয়োজনীয় ট্যাগের সংখ্যা',
        form_opt1: '🚗 ১টি ট্যাগ (ব্যক্তিগত গাড়ি/বাইক)',
        form_opt2: '👨‍👩‍👧‍👦 ৫টি ট্যাগ (পারিবারিক গাড়ি)',
        form_opt3: '🏢 ১০টি ট্যাগ (ছোট বহর)',
        form_opt4: '🚛 ৫০+ ট্যাগ (বাণিজ্যিক বহর)',
        form_msg: 'বার্তা / অতিরিক্ত তথ্য',
        form_btn: '<i class="fa-solid fa-paper-plane"></i> এখনই জমা দিন',
        form_safe_notice: '<i class="fa-solid fa-shield-check" style="color: #10b981;"></i> ১০০% নিরাপদ ও গোপনীয়।',
        wa_widget_title: '<i class="fa-brands fa-whatsapp"></i> তাত্ক্ষণিক সংযোগ',
        wa_widget_msg: 'নমস্কার! কোনো প্রশ্ন আছে বা ট্যাগ অর্ডার করতে চান? হোয়াটসঅ্যাপে চ্যাট করুন!',
        footer_p: 'স্মার্ট জরুরি যানবাহন সুরক্ষা ও গোপনীয়তা ট্যাগ সিস্টেম • © 2026 Vehicle Sampark.',
        footer_l1: 'এটি কীভাবে কাজ করে',
        footer_l2: 'জরুরি ঘটনা',
        footer_l3: 'কেন বাহন সম্পর্ক',
        footer_l4: 'জিজ্ঞাসা',
        footer_l5: 'যোগাযোগ করুন'
    },
    'ml': {
        announcement: '🚀 തൽക്ഷണ 1-ക്ലിക്ക് വാട്ട്സ്ആപ്പ് ബോട്ട് & കോൾ റിലേ • 100% മൊബൈൽ നമ്പർ സ്വകാര്യതാ സംരക്ഷണം ഉറപ്പ്!',
        nav_how: '<i class="fa-solid fa-list-check"></i> ഇത് എങ്ങനെ പ്രവർത്തിക്കുന്നു',
        nav_hazards: '<i class="fa-solid fa-triangle-exclamation"></i> പരിഹരിച്ച പ്രശ്നങ്ങൾ',
        nav_why: '<i class="fa-solid fa-shield-halved"></i> എന്തുകൊണ്ട് ഞങ്ങൾ',
        nav_faq: '<i class="fa-solid fa-circle-question"></i> സംശയങ്ങൾ',
        nav_contact: '<i class="fa-solid fa-envelope"></i> ബന്ധപ്പെടുക',
        hero_badge: '<span class="live-dot-pulse"></span> <i class="fa-solid fa-shield-halved" style="color: #10b981;"></i> നിങ്ങളുടെ വാഹനവും സ്വകാര്യതയും സംരക്ഷിക്കുക',
        hero_title: '<span id="heroTitleMain">സ്മാർട്ട് QR സ്കാൻ വഴി </span><span id="heroDynamicWord" class="gradient-text hero-dynamic-word">ആർക്കും നിങ്ങളെ ഉടനടി ബന്ധപ്പെടാം ⚡</span>',
        hero_sub: 'ഒരു ലളിതമായ സ്കാനിലൂടെ ആർക്കും നിങ്ങളെ ഉടനടി ബന്ധപ്പെടാം. നിങ്ങളുടെ നമ്പർ വെളിപ്പെടുത്താതെ തത്സമയ വാട്ട്സ്ആപ്പ് സന്ദേശങ്ങളും കോളുകളും നേടുക.',
        hero_btn_tag: '<i class="fa-solid fa-phone-volume"></i> സ്മാർട്ട് QR ടാഗ് നേടുക',
        hero_btn_wa: '<i class="fa-brands fa-whatsapp" style="font-size: 1.3rem;"></i> വാട്ട്സ്ആപ്പിൽ ചാറ്റ് ചെയ്യുക',
        trust_priv_val: '100% രഹസ്യം',
        trust_priv_lbl: 'ഫോൺ നമ്പർ സുരക്ഷിതം',
        trust_relay_val: 'ഉടനടി ബന്ധപ്പെടാം',
        trust_relay_lbl: 'കോൾ & വാട്ട്സ്ആപ്പ് ബോട്ട്',
        trust_badge_val: 'സ്മാർട്ട് ബാഡ്ജ്',
        trust_badge_lbl: 'സെന്റർ ലോഗോ ടാഗ് കാർഡ്',
        float_left: '<i class="fa-solid fa-lock" style="color: #10b981;"></i> 100% ഫോൺ സ്വകാര്യത',
        float_right: '<i class="fa-solid fa-bolt" style="color: #f97316;"></i> 3-സെക്കൻഡ് കണക്ട്',
        hero_call_pill: '<i class="fa-solid fa-phone-volume"></i> ഉടമയെ വിളിക്കുക',
        hero_wa_pill: '<i class="fa-brands fa-whatsapp"></i> വാട്ട്സ്ആപ്പ് ബോട്ട്',
        live_support: '<span style="width: 8px; height: 8px; border-radius: 50%; background: #10b981; display: inline-block; animation: pulseDot 1.5s infinite;"></span> തത്സമയ 24/7 സഹായം',

        ticker1: '100% സ്വകാര്യതാ സംരക്ഷണം - മൊബൈൽ നമ്പർ കാണിക്കില്ല',
        ticker2: 'ഉടനടിയുള്ള കോളുകളും 4-ഓപ്ഷൻ എമർജൻസി വാട്ട്സ്ആപ്പ് ബോട്ടും',
        ticker3: 'വാഹനത്തിലെ വരമ്പുകളും ടോയിംഗ് പിഴകളും ഒഴിവാക്കുക',
        ticker4: 'വീട്ടുപടിക്കൽ കാർ കഴുകലും ഗാരേജ് സഹായവും',
        ticker5: 'ആപ്പ് ഡൗൺലോഡ് ആവശ്യമില്ല - ഏത് ഫോൺ ക്യാമറയിലും സ്കാൻ ചെയ്യാം',

        how_tag: 'വിളിക്കാനും വാട്ട്സ്ആപ്പിനും സ്കാൻ ചെയ്യുക',
        how_title: 'വാഹൻ സമ്പർക്ക് 3 എളുപ്പ ഘട്ടങ്ങളിലൂടെ നിങ്ങളെ സംരക്ഷിക്കുന്നു',
        step1_title: '1. QR കോഡ് സ്കാൻ ചെയ്യുക',
        step1_desc: 'നിങ്ങളുടെ കാറിലെ QR സ്റ്റിക്കർ ഏത് സ്മാർട്ട്ഫോൺ ക്യാമറ ഉപയോഗിച്ചും ആർക്കും സ്കാൻ ചെയ്യാം.',
        step2_title: '2. ഉടനടി ബന്ധപ്പെടുക',
        step2_desc: 'സ്വകാര്യ നമ്പർ വെളിപ്പെടുത്താതെ വാഹന ഉടമയെ നേരിട്ട് വിളിക്കുകയോ വാട്ട്സ്ആപ്പ് ഉപയോഗിക്കുകയോ ചെയ്യുക.',
        step3_title: '3. പ്രശ്നം പരിഹരിക്കുക',
        step3_desc: 'പാർക്കിംഗ് പ്രശ്നങ്ങളും അടിയന്തിര സാഹചര്യങ്ങളും വേഗത്തിൽ പരിഹരിക്കുക.',

        story_tag: 'സീറോ വ്യക്തിഗത നമ്പർ പങ്കിടൽ',
        story_title: 'വാഹൻ സമ്പർക്ക് നിങ്ങളെ എങ്ങനെ സുരക്ഷിതമായി ബന്ധിപ്പിക്കുന്നു',
        story_sub: 'ഞങ്ങളുടെ ഔദ്യോഗിക വാട്ട്സ്ആപ്പ് വഴി വഴിപോക്കർക്ക് വാഹന ഉടമകളുമായി ബന്ധപ്പെടാം!',
        scene1_badge: 'രംഗം 1',
        scene2_badge: 'രംഗം 2',
        scene3_badge: 'രംഗം 3',
        scene4_badge: 'രംഗം 4',
        scene1_alert: '🚫 വഴി തടസ്സപ്പെട്ടു!',
        s1_title: 'വാഹനം തെറ്റായി പാർക്ക് ചെയ്തു',
        s1_desc: 'പാർക്കിംഗിൽ ഒരു കാർ വഴി തടസ്സപ്പെടുത്തുന്നു. ഡ്രൈവർ സ്ഥലത്തില്ല.',
        s2_title: 'വഴിപോക്കൻ QR ടാഗ് സ്കാൻ ചെയ്യുന്നു',
        s2_desc: 'കാറിലെ വാഹൻ സമ്പർക്ക് സ്റ്റിക്കർ ഫോൺ ക്യാമറ ഉപയോഗിച്ച് സ്കാൻ ചെയ്യുന്നു.',
        s3_title: 'കമ്പനി വാട്ട്സ്ആപ്പ് മാസ്കിംഗ്',
        s3_desc: 'ഔദ്യോഗിക വാട്ട്സ്ആപ്പ് ബോട്ട് വഴി ബന്ധിപ്പിച്ചു. ഫോൺ നമ്പർ രഹസ്യമായിരിക്കും.',
        s4_title: 'അജ്ഞാത മുന്നറിയിപ്പും പരിഹാരവും!',
        s4_desc: 'ഉടമയ്ക്ക് വാട്ട്സ്ആപ്പ് സന്ദേശം ലഭിക്കുകയും കാർ മാറ്റുകയും ചെയ്യുന്നു.',

        hazards_tag: 'യഥാർത്ഥ ലോക സംരക്ഷണം',
        hazards_title: 'വാഹൻ സമ്പർക്ക് പരിഹരിക്കുന്ന പ്രധാന പ്രശ്നങ്ങൾ',
        h1_title: 'വരമ്പുകളും കേടുപാടുകളും തടയൽ',
        h1_desc: 'കാറിൽ വരയുന്നതിന് പകരം ആളുകൾക്ക് നിങ്ങളെ ഉടനടി വാട്ട്സ്ആപ്പിൽ ബന്ധപ്പെടാം.',
        h2_title: 'ടോയിംഗും പിഴകളും ഒഴിവാക്കുക',
        h2_desc: 'പോലീസിനോ അയൽക്കാർക്കോ ടോ ചെയ്യുന്നതിന് മുമ്പ് നിങ്ങളെ അറിയിക്കാം.',
        h3_title: 'കാർ കഴുകൽ സേവനം',
        h3_desc: 'നിങ്ങളുടെ പാർക്കിംഗിൽ തന്നെ പ്രൊഫഷണൽ കാർ വാഷിംഗ് സേവനം.',
        h4_title: 'ഗാരേജും മെക്കാനിക് സഹായവും',
        h4_desc: 'അടുത്തുള്ള ഗാരേജ്, പഞ്ചർ റിപ്പയർ, ബാറ്ററി ജമ്പ്സ്റ്റാർട്ട് സേവനം.',
        h5_title: 'ലൈറ്റ് ഓൺ ചെയ്ത് ബാറ്ററി തീരുന്നത് തടയൽ',
        h5_desc: 'ലൈറ്റ് ഓൺ ആണെങ്കിൽ ബാറ്ററി തീരുന്നതിന് മുമ്പ് ആളുകൾക്ക് അറിയിക്കാം.',
        h6_title: 'ഗ്ലാസ് തുറന്നിരിക്കുന്നത് തടയൽ',
        h6_desc: 'മഴ പെയ്യുന്നതിന് മുമ്പ് ഗ്ലാസ് തുറന്നിട്ടുണ്ടെങ്കിൽ അയൽക്കാർക്ക് അറിയിക്കാം.',
        h7_title: 'അടിയന്തിര അപകട സാഹചര്യങ്ങൾ',
        h7_desc: 'അപകട സമയത്ത് വഴിപോക്കർക്ക് നിങ്ങളുടെ കുടുംബത്തെ ഉടനടി വിവരമറിയിക്കാം.',
        h8_title: 'ഓയിൽ ചോർച്ചയും മൃഗങ്ങളുടെ സുരക്ഷയും',
        h8_desc: 'ഓയിൽ ചോർച്ചയോ കാറിനടിയിൽ മൃഗങ്ങൾ കുടുങ്ങിയാലോ ഉടനടി വിവരമറിയിക്കാം.',

        why_tag: 'സ്വകാര്യതാ താരതമ്യം',
        why_title: 'വാഹൻ സമ്പർക്ക് വിഎസ് പേപ്പർ സ്റ്റിക്കർ',
        old_method_lbl: '❌ പഴയ രീതി',
        old_title: 'പേപ്പർ മൊബൈൽ നമ്പർ സ്റ്റിക്കർ',
        old_f1: 'ഫോൺ നമ്പർ എല്ലാവർക്കും കാണാം.',
        old_f2: 'വാട്ട്സ്ആപ്പ് സന്ദേശങ്ങൾ ഇല്ല.',
        old_f3: 'പുതിയ സ്റ്റിക്കർ ഇല്ലാതെ നമ്പർ മാറ്റാനാകില്ല.',
        old_f4: 'മഴയത്ത് പേപ്പർ നശിച്ചുപോകും.',
        new_method_lbl: '✅ വാഹൻ സമ്പർക്ക്',
        rec_badge: 'ഉത്തമം',
        new_title: 'സ്മാർട്ട് QR സ്വകാര്യതാ ടാഗ്',
        new_f1: '100% ഫോൺ നമ്പർ സ്വകാര്യത.',
        new_f2: '4-ഓപ്ഷൻ വാട്ട്സ്ആപ്പ് ബോട്ടും കോളും.',
        new_f3: 'വെബ്സൈറ്റ് വഴി എപ്പോൾ വേണമെങ്കിലും നമ്പർ മാറ്റാം.',
        new_f4: 'നീണ്ടുനിൽക്കുന്ന വാട്ടർപ്രൂഫ് ടാഗ് കാർഡ്.',

        faq_tag: 'സംശയങ്ങൾ ഉണ്ടോ?',
        faq_title: 'പതിവ് ചോദ്യങ്ങൾ',
        faq1_q: 'വാഹൻ സമ്പർക്ക് നമ്പർ എങ്ങനെ സുരക്ഷിതമാക്കുന്നു?',
        faq1_a: 'ആരെങ്കിലും സ്കാൻ ചെയ്യുമ്പോൾ കോളുകളും സന്ദേശങ്ങളും ഞങ്ങളുടെ പ്ലാറ്റ്ഫോം വഴിയാണ് പോകുന്നത്.',
        faq2_q: 'സ്കാൻ ചെയ്യുന്നയാൾ ആപ്പ് ഡൗൺലോഡ് ചെയ്യണമെന്നുണ്ടോ?',
        faq2_a: 'ഇല്ല! ഏത് സാധാരണ ഫോൺ ക്യാമറയും ഉപയോഗിക്കാം.',
        faq3_q: 'കാർ വാഷും ഗാരേജ് സേവനവും എങ്ങനെ പ്രവർത്തിക്കുന്നു?',
        faq3_a: 'വാഹൻ സമ്പർക്ക് നിങ്ങളുടെ ടാഗിനെ അംഗീകൃത ഗാരേജ് സേവനങ്ങളുമായി ബന്ധിപ്പിക്കുന്നു.',
        faq4_q: 'പിന്നീട് ഫോൺ നമ്പർ മാറ്റാനാകുമോ?',
        faq4_a: 'ഉവ്വ്! സ്റ്റിക്കർ മാറ്റാതെ എപ്പോൾ വേണമെങ്കിലും ഓൺലൈനായി നമ്പർ മാറ്റാം.',
        faq5_q: '4-ഓപ്ഷൻ വാട്ട്സ്ആപ്പ് ബോട്ട് എങ്ങനെ പ്രവർത്തിക്കുന്നു?',
        faq5_a: 'വാട്ട്സ്ആപ്പിൽ ബോട്ട് 4 ഓപ്ഷനുകൾ നൽകുകയും ഉടനടി സന്ദേശം അയക്കുകയും ചെയ്യുന്നു!',

        contact_tag: 'ബന്ധപ്പെടുക',
        contact_title: 'സ്മാർട്ട് QR ടാഗ് ഓർഡർ ചെയ്യാനും വിവരങ്ങൾക്കും',
        contact_sub: 'ഓൺലൈൻ പേയ്മെന്റ് ആവശ്യമില്ല. നേരിട്ട് വിളിക്കുകയോ വാട്ട്സ്ആപ്പ് ചെയ്യുകയോ ചെയ്യുക!',
        channels_title: '<span style="width: 42px; height: 42px; border-radius: 12px; background: rgba(16, 185, 129, 0.12); color: #10b981; display: inline-flex; align-items: center; justify-content: center; font-size: 1.25rem;"><i class="fa-solid fa-headset"></i></span> നേരിട്ടുള്ള ബന്ധപ്പെടൽ മാർഗ്ഗങ്ങൾ',
        call_title: '<i class="fa-solid fa-bolt" style="color: #fef08a;"></i> തൽക്ഷണ കോൾ ഹെൽപ്പ് ലൈൻ',
        wa_title: '<i class="fa-solid fa-comments" style="color: #ffedd5;"></i> 1-ക്ലിക്ക് വാട്ട്സ്ആപ്പ് ബോട്ട്',
        wa_btn: 'വാട്ട്സ്ആപ്പിൽ ചാറ്റ് ചെയ്യുക',
        email_title: 'ഇമെയിൽ സഹായ ലൈൻ',
        form_title: '<span style="width: 42px; height: 42px; border-radius: 12px; background: rgba(249, 115, 22, 0.12); color: #f97316; display: inline-flex; align-items: center; justify-content: center; font-size: 1.25rem;"><i class="fa-solid fa-paper-plane"></i></span> സന്ദേശം അയക്കുക',
        form_name: 'പൂർണ്ണമായ പേര് <span class="required" style="color: #f43f5e;">*</span>',
        form_phone: 'മൊബൈൽ / വാട്ട്സ്ആപ്പ് നമ്പർ <span class="required" style="color: #f43f5e;">*</span>',
        form_vehicle: 'വാഹന രജിസ്ട്രേഷൻ നമ്പർ <span style="font-weight: 500; color: #94a3b8; font-size: 0.78rem;">(നിർബന്ധമില്ല)</span>',
        form_city: 'സ്ഥലവും സംസ്ഥാനവും <span class="required" style="color: #f43f5e;">*</span>',
        form_qty: 'ആവശ്യമുള്ള ടാഗുകളുടെ എണ്ണം',
        form_opt1: '🚗 1 ടാഗ് (സ്വന്തം കാർ/ബൈക്ക്)',
        form_opt2: '👨‍👩‍👧‍👦 5 ടാഗുകൾ (കുടുംബ വാഹനങ്ങൾ)',
        form_opt3: '🏢 10 ടാഗുകൾ (ചെറിയ ബിസിനസ്സ്)',
        form_opt4: '🚛 50+ ടാഗുകൾ (വലിയ ബിസിനസ്സ്)',
        form_msg: 'സന്ദേശം / കൂടുതൽ വിവരങ്ങൾ',
        form_btn: '<i class="fa-solid fa-paper-plane"></i> ഇപ്പോൾ സമർപ്പിക്കുക',
        form_safe_notice: '<i class="fa-solid fa-shield-check" style="color: #10b981;"></i> 100% സുരക്ഷിതവും രഹസ്യവും.',
        wa_widget_title: '<i class="fa-brands fa-whatsapp"></i> തൽക്ഷണ ബന്ധപ്പെടൽ',
        wa_widget_msg: 'നമസ്കാരം! സംശയങ്ങൾ ഉണ്ടോ അതോ ടാഗ് ഓർഡർ ചെയ്യണമെന്നുണ്ടോ? ചാറ്റ് ചെയ്യുക!',
        footer_p: 'സ്മാർട്ട് അടിയന്തിര വാഹന സുരക്ഷാ ടാഗ് സിസ്റ്റം • © 2026 Vehicle Sampark.',
        footer_l1: 'ഇത് എങ്ങനെ പ്രവർത്തിക്കുന്നു',
        footer_l2: 'അടിയന്തിര കേസുകൾ',
        footer_l3: 'എന്തുകൊണ്ട് വാഹൻ സമ്പർക്ക്',
        footer_l4: 'ചോദ്യങ്ങൾ',
        footer_l5: 'ബന്ധപ്പെടുക'
    },
    'kn': {
        announcement: '🚀 ക്ഷണಿಕ 1-ಕ್ಲಿಕ್ ವಾಟ್ಸಾಪ್ ಬೋಟ್ & ಕಾಲ್ ರಿಲೇ • 100% ಮೊಬೈಲ್ ಸಂಖ್ಯೆ ಗೌಪ್ಯತೆ ರಕ್ಷಣೆ ಖಾತ್ರಿ!',
        nav_how: '<i class="fa-solid fa-list-check"></i> ಇದು ಹೇಗೆ ಕೆಲಸ ಮಾಡುತ್ತದೆ',
        nav_hazards: '<i class="fa-solid fa-triangle-exclamation"></i> ಪರಿಹರಿಸಲಾದ ಸಮಸ್ಯೆಗಳು',
        nav_why: '<i class="fa-solid fa-shield-halved"></i> ಏಕೆ ನಾವು',
        nav_faq: '<i class="fa-solid fa-circle-question"></i> ಪ್ರಶ್ನೆಗಳು',
        nav_contact: '<i class="fa-solid fa-envelope"></i> ಸಂಪರ್ಕಿಸಿ',
        hero_badge: '<span class="live-dot-pulse"></span> <i class="fa-solid fa-shield-halved" style="color: #10b981;"></i> ನಿಮ್ಮ ವಾಹನ ಮತ್ತು ವೈಯಕ್ತಿಕ ಗೌಪ್ಯತೆಯನ್ನು ರಕ್ಷಿಸಿ',
        hero_title: '<span id="heroTitleMain">ಸ್ಮಾರ್ಟ್ QR ಸ್ಕ್ಯಾನ್ ಮೂಲಕ </span><span id="heroDynamicWord" class="gradient-text hero-dynamic-word">ಯಾರು ಬೇಕಾದರೂ ನಿಮ್ಮನ್ನು ತಕ್ಷಣ ಸಂಪರ್ಕಿಸಬಹುದು ⚡</span>',
        hero_sub: 'ಒಂದು ಸರಳ ಸ್ಕ್ಯಾನ್ ಮೂಲಕ ಯಾರಾದರೂ ನಿಮ್ಮನ್ನು ತಕ್ಷಣ ಸಂಪರ್ಕಿಸಬಹುದು. ನಿಮ್ಮ ಮೊಬೈಲ್ ಸಂಖ್ಯೆಯನ್ನು ಬಹಿರಂಗಪಡಿಸದೆ ವಾಟ್ಸಾಪ್ ಸಂದೇಶಗಳು ಮತ್ತು ಕರೆಗಳನ್ನು ಪಡೆಯಿರಿ.',
        hero_btn_tag: '<i class="fa-solid fa-phone-volume"></i> ಸ್ಮಾರ್ಟ್ QR ಟ್ಯಾಗ್ ಪಡೆಯಿರಿ',
        hero_btn_wa: '<i class="fa-brands fa-whatsapp" style="font-size: 1.3rem;"></i> ವಾಟ್ಸಾಪ್‌ನಲ್ಲಿ ಚಾಟ್ ಮಾಡಿ',
        trust_priv_val: '100% ರಹಸ್ಯ',
        trust_priv_lbl: 'ಮೊಬೈಲ್ ಸಂಖ್ಯೆ ಸುರಕ್ಷಿತ',
        trust_relay_val: 'ತಕ್ಷಣದ ಸಂಪರ್ಕ',
        trust_relay_lbl: 'ಕಾಲ್ & ವಾಟ್ಸಾಪ್ ಬೋಟ್',
        trust_badge_val: 'ಸ್ಮಾರ್ಟ್ ಬ್ಯಾಡ್ಜ್',
        trust_badge_lbl: 'ಸೆಂಟರ್ ಲೋಗೋ ಟ್ಯಾಗ್ ಕಾರ್ಡ್',
        float_left: '<i class="fa-solid fa-lock" style="color: #10b981;"></i> 100% ಫೋನ್ ಗೌಪ್ಯತೆ',
        float_right: '<i class="fa-solid fa-bolt" style="color: #f97316;"></i> 3-ಸೆಕೆಂಡ್ ಕನೆಕ್ಟ್',
        hero_call_pill: '<i class="fa-solid fa-phone-volume"></i> ಮಾಲೀಕರಿಗೆ ಕರೆ ಮಾಡಿ',
        hero_wa_pill: '<i class="fa-brands fa-whatsapp"></i> ವಾಟ್ಸಾಪ್ ಬೋಟ್',
        live_support: '<span style="width: 8px; height: 8px; border-radius: 50%; background: #10b981; display: inline-block; animation: pulseDot 1.5s infinite;"></span> ನೇರ 24/7 ನೆರವು',

        ticker1: '100% ಗೌಪ್ಯತೆ ರಕ್ಷಣೆ - ಯಾವುದೇ ಮೊಬೈಲ್ ಸಂಖ್ಯೆ ತೋರಿಸುವುದಿಲ್ಲ',
        ticker2: 'ತಕ್ಷಣದ ಕರೆ ಮತ್ತು 4-ಆಯ್ಕೆಯ ತುರ್ತು ವಾಟ್ಸಾಪ್ ಬೋಟ್',
        ticker3: 'ವಾಹನಕ್ಕೆ ಗೀರುಗಳು ಮತ್ತು ಟೋಯಿಂಗ್ ದಂಡಗಳನ್ನು ತಡೆಯಿರಿ',
        ticker4: 'ಮನೆಯ ಬಳಿಯೇ ಕಾರ್ ವಾಶ್ ಮತ್ತು ಗ್ಯಾರೇಜ್ ನೆರವು',
        ticker5: 'ಆ್ಯಪ್ ಡೌನ್‌ಲೋಡ್ ಅಗತ್ಯವಿಲ್ಲ - ಯಾವುದೇ ಫೋನ್ ಕ್ಯಾಮೆರಾದಲ್ಲಿ ಸ್ಕ್ಯಾನ್ ಮಾಡಿ',

        how_tag: 'ಕರೆ ಮತ್ತು ವಾಟ್ಸಾಪ್‌ಗಾಗಿ ಸ್ಕ್ಯಾನ್ ಮಾಡಿ',
        how_title: 'ವಾಹನ್ ಸಂಪರ್ಕ್ 3 ಸುಲಭ ಹಂತಗಳಲ್ಲಿ ನಿಮ್ಮನ್ನು ರಕ್ಷಿಸುತ್ತದೆ',
        step1_title: '1. QR ಕೋಡ್ ಸ್ಕ್ಯಾನ್ ಮಾಡಿ',
        step1_desc: 'ನಿಮ್ಮ ಕಾರಿನ ವಿಂಡ್‌ಶೀಲ್ಡ್‌ನಲ್ಲಿರುವ QR ಸ್ಟಿಕ್ಕರ್ ಅನ್ನು ಯಾರಾದರೂ ಸ್ಕ್ಯಾನ್ ಮಾಡಬಹುದು.',
        step2_title: '2. ತಕ್ಷಣ ಸಂಪರ್ಕಿಸಿ',
        step2_desc: 'ಖಾಸಗಿ ಸಂಖ್ಯೆಯನ್ನು ತೋರಿಸದೆ ವಾಹನ ಮಾಲೀಕರಿಗೆ ನೇರವಾಗಿ ಕರೆ ಮಾಡಿ ಅಥವಾ ವಾಟ್ಸಾಪ್ ಆಯ್ಕೆಮಾಡಿ.',
        step3_title: '3. ಸಮಸ್ಯೆಯನ್ನು ಪರಿಹರಿಸಿ',
        step3_desc: 'ಪಾರ್ಕಿಂಗ್ ಸಮಸ್ಯೆಗಳು ಮತ್ತು ತುರ್ತು ಪರಿಸ್ಥಿತಿಗಳನ್ನು ತ್ವರಿತವಾಗಿ ಪರಿಹರಿಸಿ.',

        story_tag: 'ಝೀರೋ ವೈಯಕ್ತಿಕ ಸಂಖ್ಯೆ ಹಂಚಿಕೆ',
        story_title: 'ವಾಹನ್ ಸಂಪರ್ಕ್ ನಿಮ್ಮನ್ನು ಹೇಗೆ ಸುರಕ್ಷಿತವಾಗಿ ಸಂಪರ್ಕಿಸುತ್ತದೆ',
        story_sub: 'ನಮ್ಮ ಅಧಿಕೃತ ವಾಟ್ಸಾಪ್ ಮೂಲಕ ಸಾರ್ವಜನಿಕರು ವಾಹನ ಮಾಲೀಕರೊಂದಿಗೆ ಸಂಪರ್ಕ ಸಾಧಿಸುತ್ತಾರೆ!',
        scene1_badge: 'ದೃಶ್ಯ 1',
        scene2_badge: 'ದೃಶ್ಯ 2',
        scene3_badge: 'ದೃಶ್ಯ 3',
        scene4_badge: 'ದೃಶ್ಯ 4',
        scene1_alert: '🚫 ದಾರಿ ಬಂದ್ ಆಗಿದೆ!',
        s1_title: 'ವಾಹನ ತಪ್ಪಾಗಿ ಪಾರ್ಕ್ ಮಾಡಲಾಗಿದೆ',
        s1_desc: 'ಪಾರ್ಕಿಂಗ್‌ನಲ್ಲಿ ಕಾರ್ ದಾರಿಯನ್ನು ತಡೆದಿದೆ. ಚಾಲಕ ದೂರದಲ್ಲಿದ್ದಾರೆ.',
        s2_title: 'ಸಾರ್ವಜನಿಕರು QR ಟ್ಯಾಗ್ ಸ್ಕ್ಯಾನ್ ಮಾಡುತ್ತಾರೆ',
        s2_desc: 'ಫೋನ್ ಕ್ಯಾಮೆರಾ ಬಳಸಿ ಕಾರಿನ ಮೇಲಿರುವ ವಾಹನ್ ಸಂಪರ್ಕ್ ಸ್ಟಿಕ್ಕರ್ ಸ್ಕ್ಯಾನ್ ಮಾಡುತ್ತಾರೆ.',
        s3_title: 'ಕಂಪನಿ ವಾಟ್ಸಾಪ್ ಮಾಸ್ಕಿಂಗ್',
        s3_desc: 'ಅಧಿಕೃತ ವಾಟ್ಸಾಪ್ ಬೋಟ್ ಮೂಲಕ ಸಂಪರ್ಕಿಸಲಾಗಿದೆ. ವೈಯಕ್ತಿಕ ಸಂಖ್ಯೆ ರಹಸ್ಯವಾಗಿರುತ್ತದೆ.',
        s4_title: 'ಅನಾಮಧೇಯ ಎಚ್ಚರಿಕೆ & ಪರಿಹಾರ!',
        s4_desc: 'ಮಾಲೀಕರಿಗೆ ವಾಟ್ಸಾಪ್ ಎಚ್ಚರಿಕೆ ತಲುಪುತ್ತದೆ ಮತ್ತು ಕಾರನ್ನು ಸರಿಸುತ್ತಾರೆ.',

        hazards_tag: 'ನೈಜ ಪ್ರಪಂಚದ ರಕ್ಷಣೆ',
        hazards_title: 'ವಾಹನ್ ಸಂಪರ್ಕ್ ಮೂಲಕ ಪರಿಹರಿಸಲಾಗುವ ತುರ್ತು ಸಮಸ್ಯೆಗಳು',
        h1_title: 'ಗೀರುಗಳು ಮತ್ತು ಹಾನಿ ತಡೆಯುವುದು',
        h1_desc: 'ಕಾರಿಗೆ ಗೀಚುವ ಬದಲಿಗೆ ಜನರು ನಿಮಗೆ ತಕ್ಷಣ ವಾಟ್ಸಾಪ್ ಅಥವಾ ಕರೆ ಮಾಡಬಹುದು.',
        h2_title: 'ಟೋಯಿಂಗ್ ಮತ್ತು ದಂಡ ತಡೆಯಿರಿ',
        h2_desc: 'ಪೋಲೀಸರು ಅಥವಾ ನೆರೆಹೊರೆಯವರು ಟೋ ಮಾಡುವ ಮೊದಲು ನಿಮಗೆ ಎಚ್ಚರಿಕೆ ನೀಡಬಹುದು.',
        h3_title: 'ಮನೆಯ ಬಳಿ ಕಾರ್ ವಾಶ್ ಸೇವೆ',
        h3_desc: 'ನಿಮ್ಮ ಪಾರ್ಕಿಂಗ್ ಸ್ಥಳದಲ್ಲೇ ವೃತ್ತಿಪರ ಕಾರ್ ವಾಷಿಂಗ್ ಸೇವೆ.',
        h4_title: 'ಗ್ಯಾರೇಜ್ ಮತ್ತು ಮೆಕ್ಯಾನಿಕ್ ಪರಿಹಾರ',
        h4_desc: 'ಹತ್ತಿರದ ಗ್ಯಾರೇಜ್, ಪಂಕ್ಚರ್ ರಿಪೇರಿ ಮತ್ತು ಬ್ಯಾಟರಿ ಜಂಪ್‌ಸ್ಟಾರ್ಟ್ ನೆರವು.',
        h5_title: 'ಲೈಟ್ ಆನ್ ಆಗಿ ಬ್ಯಾಟರಿ ಖಾಲಿಯಾಗುವುದು',
        h5_desc: 'ಲೈಟ್ ಆನ್ ಆಗಿದ್ದರೆ ಬ್ಯಾಟರಿ ಖಾಲಿಯಾಗುವ ಮೊದಲು ತಿಳಿಸಬಹುದು.',
        h6_title: 'ಕಿಟಕಿ ತೆರೆದಿರುವುದು & ಕಳ್ಳತನ ತಡೆ',
        h6_desc: 'ಮಳೆ ಬರುವ ಮೊದಲು ಕಿಟಕಿ ತೆರೆದಿದ್ದರೆ ನೆರೆಹೊರೆಯವರು ಎಚ್ಚರಿಸಬಹುದು.',
        h7_title: 'ತುರ್ತು ಅಪಘಾತ ಪರಿಸ್ಥಿತಿಗಳು',
        h7_desc: 'ಅಪಘಾತದ ಸಮಯದಲ್ಲಿ ಸಾರ್ವಜನಿಕರು ನಿಮ್ಮ ಕುಟುಂಬಕ್ಕೆ ತಕ್ಷಣ ಎಚ್ಚರಿಕೆ ಕಳುಹಿಸಬಹುದು.',
        h8_title: 'ಆಯಿಲ್ ಸೋರಿಕೆ & ಪ್ರಾಣಿಗಳ ರಕ್ಷಣೆ',
        h8_desc: 'ಆಯಿಲ್ ಸೋರಿಕೆ ಅಥವಾ ಪ್ರಾಣಿಗಳು ಸಿಲುಕಿಕೊಂಡಿದ್ದರೆ ತಕ್ಷಣ ತಿಳಿಸಬಹುದು.',

        why_tag: 'ಗೌಪ್ಯತೆ ಹೋಲಿಕೆ',
        why_title: 'ವಾಹನ್ ಸಂಪರ್ಕ್ ವರ್ಸಸ್ ಪೇಪರ್ ಸ್ಟಿಕ್ಕರ್',
        old_method_lbl: '❌ ಹಳೆಯ ವಿಧಾನ',
        old_title: 'ಪೇಪರ್ ಮೊಬೈಲ್ ನಂಬರ್ ಸ್ಟಿಕ್ಕರ್',
        old_f1: 'ಫೋನ್ ಸಂಖ್ಯೆ ಎಲ್ಲರಿಗೂ ಕಾಣಿಸುತ್ತದೆ.',
        old_f2: 'ಯಾವುದೇ ವಾಟ್ಸಾಪ್ ಸಂದೇಶಗಳಿರುವುದಿಲ್ಲ.',
        old_f3: 'ಹೊಸ ಸ್ಟಿಕ್ಕರ್ ಇಲ್ಲದೆ ಸಂಖ್ಯೆ ಬದಲಾಯಿಸಲಾಗುವುದಿಲ್ಲ.',
        old_f4: 'ಮಳೆಯಲ್ಲಿ ಕಾಗದ ಹರಿದು ಹೋಗುತ್ತದೆ.',
        new_method_lbl: '✅ ವಾಹನ್ ಸಂಪರ್ಕ್',
        rec_badge: 'ಶಿಫಾರಸು ಮಾಡಲಾಗಿದೆ',
        new_title: 'ಸ್ಮಾರ್ಟ್ QR ಗೌಪ್ಯತೆ ಟ್ಯಾಗ್',
        new_f1: '100% ಫೋನ್ ಸಂಖ್ಯೆ ಗೌಪ್ಯತೆ.',
        new_f2: '4-ಆಯ್ಕೆಯ ವಾಟ್ಸಾಪ್ ಬೋಟ್ & ಕರೆ.',
        new_f3: 'ವೆಬ್‌ಸೈಟ್ ಮೂಲಕ ಯಾವಾಗ ಬೇಕಾದರೂ ಸಂಖ್ಯೆ ಬದಲಾಯಿಸಿ.',
        new_f4: 'ಬಾಳಿಕೆ ಬರುವ ವಾಟರ್‌ಪ್ರೂಫ್ ಟ್ಯಾಗ್ ಕಾರ್ಡ್.',

        faq_tag: 'ಪ್ರಶ್ನೆಗಳಿವೆಯೇ?',
        faq_title: 'ಪದೇ ಪದೇ ಕೇಳಲಾಗುವ ಪ್ರಶ್ನೆಗಳು',
        faq1_q: 'ವಾಹನ್ ಸಂಪರ್ಕ್ ಸಂಖ್ಯೆಯನ್ನು ಹೇಗೆ ಸುರಕ್ಷಿತವಾಗಿರಿಸುತ್ತದೆ?',
        faq1_a: 'ಯಾರಾದರೂ ಸ್ಕ್ಯಾನ್ ಮಾಡಿದಾಗ ಕರೆಗಳು ನಮ್ಮ ಸುರಕ್ಷಿತ ಪ್ಲಾಟ್‌ಫಾರ್ಮ್ ಮೂಲಕ ಹೋಗುತ್ತವೆ.',
        faq2_q: 'ಸ್ಕ್ಯಾನ್ ಮಾಡುವವರು ಆ್ಯಪ್ ಡೌನ್‌ಲೋಡ್ ಮಾಡಬೇಕೇ?',
        faq2_a: 'ಇಲ್ಲ! ಯಾವುದೇ ಸಾಮಾನ್ಯ ಫೋನ್ ಕ್ಯಾಮೆರಾ ಬಳಸಬಹುದು.',
        faq3_q: 'ಕಾರ್ ವಾಶ್ ಮತ್ತು ಗ್ಯಾರೇಜ್ ಸೇವೆ ಹೇಗೆ ಕೆಲಸ ಮಾಡುತ್ತದೆ?',
        faq3_a: 'ವಾಹನ್ ಸಂಪರ್ಕ್ ನಿಮ್ಮ ಟ್ಯಾಗ್ ಅನ್ನು ಪ್ರಮಾಣೀಕೃತ ಸೇವೆಗಳೊಂದಿಗೆ ಜೋಡಿಸುತ್ತದೆ.',
        faq4_q: 'ನಂತರ ಫೋನ್ ಸಂಖ್ಯೆ ಬದಲಾಯಿಸಬಹುದೇ?',
        faq4_a: 'ಹೌದು! ಸ್ಟಿಕ್ಕರ್ ಬದಲಾಯಿಸದೆ ಆನ್‌ಲೈನ್‌ನಲ್ಲಿ ಸಂಖ್ಯೆ ಬದಲಾಯಿಸಬಹುದು.',
        faq5_q: '4-ಆಯ್ಕೆಯ ವಾಟ್ಸಾಪ್ ಬೋಟ್ ಹೇಗೆ ಕೆಲಸ ಮಾಡುತ್ತದೆ?',
        faq5_a: 'ವಾಟ್ಸಾಪ್‌ನಲ್ಲಿ ಬೋಟ್ 4 ಆಯ್ಕೆಗಳನ್ನು ನೀಡುತ್ತದೆ ಮತ್ತು ತಕ್ಷಣ ಸಂದೇಶ ಕಳುಹಿಸುತ್ತದೆ!',

        contact_tag: 'ಸಂಪರ್ಕಿಸಿ',
        contact_title: 'ಸ್ಮಾರ್ಟ್ QR ಟ್ಯಾಗ್ ಆರ್ಡರ್ ಮಾಡಲು & ವಿಚಾರಣೆಗೆ',
        contact_sub: 'ಯಾವುದೇ ಆನ್‌ಲೈನ್ ಪಾವತಿ ಅಗತ್ಯವಿಲ್ಲ. ನೇರವಾಗಿ ಕರೆ ಮಾಡಿ ಅಥವಾ ವಾಟ್ಸಾಪ್ ಮಾಡಿ!',
        channels_title: '<span style="width: 42px; height: 42px; border-radius: 12px; background: rgba(16, 185, 129, 0.12); color: #10b981; display: inline-flex; align-items: center; justify-content: center; font-size: 1.25rem;"><i class="fa-solid fa-headset"></i></span> ನೇರ ಸಂಪರ್ಕ ಮಾರ್ಗಗಳು',
        call_title: '<i class="fa-solid fa-bolt" style="color: #fef08a;"></i> ತ್ವರಿತ ಕರೆ ಹೆಲ್ಪ್‌ಲೈನ್',
        wa_title: '<i class="fa-solid fa-comments" style="color: #ffedd5;"></i> 1-ಕ್ಲಿಕ್ ವಾಟ್ಸಾಪ್ ಬೋಟ್',
        wa_btn: 'ವಾಟ್ಸಾಪ್‌ನಲ್ಲಿ ಚಾಟ್ ಮಾಡಿ',
        email_title: 'ಇಮೇಲ್ ನೆರವು ಮಾರ್ಗ',
        form_title: '<span style="width: 42px; height: 42px; border-radius: 12px; background: rgba(249, 115, 22, 0.12); color: #f97316; display: inline-flex; align-items: center; justify-content: center; font-size: 1.25rem;"><i class="fa-solid fa-paper-plane"></i></span> ವಿಚಾರಣೆ ಕಳುಹಿಸಿ',
        form_name: 'ಪೂರ್ಣ ಹೆಸರು <span class="required" style="color: #f43f5e;">*</span>',
        form_phone: 'ಮೊಬೈಲ್ / ವಾಟ್ಸಾಪ್ ಸಂಖ್ಯೆ <span class="required" style="color: #f43f5e;">*</span>',
        form_vehicle: 'ವಾಹನ ನೋಂದಣಿ ಸಂಖ್ಯೆ <span style="font-weight: 500; color: #94a3b8; font-size: 0.78rem;">(ಐಚ್ಛಿಕ)</span>',
        form_city: 'ನಗರ ಮತ್ತು ರಾಜ್ಯ <span class="required" style="color: #f43f5e;">*</span>',
        form_qty: 'ಅಗತ್ಯವಿರುವ ಟ್ಯಾಗ್‌ಗಳ ಸಂಖ್ಯೆ',
        form_opt1: '🚗 1 ಟ್ಯಾಗ್ (ಸಂತ ಕಾರ್/ಬೈಕ್)',
        form_opt2: '👨‍👩‍👧‍👦 5 ಟ್ಯಾಗ್‌ಗಳು (ಕುಟುಂಬ ವಾಹನಗಳು)',
        form_opt3: '🏢 10 ಟ್ಯಾಗ್‌ಗಳು (ಸಣ್ಣ ಉದ್ಯಮ)',
        form_opt4: '🚛 50+ ಟ್ಯಾಗ್‌ಗಳು (ದೊಡ್ಡ ಉದ್ಯಮ)',
        form_msg: 'ಸಂದೇಶ / ಹೆಚ್ಚುವರಿ ವಿವರಗಳು',
        form_btn: '<i class="fa-solid fa-paper-plane"></i> ಈಗ ಸಲ್ಲಿಸಿ',
        form_safe_notice: '<i class="fa-solid fa-shield-check" style="color: #10b981;"></i> 100% ಸುರಕ್ಷಿತ ಮತ್ತು ರಹಸ್ಯ.',
        wa_widget_title: '<i class="fa-brands fa-whatsapp"></i> ತ್ವರಿತ ಸಂಪರ್ಕ',
        wa_widget_msg: 'ನಮಸ್ಕಾರ! ಪ್ರಶ್ನೆಗಳಿವೆಯೇ ಅಥವಾ ಟ್ಯಾಗ್ ಆರ್ಡರ್ ಮಾಡಬೇಕೇ? ಚಾಟ್ ಮಾಡಿ!',
        footer_p: 'ಸ್ಮಾರ್ಟ್ ತುರ್ತು ವಾಹನ ಸುರಕ್ಷತಾ ಟ್ಯಾಗ್ ಸಿಸ್ಟಮ್ • © 2026 Vehicle Sampark.',
        footer_l1: 'ಇದು ಹೇಗೆ ಕೆಲಸ ಮಾಡುತ್ತದೆ',
        footer_l2: 'ತುರ್ತು ಪ್ರಕರಣಗಳು',
        footer_l3: 'ಏಕೆ ವಾಹನ್ ಸಂಪರ್ಕ್',
        footer_l4: 'ಪ್ರಶ್ನೆಗಳು',
        footer_l5: 'ಸಂಪರ್ಕಿಸಿ'
    },
    'pa': {
        announcement: '🚀 ਤੁਰੰਤ 1-ਕਲਿੱਕ ਵਟਸਐਪ ਬੋਟ ਅਤੇ ਕਾਲ ਰਿਲੇਅ • 100% ਮੋਬਾਈਲ ਨੰਬਰ ਗੋਪਨੀਯਤਾ ਸੁਰੱਖਿਆ ਦੀ ਗਾਰੰਟੀ!',
        nav_how: '<i class="fa-solid fa-list-check"></i> ਇਹ ਕਿਵੇਂ ਕੰਮ ਕਰਦਾ ਹੈ',
        nav_hazards: '<i class="fa-solid fa-triangle-exclamation"></i> ਹੱਲ ਕੀਤੀਆਂ ਸਮੱਸਿਆਵਾਂ',
        nav_why: '<i class="fa-solid fa-shield-halved"></i> ਅਸੀਂ ਹੀ ਕਿਉਂ',
        nav_faq: '<i class="fa-solid fa-circle-question"></i> ਸਵਾਲ',
        nav_contact: '<i class="fa-solid fa-envelope"></i> ਸੰਪਰਕ ਕਰੋ',
        hero_badge: '<span class="live-dot-pulse"></span> <i class="fa-solid fa-shield-halved" style="color: #10b981;"></i> ਆਪਣੇ ਵਾਹਨ ਅਤੇ ਨਿੱਜੀ ਗੋਪਨੀਯਤਾ ਦੀ ਰੱਖਿਆ ਕਰੋ',
        hero_title: '<span id="heroTitleMain">ਸਮਾਰਟ QR ਸਕੈਨ ਨਾਲ </span><span id="heroDynamicWord" class="gradient-text hero-dynamic-word">ਕੋਈ ਵੀ ਤੁਹਾਡੇ ਨਾਲ ਤੁਰੰਤ ਸੰਪਰਕ ਕਰ ਸਕਦਾ ਹੈ ⚡</span>',
        hero_sub: 'ਇੱਕ ਸਾਧਾਰਨ ਸਕੈਨ ਨਾਲ ਕੋਈ ਵੀ ਤੁਹਾਡੇ ਨਾਲ ਤੁਰੰਤ ਸੰਪਰਕ ਕਰ ਸਕਦਾ ਹੈ। ਆਪਣਾ ਨੰਬਰ ਗੁਪਤ ਰੱਖਦੇ ਹੋਏ ਰੀਅਲ-ਟਾਈਮ ਵਟਸਐਪ ਅਲਰਟ ਅਤੇ ਕਾਲਾਂ ਪ੍ਰਾਪਤ ਕਰੋ।',
        hero_btn_tag: '<i class="fa-solid fa-phone-volume"></i> ਸਮਾਰਟ QR ਟੈਗ ਪ੍ਰਾਪਤ ਕਰੋ',
        hero_btn_wa: '<i class="fa-brands fa-whatsapp" style="font-size: 1.3rem;"></i> ਵਟਸਐਪ \'ਤੇ ਚੈਟ ਕਰੋ',
        trust_priv_val: '100% ਗੁਪਤ',
        trust_priv_lbl: 'ਫੋਨ ਨੰਬਰ ਸੁਰੱਖਿਅਤ',
        trust_relay_val: 'ਤੁਰੰਤ ਸੰਪਰਕ',
        trust_relay_lbl: 'ਕਾਲ ਅਤੇ ਵਟਸਐਪ ਬੋਟ',
        trust_badge_val: 'ਸਮਾਰਟ ਬੈਜ',
        trust_badge_lbl: 'ਸੈਂਟਰ ਲੋਗੋ ਟੈਗ ਕਾਰਡ',
        float_left: '<i class="fa-solid fa-lock" style="color: #10b981;"></i> 100% ਫੋਨ ਗੋਪਨੀਯਤਾ',
        float_right: '<i class="fa-solid fa-bolt" style="color: #f97316;"></i> 3-ਸਕਿੰਟ ਕਨੈਕਟ',
        hero_call_pill: '<i class="fa-solid fa-phone-volume"></i> ਮਾਲਕ ਨੂੰ ਕਾਲ ਕਰੋ',
        hero_wa_pill: '<i class="fa-brands fa-whatsapp"></i> ਵਟਸਐਪ ਬੋਟ',
        live_support: '<span style="width: 8px; height: 8px; border-radius: 50%; background: #10b981; display: inline-block; animation: pulseDot 1.5s infinite;"></span> ਲਾਈਵ 24/7 ਮਦਦ',

        ticker1: '100% ਗੋਪਨੀਯਤਾ ਸੁਰੱਖਿਅਤ - ਕੋਈ ਮੋਬਾਈਲ ਨੰਬਰ ਨਹੀਂ ਦਿਖਾਇਆ ਜਾਂਦਾ',
        ticker2: 'ਤੁਰੰਤ ਕਾਲ ਅਤੇ 4-ਬਦਲਵੇਂ ਸੰਕਟਕਾਲੀਨ ਵਟਸਐਪ ਬੋਟ',
        ticker3: 'ਗੱਡੀ \'ਤੇ ਝਰੀਟਾਂ ਅਤੇ ਅਚਾਨਕ ਟੋਇੰਗ ਜੁਰਮਾਨੇ ਤੋਂ ਬਚੋ',
        ticker4: 'ਘਰ ਦੇ ਬਾਹਰ ਕਾਰ ਧੋਣ ਅਤੇ ਗੈਰੇਜ ਦੀ ਮਦਦ',
        ticker5: 'ਕੋਈ ਐਪ ਡਾਊਨਲੋਡ ਕਰਨ ਦੀ ਲੋੜ ਨਹੀਂ - ਕਿਸੇ ਵੀ ਫੋਨ ਕੈਮਰੇ ਨਾਲ ਸਕੈਨ ਕਰੋ',

        how_tag: 'ਕਾਲ ਅਤੇ ਵਟਸਐਪ ਲਈ ਸਕੈਨ ਕਰੋ',
        how_title: 'ਵਾਹਨ ਸੰਪਰਕ 3 ਆਸਾਨ ਕਦਮਾਂ ਵਿੱਚ ਤੁਹਾਡੀ ਰੱਖਿਆ ਕਰਦਾ ਹੈ',
        step1_title: '1. QR ਕੋਡ ਸਕੈਨ ਕਰੋ',
        step1_desc: 'ਤੁਹਾਡੀ ਕਾਰ ਦੇ ਵਿੰਡਸ਼ੀਲਡ \'ਤੇ ਲੱਗੇ QR ਸਟਿੱਕਰ ਨੂੰ ਕੋਈ ਵੀ ਫੋਨ ਕੈਮਰੇ ਨਾਲ ਸਕੈਨ ਕਰ ਸਕਦਾ ਹੈ।',
        step2_title: '2. ਤੁਰੰਤ ਜੁੜੋ',
        step2_desc: 'ਨਿੱਜੀ ਨੰਬਰ ਦਿਖਾਏ ਬਿਨਾਂ ਵਾਹਨ ਮਾਲਕ ਨੂੰ ਸਿੱਧਾ ਕਾਲ ਕਰੋ ਜਾਂ ਵਟਸਐਪ ਚੁਣੋ।',
        step3_title: '3. ਸਮੱਸਿਆ ਦਾ ਹੱਲ ਕਰੋ',
        step3_desc: 'ਪਾਰਕਿੰਗ ਦੀਆਂ ਸਮੱਸਿਆਵਾਂ ਅਤੇ ਸੰਕਟਕਾਲੀਨ ਸਥਿਤੀਆਂ ਨੂੰ ਜਲਦੀ ਹੱਲ ਕਰੋ।',

        story_tag: 'ਜ਼ੀਰੋ ਨਿੱਜੀ ਨੰਬਰ ਸਾਂਝਾ ਕਰਨਾ',
        story_title: 'ਵਾਹਨ ਸੰਪਰਕ ਤੁਹਾਨੂੰ ਸੁਰੱਖਿਅਤ ਢੰਗ ਨਾਲ ਕਿਵੇਂ ਜੋੜਦਾ ਹੈ',
        story_sub: 'ਸਾਡੇ ਅਧਿਕਾਰਤ ਵਟਸਐਪ ਗੇਟਵੇ ਰਾਹੀਂ ਆਮ ਲੋਕ ਵਾਹਨ ਮਾਲਕਾਂ ਨਾਲ ਜੁੜਦੇ ਹਨ!',
        scene1_badge: 'ਦ੍ਰਿਸ਼ 1',
        scene2_badge: 'ਦ੍ਰਿਸ਼ 2',
        scene3_badge: 'ਦ੍ਰਿਸ਼ 3',
        scene4_badge: 'ਦ੍ਰਿਸ਼ 4',
        scene1_alert: '🚫 ਰਸਤਾ ਬੰਦ ਹੈ!',
        s1_title: 'ਗੱਡੀ ਗਲਤ ਤਰੀਕੇ ਨਾਲ ਪਾਰਕ ਕੀਤੀ ਗਈ',
        s1_desc: 'ਪਾਰਕਿੰਗ ਵਿੱਚ ਇੱਕ ਕਾਰ ਰਸਤਾ ਰੋਕ ਰਹੀ ਹੈ। ਡਰਾਈਵਰ ਦੂਰ ਹੈ।',
        s2_title: 'ਰਾਹਗੀਰ QR ਟੈਗ ਸਕੈਨ ਕਰਦਾ ਹੈ',
        s2_desc: 'ਫੋਨ ਕੈਮਰੇ ਨਾਲ ਕਾਰ \'ਤੇ ਲੱਗਾ ਵਾਹਨ ਸੰਪਰਕ ਸਟਿੱਕਰ ਸਕੈਨ ਕਰਦਾ ਹੈ।',
        s3_title: 'ਕੰਪਨੀ ਵਟਸਐਪ ਮਾਸਕਿੰਗ',
        s3_desc: 'ਅਧਿਕਾਰਤ ਵਟਸਐਪ ਬੋਟ ਰਾਹੀਂ ਜੁੜਿਆ ਹੋਇਆ। ਨਿੱਜੀ ਨੰਬਰ ਗੁਪਤ ਰਹਿੰਦਾ ਹੈ।',
        s4_title: 'ਗੁਮਨਾਮ ਅਲਰਟ ਅਤੇ ਹੱਲ!',
        s4_desc: 'ਮਾਲਕ ਨੂੰ ਵਟਸਐਪ ਅਲਰਟ ਮਿਲਦਾ ਹੈ ਅਤੇ ਉਹ ਕਾਰ ਹਟਾ ਦਿੰਦੇ ਹਨ।',

        hazards_tag: 'ਅਸਲ ਦੁਨੀਆ ਦੀ ਸੁਰੱਖਿਆ',
        hazards_title: 'ਵਾਹਨ ਸੰਪਰਕ ਦੁਆਰਾ ਹੱਲ ਕੀਤੀਆਂ ਜਾਣ ਵਾਲੀਆਂ ਸਮੱਸਿਆਵਾਂ',
        h1_title: 'ਝਰੀਟਾਂ ਅਤੇ ਨੁਕਸਾਨ ਤੋਂ ਬਚਾਅ',
        h1_desc: 'ਗੱਡੀ \'ਤੇ ਝਰੀਟਾਂ ਪਾਉਣ ਦੀ ਬਜਾਏ ਲੋਕ ਤੁਹਾਨੂੰ ਤੁਰੰਤ ਵਟਸਐਪ ਜਾਂ ਕਾਲ ਕਰ ਸਕਦੇ ਹਨ।',
        h2_title: 'ਟੋਇੰਗ ਅਤੇ ਜੁਰਮਾਨੇ ਤੋਂ ਬਚੋ',
        h2_desc: 'ਟ੍ਰੈਫਿਕ ਪੁਲਿਸ ਜਾਂ ਗੁਆਂਢੀ ਟੋ ਕਰਨ ਤੋਂ ਪਹਿਲਾਂ ਤੁਹਾਨੂੰ ਚੇਤਾਵਨੀ ਦੇ ਸਕਦੇ ਹਨ।',
        h3_title: 'ਘਰ ਦੇ ਬਾਹਰ ਕਾਰ ਧੋਣ ਦੀ ਸੇਵਾ',
        h3_desc: 'ਤੁਹਾਡੀ ਪਾਰਕਿੰਗ ਵਿੱਚ ਹੀ ਪੇਸ਼ੇਵਰ ਕਾਰ ਵਾਸ਼ਿੰਗ ਅਤੇ ਪਾਲਿਸ਼ਿੰਗ।',
        h4_title: 'ਗੈਰੇਜ ਅਤੇ ਮਕੈਨਿਕ ਹੱਲ',
        h4_desc: 'ਨੇੜਲੇ ਗੈਰੇਜ, ਪੰਕਚਰ ਮੁਰੰਮਤ ਅਤੇ ਬੈਟਰੀ ਜੰਪਸਟਾਰਟ ਦੀ ਮਦਦ।',
        h5_title: 'ਬੈਟਰੀ ਖਤਮ ਹੋਣਾ ਅਤੇ ਲਾਈਟਾਂ ਆਨ ਰਹਿਣਾ',
        h5_desc: 'ਲਾਈਟਾਂ ਆਨ ਰਹਿਣ \'ਤੇ ਬੈਟਰੀ ਖਤਮ ਹੋਣ ਤੋਂ ਪਹਿਲਾਂ ਲੋਕ ਦੱਸ ਸਕਦੇ ਹਨ।',
        h6_title: 'ਖਿੜਕੀ ਖੁੱਲ੍ਹੀ ਰਹਿਣਾ ਅਤੇ ਚੋਰੀ ਤੋਂ ਬਚਾਅ',
        h6_desc: 'ਮੀਂਹ ਤੋਂ ਪਹਿਲਾਂ ਖਿੜਕੀ ਖੁੱਲ੍ਹੀ ਹੋਣ \'ਤੇ ਗੁਆਂਢੀ ਅਲਰਟ ਕਰ ਸਕਦੇ ਹਨ।',
        h7_title: 'ਗੰਭੀਰ ਹਾਦਸੇ ਦੀਆਂ ਸਥਿਤੀਆਂ',
        h7_desc: 'ਹਾਦਸੇ ਦੇ ਸਮੇਂ ਲੋਕ ਤੁਹਾਡੇ ਪਰਿਵਾਰ ਨੂੰ ਤੁਰੰਤ ਅਲਰਟ ਭੇਜ ਸਕਦੇ ਹਨ।',
        h8_title: 'ਤੇਲ ਲੀਕ ਅਤੇ ਜਾਨਵਰਾਂ ਦੀ ਸੁਰੱਖਿਆ',
        h8_desc: 'ਤੇਲ ਲੀਕ ਹੋਣ ਜਾਂ ਗੱਡੀ ਹੇਠਾਂ ਜਾਨਵਰ ਫਸਣ \'ਤੇ ਤੁਰੰਤ ਦੱਸਿਆ ਜਾ ਸਕਦਾ ਹੈ।',

        why_tag: 'ਗੋਪਨੀਯਤਾ ਦੀ ਤੁਲਨਾ',
        why_title: 'ਵਾਹਨ ਸੰਪਰਕ ਬਨਾਮ ਕਾਗਜ਼ੀ ਸਟਿੱਕਰ',
        old_method_lbl: '❌ ਪੁਰਾਣਾ ਤਰੀਕਾ',
        old_title: 'ਕਾਗਜ਼ੀ ਮੋਬਾਈਲ ਨੰਬਰ ਸਟਿੱਕਰ',
        old_f1: 'ਫੋਨ ਨੰਬਰ ਸਭ ਦੇ ਸਾਹਮਣੇ ਆਉਂਦਾ ਹੈ।',
        old_f2: 'ਕੋਈ ਵਟਸਐਪ ਅਲਰਟ ਨਹੀਂ ਹੁੰਦਾ।',
        old_f3: 'ਨਵਾਂ ਸਟਿੱਕਰ ਲਏ ਬਿਨਾਂ ਨੰਬਰ ਨਹੀਂ ਬਦਲਿਆ ਜਾ ਸਕਦਾ।',
        old_f4: 'ਮੀਂਹ ਵਿੱਚ ਕਾਗਜ਼ ਫਟ ਜਾਂਦਾ ਹੈ।',
        new_method_lbl: '✅ ਵਾਹਨ ਸੰਪਰਕ',
        rec_badge: 'ਸਿਫਾਰਸ਼ ਕੀਤੀ ਗਈ',
        new_title: 'ਸਮਾਰਟ QR ਗੋਪਨੀਯਤਾ ਟੈਗ',
        new_f1: '100% ਫੋਨ ਨੰਬਰ ਗੋਪਨੀਯਤਾ।',
        new_f2: '4-ਬਦਲਵੇਂ ਵਟਸਐਪ ਬੋਟ ਅਤੇ ਕਾਲ।',
        new_f3: 'ਵੇਬਸਾਈਟ ਤੋਂ ਕਿਸੇ ਵੀ ਸਮੇਂ ਨੰਬਰ ਬਦਲੋ।',
        new_f4: 'ਟਿਕਾਊ ਅਤੇ ਵਾਟਰਪ੍ਰੂਫ਼ ਟੈਗ ਕਾਰਡ।',

        faq_tag: 'ਸਵਾਲ ਹਨ?',
        faq_title: 'ਅਕਸਰ ਪੁੱਛੇ ਜਾਂਦੇ ਸਵਾਲ',
        faq1_q: 'ਵਾਹਨ ਸੰਪਰਕ ਨੰਬਰ ਨੂੰ ਕਿਵੇਂ ਸੁਰੱਖਿਅਤ ਰੱਖਦਾ ਹੈ?',
        faq1_a: 'ਜਦੋਂ ਕੋਈ ਸਕੈਨ ਕਰਦਾ ਹੈ, ਤਾਂ ਕਾਲਾਂ ਸਾਡੇ ਸੁਰੱਖਿਅਤ ਪਲੇਟਫਾਰਮ ਰਾਹੀਂ ਜਾਂਦੀਆਂ ਹਨ।',
        faq2_q: 'ਕੀ ਸਕੈਨ ਕਰਨ ਵਾਲੇ ਨੂੰ ਐਪ ਡਾਊਨਲੋਡ ਕਰਨੀ ਪਵੇਗੀ?',
        faq2_a: 'ਨਹੀਂ! ਕਿਸੇ ਵੀ ਫੋਨ ਕੈਮਰੇ ਨਾਲ ਸਕੈਨ ਕੀਤਾ ਜਾ ਸਕਦਾ ਹੈ।',
        faq3_q: 'ਕਾਰ ਵਾਸ਼ ਅਤੇ ਗੈਰੇਜ ਸੇਵਾ ਕਿਵੇਂ ਕੰਮ ਕਰਦੀ ਹੈ?',
        faq3_a: 'ਵਾਹਨ ਸੰਪਰਕ ਤੁਹਾਡੇ ਟੈਗ ਨੂੰ ਪ੍ਰਮਾਣਿਤ ਸੇਵਾਵਾਂ ਨਾਲ ਜੋੜਦਾ ਹੈ।',
        faq4_q: 'ਕੀ ਬਾਅਦ ਵਿੱਚ ਨੰਬਰ ਬਦਲਿਆ ਜਾ ਸਕਦਾ ਹੈ?',
        faq4_a: 'ਹਾਂ! ਸਟਿੱਕਰ ਬਦਲੇ ਬਿਨਾਂ ਕਿਸੇ ਵੀ ਸਮੇਂ ਆਨਲਾਈਨ ਨੰਬਰ ਬਦਲੋ।',
        faq5_q: '4-ਬਦਲਵੇਂ ਵਟਸਐਪ ਬੋਟ ਕਿਵੇਂ ਕੰਮ ਕਰਦਾ ਹੈ?',
        faq5_a: 'ਵਟਸਐਪ \'ਤੇ ਬੋਟ 4 ਵਿਕਲਪ ਦਿੰਦਾ ਹੈ ਅਤੇ ਤੁਰੰਤ ਮੈਸੇਜ ਭੇਜਦਾ ਹੈ!',

        contact_tag: 'ਸੰਪਰਕ ਕਰੋ',
        contact_title: 'ਸਮਾਰਟ QR ਟੈਗ ਆਰਡਰ ਕਰਨ ਲਈ',
        contact_sub: 'ਕੋਈ ਆਨਲਾਈਨ ਭੁਗਤਾਨ ਦੀ ਲੋੜ ਨਹੀਂ। ਸਿੱਧਾ ਕਾਲ ਜਾਂ ਵਟਸਐਪ ਕਰੋ!',
        channels_title: '<span style="width: 42px; height: 42px; border-radius: 12px; background: rgba(16, 185, 129, 0.12); color: #10b981; display: inline-flex; align-items: center; justify-content: center; font-size: 1.25rem;"><i class="fa-solid fa-headset"></i></span> ਸਿੱਧੇ ਸੰਪਰਕ ਦੇ ਸਾਧਨ',
        call_title: '<i class="fa-solid fa-bolt" style="color: #fef08a;"></i> ਤੁਰੰਤ ਕਾਲ ਹੈਲਪਲਾਈਨ',
        wa_title: '<i class="fa-solid fa-comments" style="color: #ffedd5;"></i> 1-ਕਲਿੱਕ ਵਟਸਐਪ ਬੋਟ',
        wa_btn: 'ਵਟਸਐਪ \'ਤੇ ਚੈਟ ਕਰੋ',
        email_title: 'ਈਮੇਲ ਮਦਦ ਲਾਈਨ',
        form_title: '<span style="width: 42px; height: 42px; border-radius: 12px; background: rgba(249, 115, 22, 0.12); color: #f97316; display: inline-flex; align-items: center; justify-content: center; font-size: 1.25rem;"><i class="fa-solid fa-paper-plane"></i></span> ਜਾਣਕਾਰੀ ਭੇਜੋ',
        form_name: 'ਪੂਰਾ ਨਾਮ <span class="required" style="color: #f43f5e;">*</span>',
        form_phone: 'ਮੋਬਾਈਲ / ਵਟਸਐਪ ਨੰਬਰ <span class="required" style="color: #f43f5e;">*</span>',
        form_vehicle: 'ਗੱਡੀ ਦਾ ਨੰਬਰ <span style="font-weight: 500; color: #94a3b8; font-size: 0.78rem;">(ਮਰਜ਼ੀ ਮੁਤਾਬਕ)</span>',
        form_city: 'ਸ਼ਹਿਰ ਅਤੇ ਰਾਜ <span class="required" style="color: #f43f5e;">*</span>',
        form_qty: 'ਲੋੜੀਂਦੇ ਟੈਗਾਂ ਦੀ ਗਿਣਤੀ',
        form_opt1: '🚗 1 ਟੈਗ (ਨਿੱਜੀ ਕਾਰ/ਬਾਈਕ)',
        form_opt2: '👨‍👩‍👧‍👦 5 ਟੈਗ (ਪਰਿਵਾਰਕ ਗੱਡੀਆਂ)',
        form_opt3: '🏢 10 ਟੈਗ (ਛੋਟਾ ਵਪਾਰ)',
        form_opt4: '🚛 50+ ਟੈਗ (ਵੱਡਾ ਵਪਾਰ)',
        form_msg: 'ਸੁਨੇਹਾ / ਹੋਰ ਜਾਣਕਾਰੀ',
        form_btn: '<i class="fa-solid fa-paper-plane"></i> ਹੁਣੇ ਜਮ੍ਹਾਂ ਕਰੋ',
        form_safe_notice: '<i class="fa-solid fa-shield-check" style="color: #10b981;"></i> 100% ਸੁਰੱਖਿਅਤ ਅਤੇ ਗੁਪਤ।',
        wa_widget_title: '<i class="fa-brands fa-whatsapp"></i> ਤੁਰੰਤ ਸੰਪਰਕ',
        wa_widget_msg: 'ਸਤਿ ਸ਼੍ਰੀ ਅਕਾਲ! ਕੋਈ ਸਵਾਲ ਹੈ ਜਾਂ ਟੈਗ ਆਰਡਰ ਕਰਨਾ ਚਾਹੁੰਦੇ ਹੋ? ਚੈਟ ਕਰੋ!',
        footer_p: 'ਸਮਾਰਟ ਸੰਕਟਕਾਲੀਨ ਵਾਹਨ ਸੁਰੱਖਿਆ ਟੈਗ ਸਿਸਟਮ • © 2026 Vehicle Sampark.',
        footer_l1: 'ਇਹ ਕਿਵੇਂ ਕੰਮ ਕਰਦਾ ਹੈ',
        footer_l2: 'ਸੰਕਟਕਾਲੀਨ ਮਾਮਲੇ',
        footer_l3: 'ਵਾਹਨ ਸੰਪਰਕ ਕਿਉਂ',
        footer_l4: 'ਸਵਾਲ',
        footer_l5: 'ਸੰਪਰਕ ਕਰੋ'
    }
};

/**
 * Custom Multi-Language Dropdown Engine
 */
function toggleLangDropdown(event) {
    if (event) event.stopPropagation();
    const dropdown = document.getElementById('customLangDropdown');
    if (!dropdown) return;
    dropdown.classList.toggle('open');
}

document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('customLangDropdown');
    if (dropdown && !dropdown.contains(e.target)) {
        dropdown.classList.remove('open');
    }
});

function selectCustomLang(langCode, triggerTranslation = true) {
    const langLabels = {
        'en': '🇬🇧 English',
        'hi': '🇮🇳 हिंदी (Hindi)',
        'gu': '🇮🇳 ગુજરાતી (Gujarati)',
        'mr': '🇮🇳 मराठी (Marathi)',
        'ta': '🇮🇳 தமிழ் (Tamil)',
        'bn': '🇮🇳 বাংলা (Bengali)',
        'ml': '🇮🇳 മലയാളം (Malayalam)',
        'kn': '🇮🇳 ಕನ್ನಡ (Kannada)',
        'pa': '🇮🇳 ਪੰਜਾਬੀ (Punjabi)'
    };

    const currentElem = document.getElementById('customLangCurrent');
    if (currentElem && langLabels[langCode]) {
        currentElem.innerHTML = langLabels[langCode];
    }

    const items = document.querySelectorAll('.lang-menu-item');
    items.forEach(item => {
        if (item.getAttribute('data-lang') === langCode) {
            item.classList.add('active');
        } else {
            item.classList.remove('active');
        }
    });

    const dropdown = document.getElementById('customLangDropdown');
    if (dropdown) dropdown.classList.remove('open');

    const selectElem = document.getElementById('langSelectBox');
    if (selectElem) {
        selectElem.value = langCode;
        if (triggerTranslation) {
            onLangSelectChange(selectElem);
        }
    }
}

/**
 * Multi-Language Selection Engine (English & 8 Indian Languages)
 */
function onLangSelectChange(selectElem) {
    if (!selectElem) return;
    const langCode = selectElem.value;
    const previousLang = localStorage.getItem('selectedLangCode');

    localStorage.setItem('selectedLangCode', langCode);

    // 1. Native DOM Text Swapping (Instant 0ms translation for key elements)
    applyNativeTranslation(langCode);

    // 2. Set Google Translate Cookie across hostinger domain
    setGoogleTranslateCookie(langCode);

    // 3. Trigger Google Translate Widget if available (smooth)
    applyGoogleTranslate(langCode);

    // 4. Refresh page once when language is selected
    if (previousLang !== langCode) {
        setTimeout(function() {
            window.location.reload();
        }, 100);
    }
}

function applyNativeTranslation(langCode) {
    const data = landingPageI18n[langCode] || landingPageI18n['en'];
    if (!data) return;

    const setHtml = (selector, html) => {
        const elem = document.querySelector(selector);
        if (elem && html) elem.innerHTML = html;
    };

    const setAllHtml = (selector, html) => {
        const elems = document.querySelectorAll(selector);
        elems.forEach(elem => { if (elem && html) elem.innerHTML = html; });
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

    // Hero Live Demo Toast & Action Pills
    setHtml('#liveHeroPills span:nth-child(1)', data.hero_call_pill);
    setHtml('#liveHeroPills span:nth-child(2)', data.hero_wa_pill);

    // Ticker Marquee Items
    setAllHtml('.ticker-item:nth-child(1), .ticker-item:nth-child(6)', '<i class="fa-solid fa-shield-halved"></i> ' + data.ticker1);
    setAllHtml('.ticker-item:nth-child(2), .ticker-item:nth-child(7)', '<i class="fa-solid fa-bolt"></i> ' + data.ticker2);
    setAllHtml('.ticker-item:nth-child(3), .ticker-item:nth-child(8)', '<i class="fa-solid fa-car-burst"></i> ' + data.ticker3);
    setAllHtml('.ticker-item:nth-child(4), .ticker-item:nth-child(9)', '<i class="fa-solid fa-soap"></i> ' + data.ticker4);
    setAllHtml('.ticker-item:nth-child(5), .ticker-item:nth-child(10)', '<i class="fa-solid fa-qrcode"></i> ' + data.ticker5);

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
    setHtml('.story-card:nth-child(1) .story-badge-num', data.scene1_badge);
    setHtml('.story-card:nth-child(1) .alert-bubble-cartoon', data.scene1_alert);
    setHtml('.story-card:nth-child(1) h3', data.s1_title);
    setHtml('.story-card:nth-child(1) p', data.s1_desc);
    setHtml('.story-card:nth-child(2) .story-badge-num', data.scene2_badge);
    setHtml('.story-card:nth-child(2) h3', data.s2_title);
    setHtml('.story-card:nth-child(2) p', data.s2_desc);
    setHtml('.story-card:nth-child(3) .story-badge-num', data.scene3_badge);
    setHtml('.story-card:nth-child(3) h3', data.s3_title);
    setHtml('.story-card:nth-child(3) p', data.s3_desc);
    setHtml('.story-card:nth-child(4) .story-badge-num', data.scene4_badge);
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
    setHtml('.compare-box-old > div', data.old_method_lbl);
    setHtml('.compare-box-old h3', data.old_title);
    setHtml('.compare-box-old li:nth-child(1) span', data.old_f1);
    setHtml('.compare-box-old li:nth-child(2) span', data.old_f2);
    setHtml('.compare-box-old li:nth-child(3) span', data.old_f3);
    setHtml('.compare-box-old li:nth-child(4) span', data.old_f4);
    setHtml('.compare-box-new > div > span:first-child', data.new_method_lbl);
    setHtml('.compare-box-new > div > span:last-child', data.rec_badge);
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
    setHtml('.content-card span[style*="background: #ecfdf5"]', data.live_support);
    setHtml('.contact-channel-box:nth-child(1) div div div:first-child', data.call_title);
    setHtml('.contact-channel-box:nth-child(1) div div div:nth-child(2)', data.wa_title);
    setHtml('.contact-channel-box:nth-child(1) div div div:last-child', data.wa_btn);
    setHtml('.email-support-card div div div:first-child', data.email_title);
    setHtml('#landingContactForm div:nth-child(1) label', data.form_name);
    setHtml('#landingContactForm div:nth-child(2) label', data.form_phone);
    setHtml('#landingContactForm div:nth-child(3) label', data.form_vehicle);
    setHtml('#landingContactForm div:nth-child(4) label', data.form_city);
    setHtml('#landingContactForm div:nth-child(5) label', data.form_qty);
    setHtml('#landingContactForm select[name="quantity"] option[value="1"]', data.form_opt1);
    setHtml('#landingContactForm select[name="quantity"] option[value="5"]', data.form_opt2);
    setHtml('#landingContactForm select[name="quantity"] option[value="10"]', data.form_opt3);
    setHtml('#landingContactForm select[name="quantity"] option[value="50+"]', data.form_opt4);
    setHtml('#landingContactForm div:nth-child(6) label', data.form_msg);
    setHtml('#landingContactForm button[type="submit"]', data.form_btn);
    setHtml('#landingContactForm + div', data.form_safe_notice);

    // Floating WhatsApp Bubble Widget
    setHtml('#waChatBubble div:first-child', data.wa_widget_title);
    const bubbleElem = document.getElementById('waChatBubble');
    if (bubbleElem && data.wa_widget_msg) {
        // Keep close button and header intact
        const closeBtnHTML = '<button class="wa-bubble-close" onclick="document.getElementById(\'waChatBubble\').style.display=\'none\'"><i class="fa-solid fa-xmark"></i></button>';
        const headerHTML = '<div style="font-weight: 800; color: #047857; margin-bottom: 3px;">' + data.wa_widget_title + '</div>';
        bubbleElem.innerHTML = closeBtnHTML + headerHTML + data.wa_widget_msg;
    }

    // Footer Section
    setHtml('footer p', data.footer_p);
    setHtml('footer a[href="#how-it-works"]', data.footer_l1);
    setHtml('footer a[href="#hazards"]', data.footer_l2);
    setHtml('footer a[href="#why-us"]', data.footer_l3);
    setHtml('footer a[href="#faq"]', data.footer_l4);
    setHtml('footer a[href="#contact"]', data.footer_l5);
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

// Continuously suppress Google Translate floating top-left widgets & banners
setInterval(function() {
    const elements = document.querySelectorAll('.skiptranslate, iframe.skiptranslate, #goog-gt-tt, div[class*="VIpgJd-"], [id^="goog-gt-"]');
    elements.forEach(function(el) {
        if (el && el.id !== 'customLangDropdown' && !el.closest('#customLangDropdown')) {
            el.style.setProperty('display', 'none', 'important');
            el.style.setProperty('visibility', 'hidden', 'important');
            el.style.setProperty('opacity', '0', 'important');
            el.style.setProperty('pointer-events', 'none', 'important');
        }
    });
}, 250);

// Auto-restore saved language preference on load
document.addEventListener('DOMContentLoaded', function () {
    const savedCode = localStorage.getItem('selectedLangCode');
    if (savedCode) {
        selectCustomLang(savedCode, false);
        applyNativeTranslation(savedCode);
        if (savedCode !== 'en') {
            setGoogleTranslateCookie(savedCode);
            setTimeout(() => applyGoogleTranslate(savedCode), 500);
        }
    }
});


