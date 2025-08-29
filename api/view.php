<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
    exit;
}

$id = isset($_POST['id']) ? trim((string)$_POST['id']) : '';

if ($id === '' || !preg_match('/^[A-Za-z0-9_-]{1,64}$/', $id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid id']);
    exit;
}

$viewsDir = dirname(__DIR__) . '/data/views';
if (!is_dir($viewsDir)) {
    mkdir($viewsDir, 0777, true);
}

$file = $viewsDir . '/' . $id . '.txt';

try {
    $views = update_counter($file, 1, 0);
    echo json_encode([
        'success' => true,
        'id' => $id,
        'views' => $views,
    ], JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}

function update_counter(string $file, int $delta, int $minValue = 0): int {
    $fp = fopen($file, 'c+');
    if ($fp === false) {
        throw new RuntimeException('Unable to open file');
    }
    if (!flock($fp, LOCK_EX)) {
        fclose($fp);
        throw new RuntimeException('Unable to lock file');
    }
    rewind($fp);
    $contents = stream_get_contents($fp);
    $current = 0;
    if ($contents !== false && $contents !== null) {
        $contents = trim($contents);
        if ($contents !== '' && is_numeric($contents)) {
            $current = (int)$contents;
        }
    }
    $current += $delta;
    if ($current < $minValue) {
        $current = $minValue;
    }
    rewind($fp);
    ftruncate($fp, 0);
    fwrite($fp, (string)$current);
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
    return $current;
}

