<?php
// api/nearest_garages.php - Dynamic Variable Location-Based Garages API
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/functions.php';

// Accept GET or POST inputs
$rawInput = file_get_contents('php://input');
$jsonInput = json_decode($rawInput, true) ?? [];
$params = array_merge($_GET, $_POST, $jsonInput);

$userLat = filter_var($params['lat'] ?? null, FILTER_VALIDATE_FLOAT);
$userLng = filter_var($params['lng'] ?? null, FILTER_VALIDATE_FLOAT);

// Default fallback coordinates (Central Location)
if ($userLat === false || $userLat === null) {
    $userLat = 23.0225;
}
if ($userLng === false || $userLng === null) {
    $userLng = 72.5714;
}

// Dynamic Garages Template Array around ANY User Location
$garagesTemplate = [
    [
        'id' => 1,
        'name' => 'Bosch Authorized Car Service Center',
        'category' => 'Authorized Multi-Brand Service & Diagnostic Center',
        'offset_lat' => 0.0035,
        'offset_lng' => 0.0032,
        'rating' => '4.8 ⭐ (184 reviews)',
        'open_status' => 'Open Now • 24/7 Service',
        'services' => ['Engine Diagnostics', 'Oil Change', 'AC Service', 'Brake Pad Change'],
        'phone' => '+919876543210',
        'address_suffix' => 'Main Service Plaza, Near Highway'
    ],
    [
        'id' => 2,
        'name' => 'GoMechanic - Express Auto Workshop',
        'category' => 'Multi-Brand Car Repair & Denting Painting Specialist',
        'offset_lat' => -0.0068,
        'offset_lng' => 0.0055,
        'rating' => '4.7 ⭐ (128 reviews)',
        'open_status' => 'Open Now • Closes 9:00 PM',
        'services' => ['Oil Change', 'Wheel Alignment', 'Dent Repair', 'Car Wash'],
        'phone' => '+919876543211',
        'address_suffix' => 'Industrial Park, Station Road'
    ],
    [
        'id' => 3,
        'name' => '24/7 Roadside Breakdown & Towing Service',
        'category' => 'Emergency Towing, Battery Jumpstart & Flat Tyre',
        'offset_lat' => 0.0095,
        'offset_lng' => -0.0082,
        'rating' => '4.9 ⭐ (265 reviews)',
        'open_status' => 'Open 24/7 Emergency Hotline',
        'services' => ['Flat Tyre Repair', 'Battery Jumpstart', 'Flatbed Towing', 'Fuel Delivery'],
        'phone' => '+919876543212',
        'address_suffix' => 'Highway Junction 12, Emergency Center'
    ],
    [
        'id' => 4,
        'name' => 'Maruti Suzuki Arena Service Station',
        'category' => 'OEM Authorized Service & Genuine Spare Parts',
        'offset_lat' => -0.0125,
        'offset_lng' => -0.0115,
        'rating' => '4.9 ⭐ (310 reviews)',
        'open_status' => 'Open Now • Closes 8:00 PM',
        'services' => ['Periodic Maintenance', 'Engine Overhaul', 'Insurance Claim', 'Wheel Alignment'],
        'phone' => '+919876543216',
        'address_suffix' => 'Auto Zone Market, Main Boulevard'
    ],
    [
        'id' => 5,
        'name' => 'Hyundai Promise Certified Auto Service',
        'category' => 'Authorized Hyundai Vehicle Maintenance & Body Shop',
        'offset_lat' => 0.0165,
        'offset_lng' => 0.0142,
        'rating' => '4.8 ⭐ (220 reviews)',
        'open_status' => 'Open Now • Closes 8:30 PM',
        'services' => ['GScan Diagnostics', 'Synthetic Oil Change', 'Clutch Repair', 'AC Gas Refill'],
        'phone' => '+919876543217',
        'address_suffix' => 'Central Auto Hub, Ring Road'
    ],
    [
        'id' => 6,
        'name' => 'Mahindra First Choice Wheel Care',
        'category' => 'Tyre Replacement, Suspension & Alignment Specialist',
        'offset_lat' => -0.0215,
        'offset_lng' => 0.0185,
        'rating' => '4.6 ⭐ (95 reviews)',
        'open_status' => 'Open Now • Closes 8:30 PM',
        'services' => ['Tyre Replacement', 'Wheel Balancing', 'Suspension Check', 'Full Polish'],
        'phone' => '+919876543213',
        'address_suffix' => 'Ring Road Circle, Fuel Station'
    ],
    [
        'id' => 7,
        'name' => 'Tata Motors Passenger Vehicle Workshop',
        'category' => 'Authorized Tata Vehicle Repair & Electric Vehicle Care',
        'offset_lat' => 0.0275,
        'offset_lng' => -0.0225,
        'rating' => '4.7 ⭐ (175 reviews)',
        'open_status' => 'Open Now',
        'services' => ['EV Battery Diagnostic', 'Brake Service', 'Suspension Tuning', 'General Repair'],
        'phone' => '+919876543218',
        'address_suffix' => 'Express Way Commercial Complex'
    ],
    [
        'id' => 8,
        'name' => 'TVS AutoCare & General Workshop',
        'category' => 'Two-Wheeler & Four-Wheeler Repair Specialist',
        'offset_lat' => -0.0315,
        'offset_lng' => -0.0285,
        'rating' => '4.5 ⭐ (82 reviews)',
        'open_status' => 'Open Now',
        'services' => ['Clutch Cable Replace', 'Spark Plug Swap', 'Chain Lube', 'General Tuneup'],
        'phone' => '+919876543214',
        'address_suffix' => 'Market Yard Road, Bus Station'
    ],
    [
        'id' => 9,
        'name' => '3M Car Care & Detailing Studio',
        'category' => 'Ceramic Coating, Interior Deep Cleaning & Paint Protection',
        'offset_lat' => 0.0385,
        'offset_lng' => 0.0312,
        'rating' => '4.9 ⭐ (190 reviews)',
        'open_status' => 'Open Now • Closes 9:30 PM',
        'services' => ['PPF Coating', 'Foam Wash', 'Underbody Anti-Rust', 'Interior Sanitization'],
        'phone' => '+919876543219',
        'address_suffix' => 'Luxury Auto Bay, Shopping Mall Road'
    ],
    [
        'id' => 10,
        'name' => 'Pitstop Quick Lube & AC Diagnostic Bay',
        'category' => 'Fast-Track Express Oil Change & Climate Control',
        'offset_lat' => -0.0455,
        'offset_lng' => 0.0385,
        'rating' => '4.6 ⭐ (105 reviews)',
        'open_status' => 'Open Now',
        'services' => ['Express Oil Change', 'Coolant Top-up', 'AC Filter Clean', 'Wiper Replacement'],
        'phone' => '+919876543220',
        'address_suffix' => 'Cross Roads Plaza, Near Toll Gate'
    ]
];

// Backend Haversine Distance Calculation Formula (in Kilometers)
function calculateHaversineKm($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; // Earth radius in km
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return round($earthRadius * $c, 1);
}

$processedGarages = [];

foreach ($garagesTemplate as $item) {
    // Dynamically calculate exact lat/lng for garage centered on user location
    $garageLat = round($userLat + $item['offset_lat'], 5);
    $garageLng = round($userLng + $item['offset_lng'], 5);

    $computedDist = calculateHaversineKm($userLat, $userLng, $garageLat, $garageLng);
    
    // Dynamic Google Maps Turn-by-Turn Directions URL
    $directionsUrl = "https://www.google.com/maps/dir/?api=1&origin={$userLat},{$userLng}&destination=" . urlencode($item['name'] . ', ' . $item['address_suffix']);
    $searchUrl = "https://www.google.com/maps/search/" . urlencode($item['name']) . "/@{$garageLat},{$garageLng},15z";

    $processedGarages[] = [
        'id' => $item['id'],
        'name' => $item['name'],
        'category' => $item['category'],
        'lat' => $garageLat,
        'lng' => $garageLng,
        'computed_distance' => $computedDist,
        'distance_text' => $computedDist . " km away",
        'rating' => $item['rating'],
        'open_status' => $item['open_status'],
        'services' => $item['services'],
        'phone' => $item['phone'],
        'address' => $item['address_suffix'],
        'directions_url' => $directionsUrl,
        'search_url' => $searchUrl
    ];
}

// Backend Sorting: Sort Garages by Computed Distance ASC (Nearest First)
usort($processedGarages, function($a, $b) {
    return $a['computed_distance'] <=> $b['computed_distance'];
});

echo json_encode([
    'success' => true,
    'user_lat' => $userLat,
    'user_lng' => $userLng,
    'total' => count($processedGarages),
    'garages' => $processedGarages
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
