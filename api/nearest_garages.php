<?php
// api/nearest_garages.php - Backend API for Processing Location & Searching Nearest Garages
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/functions.php';

// Accept GET or POST inputs
$rawInput = file_get_contents('php://input');
$jsonInput = json_decode($rawInput, true) ?? [];
$params = array_merge($_GET, $_POST, $jsonInput);

$userLat = filter_var($params['lat'] ?? null, FILTER_VALIDATE_FLOAT);
$userLng = filter_var($params['lng'] ?? null, FILTER_VALIDATE_FLOAT);

// Master Database of Garages & Service Centers
$garagesDataset = [
    [
        'id' => 1,
        'name' => 'Bosch Authorized Car Service Center',
        'category' => 'Authorized Multi-Brand Service & Diagnostic Center',
        'lat' => 23.0275,
        'lng' => 72.5764,
        'base_dist' => 0.6,
        'rating' => '4.8 ⭐ (164 reviews)',
        'open_status' => 'Open Now • 24/7 Service',
        'services' => ['Engine Diagnostics', 'Oil Change', 'AC Service', 'Brake Pad Change'],
        'phone' => '+919876543210',
        'address' => 'Main Highway Road, Service Plaza'
    ],
    [
        'id' => 2,
        'name' => 'GoMechanic - Express Auto Workshop',
        'category' => 'Multi-Brand Car Repair & Denting Painting',
        'lat' => 23.0335,
        'lng' => 72.5634,
        'base_dist' => 1.2,
        'rating' => '4.7 ⭐ (112 reviews)',
        'open_status' => 'Open Now • Closes 9:00 PM',
        'services' => ['Oil Change', 'Wheel Alignment', 'Dent Repair', 'Car Washing'],
        'phone' => '+919876543211',
        'address' => 'Sector 4 Industrial Park, Station Road'
    ],
    [
        'id' => 3,
        'name' => '24/7 Roadside Assistance & Towing Service',
        'category' => 'Emergency Towing, Battery Jumpstart & Flat Tyre',
        'lat' => 23.0075,
        'lng' => 72.5834,
        'base_dist' => 1.8,
        'rating' => '4.9 ⭐ (245 reviews)',
        'open_status' => 'Open 24/7 Emergency Hotline',
        'services' => ['Flat Tyre Repair', 'Battery Jumpstart', 'Flatbed Towing', 'Fuel Delivery'],
        'phone' => '+919876543212',
        'address' => 'Highway Junction 12, Breakdown Center'
    ],
    [
        'id' => 4,
        'name' => 'Mahindra First Choice Wheel Care',
        'category' => 'Tyre Replacement, Suspension & Maintenance',
        'lat' => 23.0445,
        'lng' => 72.5894,
        'base_dist' => 2.5,
        'rating' => '4.6 ⭐ (89 reviews)',
        'open_status' => 'Open Now • Closes 8:30 PM',
        'services' => ['Tyre Replacement', 'Wheel Balancing', 'Suspension Check', 'Full Car Polish'],
        'phone' => '+919876543213',
        'address' => 'Ring Road Circle, Fuel Station'
    ],
    [
        'id' => 5,
        'name' => 'TVS AutoCare & Mechanic Shop',
        'category' => 'Two-Wheeler & Four-Wheeler Repair Specialist',
        'lat' => 22.9945,
        'lng' => 72.5504,
        'base_dist' => 3.2,
        'rating' => '4.5 ⭐ (76 reviews)',
        'open_status' => 'Open Now',
        'services' => ['Clutch Cable Change', 'Spark Plug Replace', 'Chain Lube', 'General Service'],
        'phone' => '+919876543214',
        'address' => 'Market Yard Road, Bus Station'
    ],
    [
        'id' => 6,
        'name' => 'Express Multi-Brand Car Care & AC Specialist',
        'category' => 'Fast Track Maintenance & Electrical Repair',
        'lat' => 23.0575,
        'lng' => 72.5464,
        'base_dist' => 4.1,
        'rating' => '4.8 ⭐ (140 reviews)',
        'open_status' => 'Open Now',
        'services' => ['AC Gas Refill', 'Electrical Repair', 'Headlight Alignment', 'Battery Check'],
        'phone' => '+919876543215',
        'address' => 'Grand Trunk Road, Pillar 140'
    ]
];

// Backend Haversine Distance Calculation Formula (in Kilometers)
function calculateHaversineKm($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; // km
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return round($earthRadius * $c, 1);
}

$processedGarages = [];

foreach ($garagesDataset as $item) {
    $computedDist = $item['base_dist'];
    $directionsUrl = "https://www.google.com/maps/search/?api=1&query=" . urlencode($item['name'] . ' ' . $item['address']);

    if ($userLat !== false && $userLng !== false && $userLat !== null && $userLng !== null) {
        $computedDist = calculateHaversineKm($userLat, $userLng, $item['lat'], $item['lng']);
        $directionsUrl = "https://www.google.com/maps/dir/?api=1&origin={$userLat},{$userLng}&destination=" . urlencode($item['name'] . ' ' . $item['address']);
    }

    $item['computed_distance'] = $computedDist;
    $item['distance_text'] = $computedDist . " km away";
    $item['directions_url'] = $directionsUrl;
    $processedGarages[] = $item;
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
