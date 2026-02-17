<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$db = $app->make('db');

try {
    $rows = $db->select('SELECT id, image_path FROM products WHERE image_path IS NOT NULL LIMIT 50');
    $base = rtrim(config('app.url') ?: 'http://127.0.0.1:8000', '/');
    $results = [];
    foreach ($rows as $r) {
        $path = ltrim($r->image_path, '/');
        $url = $base . '/storage/' . str_replace('%2F','/', rawurlencode($path));
        // Use get_headers to fetch headers, allow redirects
        $hdrs = @get_headers($url, 1);
        if ($hdrs === false) {
            $results[] = ['id' => $r->id, 'path' => $path, 'url' => $url, 'error' => 'no_response'];
            continue;
        }
        // Status line is the first element
        $statusLine = $hdrs[0] ?? '';
        preg_match('#HTTP/\d\.\d\s+(\d{3})#', $statusLine, $m);
        $status = isset($m[1]) ? (int)$m[1] : null;
        $ct = $hdrs['Content-Type'] ?? ($hdrs['content-type'] ?? null);
        $len = $hdrs['Content-Length'] ?? ($hdrs['content-length'] ?? null);
        $results[] = ['id' => $r->id, 'path' => $path, 'url' => $url, 'status' => $status, 'content_type' => $ct, 'content_length' => $len];
    }
    echo json_encode($results, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
