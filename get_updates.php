<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get optional parameters
$since = $_GET['since'] ?? 0; // Timestamp to get updates since
$imageIds = $_GET['image_ids'] ?? null; // Comma-separated list of image IDs to check

// Create data directory if it doesn't exist
$dataDir = 'data';
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}

// File paths
$likesFile = $dataDir . '/likes.txt';
$viewsFile = $dataDir . '/views.txt';
$lastUpdateFile = $dataDir . '/last_update.txt';

// Initialize files if they don't exist
if (!file_exists($likesFile)) {
    file_put_contents($likesFile, '{}');
}

if (!file_exists($viewsFile)) {
    file_put_contents($viewsFile, '{}');
}

if (!file_exists($lastUpdateFile)) {
    file_put_contents($lastUpdateFile, time());
}

// Read data from files
$likesData = json_decode(file_get_contents($likesFile), true) ?: [];
$viewsData = json_decode(file_get_contents($viewsFile), true) ?: [];
$lastUpdate = (int)file_get_contents($lastUpdateFile);

// Check if there are any updates since the last check
if ($lastUpdate <= $since) {
    echo json_encode([
        'success' => true,
        'updates' => [],
        'last_update' => $lastUpdate
    ]);
    exit;
}

// Prepare updates array
$updates = [];

// If specific image IDs are requested, only return those
if ($imageIds) {
    $requestedIds = explode(',', $imageIds);
    foreach ($requestedIds as $imageId) {
        $imageId = trim($imageId);
        if (isset($likesData[$imageId]) || isset($viewsData[$imageId])) {
            $updates[] = [
                'image_id' => $imageId,
                'likes' => $likesData[$imageId] ?? 0,
                'views' => $viewsData[$imageId] ?? 0
            ];
        }
    }
} else {
    // Return all images with data
    $allImageIds = array_unique(array_merge(array_keys($likesData), array_keys($viewsData)));
    
    foreach ($allImageIds as $imageId) {
        $updates[] = [
            'image_id' => $imageId,
            'likes' => $likesData[$imageId] ?? 0,
            'views' => $viewsData[$imageId] ?? 0
        ];
    }
}

// Update the last update timestamp
file_put_contents($lastUpdateFile, time());

// Return the updates
echo json_encode([
    'success' => true,
    'updates' => $updates,
    'last_update' => time()
]);
?>