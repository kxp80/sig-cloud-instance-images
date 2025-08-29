<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
    exit;
}

$idsParam = isset($_GET['ids']) ? (string)$_GET['ids'] : '';
if ($idsParam === '') {
    echo json_encode(['success' => true, 'data' => new stdClass()]);
    exit;
}

$ids = array_filter(array_map('trim', explode(',', $idsParam)));
$ids = array_values(array_unique(array_slice($ids, 0, 200)));

$likesDir = dirname(__DIR__) . '/data/likes';
$viewsDir = dirname(__DIR__) . '/data/views';

$result = [];
foreach ($ids as $id) {
    if (!preg_match('/^[A-Za-z0-9_-]{1,64}$/', $id)) {
        continue;
    }
    $likesFile = $likesDir . '/' . $id . '.txt';
    $viewsFile = $viewsDir . '/' . $id . '.txt';
    $likes = read_counter($likesFile);
    $views = read_counter($viewsFile);
    $result[$id] = [
        'likes' => $likes,
        'views' => $views,
    ];
}

echo json_encode(['success' => true, 'data' => $result], JSON_UNESCAPED_SLASHES);

function read_counter(string $file): int {
    if (!is_file($file)) {
        return 0;
    }
    $fp = fopen($file, 'r');
    if ($fp === false) {
        return 0;
    }
    if (!flock($fp, LOCK_SH)) {
        fclose($fp);
        return 0;
    }
    $contents = stream_get_contents($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
    $contents = trim((string)$contents);
    return is_numeric($contents) ? (int)$contents : 0;
}

