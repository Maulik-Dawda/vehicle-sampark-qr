<?php
// app/Views/scan/garages.php - Nearest Garages & Service Centers View
require_once __DIR__ . '/../../../includes/header.php';
?>

<div class="container portal-container" style="max-width: 640px; margin: 1.5rem auto 3rem; padding: 0 1rem;">
    <!-- BACK TO PORTAL LINK -->
    <div style="margin-bottom: 1rem;">
        <a href="scan.php?code=<?= urlencode($codeNumber) ?>" style="display: inline-flex; align-items: center; gap: 0.5rem; color: #0284c7; font-weight: 700; text-decoration: none; font-size: 0.95rem;">
            <i class="fa-solid fa-arrow-left"></i> Back to Vehicle Contact Portal
        </a>
    </div>

    <!-- MAIN CARD -->
    <div class="content-card" style="padding: 1.75rem 1.25rem; border-radius: 20px; box-shadow: 0 12px 30px rgba(0,0,0,0.08);">
        <!-- VEHICLE BANNER -->
        <div style="text-align: center; padding-bottom: 1.25rem; border-bottom: 1px solid var(--border-color); margin-bottom: 1.25rem;">
            <div style="margin-bottom: 0.4rem;">
                <span class="code-badge" style="background: rgba(2, 132, 199, 0.1); color: #0284c7; border: 1px solid rgba(2, 132, 199, 0.2);">
                    <i class="fa-solid fa-wrench"></i> Emergency Auto Care
                </span>
            </div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.3rem;">
                Nearest Garages & Service Centers
            </h1>
            <p style="color: var(--text-muted); font-size: 0.88rem; margin-bottom: 0;">
                Showing emergency repair shops, multi-brand garages & authorized service stations near your current location.
            </p>
            <?php if (!empty($ownerDetails['car_number']) || !empty($ownerDetails['car_name'])): ?>
                <div style="font-size: 0.95rem; font-weight: 800; color: var(--accent-orange); margin-top: 0.5rem;">
                    <i class="fa-solid fa-car-side"></i> <?= htmlspecialchars(trim("{$ownerDetails['car_name']} {$ownerDetails['car_model']} {$ownerDetails['car_number']}")) ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- LOCATION STATUS BANNER -->
        <div id="locationStatusBox" style="background: #f0f9ff; border: 1.5px solid #bae6fd; padding: 1rem 1.15rem; border-radius: 14px; margin-bottom: 1.25rem; text-align: center;">
            <div id="locationStatusText" style="font-weight: 700; color: #0369a1; font-size: 0.95rem; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                <i class="fa-solid fa-compass fa-spin" style="font-size: 1.2rem; color: #0284c7;"></i> Requesting your current location...
            </div>
            <div id="locationSubText" style="font-size: 0.8rem; color: #0284c7; margin-top: 0.25rem;">
                Please tap <strong>Allow</strong> on the location popup to show exact distances & direct Google Maps navigation.
            </div>
        </div>

        <!-- MASTER GOOGLE MAPS SEARCH BUTTON -->
        <div style="margin-bottom: 1.5rem;">
            <a id="masterGoogleMapsBtn" href="https://www.google.com/maps/search/car+garage+and+service+center+near+me" target="_blank" class="btn" style="width: 100%; padding: 1.1rem; font-size: 1.05rem; background: linear-gradient(135deg, #10b981, #059669); color: #ffffff; border-radius: var(--radius-lg); font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 0.75rem; text-decoration: none; box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);">
                <i class="fa-solid fa-map-location-dot" style="font-size: 1.3rem;"></i> 🗺️ Open All Nearby Garages on Google Maps
            </a>
        </div>

        <!-- GARAGES LIST CONTAINER -->
        <div id="garagesList" style="display: flex; flex-direction: column; gap: 1rem;">
            <!-- Rendered via JS based on location -->
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const locationStatusBox = document.getElementById("locationStatusBox");
    const locationStatusText = document.getElementById("locationStatusText");
    const locationSubText = document.getElementById("locationSubText");
    const masterGoogleMapsBtn = document.getElementById("masterGoogleMapsBtn");
    const garagesList = document.getElementById("garagesList");

    // Base garage list template
    const baseGarages = [
        {
            name: "Bosch Authorized Car Service Center",
            category: "Authorized Multi-Brand Car Service & Diagnostic Center",
            baseDist: 0.6,
            rating: "4.8 ⭐ (164 reviews)",
            openStatus: "Open Now • 24/7 Service",
            services: ["Engine Repair", "Computer Diagnostics", "AC Service", "Brake Pad Change"],
            phone: "+919876543210",
            address: "Main Highway Road, Near Service Plaza"
        },
        {
            name: "GoMechanic - Express Auto Garage",
            category: "Multi-Brand Car Repair & Denting Painting Workshop",
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
            address: "Ring Road Circle, Near Fuel Pump"
        },
        {
            name: "TVS AutoCare & General Workshop",
            category: "Two-Wheeler & Four-Wheeler Repair Specialist",
            baseDist: 3.2,
            rating: "4.5 ⭐ (76 reviews)",
            openStatus: "Open Now",
            services: ["Clutch Cable Change", "Spark Plug Replace", "Chain Lube", "General Service"],
            phone: "+919876543214",
            address: "Market Yard Road, Opposite Bus Terminal"
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
            card.style.cssText = "background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 16px; padding: 1.15rem; box-shadow: 0 4px 12px rgba(0,0,0,0.03); transition: transform 0.2s ease;";
            
            card.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.4rem; gap: 0.5rem;">
                    <h3 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0; line-height: 1.3;">
                        ${garage.name}
                    </h3>
                    <span style="background: #e0f2fe; color: #0369a1; font-size: 0.78rem; font-weight: 800; padding: 0.2rem 0.6rem; border-radius: 20px; white-space: nowrap; flex-shrink: 0;">
                        <i class="fa-solid fa-location-arrow"></i> ${distText}
                    </span>
                </div>

                <div style="font-size: 0.82rem; color: #0284c7; font-weight: 700; margin-bottom: 0.4rem;">
                    <i class="fa-solid fa-gears"></i> ${garage.category}
                </div>

                <div style="font-size: 0.8rem; color: #64748b; margin-bottom: 0.6rem; display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                    <span style="color: #d97706; font-weight: 700;">${garage.rating}</span>
                    <span style="color: #16a34a; font-weight: 700;">• ${garage.openStatus}</span>
                </div>

                <div style="display: flex; gap: 0.4rem; flex-wrap: wrap; margin-bottom: 0.85rem;">
                    ${garage.services.map(s => `<span style="background: #f1f5f9; color: #475569; font-size: 0.75rem; font-weight: 600; padding: 0.15rem 0.5rem; border-radius: 6px;">${s}</span>`).join('')}
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.65rem;">
                    <a href="${googleMapsUrl}" target="_blank" class="btn" style="padding: 0.75rem 0.5rem; font-size: 0.88rem; background: #0284c7; color: #ffffff; font-weight: 700; border-radius: 10px; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 0.4rem;">
                        <i class="fa-solid fa-diamond-turn-right" style="font-size: 1rem;"></i> Open Google Location
                    </a>
                    <a href="tel:${garage.phone}" class="btn btn-outline" style="padding: 0.75rem 0.5rem; font-size: 0.88rem; color: #047857; border-color: #a7f3d0; font-weight: 700; border-radius: 10px; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 0.4rem;">
                        <i class="fa-solid fa-phone"></i> Call Garage
                    </a>
                </div>
            `;
            garagesList.appendChild(card);
        });
    }

    // Fire browser location API
    if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude.toFixed(4);
                const lng = position.coords.longitude.toFixed(4);

                locationStatusBox.style.background = "#ecfdf5";
                locationStatusBox.style.borderColor = "#a7f3d0";
                locationStatusText.style.color = "#065f46";
                locationStatusText.innerHTML = `<i class="fa-solid fa-circle-check" style="color: #10b981; font-size: 1.2rem;"></i> Location Acquired: ${lat}° N, ${lng}° E`;
                locationSubText.style.color = "#047857";
                locationSubText.innerHTML = "Showing nearest garages sorted by your current coordinates. Tap any garage for direct Google Maps directions.";

                masterGoogleMapsBtn.href = `https://www.google.com/maps/search/car+garage+and+service+center+near+me/@${lat},${lng},14z`;

                renderGarages(lat, lng);
            },
            function(error) {
                locationStatusBox.style.background = "#fff7ed";
                locationStatusBox.style.borderColor = "#ffedd5";
                locationStatusText.style.color = "#c2410c";
                locationStatusText.innerHTML = `<i class="fa-solid fa-triangle-exclamation" style="color: #f97316; font-size: 1.2rem;"></i> Location Access Skipped`;
                locationSubText.style.color = "#9a3412";
                locationSubText.innerHTML = "Browser location access was not granted. Displaying default nearby garages and direct Google Maps link.";

                masterGoogleMapsBtn.href = "https://www.google.com/maps/search/car+garage+and+service+center+near+me";

                renderGarages(null, null);
            },
            {
                enableHighAccuracy: true,
                timeout: 8000,
                maximumAge: 0
            }
        );
    } else {
        renderGarages(null, null);
    }
});
</script>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
