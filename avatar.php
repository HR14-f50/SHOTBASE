<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/icon_catalog.php';
$motif = is_string($_GET['motif'] ?? null) ? $_GET['motif'] : '';
$color = is_string($_GET['color'] ?? null) ? $_GET['color'] : '';
if (!isset(iconMotifs()[$motif], iconColors()[$color])) {
  http_response_code(404);
  exit;
}
header('Content-Type: image/svg+xml; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'; sandbox");
header('Cache-Control: public, max-age=86400');
$svg = file_get_contents(__DIR__ . '/assets/icons/motifs/' . $motif . '.svg');
echo str_replace('<g ', '<rect width="100" height="100" fill="' . iconColors()[$color]['hex'] . '"/><g ', $svg);
