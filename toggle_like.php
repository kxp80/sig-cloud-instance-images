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

if (!$input || !isset($input['image_id']) || !isset($input['action'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$imageId = $input['image_id'];
$action = $input['action'];

if (!in_array($action, ['like', 'unlike'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
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

// Initialize files if they don't exist
if (!file_exists($likesFile)) {
    file_put_contents($likesFile, '{}');
}

if (!file_exists($likedByFile)) {
    file_put_contents($likedByFile, '{}');
}

// Read current data
$likesData = json_decode(file_get_contents($likesFile), true) ?: [];
$likedByData = json_decode(file_get_contents($likedByFile), true) ?: [];

// Get user identifier (simple IP-based for demo)
$userId = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

// Initialize image data if not exists
if (!isset($likesData[$imageId])) {
    $likesData[$imageId] = 0;
}

if (!isset($likedByData[$imageId])) {
    $likedByData[$imageId] = [];
}

$currentLikes = $likesData[$imageId];
$userLiked = in_array($userId, $likedByData[$imageId]);

// Process the action
if ($action === 'like' && !$userLiked) {
    // Add like
    $likesData[$imageId]++;
    $likedByData[$imageId][] = $userId;
    $success = true;
} elseif ($action === 'unlike' && $userLiked) {
    // Remove like
    $likesData[$imageId] = max(0, $likesData[$imageId] - 1);
    $likedByData[$imageId] = array_values(array_filter($likedByData[$imageId], function($id) use ($userId) {
        return $id !== $userId;
    }));
    $success = true;
} else {
    // No change needed
    $success = true;
}

// Save data back to files
if ($success) {
    // Use file locking to prevent race conditions
    $likesLock = fopen($likesFile, 'c+');
    $likedByLock = fopen($likedByFile, 'c+');
    
    if ($likesLock && $likedByLock) {
        // Lock both files
        if (flock($likesLock, LOCK_EX) && flock($likedByLock, LOCK_EX)) {
            // Write data
            ftruncate($likesLock, 0);
            rewind($likesLock);
            fwrite($likesLock, json_encode($likesData, JSON_PRETTY_PRINT));
            
            ftruncate($likedByLock, 0);
            rewind($likedByLock);
            fwrite($likedByLock, json_encode($likedByData, JSON_PRETTY_PRINT));
            
            // Unlock files
            flock($likesLock, LOCK_UN);
            flock($likedByLock, LOCK_UN);
        } else {
            $success = false;
        }
        
        fclose($likesLock);
        fclose($likedByLock);
    } else {
        $success = false;
    }
}

// Return response
if ($success) {
    echo json_encode([
        'success' => true,
        'image_id' => $imageId,
        'action' => $action,
        'new_likes' => $likesData[$imageId],
        'user_liked' => in_array($userId, $likedByData[$imageId])
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update like data'
    ]);
}
?>