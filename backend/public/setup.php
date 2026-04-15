<?php
/**
 * One-time setup script for cPanel shared hosting (no terminal required).
 * -----------------------------------------------------------------------
 * 1. Upload your backend as normal.
 * 2. Visit  https://admin.e-modern.ug/setup.php?token=me-setup-2025
 * 3. Read the output — all green = you are done.
 * 4. DELETE this file immediately afterwards.
 */

define('SETUP_TOKEN', 'me-setup-2025');

// ── Auth ──────────────────────────────────────────────────────────────────────
if (empty($_GET['token']) || $_GET['token'] !== SETUP_TOKEN) {
    http_response_code(403);
    echo '<pre>403 Forbidden — pass  ?token=me-setup-2025  in the URL.</pre>';
    exit;
}

// ── Bootstrap Laravel ─────────────────────────────────────────────────────────
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

// Boot the HTTP kernel so facades work
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::capture();
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;

$results = [];

function row(string $icon, string $label, string $detail = ''): string {
    return "<tr>
        <td style='padding:8px 12px;font-size:20px;'>$icon</td>
        <td style='padding:8px 12px;font-weight:600;'>$label</td>
        <td style='padding:8px 12px;color:#555;font-size:13px;'>$detail</td>
    </tr>";
}

// ── 1. Migrations ─────────────────────────────────────────────────────────────
try {
    Artisan::call('migrate', ['--force' => true]);
    $out = trim(Artisan::output());
    $results[] = row('✅', 'Migrations ran', $out ?: 'No new migrations.');
} catch (Throwable $e) {
    $results[] = row('❌', 'Migrations FAILED', htmlspecialchars($e->getMessage()));
}

// ── 2. Storage symlink ────────────────────────────────────────────────────────
$link   = __DIR__ . '/storage';
$target = __DIR__ . '/../storage/app/public';

if (is_link($link)) {
    $results[] = row('✅', 'Storage link', 'Already exists — skipped.');
} elseif (file_exists($link)) {
    $results[] = row('⚠️', 'Storage link', 'A real directory called /public/storage exists — remove it manually then re-run.');
} else {
    try {
        symlink($target, $link);
        $results[] = row('✅', 'Storage link created', "$link → $target");
    } catch (Throwable $e) {
        // Fallback: copy instead of symlink (some hosts block symlinks)
        $results[] = row('⚠️', 'Storage symlink blocked', 'Trying folder copy…');
        try {
            if (!is_dir($link)) mkdir($link, 0775, true);
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($target, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST) as $item) {
                $dest = $link . '/' . (new RecursiveDirectoryIterator($target))->getSubPathname();
                if ($item->isDir()) @mkdir($dest, 0775, true);
                else @copy($item, $dest);
            }
            $results[] = row('✅', 'Storage folder copied', 'Symlink not supported; folder copy used instead.');
        } catch (Throwable $e2) {
            $results[] = row('❌', 'Storage FAILED', htmlspecialchars($e2->getMessage()));
        }
    }
}

// ── 3. Permissions ────────────────────────────────────────────────────────────
$permDirs = [
    __DIR__ . '/../storage',
    __DIR__ . '/../storage/framework',
    __DIR__ . '/../storage/framework/sessions',
    __DIR__ . '/../storage/framework/views',
    __DIR__ . '/../storage/framework/cache',
    __DIR__ . '/../storage/framework/cache/data',
    __DIR__ . '/../storage/logs',
    __DIR__ . '/../storage/app',
    __DIR__ . '/../storage/app/public',
    __DIR__ . '/../bootstrap/cache',
];

$permOk = 0;
foreach ($permDirs as $dir) {
    if (is_dir($dir)) {
        @chmod($dir, 0775);
        $permOk++;
    }
}
$results[] = row('✅', 'Permissions set', "chmod 775 applied to $permOk storage/cache directories.");

// ── 4. Clear caches ───────────────────────────────────────────────────────────
try {
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('cache:clear');
    $results[] = row('✅', 'All caches cleared', 'config · route · view · cache');
} catch (Throwable $e) {
    $results[] = row('⚠️', 'Cache clear partial', htmlspecialchars($e->getMessage()));
}

// ── 5. Environment check ──────────────────────────────────────────────────────
$env   = config('app.env');
$debug = config('app.debug') ? 'true ⚠️ set to false in .env!' : 'false ✅';
$url   = config('app.url');
$db    = config('database.default');

$results[] = row('ℹ️', 'APP_ENV',   htmlspecialchars($env));
$results[] = row('ℹ️', 'APP_DEBUG', $debug);
$results[] = row('ℹ️', 'APP_URL',   htmlspecialchars($url));
$results[] = row('ℹ️', 'DB driver', htmlspecialchars($db));
$results[] = row('ℹ️', 'Session',   config('session.driver'));
$results[] = row('ℹ️', 'Cache',     config('cache.default'));

// ── 6. DB connection test ─────────────────────────────────────────────────────
try {
    \Illuminate\Support\Facades\DB::connection()->getPdo();
    $results[] = row('✅', 'Database connected', config('database.connections.'.config('database.default').'.database'));
} catch (Throwable $e) {
    $results[] = row('❌', 'Database FAILED', htmlspecialchars($e->getMessage()));
}

// ── Output ────────────────────────────────────────────────────────────────────
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Setup — Modern Electronics Admin</title>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
       background:#f3f4f6;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
  .card{background:#fff;border-radius:16px;box-shadow:0 4px 24px rgba(0,0,0,.10);
        width:100%;max-width:720px;overflow:hidden}
  .header{background:#111827;color:#fff;padding:24px 28px}
  .header h1{font-size:20px;font-weight:800;letter-spacing:-.02em}
  .header p{margin-top:6px;font-size:13px;color:#9ca3af}
  table{width:100%;border-collapse:collapse}
  tr+tr{border-top:1px solid #f3f4f6}
  .warning{background:#fefce8;border-top:1px solid #fef08a;padding:16px 28px;
            font-size:13px;color:#854d0e;font-weight:600;text-align:center}
</style>
</head>
<body>
<div class="card">
  <div class="header">
    <h1>Modern Electronics — Server Setup</h1>
    <p>Results of the one-time setup run. Check every row is ✅ then delete <strong>public/setup.php</strong>.</p>
  </div>
  <table>
    <?= implode('', $results) ?>
  </table>
  <div class="warning">
    ⚠️&nbsp; DELETE &nbsp;<code>public/setup.php</code>&nbsp; immediately after setup to close this security hole.
  </div>
</div>
</body>
</html>
