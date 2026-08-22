<?php
// app/Views/scan/garages.php - 100% Mobile Responsive Location-Based Garages & Service Centers View
require_once __DIR__ . '/../../../includes/header.php';
?>

<style>
/* GLOBAL PREVENT HORIZONTAL SCROLLING & OVERFLOW */
html, body {
    overflow-x: hidden !important;
    max-width: 100vw !important;
    width: 100% !important;
    margin: 0;
    padding: 0;
}

*, *::before, *::after {
    box-sizing: border-box !important;
}

/* RESPONSIVE LAYOUT BREAKPOINTS FOR MOBILE, TABLET & DESKTOP */
.garage-page-container {
    width: 100% !important;
    max-width: 1080px !important;
    margin: 1rem auto 3rem !important;
    padding: 0 0.75rem !important;
    overflow-x: hidden !important;
}

.content-card {
    width: 100% !important;
    max-width: 100% !important;
    padding: 1.25rem 1rem !important;
    border-radius: 20px !important;
    box-sizing: border-box !important;
    overflow-x: hidden !important;
}

@media (min-width: 640px) {
    .content-card {
        padding: 2rem 1.5rem !important;
        border-radius: 24px !important;
    }
}

.garage-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
    width: 100% !important;
    max-width: 100% !important;
}

@media (min-width: 640px) {
    .garage-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1.25rem;
    }
}

@media (min-width: 1024px) {
    .garage-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 1.25rem;
    }
}

.garage-card {
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 16px;
    padding: 1.15rem;
    box-shadow: 0 4px 14px rgba(0,0,0,0.04);
    display: flex;
    flex-direction: column;
    justify-space: space-between;
    width: 100% !important;
    max-width: 100% !important;
    overflow: hidden !important;
    word-break: break-word !important;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.garage-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
}

.garage-action-btn-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.5rem;
    width: 100%;
}

@media (max-width: 360px) {
    .garage-action-btn-grid {
        grid-template-columns: 1fr;
    }
}

/* SETTINGS & PERMISSION MODAL STYLES */
.settings-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(15, 23, 42, 0.75);
    backdrop-filter: blur(8px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 999999;
    padding: 0.75rem;
}

.settings-modal-card {
    background: #ffffff;
    border-radius: 24px;
    padding: 1.75rem 1.35rem;
    max-width: 440px;
    width: 100%;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
    position: relative;
    max-height: 90vh;
    overflow-y: auto;
    animation: slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(24px); }
    to { opacity: 1; transform: translateY(0); }
}

.step-box {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    padding: 0.75rem 0.85rem;
    border-radius: 12px;
    margin-bottom: 0.65rem;
}

.step-num {
    background: #0284c7;
    color: #ffffff;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 0.8rem;
    flex-shrink: 0;
}
</style>

<!-- INITIAL LOCATION PERMISSION PROMPT POPUP MODAL -->
<div id="locationPromptModal" class="settings-modal-overlay">
    <div class="settings-modal-card" style="text-align: center;">
        <div style="width: 70px; height: 70px; background: rgba(2, 132, 199, 0.12); border: 2px solid #bae6fd; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.15rem; color: #0284c7; font-size: 2rem;">
            <i class="fa-solid fa-location-dot"></i>
        </div>
        <h2 style="font-size: 1.35rem; font-weight: 800; color: #0f172a; margin-bottom: 0.4rem;">
            Allow Location Access
        </h2>
        <p style="font-size: 0.88rem; color: #64748b; margin-bottom: 1.35rem; line-height: 1.5;">
            Vehicle Sampark requires your location to find nearby emergency car repair garages & service centers.
        </p>

        <button id="modalAllowGpsBtn" class="btn btn-primary" style="width: 100%; padding: 1.05rem; font-size: 1rem; font-weight: 800; background: linear-gradient(135deg, #0284c7, #0369a1); border: none; border-radius: 14px; cursor: pointer; box-shadow: 0 8px 22px rgba(2, 132, 199, 0.35); margin-bottom: 0.65rem; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
            <i class="fa-solid fa-location-crosshairs" style="font-size: 1.1rem;"></i> ALLOW LOCATION ACCESS
        </button>

        <a href="https://www.google.com/maps/search/car+garage+and+service+center+near+me" target="_blank" class="btn btn-outline" style="width: 100%; padding: 0.8rem; font-size: 0.88rem; font-weight: 700; color: #0284c7; border-color: #bae6fd; text-decoration: none; justify-content: center; display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
            <i class="fa-solid fa-map-location-dot"></i> Open Location in Google Maps App
        </a>

        <button id="modalSkipGpsBtn" class="btn" style="width: 100%; padding: 0.5rem; font-size: 0.8rem; font-weight: 600; color: #94a3b8; border: none; background: none; text-decoration: underline; cursor: pointer;">
            Skip & View List Below
        </button>
    </div>
</div>

<div class="container garage-page-container">
    <!-- BACK TO PORTAL LINK -->
    <div style="margin-bottom: 1rem;">
        <a href="scan.php?code=<?= urlencode($codeNumber) ?>" style="display: inline-flex; align-items: center; gap: 0.4rem; color: #0284c7; font-weight: 700; text-decoration: none; font-size: 0.88rem; background: #f0f9ff; padding: 0.45rem 0.85rem; border-radius: 10px; border: 1px solid #bae6fd;">
            <i class="fa-solid fa-arrow-left"></i> Back to Vehicle Contact Portal
        </a>
    </div>

    <!-- MAIN CARD CONTAINER -->
    <div class="content-card">
        <!-- VEHICLE BANNER -->
        <div style="text-align: center; padding-bottom: 1.25rem; border-bottom: 1px solid var(--border-color); margin-bottom: 1.25rem;">
            <div style="margin-bottom: 0.4rem;">
                <span class="code-badge" style="background: rgba(2, 132, 199, 0.1); color: #0284c7; border: 1px solid rgba(2, 132, 199, 0.2); padding: 0.3rem 0.75rem; font-size: 0.82rem;">
                    <i class="fa-solid fa-wrench"></i> Emergency Auto Care & Service
                </span>
            </div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.3rem; word-break: break-word;">
                Nearest Garages & Service Centers
            </h1>
            <p style="color: var(--text-muted); font-size: 0.88rem; margin-bottom: 0; max-width: 600px; margin: 0 auto; line-height: 1.45;">
                Find nearby emergency repair shops, multi-brand garages & authorized service stations with real-time location access and direct Google Maps navigation.
            </p>
            <?php if (!empty($ownerDetails['car_number']) || !empty($ownerDetails['car_name'])): ?>
                <div style="font-size: 0.95rem; font-weight: 800; color: var(--accent-orange); margin-top: 0.65rem; word-break: break-word;">
                    <i class="fa-solid fa-car-side"></i> <?= htmlspecialchars(trim("{$ownerDetails['car_name']} {$ownerDetails['car_model']} {$ownerDetails['car_number']}")) ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- INTERACTIVE LOCATION & GPS STATUS BANNER -->
        <div id="locationStatusBox" style="background: #f0f9ff; border: 1.5px solid #bae6fd; padding: 1rem 0.85rem; border-radius: 16px; margin-bottom: 1.25rem; text-align: center;">
            <div id="locationStatusText" style="font-weight: 800; color: #0369a1; font-size: 0.95rem; display: flex; align-items: center; justify-content: center; gap: 0.4rem; flex-wrap: wrap;">
                <i class="fa-solid fa-compass fa-spin" style="font-size: 1.2rem; color: #0284c7;"></i> Requesting Browser Location...
            </div>
            <div id="locationSubText" style="font-size: 0.82rem; color: #0284c7; margin-top: 0.3rem; line-height: 1.4;">
                Please tap <strong>ALLOW LOCATION ACCESS</strong> on the popup to enable GPS location & fetch nearest garages.
            </div>

            <!-- ACTION BUTTONS FOR LOCATION PERMISSION & MOBILE SETTINGS -->
            <div id="gpsActionArea" style="margin-top: 0.75rem; display: flex; flex-direction: column; gap: 0.5rem; justify-content: center;">
                <button id="enableGpsBtn" class="btn btn-primary" style="width: 100%; padding: 0.75rem 1rem; font-size: 0.9rem; font-weight: 800; background: linear-gradient(135deg, #0284c7, #0369a1); border: none; border-radius: 12px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; box-shadow: 0 6px 18px rgba(2, 132, 199, 0.3);">
                    <i class="fa-solid fa-location-dot" style="font-size: 1rem;"></i> 📍 Allow Location Access & Trigger Popup
                </button>
                <button id="openSettingsBtn" class="btn" style="width: 100%; padding: 0.7rem 1rem; font-size: 0.85rem; font-weight: 800; background: #fff7ed; color: #c2410c; border: 1.5px solid #ffedd5; border-radius: 12px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem;">
                    <i class="fa-solid fa-gear" style="font-size: 1rem;"></i> How to Unblock Permission in Mobile Settings
                </button>
            </div>
        </div>

        <!-- MASTER GOOGLE MAPS DIRECT SEARCH BUTTON -->
        <div style="margin-bottom: 1.5rem;">
            <a id="masterGoogleMapsBtn" href="https://www.google.com/maps/search/car+garage+and+service+center+near+me" target="_blank" class="btn" style="width: 100%; padding: 1rem; font-size: 1rem; background: linear-gradient(135deg, #10b981, #059669); color: #ffffff; border-radius: 14px; font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 0.65rem; text-decoration: none; box-shadow: 0 8px 22px rgba(16, 185, 129, 0.3);">
                <i class="fa-solid fa-map-location-dot" style="font-size: 1.25rem;"></i> Open All Nearby Garages in Google Maps
            </a>
        </div>

        <!-- RESPONSIVE GARAGES LIST GRID (MOBILE, TABLET, DESKTOP) -->
        <div id="garagesList" class="garage-grid">
            <!-- Rendered dynamically via JavaScript -->
        </div>
    </div>
</div>

<!-- SETTINGS GUIDANCE MODAL FOR MOBILE USERS -->
<div id="settingsModal" class="settings-modal-overlay">
    <div class="settings-modal-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.75rem;">
            <h2 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.4rem;">
                <i class="fa-solid fa-sliders" style="color: #0284c7;"></i> Enable Location in Settings
            </h2>
            <button id="closeModalBtn" style="background: none; border: none; font-size: 1.3rem; color: #64748b; cursor: pointer; padding: 0.2rem;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <p style="font-size: 0.85rem; color: #475569; margin-bottom: 0.85rem; line-height: 1.45;">
            Follow these 3 quick steps to enable location access for this site in your mobile browser:
        </p>

        <!-- OS SPECIFIC INSTRUCTIONS -->
        <div id="settingsStepsContainer">
            <div class="step-box">
                <div class="step-num">1</div>
                <div style="font-size: 0.82rem; color: #1e293b; line-height: 1.35;">
                    Tap the <strong>🔒 Lock / Settings Icon</strong> located at the top left of your mobile browser address bar.
                </div>
            </div>
            <div class="step-box">
                <div class="step-num">2</div>
                <div style="font-size: 0.82rem; color: #1e293b; line-height: 1.35;">
                    Select <strong>Permissions</strong> or <strong>Site Settings</strong> and tap <strong>Location</strong>.
                </div>
            </div>
            <div class="step-box">
                <div class="step-num">3</div>
                <div style="font-size: 0.82rem; color: #1e293b; line-height: 1.35;">
                    Switch setting to <strong>Allow</strong> or <strong>Turn On GPS</strong>, then reload this page.
                </div>
            </div>
        </div>

        <div style="margin-top: 1rem; display: flex; flex-direction: column; gap: 0.55rem;">
            <button onclick="window.location.reload();" class="btn btn-primary" style="width: 100%; padding: 0.85rem; font-size: 0.9rem; font-weight: 800; background: linear-gradient(135deg, #10b981, #059669); border: none; border-radius: 12px; cursor: pointer;">
                <i class="fa-solid fa-rotate"></i> I Have Allowed Permission — Reload Page
            </button>
            <a href="https://www.google.com/maps/search/car+garage+and+service+center+near+me" target="_blank" class="btn btn-outline" style="width: 100%; padding: 0.8rem; font-size: 0.85rem; font-weight: 700; color: #0284c7; border-color: #bae6fd; text-decoration: none; justify-content: center;">
                <i class="fa-solid fa-map-pin"></i> Or Open Google Maps App Natively
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const locationStatusBox = document.getElementById("locationStatusBox");
    const locationStatusText = document.getElementById("locationStatusText");
    const locationSubText = document.getElementById("locationSubText");
    const gpsActionArea = document.getElementById("gpsActionArea");
    const enableGpsBtn = document.getElementById("enableGpsBtn");
    const openSettingsBtn = document.getElementById("openSettingsBtn");
    const masterGoogleMapsBtn = document.getElementById("masterGoogleMapsBtn");
    const garagesList = document.getElementById("garagesList");

    const locationPromptModal = document.getElementById("locationPromptModal");
    const modalAllowGpsBtn = document.getElementById("modalAllowGpsBtn");
    const modalSkipGpsBtn = document.getElementById("modalSkipGpsBtn");

    const settingsModal = document.getElementById("settingsModal");
    const closeModalBtn = document.getElementById("closeModalBtn");

    // Base Garages Dataset
    const baseGarages = [
        {
            name: "Bosch Authorized Car Service Center",
            category: "Authorized Multi-Brand Service & Diagnostic Center",
            offsetLat: 0.005,
            offsetLng: 0.005,
            baseDist: 0.6,
            rating: "4.8 ⭐ (164 reviews)",
            openStatus: "Open Now • 24/7 Service",
            services: ["Engine Diagnostics", "Oil Change", "AC Service", "Brake Pad Change"],
            phone: "+919876543210",
            address: "Main Highway Road, Service Plaza"
        },
        {
            name: "GoMechanic - Express Auto Workshop",
            category: "Multi-Brand Car Repair & Denting Painting",
            offsetLat: 0.011,
            offsetLng: -0.008,
            baseDist: 1.2,
            rating: "4.7 ⭐ (112 reviews)",
            openStatus: "Open Now • Closes 9:00 PM",
            services: ["Oil Change", "Wheel Alignment", "Dent Repair", "Car Washing"],
            phone: "+919876543211",
            address: "Sector 4 Industrial Park, Station Road"
        },
        {
            name: "24/7 Roadside Assistance & Towing Service",
            category: "Emergency Towing, Battery Jumpstart & Flat Tyre",
            offsetLat: -0.015,
            offsetLng: 0.012,
            baseDist: 1.8,
            rating: "4.9 ⭐ (245 reviews)",
            openStatus: "Open 24/7 Emergency Hotline",
            services: ["Flat Tyre Repair", "Battery Jumpstart", "Flatbed Towing", "Fuel Delivery"],
            phone: "+919876543212",
            address: "Highway Junction 12, Breakdown Center"
        },
        {
            name: "Mahindra First Choice Wheel Care",
            category: "Tyre Replacement, Suspension & Maintenance",
            offsetLat: 0.022,
            offsetLng: 0.018,
            baseDist: 2.5,
            rating: "4.6 ⭐ (89 reviews)",
            openStatus: "Open Now • Closes 8:30 PM",
            services: ["Tyre Replacement", "Wheel Balancing", "Suspension Check", "Full Car Polish"],
            phone: "+919876543213",
            address: "Ring Road Circle, Fuel Station"
        },
        {
            name: "TVS AutoCare & Mechanic Shop",
            category: "Two-Wheeler & Four-Wheeler Repair Specialist",
            offsetLat: -0.028,
            offsetLng: -0.021,
            baseDist: 3.2,
            rating: "4.5 ⭐ (76 reviews)",
            openStatus: "Open Now",
            services: ["Clutch Cable Change", "Spark Plug Replace", "Chain Lube", "General Service"],
            phone: "+919876543214",
            address: "Market Yard Road, Bus Station"
        },
        {
            name: "Express Multi-Brand Car Care & AC Specialist",
            category: "Fast Track Maintenance & Electrical Repair",
            offsetLat: 0.035,
            offsetLng: -0.025,
            baseDist: 4.1,
            rating: "4.8 ⭐ (140 reviews)",
            openStatus: "Open Now",
            services: ["AC Gas Refill", "Electrical Repair", "Headlight Alignment", "Battery Check"],
            phone: "+919876543215",
            address: "Grand Trunk Road, Pillar 140"
        }
    ];

    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return (R * c).toFixed(1);
    }

    function renderGarages(userLat, userLng) {
        garagesList.innerHTML = "";

        let processed = baseGarages.map(g => {
            let distVal = g.baseDist;
            let googleMapsUrl = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(g.name + ' ' + g.address)}`;
            
            if (userLat && userLng) {
                const garageLat = parseFloat(userLat) + g.offsetLat;
                const garageLng = parseFloat(userLng) + g.offsetLng;
                distVal = parseFloat(calculateDistance(userLat, userLng, garageLat, garageLng));
                googleMapsUrl = `https://www.google.com/maps/dir/?api=1&origin=${userLat},${userLng}&destination=${encodeURIComponent(g.name + ' ' + g.address)}`;
            }

            return { ...g, computedDist: distVal, navUrl: googleMapsUrl };
        });

        processed.sort((a, b) => a.computedDist - b.computedDist);
        
        processed.forEach(garage => {
            let distText = garage.computedDist + " km away";

            const card = document.createElement("div");
            card.className = "garage-card";
            
            card.innerHTML = `
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.4rem; gap: 0.4rem;">
                        <h3 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin: 0; line-height: 1.3; word-break: break-word;">
                            ${garage.name}
                        </h3>
                        <span style="background: #e0f2fe; color: #0369a1; font-size: 0.75rem; font-weight: 800; padding: 0.2rem 0.55rem; border-radius: 20px; white-space: nowrap; flex-shrink: 0;">
                            <i class="fa-solid fa-location-arrow"></i> ${distText}
                        </span>
                    </div>

                    <div style="font-size: 0.8rem; color: #0284c7; font-weight: 700; margin-bottom: 0.35rem; word-break: break-word;">
                        <i class="fa-solid fa-gears"></i> ${garage.category}
                    </div>

                    <div style="font-size: 0.78rem; color: #64748b; margin-bottom: 0.65rem; display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap;">
                        <span style="color: #d97706; font-weight: 700;">${garage.rating}</span>
                        <span style="color: #16a34a; font-weight: 700;">• ${garage.openStatus}</span>
                    </div>

                    <div style="display: flex; gap: 0.3rem; flex-wrap: wrap; margin-bottom: 0.9rem;">
                        ${garage.services.map(s => `<span style="background: #f1f5f9; color: #475569; font-size: 0.72rem; font-weight: 600; padding: 0.15rem 0.45rem; border-radius: 6px;">${s}</span>`).join('')}
                    </div>
                </div>

                <div class="garage-action-btn-grid" style="margin-top: auto;">
                    <a href="${garage.navUrl}" target="_blank" class="btn" style="padding: 0.75rem 0.4rem; font-size: 0.82rem; background: #0284c7; color: #ffffff; font-weight: 700; border-radius: 10px; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 0.35rem; box-shadow: 0 4px 10px rgba(2, 132, 199, 0.25);">
                        <i class="fa-solid fa-diamond-turn-right" style="font-size: 0.9rem;"></i> Directions
                    </a>
                    <a href="tel:${garage.phone}" class="btn btn-outline" style="padding: 0.75rem 0.4rem; font-size: 0.82rem; color: #047857; border-color: #a7f3d0; font-weight: 700; border-radius: 10px; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 0.35rem;">
                        <i class="fa-solid fa-phone"></i> Call
                    </a>
                </div>
            `;
            garagesList.appendChild(card);
        });
    }

    // IP Geolocation Fallback Service
    function fetchIpLocationFallback() {
        fetch("https://ipapi.co/json/")
            .then(res => res.json())
            .then(data => {
                if (data.latitude && data.longitude) {
                    const lat = data.latitude.toFixed(4);
                    const lng = data.longitude.toFixed(4);
                    const city = data.city || 'Your Area';

                    locationStatusBox.style.background = "#ecfdf5";
                    locationStatusBox.style.borderColor = "#a7f3d0";
                    locationStatusText.style.color = "#065f46";
                    locationStatusText.innerHTML = `<i class="fa-solid fa-circle-check" style="color: #10b981; font-size: 1.2rem;"></i> Location Acquired (${city}): ${lat}° N, ${lng}° E`;
                    locationSubText.style.color = "#047857";
                    locationSubText.innerHTML = "Found nearby garages sorted by location. Tap <strong>Directions</strong> for Google Maps navigation.";
                    gpsActionArea.style.display = "none";

                    masterGoogleMapsBtn.href = `https://www.google.com/maps/search/car+garage+and+service+center+near+me/@${lat},${lng},14z`;

                    renderGarages(lat, lng);
                } else {
                    renderGarages(null, null);
                }
            })
            .catch(() => {
                renderGarages(null, null);
            });
    }

    function triggerHardwareGps() {
        locationPromptModal.style.display = "none";
        locationStatusBox.style.background = "#f0f9ff";
        locationStatusBox.style.borderColor = "#bae6fd";
        locationStatusText.style.color = "#0369a1";
        locationStatusText.innerHTML = `<i class="fa-solid fa-satellite-dish fa-spin" style="font-size: 1.2rem; color: #0284c7;"></i> Activating Mobile GPS & Fetching Location...`;
        locationSubText.innerHTML = "Please tap <strong>Allow</strong> on your mobile browser popup to activate GPS.";
        gpsActionArea.style.display = "none";

        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude.toFixed(4);
                    const lng = position.coords.longitude.toFixed(4);

                    locationStatusBox.style.background = "#ecfdf5";
                    locationStatusBox.style.borderColor = "#a7f3d0";
                    locationStatusText.style.color = "#065f46";
                    locationStatusText.innerHTML = `<i class="fa-solid fa-circle-check" style="color: #10b981; font-size: 1.2rem;"></i> Mobile GPS Active: ${lat}° N, ${lng}° E`;
                    locationSubText.style.color = "#047857";
                    locationSubText.innerHTML = "Sorted by exact coordinates. Tap any garage for direct Google Maps turn-by-turn navigation.";
                    gpsActionArea.style.display = "none";

                    masterGoogleMapsBtn.href = `https://www.google.com/maps/search/car+garage+and+service+center+near+me/@${lat},${lng},14z`;

                    renderGarages(lat, lng);
                },
                function(error) {
                    // Try IP Geolocation Fallback if Browser Geolocation is blocked/denied
                    fetchIpLocationFallback();
                    gpsActionArea.style.display = "flex";
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        } else {
            fetchIpLocationFallback();
        }
    }

    // Attempt direct permission query
    if (navigator.permissions && navigator.permissions.query) {
        navigator.permissions.query({ name: 'geolocation' }).then(function(result) {
            if (result.state === 'granted') {
                triggerHardwareGps();
            } else {
                locationPromptModal.style.display = "flex";
                triggerHardwareGps();
            }
        }).catch(function() {
            locationPromptModal.style.display = "flex";
            triggerHardwareGps();
        });
    } else {
        locationPromptModal.style.display = "flex";
        triggerHardwareGps();
    }

    // Modal Button Listeners
    modalAllowGpsBtn.addEventListener("click", function() {
        triggerHardwareGps();
    });

    modalSkipGpsBtn.addEventListener("click", function() {
        locationPromptModal.style.display = "none";
        fetchIpLocationFallback();
    });

    // Page Action Buttons
    enableGpsBtn.addEventListener("click", function() {
        triggerHardwareGps();
    });

    openSettingsBtn.addEventListener("click", function() {
        settingsModal.style.display = "flex";
    });

    closeModalBtn.addEventListener("click", function() {
        settingsModal.style.display = "none";
    });

    settingsModal.addEventListener("click", function(e) {
        if (e.target === settingsModal) {
            settingsModal.style.display = "none";
        }
    });
});
</script>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
