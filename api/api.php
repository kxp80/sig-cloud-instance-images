<?php
declare(strict_types=1);

// Simple PHP API for likes/views backed by per-image .txt files in ../data
// Actions: get, get_many, like, unlike, view

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$dataDir = __DIR__ . '/../data';
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0777, true);
}

function respond($data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function sanitize_id(string $id): string {
    // Allow only alphanumeric, underscore and dash
    return preg_replace('/[^a-zA-Z0-9_-]/', '_', $id);
}

function file_path(string $id): string {
    global $dataDir;
    return $dataDir . '/' . sanitize_id($id) . '.txt';
}

function read_counts(string $id): array {
    $path = file_path($id);
    if (!file_exists($path)) {
        return ['likes' => 0, 'views' => 0];
    }
    $content = @file_get_contents($path);
    if ($content === false || $content === '') {
        return ['likes' => 0, 'views' => 0];
    }
    $likes = 0; $views = 0;
    $lines = preg_split('/\r?\n/', (string)$content);
    foreach ($lines as $line) {
        if (preg_match('/^\s*likes\s*[:=]\s*(\d+)/i', $line, $m)) {
            $likes = max(0, (int)$m[1]);
        } elseif (preg_match('/^\s*views\s*[:=]\s*(\d+)/i', $line, $m)) {
            $views = max(0, (int)$m[1]);
        }
    }
    // Backward compatibility: if file accidentally contains JSON
    if ($likes === 0 && $views === 0) {
        $json = json_decode($content, true);
        if (is_array($json)) {
            $likes = isset($json['likes']) && is_numeric($json['likes']) ? max(0, (int)$json['likes']) : 0;
            $views = isset($json['views']) && is_numeric($json['views']) ? max(0, (int)$json['views']) : 0;
        }
    }
    return ['likes' => $likes, 'views' => $views];
}

function write_counts(string $id, array $counts): array {
    $path = file_path($id);
    $fp = fopen($path, 'c+');
    if ($fp === false) {
        respond(['error' => 'Cannot open storage file'], 500);
    }
    try {
        if (!flock($fp, LOCK_EX)) {
            respond(['error' => 'Cannot lock storage file'], 500);
        }
        // Merge with existing values
        $current = stream_get_contents($fp);
        $existing = ['likes' => 0, 'views' => 0];
        if ($current !== false && $current !== '') {
            $parsed = read_counts($id);
            $existing = $parsed;
        }
        $likes = isset($counts['likes']) ? max(0, (int)$counts['likes']) : $existing['likes'];
        $views = isset($counts['views']) ? max(0, (int)$counts['views']) : $existing['views'];

        $text = "likes=${likes}\nviews=${views}\n";
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, $text);
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
        return ['likes' => $likes, 'views' => $views];
    } catch (Throwable $e) {
        try { fclose($fp); } catch (Throwable $e2) { /* ignore */ }
        respond(['error' => 'Write failed'], 500);
    }
}

// Parse input (JSON body preferred)
$input = [];
if (isset($_SERVER['CONTENT_TYPE']) && stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $raw = file_get_contents('php://input') ?: '';
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        $input = $decoded;
    }
}
// Fallback to query/form
if (empty($input)) {
    $input = $_REQUEST;
}

$action = isset($input['action']) ? (string)$input['action'] : '';
if ($action === '') {
    respond(['error' => 'Missing action'], 400);
}

switch ($action) {
    case 'get': {
        $id = isset($input['id']) ? (string)$input['id'] : '';
        if ($id === '') respond(['error' => 'Missing id'], 400);
        $counts = read_counts($id);
        respond(['id' => $id, 'likes' => $counts['likes'], 'views' => $counts['views']]);
    }
    case 'get_many': {
        $ids = $input['ids'] ?? [];
        if (is_string($ids)) {
            $ids = array_filter(explode(',', $ids));
        }
        if (!is_array($ids) || empty($ids)) {
            respond(['error' => 'Missing ids'], 400);
        }
        $result = [];
        foreach ($ids as $id) {
            $idStr = (string)$id;
            $counts = read_counts($idStr);
            $result[$idStr] = $counts;
        }
        respond(['counts' => $result]);
    }
    case 'like': {
        $id = isset($input['id']) ? (string)$input['id'] : '';
        if ($id === '') respond(['error' => 'Missing id'], 400);
        $counts = read_counts($id);
        $counts['likes'] = ($counts['likes'] ?? 0) + 1;
        $saved = write_counts($id, $counts);
        respond(['id' => $id, 'likes' => $saved['likes'], 'views' => $saved['views']]);
    }
    case 'unlike': {
        $id = isset($input['id']) ? (string)$input['id'] : '';
        if ($id === '') respond(['error' => 'Missing id'], 400);
        $counts = read_counts($id);
        $likes = ($counts['likes'] ?? 0) - 1;
        if ($likes < 0) $likes = 0;
        $counts['likes'] = $likes;
        $saved = write_counts($id, $counts);
        respond(['id' => $id, 'likes' => $saved['likes'], 'views' => $saved['views']]);
    }
    case 'view': {
        $id = isset($input['id']) ? (string)$input['id'] : '';
        if ($id === '') respond(['error' => 'Missing id'], 400);
        $counts = read_counts($id);
        $counts['views'] = ($counts['views'] ?? 0) + 1;
        $saved = write_counts($id, $counts);
        respond(['id' => $id, 'likes' => $saved['likes'], 'views' => $saved['views']]);
    }
    default:
        respond(['error' => 'Unknown action'], 400);
}

