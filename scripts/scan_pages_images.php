<?php
require __DIR__ . '/../vendor/autoload.php';

$base = rtrim((getenv('APP_URL') ?: 'http://127.0.0.1:8000'), '/');
$pages = [
    '/',
    '/products',
    '/products?page=1',
    '/admin',
    '/admin/products',
    '/manager',
    '/cashier',
    '/login'
];

function fetch_html($url) {
    // use stream context to include headers in response
    $opts = [
        'http' => [
            'method' => 'GET',
            'ignore_errors' => true,
            'header' => "User-Agent: ImageScanner/1.0\r\n",
            'timeout' => 10,
        ]
    ];
    $context = stream_context_create($opts);
    $html = @file_get_contents($url, false, $context);
    $headers = isset($http_response_header) ? $http_response_header : [];
    return [$html, $headers];
}

function normalize_url($src, $base) {
    if (preg_match('#^https?://#i', $src)) return $src;
    if (strpos($src, '//') === 0) return parse_url($base, PHP_URL_SCHEME) . ':' . $src;
    if ($src[0] === '/') return rtrim($base, '/') . $src;
    // relative path
    return rtrim($base, '/') . '/' . ltrim($src, './');
}

function head_info($url) {
    // get headers (follows redirects)
    $hdrs = @get_headers($url, 1);
    if ($hdrs === false) return ['error' => 'no_response'];
    $statusLine = $hdrs[0] ?? '';
    preg_match('#HTTP/\d\.\d\s+(\d{3})#', $statusLine, $m);
    $status = isset($m[1]) ? (int)$m[1] : null;
    $ct = $hdrs['Content-Type'] ?? ($hdrs['content-type'] ?? null);
    $len = $hdrs['Content-Length'] ?? ($hdrs['content-length'] ?? null);
    // If redirect chain present, get last status if array
    if (is_array($status)) $status = (int)end($status);
    if (is_array($ct)) $ct = end($ct);
    if (is_array($len)) $len = end($len);
    return ['status' => $status, 'content_type' => $ct, 'content_length' => $len, 'raw_headers' => $hdrs];
}

$report = [];
foreach ($pages as $p) {
    $url = $base . (strpos($p, '/') === 0 ? $p : '/' . $p);
    list($html, $headers) = fetch_html($url);
    $pageEntry = ['page' => $url, 'http_headers' => $headers, 'images' => []];
    if ($html === false || strlen($html) === 0) {
        $pageEntry['error'] = 'no_html_or_empty';
        $report[] = $pageEntry;
        continue;
    }
    // parse HTML for <img>
    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    if (@$dom->loadHTML($html)) {
        $imgs = $dom->getElementsByTagName('img');
        foreach ($imgs as $img) {
            $src = $img->getAttribute('src');
            $abs = normalize_url($src, $base);
            $info = head_info($abs);
            $pageEntry['images'][] = ['src' => $src, 'abs' => $abs, 'info' => $info];
        }
    } else {
        $pageEntry['error'] = 'html_parse_failed';
    }
    $report[] = $pageEntry;
}

echo json_encode($report, JSON_PRETTY_PRINT);
