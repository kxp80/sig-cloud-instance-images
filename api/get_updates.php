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
    // Data directory path
    $dataDir = '../data';
    
    // File paths
    $likesFile = $dataDir . '/likes.txt';
    $viewsFile = $dataDir . '/views.txt';
    
    $updates = [];
    
    // Read likes data
    if (file_exists($likesFile)) {
        $likesContent = file_get_contents($likesFile);
        if ($likesContent) {
            $likesData = json_decode($likesContent, true) ?: [];
            foreach ($likesData as $imageId => $likes) {
                $updates[] = [
                    'image_id' => $imageId,
                    'likes' => (int)$likes
                ];
            }
        }
    }
    
    // Read views data
    if (file_exists($viewsFile)) {
        $viewsContent = file_get_contents($viewsFile);
        if ($viewsContent) {
            $viewsData = json_decode($viewsContent, true) ?: [];
            foreach ($viewsData as $imageId => $views) {
                // Find existing update for this image or create new one
                $found = false;
                foreach ($updates as &$update) {
                    if ($update['image_id'] == $imageId) {
                        $update['views'] = (int)$views;
                        $found = true;
                        break;
                    }
                }
                
                if (!$found) {
                    $updates[] = [
                        'image_id' => $imageId,
                        'views' => (int)$views
                    ];
                }
            }
        }
    }
    
    // Return the updates
    echo json_encode($updates);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>