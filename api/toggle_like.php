<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['image_id']) || !isset($input['action'])) {
        throw new Exception('Missing required parameters');
    }
    
    $imageId = $input['image_id'];
    $action = $input['action'];
    
    if (!in_array($action, ['like', 'unlike'])) {
        throw new Exception('Invalid action');
    }
    
    // Create data directory if it doesn't exist
    $dataDir = '../data';
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0755, true);
    }
    
    // File paths
    $likesFile = $dataDir . '/likes.txt';
    $likedImagesFile = $dataDir . '/liked_images.txt';
    
    // Initialize files if they don't exist
    if (!file_exists($likesFile)) {
        file_put_contents($likesFile, '');
    }
    if (!file_exists($likedImagesFile)) {
        file_put_contents($likedImagesFile, '');
    }
    
    // Read current data
    $likesData = [];
    $likedImagesData = [];
    
    if (file_exists($likesFile)) {
        $likesContent = file_get_contents($likesFile);
        if ($likesContent) {
            $likesData = json_decode($likesContent, true) ?: [];
        }
    }
    
    if (file_exists($likedImagesFile)) {
        $likedContent = file_get_contents($likedImagesFile);
        if ($likedContent) {
            $likedImagesData = json_decode($likedContent, true) ?: [];
        }
    }
    
    // Update likes count
    if (!isset($likesData[$imageId])) {
        $likesData[$imageId] = 0;
    }
    
    if ($action === 'like') {
        $likesData[$imageId]++;
        $likedImagesData[$imageId] = true;
    } else {
        $likesData[$imageId] = max(0, $likesData[$imageId] - 1);
        if (isset($likedImagesData[$imageId])) {
            unset($likedImagesData[$imageId]);
        }
    }
    
    // Write data back to files
    file_put_contents($likesFile, json_encode($likesData));
    file_put_contents($likedImagesFile, json_encode($likedImagesData));
    
    // Return success response
    echo json_encode([
        'success' => true,
        'image_id' => $imageId,
        'action' => $action,
        'likes' => $likesData[$imageId],
        'liked' => $action === 'like'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>