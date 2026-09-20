<?php
declare(strict_types=1);

// Keep deployment credentials outside the repository. Set these variables in
// the web server/PHP-FPM environment for the target installation.
define('DB_HOST', getenv('SHOTBASE_DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('SHOTBASE_DB_NAME') ?: 'shotbase');
define('DB_USER', getenv('SHOTBASE_DB_USER') ?: 'shotbase');
define('DB_PASS', getenv('SHOTBASE_DB_PASS') ?: '');

define('BASE_URL', getenv('SHOTBASE_BASE_URL') ?: '/shotbase');

define('SERVICE_NAME', 'SHOTBASE');

define('MAX_UPLOAD_BYTES', 20 * 1024 * 1024);
define('MAX_STORED_BYTES', 2 * 1024 * 1024);
define('MAX_UPLOAD_BATCH', 50);
define('MAX_LONG_SIDE', 2000);
define('MAX_PHOTOS_PER_PROJECT', 200);

define('PHOTO_UPLOAD_DIR', __DIR__ . '/../uploads/photos/');
define('PHOTO_ORIGINAL_DIR', __DIR__ . '/../uploads/originals/');

date_default_timezone_set('Asia/Tokyo');
