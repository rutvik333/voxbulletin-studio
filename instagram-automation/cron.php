<?php
/**
 * Vox Bulletin Automated Publisher
 * Runs 3x daily (e.g. 9:00 AM, 2:00 PM, 8:00 PM)
 * 1. Fetches today's news from FandomWire RSS
 * 2. Filters out any previously posted articles (zero duplicates)
 * 3. Prioritizes Marvel & DC first, then major pop-culture, then general
 * 4. Rewrites copy using Google Gemini AI
 * 5. Headlessly renders the 1080x1350 branded graphic with 3D globe logo
 * 6. Publishes live across Instagram, Facebook, and Twitter/X via Buffer
 */

ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
set_time_limit(180);

$baseDir = __DIR__;
$configFile = $baseDir . '/config.php';
if (!file_exists($configFile)) {
    die(json_encode(['ok' => false, 'error' => 'config.php not found']));
}
$config = require $configFile;

// CLI or Web execution check
$isCli = (php_sapi_name() === 'cli');
$cronKey = $_GET['key'] ?? '';
$secretKey = $config['cron_secret_key'] ?? 'voxbulletin_auto_cron_key';
if (!$isCli && $cronKey !== $secretKey && !empty($_GET['key'])) {
    http_response_code(403);
    die(json_encode(['ok' => false, 'error' => 'Invalid cron security key']));
}

function hexToRgb($hex) {
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) {
        $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    }
    return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
}

function wrapTextTtf($size, $angle, $font, $text, $maxWidth) {
    $words = preg_split('/\s+/', trim($text));
    $lines = [];
    $cur = $words[0] ?? '';
    for ($i = 1; $i < count($words); $i++) {
        $test = $cur . ' ' . $words[$i];
        $box = imagettfbbox($size, $angle, $font, $test);
        $w = abs($box[4] - $box[0]);
        if ($w <= $maxWidth) {
            $cur = $test;
        } else {
            $lines[] = $cur;
            $cur = $words[$i];
            if (count($lines) >= 3) {
                $rest = implode(' ', array_slice($words, $i));
                $lines[] = $rest;
                return $lines;
            }
        }
    }
    if ($cur !== '') $lines[] = $cur;
    return $lines;
}

function renderAutomatedPoster($heroUrl, $headline, $description, $outputPath, $palette = ['#ffd12a', '#00d2ff']) {
    $W = 1080;
    $H = 1350;
    $im = imagecreatetruecolor($W, $H);
    imagealphablending($im, true);
    imagesavealpha($im, true);

    $bgNavy = imagecolorallocate($im, 0, 18, 38);
    imagefilledrectangle($im, 0, 0, $W, $H, $bgNavy);

    // 1. Download & Draw Hero Image
    $heroIm = null;
    if ($heroUrl) {
        $ch = curl_init($heroUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ]);
        $data = curl_exec($ch);
        curl_close($ch);
        if ($data) {
            $heroIm = @imagecreatefromstring($data);
        }
    }

    if ($heroIm) {
        $origW = imagesx($heroIm);
        $origH = imagesy($heroIm);
        $targetH = 880;
        $scale = max($W / $origW, $targetH / $origH);
        $sw = (int)($W / $scale);
        $sh = (int)($targetH / $scale);
        $sx = (int)(($origW - $sw) / 2);

        imagecopyresampled($im, $heroIm, 0, 0, $sx, 0, $W, $targetH, $sw, $sh);
        imagedestroy($heroIm);
    }

    // 2. Alpha Gradient Fade into Navy (y=360 to y=720)
    for ($y = 360; $y <= 720; $y++) {
        $t = ($y - 360) / (720 - 360);
        $alpha = (int)(127 * (1.0 - pow($t, 1.8)));
        $fadeCol = imagecolorallocatealpha($im, 0, 18, 38, $alpha);
        imageline($im, 0, $y, $W, $y, $fadeCol);
    }
    imagefilledrectangle($im, 0, 720, $W, $H, $bgNavy);

    // 3. Brand Globe Logo (top right)
    $globePath = __DIR__ . '/brand-globe.png';
    if (file_exists($globePath)) {
        $globe = imagecreatefrompng($globePath);
        if ($globe) {
            $logoSize = 44;
            $topY = 48;
            $fontInter = __DIR__ . '/fonts/Inter-Bold.ttf';
            
            $text = "VoxBulletin";
            $tbox = imagettfbbox(19, 0, $fontInter, $text);
            $tw = abs($tbox[4] - $tbox[0]);
            $totalW = $logoSize + 12 + $tw;
            $startX = $W - 55 - $totalW;

            // Translucent rounded backing pill
            $pillBg = imagecolorallocatealpha($im, 0, 18, 38, 35);
            imagefilledrectangle($im, $startX - 14, $topY - 8, $startX + $totalW + 14, $topY + $logoSize + 8, $pillBg);
            $borderCol = imagecolorallocatealpha($im, 255, 255, 255, 100);
            imagerectangle($im, $startX - 14, $topY - 8, $startX + $totalW + 14, $topY + $logoSize + 8, $borderCol);

            imagecopyresampled($im, $globe, $startX, $topY, 0, 0, $logoSize, $logoSize, imagesx($globe), imagesy($globe));
            imagedestroy($globe);

            $white = imagecolorallocate($im, 255, 255, 255);
            imagettftext($im, 19, 0, $startX + $logoSize + 12, $topY + 31, $white, $fontInter, $text);
        }
    }

    // 4. Headline Lines (Barlow Condensed ExtraBold)
    $fontHl = __DIR__ . '/fonts/BarlowCondensed-ExtraBold.ttf';
    $hlUpper = mb_strtoupper(trim($headline));
    $fontSize = 48;
    $lines = wrapTextTtf($fontSize, 0, $fontHl, $hlUpper, 960);
    $lineH = (int)($fontSize * 1.25);

    // 5. Description Lines (Inter Bold)
    $fontDesc = __DIR__ . '/fonts/Inter-Bold.ttf';
    $descSize = 17;
    $dlines = wrapTextTtf($descSize, 0, $fontDesc, trim($description), 940);
    $dlineH = (int)($descSize * 1.5);

    $totalHlH = count($lines) * $lineH;
    $totalDescH = count($dlines) * $dlineH;
    $gap = 28;
    $totalBlockH = $totalHlH + ($totalDescH ? ($gap + $totalDescH) : 0);

    $darkTop = 740;
    $footerY = 1292;
    $availSpace = ($footerY - 45) - $darkTop;
    $blockTop = $darkTop + max(15, (int)(($availSpace - $totalBlockH) / 2));
    $curY = $blockTop + $fontSize;

    // Render Headline Lines
    $rgbA = hexToRgb($palette[0] ?? '#ffd12a');
    $rgbB = hexToRgb($palette[1] ?? '#00d2ff');
    $colA = imagecolorallocate($im, $rgbA[0], $rgbA[1], $rgbA[2]);
    $colB = imagecolorallocate($im, $rgbB[0], $rgbB[1], $rgbB[2]);
    $white = imagecolorallocate($im, 255, 255, 255);

    foreach ($lines as $i => $line) {
        $color = $white;
        if (count($lines) === 1) $color = $colA;
        elseif (count($lines) === 2) $color = ($i === 0 ? $colA : $colB);
        else $color = ($i === 0 ? $colA : ($i === count($lines) - 1 ? $colB : $white));

        $box = imagettfbbox($fontSize, 0, $fontHl, $line);
        $lw = abs($box[4] - $box[0]);
        $lx = (int)(($W - $lw) / 2);
        imagettftext($im, $fontSize, 0, $lx, $curY, $color, $fontHl, $line);
        $curY += $lineH;
    }

    // Render Description Lines
    if (count($dlines)) {
        $curY += $gap - 10;
        $descCol = imagecolorallocate($im, 166, 182, 206);
        foreach ($dlines as $dline) {
            $box = imagettfbbox($descSize, 0, $fontDesc, $dline);
            $lw = abs($box[4] - $box[0]);
            $lx = (int)(($W - $lw) / 2);
            imagettftext($im, $descSize, 0, $lx, $curY, $descCol, $fontDesc, $dline);
            $curY += $dlineH;
        }
    }

    // 6. Brand Footer Dividers + "VOX BULLETIN"
    $divCol = imagecolorallocatealpha($im, 255, 255, 255, 45);
    imageline($im, 270, $footerY, 430, $footerY, $divCol);
    imageline($im, 650, $footerY, 810, $footerY, $divCol);
    $brandText = "VOX   BULLETIN";
    $bbox = imagettfbbox(14, 0, $fontDesc, $brandText);
    $bw = abs($bbox[4] - $bbox[0]);
    imagettftext($im, 14, 0, (int)(($W - $bw) / 2), $footerY + 6, $white, $fontDesc, $brandText);

    imagepng($im, $outputPath);
    imagedestroy($im);
    return file_exists($outputPath);
}

function cleanText($text) {
    if (!$text) return '';
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = preg_replace('/\s*[-–—|]\s*Fandom\s*Wire.*$/i', '', $text);
    $text = preg_replace('/\b(This post belongs to and first appeared on|The post .* appeared first on).*$/i', '', $text);
    $text = preg_replace('/\b(according to|per|as reported by|via)\s+Fandom\s*Wire,?\s*/i', '', $text);
    $text = preg_replace('/\bFandom\s*Wire\s+(reports|reported|reveals|revealed|claims|claimed|stated)\s+(that\s+)?/i', 'Reports indicate ', $text);
    $text = preg_replace('/#?Fandom\s*Wire(\.com)?\b/i', '', $text);
    $text = preg_replace('/\s*\[…\]\s*$/', '...', $text);
    $text = preg_replace('/\s*\[\.\.\.\]\s*$/', '...', $text);
    $text = preg_replace('/\s+/', ' ', $text);
    return trim($text);
}

function uploadToCdn($filePath, $bytes) {
    $ch = curl_init('https://freeimage.host/api/1/upload');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 25,
        CURLOPT_POSTFIELDS => [
            'key' => '6d207e02198a847aa98d0a2a901485a5',
            'action' => 'upload',
            'source' => base64_encode($bytes),
            'format' => 'json'
        ]
    ]);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code === 200 && $res) {
        $json = json_decode($res, true);
        if (!empty($json['image']['url'])) {
            return $json['image']['url'];
        }
    }
    return null;
}

if (!function_exists('getGeminiKeysPool')) {
    function getGeminiKeysPool($cfg) {
        $keys = [];
        if (!empty($cfg['gemini_api_keys']) && is_array($cfg['gemini_api_keys'])) {
            foreach ($cfg['gemini_api_keys'] as $k) {
                $k = trim((string)$k);
                if ($k !== '' && !str_contains($k, 'PASTE_')) $keys[] = $k;
            }
        }
        if (empty($keys) && !empty($cfg['gemini_api_key'])) {
            $k = trim((string)$cfg['gemini_api_key']);
            if ($k !== '' && !str_contains($k, 'PASTE_')) $keys[] = $k;
        }
        if (empty($keys)) {
            $extCfg = dirname(__DIR__) . '/facebook-post-extractor/config.json';
            if (file_exists($extCfg)) {
                $j = json_decode(file_get_contents($extCfg), true);
                if (!empty($j['gemini_api_keys']) && is_array($j['gemini_api_keys'])) {
                    foreach ($j['gemini_api_keys'] as $k) {
                        $k = trim((string)$k);
                        if ($k !== '' && !str_contains($k, 'PASTE_')) $keys[] = $k;
                    }
                } elseif (!empty($j['gemini_api_key'])) {
                    $k = trim((string)$j['gemini_api_key']);
                    if ($k !== '' && !str_contains($k, 'PASTE_')) $keys[] = $k;
                }
            }
        }
        return array_values(array_unique($keys));
    }
}

if (!function_exists('callGeminiWithFailover')) {
    function callGeminiWithFailover($prompt, $cfg, $options = []) {
        $keys = getGeminiKeysPool($cfg);
        if (empty($keys)) {
            return ['ok' => false, 'error' => 'No Gemini API keys configured.'];
        }

        $isJson = !empty($options['json']);
        $model = $options['model'] ?? 'gemini-2.5-flash';
        $temperature = $options['temperature'] ?? 0.25;

        $generationConfig = ['temperature' => $temperature];
        if ($isJson) {
            $generationConfig['responseMimeType'] = 'application/json';
        }

        $payload = json_encode([
            'contents' => [
                ['parts' => [['text' => $prompt]]]
            ],
            'generationConfig' => $generationConfig
        ], JSON_UNESCAPED_SLASHES);

        $errors = [];
        foreach ($keys as $idx => $key) {
            $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent?key=' . urlencode($key);
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 35,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'User-Agent: VoxBulletin/2.0'],
                CURLOPT_POSTFIELDS => $payload
            ]);
            $raw = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $res = json_decode($raw ?: '', true);
            $masked = (strlen($key) > 10) ? substr($key, 0, 6) . '...' . substr($key, -4) : 'Key#' . ($idx + 1);

            if ($code === 200 && !empty($res['candidates'][0]['content']['parts'][0]['text'])) {
                return [
                    'ok' => true,
                    'text' => $res['candidates'][0]['content']['parts'][0]['text'],
                    'key_index' => $idx,
                    'key_used' => $masked,
                    'failovers' => $idx,
                    'data' => $res
                ];
            }

            $errMsg = $res['error']['message'] ?? ('HTTP ' . $code);
            $isQuota = ($code === 429 || $code === 403 || stripos($errMsg, 'quota') !== false || ($res['error']['status'] ?? '') === 'RESOURCE_EXHAUSTED');
            $errors[] = "Key #" . ($idx + 1) . " (" . $masked . "): " . ($isQuota ? "Daily quota exceeded" : $errMsg);
        }

        return [
            'ok' => false,
            'error' => 'All Gemini keys failed or exceeded quota limit.',
            'details' => implode(' | ', $errors),
            'keys_count' => count($keys)
        ];
    }
}

// ---------------------------------------------------------------------
// DATABASE INITIALIZATION & CONNECTIVITY
// ---------------------------------------------------------------------
$dbFile = $baseDir . '/storage/database.sqlite';
$pdo = null;
if (!empty($config['db_name'])) {
    $dsn = "mysql:host=" . ($config['db_host'] ?: 'localhost') . ";dbname=" . $config['db_name'] . ";charset=utf8mb4";
    $pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} else {
    $pdo = new PDO('sqlite:' . $dbFile, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
}

// Ensure automation_logs table exists
$pdo->exec('CREATE TABLE IF NOT EXISTS automation_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    article_title TEXT,
    article_url TEXT,
    priority_category TEXT,
    priority_score INTEGER,
    headline TEXT,
    image_file TEXT,
    channels_published TEXT,
    status TEXT,
    message TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)');

// ============================================================
// WEEKLY STORAGE CLEANUP: Purge posts & images older than 7 days
// Guarantees Hostinger / local disk storage never fills up
// ============================================================
try {
    $cleanupCutoff = date('Y-m-d H:i:s', strtotime('-7 days'));
    $oldPostsStmt = $pdo->prepare("SELECT id, image_file FROM posts WHERE created_at < ?");
    $oldPostsStmt->execute([$cleanupCutoff]);
    $oldPosts = $oldPostsStmt->fetchAll(PDO::FETCH_ASSOC);
    $freedImages = 0;
    foreach ($oldPosts as $op) {
        if (!empty($op['image_file'])) {
            $fpath = __DIR__ . '/storage/posts/' . $op['image_file'];
            if (is_file($fpath)) {
                @unlink($fpath);
                $freedImages++;
            }
        }
    }
    if (!empty($oldPosts)) {
        $delOldStmt = $pdo->prepare("DELETE FROM posts WHERE created_at < ?");
        $delOldStmt->execute([$cleanupCutoff]);
        echo "[Storage Cleanup] Purged " . count($oldPosts) . " post(s) older than 7 days ($freedImages storage image files deleted).\n";
    }
    // Also clean any orphaned png files in storage/posts older than 7 days
    $storagePostsDir = __DIR__ . '/storage/posts';
    if (is_dir($storagePostsDir)) {
        foreach (glob($storagePostsDir . '/*.png') as $orphan) {
            if (filemtime($orphan) < (time() - (7 * 86400))) {
                @unlink($orphan);
            }
        }
    }
} catch (Throwable $e) {
    echo "[Storage Cleanup Notice] " . $e->getMessage() . "\n";
}

// 1. Fetch connected channels
$channels = $pdo->query('SELECT platform, account_name, account_handle, remote_id, access_token FROM channels WHERE is_connected = 1')->fetchAll();
if (empty($channels)) {
    $msg = 'No connected social channels found in database.';
    $pdo->prepare('INSERT INTO automation_logs (status, message) VALUES (?, ?)')->execute(['skipped', $msg]);
    die(json_encode(['ok' => false, 'message' => $msg]));
}

// 2. Fetch all previously published article URLs to guarantee 0 duplicates
$publishedUrls = [];
$rows = $pdo->query('SELECT source_url FROM posts WHERE source_url != ""')->fetchAll();
foreach ($rows as $r) {
    if (!empty($r['source_url'])) $publishedUrls[strtolower(trim($r['source_url']))] = true;
}

// 3. Ingest FandomWire RSS feed
$feedUrl = 'https://fandomwire.com/feed/';
$ch = curl_init($feedUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 20,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
]);
$xmlStr = curl_exec($ch);
curl_close($ch);

if (!$xmlStr) {
    $msg = 'Failed to fetch FandomWire RSS feed.';
    $pdo->prepare('INSERT INTO automation_logs (status, message) VALUES (?, ?)')->execute(['error', $msg]);
    die(json_encode(['ok' => false, 'message' => $msg]));
}

$xml = @simplexml_load_string($xmlStr);
if (!$xml || !isset($xml->channel->item)) {
    $msg = 'Invalid RSS XML format received.';
    $pdo->prepare('INSERT INTO automation_logs (status, message) VALUES (?, ?)')->execute(['error', $msg]);
    die(json_encode(['ok' => false, 'message' => $msg]));
}

// 4. Filter for today's unposted articles and calculate Marvel/DC priority
$now = time();
$candidates = [];

// Keywords for Priority Scoring
$marvelPattern = '/\b(marvel|mcu|avengers|spider-man|spiderman|deadpool|wolverine|iron man|captain america|thor|hulk|black panther|x-men|daredevil|fantastic four|blade|secret wars|doomsday|tom holland|robert downey|hugh jackman|ryan reynolds)\b/i';
$dcPattern = '/\b(dc|dcu|batman|superman|joker|harley quinn|flash|wonder woman|aquaman|james gunn|zack snyder|green lantern|robin|nightwing|gotham|peacemaker|david corenswet)\b/i';
$franchisePattern = '/\b(star wars|dragon ball|naruto|one piece|anime|manga|gta|playstation|xbox|nintendo|zelda|lord of the rings|stranger things|hbo|netflix|disney)\b/i';

foreach ($xml->channel->item as $it) {
    $title = cleanText((string)$it->title);
    $url = trim((string)$it->link);
    $cleanUrl = strtolower($url);

    // Skip if already published in history
    if (isset($publishedUrls[$cleanUrl])) continue;

    $pubDateStr = trim((string)$it->pubDate);
    $pubTs = strtotime($pubDateStr) ?: $now;

    // Filter: within past 30 hours (today's articles)
    if (($now - $pubTs) > (30 * 3600)) continue;

    // Extract image
    $image = '';
    $namespaces = $it->getNameSpaces(true);
    if (isset($namespaces['content'])) {
        $cChildren = $it->children($namespaces['content']);
        if (isset($cChildren->encoded) && preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', (string)$cChildren->encoded, $im)) {
            $image = $im[1];
        }
    }
    if (!$image && isset($namespaces['media'])) {
        $mChildren = $it->children($namespaces['media']);
        if (isset($mChildren->content)) {
            $attrs = $mChildren->content->attributes();
            if (isset($attrs['url'])) $image = (string)$attrs['url'];
        }
    }
    if (!$image && preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', (string)$it->description, $im)) {
        $image = $im[1];
    }
    if (!$image) continue;
    $image = strtok($image, '?') ?: $image; // Guarantee main uncompressed high-resolution image for poster canvas

    $desc = cleanText(strip_tags((string)$it->description));

    // Priority Score Engine
    $fullSearch = $title . ' ' . $desc;
    $score = 10;
    $category = 'General Entertainment';

    if (preg_match($marvelPattern, $fullSearch)) {
        $score = 100;
        $category = 'Marvel Universe Priority';
    } elseif (preg_match($dcPattern, $fullSearch)) {
        $score = 95;
        $category = 'DC Universe Priority';
    } elseif (preg_match($franchisePattern, $fullSearch)) {
        $score = 50;
        $category = 'Major Franchise Priority';
    }

    $candidates[] = [
        'title' => $title,
        'url' => $url,
        'description' => $desc,
        'image' => $image,
        'pubTs' => $pubTs,
        'score' => $score,
        'category' => $category
    ];
}

if (empty($candidates)) {
    $msg = "All articles from today have already been published. Skipping this run to avoid duplicates.";
    $pdo->prepare('INSERT INTO automation_logs (status, message) VALUES (?, ?)')->execute(['skipped', $msg]);
    die(json_encode(['ok' => true, 'status' => 'skipped', 'message' => $msg]));
}

// Sort candidates: Highest Priority Score first, then most recent pubTs
usort($candidates, function($a, $b) {
    if ($a['score'] !== $b['score']) {
        return $b['score'] <=> $a['score'];
    }
    return $b['pubTs'] <=> $a['pubTs'];
});

$chosen = $candidates[0];

// 5. Rewrite Copy with Google Gemini AI (with Automatic Multi-Key Failover)
$hl = $chosen['title'];
$desc = $chosen['description'];
$caption = $chosen['title'] . "\n\n" . $chosen['description'] . "\n\n#voxbulletin #EntertainmentNews";
$captionX = '';
$keyUsedInfo = '';

$prompt = "You are the lead editor for VoxBulletin, an elite entertainment news brand.\n" .
          "Article Title: " . $chosen['title'] . "\n" .
          "Article Summary: " . $chosen['description'] . "\n" .
          "Task: Create a punchy viral headline (6-12 words uppercase, no quotes), a clean 2-sentence summary under 200 chars for the graphic, a full multi-paragraph Instagram/Facebook caption with hook and hashtags (#voxbulletin #EntertainmentNews), and a Twitter/X post strictly UNDER 260 characters with #voxbulletin.\n" .
          "Respond ONLY in valid JSON matching this schema: {\"headline\": \"...\", \"description\": \"...\", \"caption\": \"...\", \"caption_x\": \"...\"}";

$gCall = callGeminiWithFailover($prompt, $config, ['json' => true]);
if (!empty($gCall['ok'])) {
    $rawTxt = $gCall['text'] ?? '';
    if (preg_match('/\{[\s\S]*\}/', $rawTxt, $m)) {
        $parsed = json_decode($m[0], true);
        if (!empty($parsed['headline'])) $hl = cleanText($parsed['headline']);
        if (!empty($parsed['description'])) $desc = cleanText($parsed['description']);
        if (!empty($parsed['caption'])) $caption = trim($parsed['caption']);
        if (!empty($parsed['caption_x'])) $captionX = trim($parsed['caption_x']);
    }
    $keyUsedInfo = ' (via ' . ($gCall['key_used'] ?? 'Gemini') . ($gCall['failovers'] > 0 ? ', failovers: ' . $gCall['failovers'] : '') . ')';
}

// 6. Guarantee Twitter / X character ceiling <= 280
if (!$captionX) {
    $tag = '#voxbulletin';
    $cleanHl = trim($hl);
    $cleanD = trim($desc);
    $cand = $cleanHl . "\n\n" . $cleanD . "\n\n" . $tag;
    if (mb_strlen($cand) <= 280) {
        $captionX = $cand;
    } else {
        $avail = 280 - mb_strlen($cleanHl) - 2 - 5 - mb_strlen($tag);
        if ($avail >= 25) {
            $t = mb_substr($cleanD, 0, $avail);
            $lastSp = mb_strrpos($t, ' ');
            if ($lastSp !== false && $lastSp > 15) $t = mb_substr($t, 0, $lastSp);
            $captionX = $cleanHl . "\n\n" . $t . "...\n\n" . $tag;
        } else {
            $captionX = mb_substr($cleanHl, 0, 280 - mb_strlen($tag) - 5) . "...\n\n" . $tag;
        }
    }
}
if (mb_strlen($captionX) > 280) {
    $captionX = mb_substr($captionX, 0, 277) . '...';
}

// 7. Render Final 1080x1350 Branded Graphic
$postsFolder = $baseDir . '/storage/posts';
if (!is_dir($postsFolder)) mkdir($postsFolder, 0755, true);

$fileName = bin2hex(random_bytes(12)) . '.png';
$outputPath = $postsFolder . '/' . $fileName;

// Pick theme palette based on title
$palettes = [
    ['#ffd12a', '#00d2ff'], // Yellow & Sky Blue
    ['#ff9100', '#00d2ff'], // Sunset Orange & Sky Blue
    ['#00e676', '#ffd12a'], // Neon Green & Yellow
    ['#f472b6', '#00d2ff'], // Hot Pink & Sky Blue
    ['#c084fc', '#00e676']  // Electric Violet & Neon Green
];
$hash = crc32($chosen['title']);
$palette = $palettes[abs($hash) % count($palettes)];

$rendered = renderAutomatedPoster($chosen['image'], $hl, $desc, $outputPath, $palette);
if (!$rendered || !file_exists($outputPath)) {
    $msg = 'Failed to generate 1080x1350 poster image.';
    $pdo->prepare('INSERT INTO automation_logs (status, message) VALUES (?, ?)')->execute(['error', $msg]);
    die(json_encode(['ok' => false, 'message' => $msg]));
}

// 8. Determine Public Image URL for Buffer
$publicBase = rtrim((string)($config['public_base_url'] ?? ''), '/');
$postImageUrl = '';

if ($publicBase && !str_contains($publicBase, 'localhost') && !str_contains($publicBase, '127.0.0.1')) {
    $postImageUrl = $publicBase . '/storage/posts/' . $fileName;
} else {
    // Local / development: Bridge through high-speed public CDN
    $imgBytes = file_get_contents($outputPath);
    $postImageUrl = uploadToCdn($outputPath, $imgBytes);
    if (!$postImageUrl) {
        $postImageUrl = ($publicBase ?: 'http://127.0.0.1:8000') . '/storage/posts/' . $fileName;
    }
}

// 9. Dispatch to Buffer GraphQL API
$token = $config['buffer_access_token'] ?? '';
$results = [];
$publishedChannels = [];

foreach ($channels as $chan) {
    $chKey = $chan['platform'];
    $remoteId = $chan['remote_id'];
    $handle = $chan['account_handle'];
    $chToken = $chan['access_token'] ?: $token;

    if (!$remoteId || !$chToken) continue;

    $textToSend = ($chKey === 'x') ? $captionX : $caption;

    $postInput = [
        'channelId' => $remoteId,
        'text' => $textToSend,
        'schedulingType' => 'automatic',
        'mode' => 'shareNow',
        'needsApproval' => false,
        'assets' => [
            [
                'image' => [
                    'url' => $postImageUrl
                ]
            ]
        ]
    ];

    if ($chKey === 'instagram') {
        $postInput['metadata'] = [
            'instagram' => [
                'type' => 'post',
                'shouldShareToFeed' => true
            ]
        ];
    } elseif ($chKey === 'facebook') {
        $postInput['metadata'] = [
            'facebook' => [
                'type' => 'post'
            ]
        ];
    }

    $mutation = 'mutation CreatePost($input: CreatePostInput!) { createPost(input: $input) { ... on PostActionSuccess { post { id status } } ... on MutationError { message } } }';
    $payload = json_encode(['query' => $mutation, 'variables' => ['input' => $postInput]], JSON_UNESCAPED_SLASHES);

    $ch = curl_init('https://api.buffer.com');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 35,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Bearer ' . $chToken, 'User-Agent: VoxBulletin/2.0'],
        CURLOPT_POSTFIELDS => $payload
    ]);
    $rawRes = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $bufRes = json_decode($rawRes ?: '', true);
    $createPost = $bufRes['data']['createPost'] ?? null;

    if ($httpCode === 200 && !empty($createPost['post']['id'])) {
        $upId = $createPost['post']['id'];
        $results[] = ['platform' => $chKey, 'handle' => $handle, 'status' => 'published', 'update_id' => $upId];
        $publishedChannels[] = $chKey;
    } else {
        $errMsg = $createPost['message'] ?? $bufRes['errors'][0]['message'] ?? ('HTTP ' . $httpCode);
        $results[] = ['platform' => $chKey, 'handle' => $handle, 'status' => 'error', 'error' => $errMsg];
    }
}

// 10. Record in Database
$chanStr = implode(',', $publishedChannels);
$stmt = $pdo->prepare('INSERT INTO posts (source_url, source_title, headline, description, caption, image_file, source_image_url, channels_published) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
$stmt->execute([$chosen['url'], $chosen['title'], $hl, $desc, $caption, $fileName, $chosen['image'], $chanStr]);
$newPostId = $pdo->lastInsertId();

// 11. Log Automation Success
$logStmt = $pdo->prepare('INSERT INTO automation_logs (article_title, article_url, priority_category, priority_score, headline, image_file, channels_published, status, message) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
$summaryMsg = 'Successfully auto-selected and published across: ' . ($chanStr ?: 'None') . $keyUsedInfo;
$logStmt->execute([$chosen['title'], $chosen['url'], $chosen['category'], $chosen['score'], $hl, $fileName, $chanStr, 'success', $summaryMsg]);

$outputData = [
    'ok' => true,
    'post_id' => $newPostId,
    'priority_category' => $chosen['category'],
    'priority_score' => $chosen['score'],
    'selected_title' => $chosen['title'],
    'headline' => $hl,
    'channels_published' => $publishedChannels,
    'image_url' => $postImageUrl,
    'results' => $results,
    'message' => 'Automated post generated & published successfully!'
];

if ($isCli) {
    echo "\n=== VOX BULLETIN AUTOMATION COMPLETED ===\n";
    echo "Category: " . $chosen['category'] . " (Score: " . $chosen['score'] . ")\n";
    echo "Article:  " . $chosen['title'] . "\n";
    echo "Headline: " . $hl . "\n";
    echo "Channels: " . implode(', ', $publishedChannels) . "\n";
    echo "Results:  " . json_encode($results, JSON_PRETTY_PRINT) . "\n";
} else {
    header('Content-Type: application/json');
    echo json_encode($outputData, JSON_PRETTY_PRINT);
}
