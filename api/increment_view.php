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
    
    if (!$input || !isset($input['image_id'])) {
        throw new Exception('Missing image_id parameter');
    }
    
    $imageId = $input['image_id'];
    
    // Create data directory if it doesn't exist
    $dataDir = '../data';
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0755, true);
    }
    
    // File path for views
    $viewsFile = $dataDir . '/views.txt';
    
    // Initialize file if it doesn't exist
    if (!file_exists($viewsFile)) {
        file_put_contents($viewsFile, '');
    }
    
    // Read current views data
    $viewsData = [];
    
    if (file_exists($viewsFile)) {
        $viewsContent = file_get_contents($viewsFile);
        if ($viewsContent) {
            $viewsData = json_decode($viewsContent, true) ?: [];
        }
    }
    
    // Increment view count
    if (!isset($viewsData[$imageId])) {
        $viewsData[$imageId] = 0;
    }
    
    $viewsData[$imageId]++;
    
    // Write data back to file
    file_put_contents($viewsFile, json_encode($viewsData));
    
    // Return success response
    echo json_encode([
        'success' => true,
        'image_id' => $imageId,
        'views' => $viewsData[$imageId]
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>