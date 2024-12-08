<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');

// Get the root directory path
$rootDir = dirname(__DIR__, 2);
require_once $rootDir . '/vendor/autoload.php';

// Load environment variables from root directory
$dotenv = Dotenv\Dotenv::createImmutable($rootDir);
$dotenv->load();

// Set CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/AIScraper.php';

// Handle the API request
try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        throw new Exception("Invalid JSON input");
    }

    $scraper = new AIScraper();
    $result = $scraper->retrieve([
        'webpage_url' => $input['webpage_url'] ?? null,
        'api_method_name' => $input['api_method_name'] ?? null,
        'api_response_structure' => $input['api_response_structure'] ?? null,
        'api_key' => $input['api_key'] ?? null
    ]);

    echo json_encode($result);
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'reason' => $e->getMessage()
    ]);
}
