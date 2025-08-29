<?php
header('Content-Type: application/json');

$id = $_GET['id'] ?? '';
$action = $_GET['action'] ?? '';

if (!$id || !in_array($action, ['like', 'unlike'], true)) {
    echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
    exit;
}

$dataDir = __DIR__ . '/data';
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}

$file = "$dataDir/{$id}.txt";

// Load existing data or initialise
if (file_exists($file)) {
    [$likes, $views] = array_map('intval', explode('|', file_get_contents($file)));
} else {
    $likes = 0;
    $views = 0;
}

// Update likes based on action
if ($action === 'like') {
    $likes++;
} else {
    if ($likes > 0) {
        $likes--; // Prevent negative likes
    }
}

file_put_contents($file, "$likes|$views");

echo json_encode(['success' => true, 'id' => $id, 'likes' => $likes]);
?>