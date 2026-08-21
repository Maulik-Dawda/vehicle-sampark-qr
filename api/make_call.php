<?php
// api/make_call.php - IVR Calling API Endpoint Entry Point

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../app/Controllers/ApiController.php';

$controller = new ApiController($pdo);
$controller->makeCall();
