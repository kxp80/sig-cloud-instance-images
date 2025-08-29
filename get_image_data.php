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

// Get image ID from query parameter
$imageId = $_GET['image_id'] ?? null;

if (!$imageId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing image_id parameter']);
    exit;
}

// Create data directory if it doesn't exist
$dataDir = 'data';
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}

// File paths
$likesFile = $dataDir . '/likes.txt';
$likedByFile = $dataDir . '/liked_by.txt';
$viewsFile = $dataDir . '/views.txt';

// Initialize files if they don't exist
if (!file_exists($likesFile)) {
    file_put_contents($likesFile, '{}');
}

if (!file_exists($likedByFile)) {
    file_put_contents($likedByFile, '{}');
}

if (!file_exists($viewsFile)) {
    file_put_contents($viewsFile, '{}');
}

// Read data from files
$likesData = json_decode(file_get_contents($likesFile), true) ?: [];
$likedByData = json_decode(file_get_contents($likedByFile), true) ?: [];
$viewsData = json_decode(file_get_contents($viewsFile), true) ?: [];

// Get user identifier (simple IP-based for demo)
$userId = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

// Get data for the specific image
$likes = $likesData[$imageId] ?? 0;
$views = $viewsData[$imageId] ?? 0;
$userLiked = false;

// Check if user has liked this image
if (isset($likedByData[$imageId]) && is_array($likedByData[$imageId])) {
    $userLiked = in_array($userId, $likedByData[$imageId]);
}

// Return the data
echo json_encode([
    'success' => true,
    'image_id' => $imageId,
    'likes' => $likes,
    'views' => $views,
    'liked' => $userLiked
]);
?>