<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['image_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing image_id parameter']);
    exit;
}

$imageId = $input['image_id'];

// Create data directory if it doesn't exist
$dataDir = 'data';
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}

// File paths
$viewsFile = $dataDir . '/views.txt';
$viewHistoryFile = $dataDir . '/view_history.txt';

// Initialize files if they don't exist
if (!file_exists($viewsFile)) {
    file_put_contents($viewsFile, '{}');
}

if (!file_exists($viewHistoryFile)) {
    file_put_contents($viewHistoryFile, '{}');
}

// Read current data
$viewsData = json_decode(file_get_contents($viewsFile), true) ?: [];
$viewHistoryData = json_decode(file_get_contents($viewHistoryFile), true) ?: [];

// Get user identifier (simple IP-based for demo)
$userId = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$currentTime = time();

// Initialize image data if not exists
if (!isset($viewsData[$imageId])) {
    $viewsData[$imageId] = 0;
}

if (!isset($viewHistoryData[$imageId])) {
    $viewHistoryData[$imageId] = [];
}

// Check if user has viewed this image recently (within 1 hour to prevent spam)
$recentView = false;
if (isset($viewHistoryData[$imageId][$userId])) {
    $lastViewTime = $viewHistoryData[$imageId][$userId];
    if ($currentTime - $lastViewTime < 3600) { // 1 hour = 3600 seconds
        $recentView = true;
    }
}

// Increment view count if not a recent view
if (!$recentView) {
    $viewsData[$imageId]++;
    $viewHistoryData[$imageId][$userId] = $currentTime;
}

// Save data back to files
$success = false;

// Use file locking to prevent race conditions
$viewsLock = fopen($viewsFile, 'c+');
$historyLock = fopen($viewHistoryFile, 'c+');

if ($viewsLock && $historyLock) {
    // Lock both files
    if (flock($viewsLock, LOCK_EX) && flock($historyLock, LOCK_EX)) {
        // Write data
        ftruncate($viewsLock, 0);
        rewind($viewsLock);
        fwrite($viewsLock, json_encode($viewsData, JSON_PRETTY_PRINT));
        
        ftruncate($historyLock, 0);
        rewind($historyLock);
        fwrite($historyLock, json_encode($viewHistoryData, JSON_PRETTY_PRINT));
        
        // Unlock files
        flock($viewsLock, LOCK_UN);
        flock($historyLock, LOCK_UN);
        
        $success = true;
    }
    
    fclose($viewsLock);
    fclose($historyLock);
}

// Return response
if ($success) {
    echo json_encode([
        'success' => true,
        'image_id' => $imageId,
        'new_views' => $viewsData[$imageId],
        'view_incremented' => !$recentView
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update view data'
    ]);
}
?>