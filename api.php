<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

class GalleryAPI {
    private $dataDir = 'data/';
    
    public function __construct() {
        // Ensure data directory exists
        if (!is_dir($this->dataDir)) {
            mkdir($this->dataDir, 0755, true);
        }
    }
    
    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $segments = explode('/', trim($path, '/'));
        
        // Remove 'api.php' from segments if present
        if (in_array('api.php', $segments)) {
            $segments = array_values(array_filter($segments, function($seg) {
                return $seg !== 'api.php';
            }));
        }
        
        try {
            switch ($method) {
                case 'GET':
                    $this->handleGet($segments);
                    break;
                case 'POST':
                    $this->handlePost($segments);
                    break;
                default:
                    $this->sendError('Method not allowed', 405);
            }
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 500);
        }
    }
    
    private function handleGet($segments) {
        if (count($segments) === 0) {
            $this->sendError('Invalid endpoint', 400);
            return;
        }
        
        $action = $segments[0];
        
        switch ($action) {
            case 'likes':
                if (isset($segments[1])) {
                    $imageId = $segments[1];
                    $likes = $this->getLikes($imageId);
                    $this->sendSuccess(['imageId' => $imageId, 'likes' => $likes]);
                } else {
                    $this->sendError('Image ID required', 400);
                }
                break;
                
            case 'views':
                if (isset($segments[1])) {
                    $imageId = $segments[1];
                    $views = $this->getViews($imageId);
                    $this->sendSuccess(['imageId' => $imageId, 'views' => $views]);
                } else {
                    $this->sendError('Image ID required', 400);
                }
                break;
                
            case 'stats':
                if (isset($segments[1])) {
                    $imageId = $segments[1];
                    $likes = $this->getLikes($imageId);
                    $views = $this->getViews($imageId);
                    $isLiked = $this->isImageLiked($imageId);
                    $this->sendSuccess([
                        'imageId' => $imageId,
                        'likes' => $likes,
                        'views' => $views,
                        'isLiked' => $isLiked
                    ]);
                } else {
                    $this->sendError('Image ID required', 400);
                }
                break;
                
            default:
                $this->sendError('Unknown endpoint', 404);
        }
    }
    
    private function handlePost($segments) {
        if (count($segments) === 0) {
            $this->sendError('Invalid endpoint', 400);
            return;
        }
        
        $action = $segments[0];
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['imageId'])) {
            $this->sendError('Invalid input data', 400);
            return;
        }
        
        $imageId = $input['imageId'];
        
        switch ($action) {
            case 'like':
                $newLikes = $this->toggleLike($imageId);
                $isLiked = $this->isImageLiked($imageId);
                $this->sendSuccess([
                    'imageId' => $imageId,
                    'likes' => $newLikes,
                    'isLiked' => $isLiked,
                    'action' => $isLiked ? 'liked' : 'unliked'
                ]);
                break;
                
            case 'view':
                $newViews = $this->incrementViews($imageId);
                $this->sendSuccess([
                    'imageId' => $imageId,
                    'views' => $newViews
                ]);
                break;
                
            default:
                $this->sendError('Unknown endpoint', 404);
        }
    }
    
    private function getLikes($imageId) {
        $filename = $this->dataDir . "likes_{$imageId}.txt";
        if (file_exists($filename)) {
            $content = file_get_contents($filename);
            return (int)trim($content);
        }
        return 0;
    }
    
    private function getViews($imageId) {
        $filename = $this->dataDir . "views_{$imageId}.txt";
        if (file_exists($filename)) {
            $content = file_get_contents($filename);
            return (int)trim($content);
        }
        return 0;
    }
    
    private function setLikes($imageId, $likes) {
        $filename = $this->dataDir . "likes_{$imageId}.txt";
        file_put_contents($filename, $likes);
    }
    
    private function setViews($imageId, $views) {
        $filename = $this->dataDir . "views_{$imageId}.txt";
        file_put_contents($filename, $views);
    }
    
    private function toggleLike($imageId) {
        $currentLikes = $this->getLikes($imageId);
        $isCurrentlyLiked = $this->isImageLiked($imageId);
        
        if ($isCurrentlyLiked) {
            // Unlike
            $newLikes = max(0, $currentLikes - 1);
            $this->setImageLiked($imageId, false);
        } else {
            // Like
            $newLikes = $currentLikes + 1;
            $this->setImageLiked($imageId, true);
        }
        
        $this->setLikes($imageId, $newLikes);
        return $newLikes;
    }
    
    private function incrementViews($imageId) {
        $currentViews = $this->getViews($imageId);
        $newViews = $currentViews + 1;
        $this->setViews($imageId, $newViews);
        return $newViews;
    }
    
    private function isImageLiked($imageId) {
        $filename = $this->dataDir . "user_likes_{$imageId}.txt";
        if (file_exists($filename)) {
            $content = file_get_contents($filename);
            return trim($content) === '1';
        }
        return false;
    }
    
    private function setImageLiked($imageId, $liked) {
        $filename = $this->dataDir . "user_likes_{$imageId}.txt";
        file_put_contents($filename, $liked ? '1' : '0');
    }
    
    private function sendSuccess($data) {
        http_response_code(200);
        echo json_encode(['success' => true, 'data' => $data]);
    }
    
    private function sendError($message, $code = 400) {
        http_response_code($code);
        echo json_encode(['success' => false, 'error' => $message]);
    }
}

$api = new GalleryAPI();
$api->handleRequest();
?>