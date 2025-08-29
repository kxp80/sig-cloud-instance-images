<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    // Get image ID from query parameter
    if (!isset($_GET['image_id'])) {
        throw new Exception('Missing image_id parameter');
    }
    
    $imageId = $_GET['image_id'];
    
    // Data directory path
    $dataDir = '../data';
    
    // File paths
    $likesFile = $dataDir . '/likes.txt';
    $viewsFile = $dataDir . '/views.txt';
    $likedImagesFile = $dataDir . '/liked_images.txt';
    
    // Initialize response data
    $response = [
        'likes' => 0,
        'views' => 0,
        'liked' => false
    ];
    
    // Read likes data
    if (file_exists($likesFile)) {
        $likesContent = file_get_contents($likesFile);
        if ($likesContent) {
            $likesData = json_decode($likesContent, true) ?: [];
            if (isset($likesData[$imageId])) {
                $response['likes'] = (int)$likesData[$imageId];
            }
        }
    }
    
    // Read views data
    if (file_exists($viewsFile)) {
        $viewsContent = file_get_contents($viewsFile);
        if ($viewsContent) {
            $viewsData = json_decode($viewsContent, true) ?: [];
            if (isset($viewsData[$imageId])) {
                $response['views'] = (int)$viewsData[$imageId];
            }
        }
    }
    
    // Read liked images data
    if (file_exists($likedImagesFile)) {
        $likedContent = file_get_contents($likedImagesFile);
        if ($likedContent) {
            $likedImagesData = json_decode($likedContent, true) ?: [];
            $response['liked'] = isset($likedImagesData[$imageId]) && $likedImagesData[$imageId];
        }
    }
    
    // Return the data
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>