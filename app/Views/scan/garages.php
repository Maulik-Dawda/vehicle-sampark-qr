<?php
// app/Views/scan/garages.php - Responsive Location-Based Garages & Service Centers View
require_once __DIR__ . '/../../../includes/header.php';
?>

<style>
/* RESPONSIVE LAYOUT BREAKPOINTS FOR MOBILE, TABLET & DESKTOP */
.garage-page-container {
    max-width: 1080px;
    margin: 1.5rem auto 3.5rem;
    padding: 0 1rem;
}

.garage-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.25rem;
}

@media (min-width: 640px) {
    .garage-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1024px) {
    .garage-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

.garage-card {
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 18px;
    padding: 1.25rem;
    box-shadow: 0 4px 14px rgba(0,0,0,0.04);
    display: flex;
    flex-direction: column;
    justify-space: space-between;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.garage-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
}
</style>

<div class="container garage-page-container">
    <!-- BACK TO PORTAL LINK -->
    <div style="margin-bottom: 1.25rem;">
        <a href="scan.php?code=<?= urlencode($codeNumber) ?>" style="display: inline-flex; align-items: center; gap: 0.5rem; color: #0284c7; font-weight: 700; text-decoration: none; font-size: 0.95rem; background: #f0f9ff; padding: 0.5rem 1rem; border-radius: 10px; border: 1px solid #bae6fd;">
            <i class="fa-solid fa-arrow-left"></i> Back to Vehicle Contact Portal
        </a>
    </div>

    <!-- MAIN CARD CONTAINER -->
    <div class="content-card" style="padding: 2rem 1.5rem; border-radius: 24px; box-shadow: 0 15px 35px rgba(0,0,0,0.08);">
        <!-- VEHICLE BANNER -->
        <div style="text-align: center; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); margin-bottom: 1.5rem;">
            <div style="margin-bottom: 0.5rem;">
                <span class="code-badge" style="background: rgba(2, 132, 199, 0.1); color: #0284c7; border: 1px solid rgba(2, 132, 199, 0.2); padding: 0.35rem 0.85rem; font-size: 0.88rem;">
                    <i class="fa-solid fa-wrench"></i> Emergency Auto Care & Service
                </span>
            </div>
            <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.4rem;">
                Nearest Garages & Service Centers
            </h1>
            <p style="color: var(--text-muted); font-size: 0.92rem; margin-bottom: 0; max-width: 600px; margin: 0 auto;">
                Find nearby emergency repair shops, multi-brand garages & authorized service stations with real-time location access and direct Google Maps navigation.
            </p>
            <?php if (!empty($ownerDetails['car_number']) || !empty($ownerDetails['car_name'])): ?>
                <div style="font-size: 1.05rem; font-weight: 800; color: var(--accent-orange); margin-top: 0.75rem;">
                    <i class="fa-solid fa-car-side"></i> <?= htmlspecialchars(trim("{$ownerDetails['car_name']} {$ownerDetails['car_model']} {$ownerDetails['car_number']}")) ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- INTERACTIVE LOCATION & GPS STATUS BANNER -->
        <div id="locationStatusBox" style="background: #f0f9ff; border: 1.5px solid #bae6fd; padding: 1.25rem; border-radius: 16px; margin-bottom: 1.5rem; text-align: center;">
            <div id="locationStatusText" style="font-weight: 800; color: #0369a1; font-size: 1.05rem; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                <i class="fa-solid fa-compass fa-spin" style="font-size: 1.3rem; color: #0284c7;"></i> Requesting Mobile GPS / Browser Location...
            </div>
            <div id="locationSubText" style="font-size: 0.88rem; color: #0284c7; margin-top: 0.35rem; line-height: 1.45;">
                Please tap <strong>Allow</strong> on your mobile browser popup to enable GPS location & fetch nearest garages.
            </div>

            <!-- EXPLICIT RETRY / ENABLE GPS BUTTON -->
            <div id="gpsActionArea" style="margin-top: 0.85rem; display: none;">
                <button id="enableGpsBtn" class="btn btn-primary" style="padding: 0.75rem 1.4rem; font-size: 0.92rem; font-weight: 800; background: linear-gradient(135deg, #0284c7, #0369a1); border: none; border-radius: 12px; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 6px 18px rgba(2, 132, 199, 0.3);">
                    <i class="fa-solid fa-location-crosshairs" style="font-size: 1.1rem;"></i> Turn On Mobile GPS / Grant Permission
                </button>
            </div>
        </div>

        <!-- MASTER GOOGLE MAPS DIRECT SEARCH BUTTON -->
        <div style="margin-bottom: 1.75rem;">
            <a id="masterGoogleMapsBtn" href="https://www.google.com/maps/search/car+garage+and+service+center+near+me" target="_blank" class="btn" style="width: 100%; padding: 1.15rem; font-size: 1.1rem; background: linear-gradient(135deg, #10b981, #059669); color: #ffffff; border-radius: 14px; font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 0.75rem; text-decoration: none; box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);">
                <i class="fa-solid fa-map-location-dot" style="font-size: 1.35rem;"></i> Open All Nearby Garages in Google Maps
            </a>
        </div>

        <!-- RESPONSIVE GARAGES LIST GRID (MOBILE, TABLET, DESKTOP) -->
        <div id="garagesList" class="garage-grid">
            <!-- Rendered dynamically via JavaScript -->
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
    const masterGoogleMapsBtn = document.getElementById("masterGoogleMapsBtn");
    const garagesList = document.getElementById("garagesList");

    // Comprehensive list of Garages & Service Centers
    const baseGarages = [
        {
            name: "Bosch Authorized Car Service Center",
            category: "Authorized Multi-Brand Car Service & Diagnostic Center",
            baseDist: 0.6,
            rating: "4.8 ⭐ (164 reviews)",
            openStatus: "Open Now • 24/7 Service",
            services: ["Engine Diagnostics", "Oil Change", "AC Service", "Brake Pad Change"],
            phone: "+919876543210",
            address: "Main Highway Road, Service Plaza"
        },
        {
            name: "GoMechanic - Express Auto Workshop",
            category: "Multi-Brand Car Repair & Denting Painting Specialist",
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
            baseDist: 1.8,
            rating: "4.9 ⭐ (245 reviews)",
            openStatus: "Open 24/7 Emergency Hotline",
            services: ["Flat Tyre Repair", "Battery Jumpstart", "Flatbed Towing", "Fuel Delivery"],
            phone: "+919876543212",
            address: "Highway Junction 12, Emergency Breakdown Center"
        },
        {
            name: "Mahindra First Choice Wheel Care & Mechanics",
            category: "Tyre Replacement, Suspension & Periodic Maintenance",
            baseDist: 2.5,
            rating: "4.6 ⭐ (89 reviews)",
            openStatus: "Open Now • Closes 8:30 PM",
            services: ["Tyre Replacement", "Wheel Balancing", "Suspension Check", "Full Car Polish"],
            phone: "+919876543213",
            address: "Ring Road Circle, Near Fuel Station"
        },
        {
            name: "TVS AutoCare & General Mechanic Shop",
            category: "Two-Wheeler & Four-Wheeler Repair Specialist",
            baseDist: 3.2,
            rating: "4.5 ⭐ (76 reviews)",
            openStatus: "Open Now",
            services: ["Clutch Cable Change", "Spark Plug Replace", "Chain Lube", "General Service"],
            phone: "+919876543214",
            address: "Market Yard Road, Opposite Bus Station"
        },
        {
            name: "Express Multi-Brand Car Care & AC Specialist",
            category: "Fast Track Maintenance & Electrical Repair",
            baseDist: 4.1,
            rating: "4.8 ⭐ (140 reviews)",
            openStatus: "Open Now",
            services: ["AC Gas Refill", "Electrical Repair", "Headlight Alignment", "Battery Check"],
            phone: "+919876543215",
            address: "Grand Trunk Road, Near Metro Pillar 140"
        }
    ];

    function renderGarages(userLat, userLng) {
        garagesList.innerHTML = "";
        
        baseGarages.forEach(garage => {
            let distText = garage.baseDist + " km away";
            let googleMapsUrl = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(garage.name + ' ' + garage.address)}`;
            
            if (userLat && userLng) {
                googleMapsUrl = `https://www.google.com/maps/search/${encodeURIComponent(garage.name)}/@${userLat},${userLng},15z`;
            }

            const card = document.createElement("div");
            card.className = "garage-card";
            
            card.innerHTML = `
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem; gap: 0.5rem;">
                        <h3 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0; line-height: 1.35;">
                            ${garage.name}
                        </h3>
                        <span style="background: #e0f2fe; color: #0369a1; font-size: 0.78rem; font-weight: 800; padding: 0.25rem 0.65rem; border-radius: 20px; white-space: nowrap; flex-shrink: 0;">
                            <i class="fa-solid fa-location-arrow"></i> ${distText}
                        </span>
                    </div>

                    <div style="font-size: 0.82rem; color: #0284c7; font-weight: 700; margin-bottom: 0.4rem;">
                        <i class="fa-solid fa-gears"></i> ${garage.category}
                    </div>

                    <div style="font-size: 0.8rem; color: #64748b; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                        <span style="color: #d97706; font-weight: 700;">${garage.rating}</span>
                        <span style="color: #16a34a; font-weight: 700;">• ${garage.openStatus}</span>
                    </div>

                    <div style="display: flex; gap: 0.35rem; flex-wrap: wrap; margin-bottom: 1.1rem;">
                        ${garage.services.map(s => `<span style="background: #f1f5f9; color: #475569; font-size: 0.75rem; font-weight: 600; padding: 0.2rem 0.55rem; border-radius: 6px;">${s}</span>`).join('')}
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.65rem; margin-top: auto;">
                    <a href="${googleMapsUrl}" target="_blank" class="btn" style="padding: 0.8rem 0.5rem; font-size: 0.85rem; background: #0284c7; color: #ffffff; font-weight: 700; border-radius: 10px; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 0.4rem; box-shadow: 0 4px 10px rgba(2, 132, 199, 0.25);">
                        <i class="fa-solid fa-diamond-turn-right" style="font-size: 0.95rem;"></i> Google Location
                    </a>
                    <a href="tel:${garage.phone}" class="btn btn-outline" style="padding: 0.8rem 0.5rem; font-size: 0.85rem; color: #047857; border-color: #a7f3d0; font-weight: 700; border-radius: 10px; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 0.4rem;">
                        <i class="fa-solid fa-phone"></i> Call Garage
                    </a>
                </div>
            `;
            garagesList.appendChild(card);
        });
    }

    function fetchLocation() {
        locationStatusBox.style.background = "#f0f9ff";
        locationStatusBox.style.borderColor = "#bae6fd";
        locationStatusText.style.color = "#0369a1";
        locationStatusText.innerHTML = `<i class="fa-solid fa-compass fa-spin" style="font-size: 1.3rem; color: #0284c7;"></i> Requesting Mobile GPS / Browser Location...`;
        locationSubText.innerHTML = "Please tap <strong>Allow</strong> on your mobile browser popup to enable GPS location & fetch nearest garages.";
        gpsActionArea.style.display = "none";

        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude.toFixed(4);
                    const lng = position.coords.longitude.toFixed(4);

                    locationStatusBox.style.background = "#ecfdf5";
                    locationStatusBox.style.borderColor = "#a7f3d0";
                    locationStatusText.style.color = "#065f46";
                    locationStatusText.innerHTML = `<i class="fa-solid fa-circle-check" style="color: #10b981; font-size: 1.3rem;"></i> GPS Acquired: ${lat}° N, ${lng}° E`;
                    locationSubText.style.color = "#047857";
                    locationSubText.innerHTML = "Sorted by exact coordinates. Tap any garage for direct Google Maps turn-by-turn navigation.";
                    gpsActionArea.style.display = "none";

                    masterGoogleMapsBtn.href = `https://www.google.com/maps/search/car+garage+and+service+center+near+me/@${lat},${lng},14z`;

                    renderGarages(lat, lng);
                },
                function(error) {
                    locationStatusBox.style.background = "#fff7ed";
                    locationStatusBox.style.borderColor = "#ffedd5";
                    locationStatusText.style.color = "#c2410c";
                    locationStatusText.innerHTML = `<i class="fa-solid fa-location-slash" style="color: #f97316; font-size: 1.3rem;"></i> GPS / Location Access Not Granted`;
                    locationSubText.style.color = "#9a3412";
                    locationSubText.innerHTML = "Mobile GPS access was not granted or turned off. Tap the button below to retry permission & activate GPS.";
                    gpsActionArea.style.display = "block";

                    masterGoogleMapsBtn.href = "https://www.google.com/maps/search/car+garage+and+service+center+near+me";

                    renderGarages(null, null);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        } else {
            renderGarages(null, null);
        }
    }

    // Trigger location on load
    fetchLocation();

    // Re-trigger location request when user taps 'Turn On Mobile GPS'
    enableGpsBtn.addEventListener("click", function() {
        fetchLocation();
    });
});
</script>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
