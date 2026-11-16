<?php
// All endpoints require an authenticated session. Server-side fetching is domain restricted.
ini_set('display_errors','0');
session_start();
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');
$configFile=dirname(__DIR__).'/config.php';
if (!is_file($configFile)) { http_response_code(503); echo json_encode(['error'=>'Missing config.php. See README.']); exit; }
$c=require $configFile;
require_once dirname(__DIR__).'/mailer.php';
function out($data,$code=200){http_response_code($code);echo json_encode($data,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);exit;}
function db(){
 global $c;
 if(!empty($c['db_name']) && $c['db_name']!=='YOUR_HOSTINGER_DATABASE'){
  try{
   $pdo = new PDO('mysql:host='.$c['db_host'].';dbname='.$c['db_name'].';charset=utf8mb4',$c['db_user'],$c['db_pass'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
   $pdo->exec("CREATE TABLE IF NOT EXISTS channels (id INT AUTO_INCREMENT PRIMARY KEY, platform VARCHAR(50) NOT NULL UNIQUE, account_name VARCHAR(100) NOT NULL, account_handle VARCHAR(100) NOT NULL, permissions TEXT, access_token TEXT, is_connected TINYINT(1) DEFAULT 1, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP);");
   return $pdo;
  }catch(Throwable $e){}
 }
 $dbFile=dirname(__DIR__).'/storage/database.sqlite';
 $pdo=new PDO('sqlite:'.$dbFile,null,null,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
 $pdo->exec("CREATE TABLE IF NOT EXISTS posts (id INTEGER PRIMARY KEY AUTOINCREMENT, source_url TEXT NOT NULL, source_title TEXT NOT NULL, headline TEXT NOT NULL, description TEXT NOT NULL, caption TEXT NOT NULL, image_file TEXT DEFAULT NULL, source_image_url TEXT, channels_published TEXT DEFAULT '', created_at DATETIME DEFAULT CURRENT_TIMESTAMP);");
 $pdo->exec("CREATE TABLE IF NOT EXISTS channels (id INTEGER PRIMARY KEY AUTOINCREMENT, platform TEXT NOT NULL UNIQUE, account_name TEXT NOT NULL, account_handle TEXT NOT NULL, permissions TEXT, access_token TEXT, is_connected INTEGER DEFAULT 1, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP);");
 return $pdo;
}
function fetchUrl($url,$max=2000000){
 $u=parse_url($url);if(!$u||($u['scheme']??'')!=='https'||!in_array(strtolower($u['host']??''),['fandomwire.com','www.fandomwire.com'],true))throw new Exception('Only FandomWire HTTPS URLs are allowed');
 $ch=curl_init($url);curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_CONNECTTIMEOUT=>8,CURLOPT_TIMEOUT=>20,CURLOPT_USERAGENT=>'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',CURLOPT_MAXFILESIZE=>$max,CURLOPT_HTTPHEADER=>['Accept: text/html,application/xhtml+xml']]);
 $body=curl_exec($ch);$status=curl_getinfo($ch,CURLINFO_HTTP_CODE);$type=curl_getinfo($ch,CURLINFO_CONTENT_TYPE);curl_close($ch);
 if($body===false||$status!==200||strlen($body)>$max||stripos((string)$type,'text/html')===false)throw new Exception('Source page could not be retrieved (HTTP '.$status.').');return $body;
}
function dom($html){$d=new DOMDocument();libxml_use_internal_errors(true);$d->loadHTML('<?xml encoding="utf-8"?>'.$html);libxml_clear_errors();return $d;}
function meta($d,$property){$xp=new DOMXPath($d);$n=$xp->query('//meta[@property="'.$property.'"]/@content');return $n->length?trim($n->item(0)->nodeValue):'';}
function cleanFandomWire($text){
 if(!$text)return '';
 $text=html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
 $text=preg_replace('/\s*[-–—|]\s*Fandom\s*Wire.*$/i','',$text);
 $text=preg_replace('/\b(This post belongs to and first appeared on|The post .* appeared first on).*$/i','',$text);
 $text=preg_replace('/\b(according to|per|as reported by|via)\s+Fandom\s*Wire,?\s*/i','',$text);
 $text=preg_replace('/\bFandom\s*Wire\s+(reports|reported|reveals|revealed|claims|claimed|stated)\s+(that\s+)?/i','Reports indicate ',$text);
 $text=preg_replace('/#?Fandom\s*Wire(\.com)?\b/i','',$text);
 $text=preg_replace('/\s*\[…\]\s*$/','',$text);
 $text=preg_replace('/\s*\[\.\.\.\]\s*$/','',$text);
 $text=preg_replace('/\s+\[\.\.\.\]\s*/',' ',$text);
 $text=preg_replace('/\s+\[…\]\s*/',' ',$text);
 $text=preg_replace('/\s+/',' ',$text);
 return trim($text);
}
function getPublicPosterUrl($file, $bytes, $configPublicBaseUrl){
 $baseUrl = rtrim((string)($configPublicBaseUrl ?: ''), '/');
 if($baseUrl && !str_contains($baseUrl, 'localhost') && !str_contains($baseUrl, '127.0.0.1')){
  return $baseUrl . '/storage/posts/' . $file;
 }
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
 if($code === 200 && $res){
  $json = json_decode($res, true);
  if(!empty($json['image']['url'])){
   return $json['image']['url'];
  }
 }
 return ($baseUrl ?: 'http://127.0.0.1:8000') . '/storage/posts/' . $file;
}

function getGeminiKeysPool($cfg) {
 $keys = [];
 if (!empty($cfg['gemini_api_keys']) && is_array($cfg['gemini_api_keys'])) {
  foreach ($cfg['gemini_api_keys'] as $k) {
   $k = trim((string)$k);
   if ($k !== '' && !str_contains($k, 'PASTE_')) {
    $keys[] = $k;
   }
  }
 }
 if (empty($keys) && !empty($cfg['gemini_api_key'])) {
  $k = trim((string)$cfg['gemini_api_key']);
  if ($k !== '' && !str_contains($k, 'PASTE_')) {
   $keys[] = $k;
  }
 }
 if (empty($keys)) {
  $extCfg = dirname(dirname(__DIR__)) . '/facebook-post-extractor/config.json';
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

function testGeminiApiKey($key) {
	$key = trim((string)$key);
	if (!$key || str_contains($key, 'PASTE_')) {
		return ['ok' => false, 'error' => 'API Key is empty or placeholder.'];
	}

	$modelsToTry = ['gemini-flash-lite-latest', 'gemini-3.5-flash-lite', 'gemini-3-flash-preview', 'gemini-2.5-flash'];
	$lastErr = '';
	$lastCode = 0;
	$quotaHitOn = [];

	foreach ($modelsToTry as $model) {
		$url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . '?key=' . urlencode($key);

		$ch = curl_init($url);
		curl_setopt_array($ch, [
			CURLOPT_HTTPGET => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 5,
			CURLOPT_CONNECTTIMEOUT => 2,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false,
			CURLOPT_HTTPHEADER => ['User-Agent: VoxBulletin/2.0']
		]);
		$resRaw = curl_exec($ch);
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curlErr = curl_error($ch);
		curl_close($ch);

		if ($code === 200) {
			return ['ok' => true, 'model' => $model, 'message' => "Key is active and responsive (200 OK via $model)!"];
		}

		$res = json_decode($resRaw ?: '', true);
		$errMsg = $res['error']['message'] ?? ($curlErr ?: ('HTTP ' . $code));
		$isQuota = ($code === 429 || $code === 403 || stripos($errMsg, 'quota') !== false || stripos($errMsg, 'rate') !== false || ($res['error']['status'] ?? '') === 'RESOURCE_EXHAUSTED');

		if ($isQuota) {
			$quotaHitOn[] = $model;
			$lastErr = "Daily quota reached on $model (HTTP $code). Key is valid! Failover to backup keys is ready.";
			$lastCode = $code;
			continue;
		}

		if ($code === 0 || $code === 404 || stripos($errMsg, 'no longer available') !== false || stripos($errMsg, 'not found') !== false) {
			$lastErr = $errMsg;
			$lastCode = $code;
			continue;
		}

		return [
			'ok' => false,
			'error' => $errMsg,
			'is_quota' => false,
			'code' => $code
		];
	}

	return [
		'ok' => false,
		'error' => $lastErr ?: 'Validation failed across tested Gemini models.',
		'is_quota' => !empty($quotaHitOn),
		'code' => $lastCode
	];
}

function callGeminiWithFailover($prompt, $cfg, $options = []) {
	$keys = getGeminiKeysPool($cfg);
	if (empty($keys)) {
		return ['ok' => false, 'error' => 'No Gemini API keys configured. Please add one or more keys in Settings.'];
	}

	$isJson = !empty($options['json']);
	$preferredModel = $options['model'] ?? 'gemini-flash-lite-latest';
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

	$modelsToTry = array_values(array_unique([$preferredModel, 'gemini-flash-lite-latest', 'gemini-3.5-flash-lite', 'gemini-3-flash-preview', 'gemini-2.5-flash', 'gemini-3.5-flash']));
	$errors = [];

	foreach ($keys as $idx => $key) {
		$masked = (strlen($key) > 10) ? substr($key, 0, 6) . '...' . substr($key, -4) : 'Key#' . ($idx + 1);

		foreach ($modelsToTry as $model) {
			$url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent?key=' . urlencode($key);
			$ch = curl_init($url);
			curl_setopt_array($ch, [
				CURLOPT_POST => true,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_TIMEOUT => 9,
				CURLOPT_CONNECTTIMEOUT => 3,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'User-Agent: VoxBulletin/2.0'],
				CURLOPT_POSTFIELDS => $payload
			]);
			$raw = curl_exec($ch);
			$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);

			$res = json_decode($raw ?: '', true);

			if ($code === 200 && !empty($res['candidates'][0]['content']['parts'][0]['text'])) {
				return [
					'ok' => true,
					'text' => $res['candidates'][0]['content']['parts'][0]['text'],
					'key_index' => $idx,
					'key_used' => $masked,
					'model_used' => $model,
					'failovers' => $idx,
					'data' => $res
				];
			}

			$errMsg = $res['error']['message'] ?? ('HTTP ' . $code);
			$isQuota = ($code === 429 || $code === 403 || stripos($errMsg, 'quota') !== false || stripos($errMsg, 'rate') !== false || ($res['error']['status'] ?? '') === 'RESOURCE_EXHAUSTED');

			if ($isQuota) {
				$errors[] = "Key #" . ($idx + 1) . " ($masked) on $model: Quota exceeded (HTTP $code)";
				continue;
			}

			if ($code === 0 || $code === 404 || stripos($errMsg, 'no longer available') !== false || stripos($errMsg, 'not found') !== false) {
				continue;
			}

			$errors[] = "Key #" . ($idx + 1) . " ($masked) on $model: $errMsg";
		}
	}

	return [
		'ok' => false,
		'error' => 'All Gemini API keys in pool (' . count($keys) . ') failed or exceeded daily quota.',
		'details' => implode(' | ', $errors),
		'keys_count' => count($keys)
	];
}
function csrf(){if(!hash_equals($_SESSION['csrf']??'',$_SERVER['HTTP_X_CSRF_TOKEN']??''))out(['error'=>'Invalid security token'],403);}
$action=$_GET['action']??'';
if($action==='login'){
 if($_SERVER['REQUEST_METHOD']!=='POST')out(['error'=>'Method not allowed'],405);
 $input=json_decode(file_get_contents('php://input'),true)?:[];
 if(!hash_equals((string)$c['admin_password'],(string)($input['password']??''))) {sleep(1);out(['error'=>'Incorrect password'],401);}
 session_regenerate_id(true);$_SESSION['logged_in']=true;$_SESSION['csrf']=bin2hex(random_bytes(24));out(['ok'=>true,'csrf'=>$_SESSION['csrf']]);
}
if($action==='session')out(['logged_in'=>!empty($_SESSION['logged_in']),'csrf'=>$_SESSION['csrf']??'']);
if(empty($_SESSION['logged_in']))out(['error'=>'Please sign in'],401);
if($action==='logout'){csrf();session_destroy();out(['ok'=>true]);}
try {
 if($action==='cron'){
  require dirname(__DIR__) . '/cron.php';
  exit;
 }
 if($action==='cron_logs'){
  $logs = db()->query('SELECT id, article_title, article_url, priority_category, priority_score, headline, image_file, channels_published, status, message, created_at FROM automation_logs ORDER BY id DESC LIMIT 20')->fetchAll();
  out(['logs' => $logs]);
 }
 if($action==='news'){
  $items=[];
  $ch=curl_init('https://fandomwire.com/feed/');
  curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>15,CURLOPT_USERAGENT=>'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36']);
  $xmlStr=curl_exec($ch);curl_close($ch);
  if($xmlStr){
   $xml=@simplexml_load_string($xmlStr);
   if($xml && isset($xml->channel->item)){
    $todayUtc = gmdate('Y-m-d');
    $todayLoc = date('Y-m-d');
    foreach($xml->channel->item as $it){
     $title=cleanFandomWire(trim((string)$it->title));
     $url=trim((string)$it->link);
     $pubDateStr=trim((string)$it->pubDate);
     $pubTs=strtotime($pubDateStr) ?: time();

     // Strictly filter for today's date (or within 24 hours)
     $itemUtc = gmdate('Y-m-d', $pubTs);
     $itemLoc = date('Y-m-d', $pubTs);
     $isToday = ($itemUtc === $todayUtc || $itemLoc === $todayLoc || (time() - $pubTs) <= 86400);
     if(!$isToday) continue;

     // Extract image URL
     $image='';
     $namespaces=$it->getNameSpaces(true);
     if(isset($namespaces['content'])){
      $contentChildren=$it->children($namespaces['content']);
      if(isset($contentChildren->encoded) && preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i',(string)$contentChildren->encoded,$im)){
       $image=$im[1];
      }
     }
     if(!$image && isset($namespaces['media'])){
      $mediaChildren=$it->children($namespaces['media']);
      if(isset($mediaChildren->content)){
       $attrs=$mediaChildren->content->attributes();
       if(isset($attrs['url']))$image=(string)$attrs['url'];
      }
     }
     if(!$image && preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i',(string)$it->description,$im)){
      $image=$im[1];
     }

     // Clean description (never include source website name)
     $desc=cleanFandomWire(trim(strip_tags((string)$it->description)));
     $desc=preg_replace('/\s*\[\.\.\.\]\s*$/','...',$desc);
     $desc=preg_replace('/The post .* appeared first on FandomWire\.\s*$/i','',$desc);
     $desc=trim($desc);

     // High-resolution main image for canvas vs 300x300 compressed thumbnail for story list
     $mainImage = $image ? (strtok($image, '?') ?: $image) : '';
     $thumbImage = $mainImage ? ($mainImage . '?width=300&height=300&fit=crop&format=auto&quality=60') : '';

     $timeStr=date('g:i A', $pubTs);
     if(strlen($title)>10 && !isset($items[$url])){
      $items[$url]=[
       'title'=>$title,
       'url'=>$url,
       'description'=>$desc,
       'image'=>$mainImage,
       'thumbnail'=>$thumbImage,
       'time'=>$timeStr,
       'date_badge'=>'TODAY '.$timeStr,
       'pubDate'=>$pubDateStr
      ];
     }
     if(count($items)>=40)break;
    }
   }
  }
  out(['items'=>array_values($items),'count'=>count($items),'date'=>date('M d, Y')]);
 }
 if($action==='article'){
  $url=$_GET['url']??'';
  $title='';$desc='';$image='';$date='';
  $ch=curl_init('https://fandomwire.com/feed/');
  curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_USERAGENT=>'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36']);
  $xmlStr=curl_exec($ch);curl_close($ch);
  if($xmlStr){
   $xml=@simplexml_load_string($xmlStr);
   if($xml && isset($xml->channel->item)){
    foreach($xml->channel->item as $it){
     if(trim((string)$it->link) === $url){
      $title=cleanFandomWire(trim((string)$it->title));
      $desc=cleanFandomWire(trim(strip_tags((string)$it->description)));
      $date=trim((string)$it->pubDate);
      $namespaces=$it->getNameSpaces(true);
      if(isset($namespaces['content'])){
       $contentChildren=$it->children($namespaces['content']);
       if(isset($contentChildren->encoded) && preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i',(string)$contentChildren->encoded,$im)){
        $image=$im[1];
       }
      }
      break;
     }
    }
   }
  }
  if(!$title){
   try {
    $d=dom(fetchUrl($url));$xp=new DOMXPath($d);
    $n=$xp->query('//meta[@name="description"]/@content');if($n->length)$desc=cleanFandomWire(trim($n->item(0)->nodeValue));
    $image=meta($d,'og:image');$title=cleanFandomWire(meta($d,'og:title'));$date=meta($d,'article:published_time');
   } catch(Throwable $e){}
  }
  $mainImage = $image ? (strtok($image, '?') ?: $image) : '';
  $thumbImage = $mainImage ? ($mainImage . '?width=300&height=300&fit=crop&format=auto&quality=60') : '';
  out(['title'=>$title,'description'=>$desc,'image'=>$mainImage,'thumbnail'=>$thumbImage,'date'=>$date,'url'=>$url]);
 }
 if($action==='image'){
  $url=$_GET['url']??'';$p=parse_url($url);$host=strtolower($p['host']??'');
  $validHost = ($host==='fandomwire.com' || str_ends_with($host,'.fandomwire.com') || $host==='wp.com' || str_ends_with($host,'.wp.com'));
  if(($p['scheme']??'')!=='https' || !$validHost) out(['error'=>'Image host not supported; upload the image manually'],422);
  $ch=curl_init($url);
  curl_setopt_array($ch,[
   CURLOPT_RETURNTRANSFER=>true,
   CURLOPT_FOLLOWLOCATION=>true,
   CURLOPT_TIMEOUT=>25,
   CURLOPT_USERAGENT=>'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
   CURLOPT_SSL_VERIFYPEER=>false,
   CURLOPT_MAXFILESIZE=>10000000
  ]);
  $bin=curl_exec($ch);$code=curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);
  if($code!==200||!$bin||strlen($bin)>10000000||!($info=@getimagesizefromstring($bin)))out(['error'=>'Could not fetch source image'],422);
  if(!in_array($info[2],[IMAGETYPE_JPEG,IMAGETYPE_PNG,IMAGETYPE_WEBP],true))out(['error'=>'Unsupported image type'],422);
  header('Access-Control-Allow-Origin: *');
  header('Cache-Control: public, max-age=86400');
  header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 86400));
  header('Content-Type: '.$info['mime']);echo $bin;exit;
 }
 if($action==='test_gemini_key'){
  csrf();
  $in = json_decode(file_get_contents('php://input'), true) ?: [];
  $key = trim((string)($in['key'] ?? ''));
  if(!$key) out(['error' => 'No key provided to test'], 400);
  $res = testGeminiApiKey($key);
  if($res['ok']){
   out(['ok' => true, 'message' => $res['message']]);
  } else {
   out(['error' => $res['error'], 'is_quota' => !empty($res['is_quota'])], 400);
  }
 }
 if($action==='get_settings'){
  csrf();
  $keys = getGeminiKeysPool($c);
  $maskedList = [];
  foreach($keys as $k){
   $len = strlen($k);
   $maskedList[] = ($len > 10) ? substr($k, 0, 6) . '...' . substr($k, -4) : '********';
  }
  $firstKey = $keys[0] ?? '';
  $masked = $maskedList[0] ?? '';

  $bufToken = $c['buffer_access_token'] ?? '';
  $bufMasked = '';
  if($bufToken){
   $len = strlen($bufToken);
   $bufMasked = $len > 10 ? substr($bufToken, 0, 6) . '...' . substr($bufToken, -4) : '********';
  }
  out([
   'has_gemini_key' => !empty($keys),
   'gemini_keys' => $keys,
   'gemini_masked_list' => $maskedList,
   'gemini_count' => count($keys),
   'gemini_key' => $firstKey,
   'gemini_masked' => $masked,
   'buffer_access_token' => $bufToken,
   'buffer_masked' => $bufMasked,
   'has_buffer_token' => !empty($bufToken),
   'public_base_url' => $c['public_base_url'] ?? '',
   'email_notifications_enabled' => !empty($c['email_notifications_enabled']),
   'notification_email' => $c['notification_email'] ?? 'bulletinvox@gmail.com',
   'smtp_host' => $c['smtp_host'] ?? 'smtp.gmail.com',
   'smtp_port' => (int)($c['smtp_port'] ?? 465),
   'smtp_user' => $c['smtp_user'] ?? 'bulletinvox@gmail.com',
   'has_smtp_pass' => !empty($c['smtp_pass']),
   'meta_access_token' => $c['meta_access_token'] ?? '',
   'meta_instagram_id' => $c['meta_instagram_id'] ?? '',
   'meta_facebook_page_id' => $c['meta_facebook_page_id'] ?? '',
   'post_headline_font_size' => (int)($c['post_headline_font_size'] ?? 0),
   'post_description_font_size' => (int)($c['post_description_font_size'] ?? 0),
   'post_logo_alignment' => $c['post_logo_alignment'] ?? 'top-right',
   'post_footer_branding' => isset($c['post_footer_branding']) ? !empty($c['post_footer_branding']) : true,
   'post_headline_case' => $c['post_headline_case'] ?? 'uppercase',
   'post_color_palette' => $c['post_color_palette'] ?? 'gold-cyan'
  ]);
 }
 if($action==='save_settings'){
  csrf();
  $in = json_decode(file_get_contents('php://input'), true) ?: [];
  
  $submittedKeys = [];
  if (isset($in['gemini_api_keys']) && is_array($in['gemini_api_keys'])) {
   foreach ($in['gemini_api_keys'] as $k) {
    $k = trim((string)$k);
    if ($k !== '' && !str_contains($k, 'PASTE_')) $submittedKeys[] = $k;
   }
  } elseif (!empty($in['gemini_api_key'])) {
   $submittedKeys = [trim((string)$in['gemini_api_key'])];
  }

  $finalKeys = !empty($submittedKeys) ? array_values(array_unique($submittedKeys)) : getGeminiKeysPool($c);
  $newPass = trim((string)($in['admin_password'] ?? ''));
  $bufToken = trim((string)($in['buffer_access_token'] ?? ''));
  $pubUrl = trim((string)($in['public_base_url'] ?? ''));
  $metaToken = trim((string)($in['meta_access_token'] ?? ''));
  $metaIg = trim((string)($in['meta_instagram_id'] ?? ''));
  $metaFb = trim((string)($in['meta_facebook_page_id'] ?? ''));
  $emailEnabled = isset($in['email_notifications_enabled']) ? !empty($in['email_notifications_enabled']) : (!empty($c['email_notifications_enabled']));
  $notifyEmail = isset($in['notification_email']) ? trim((string)$in['notification_email']) : ($c['notification_email'] ?? 'bulletinvox@gmail.com');
  $smtpHost = isset($in['smtp_host']) ? trim((string)$in['smtp_host']) : ($c['smtp_host'] ?? 'smtp.gmail.com');
  $smtpPort = isset($in['smtp_port']) ? (int)$in['smtp_port'] : (int)($c['smtp_port'] ?? 465);
  $smtpUser = isset($in['smtp_user']) ? trim((string)$in['smtp_user']) : ($c['smtp_user'] ?? 'bulletinvox@gmail.com');
  $smtpPass = (isset($in['smtp_pass']) && trim((string)$in['smtp_pass']) !== '') ? str_replace(' ', '', trim((string)$in['smtp_pass'])) : ($c['smtp_pass'] ?? 'pvtvemnrwyokncxb');
  $test = !empty($in['test']);

  if($test && !empty($finalKeys)){
   $testRes = testGeminiApiKey($finalKeys[0]);
   if(!$testRes['ok'] && empty($testRes['is_quota'])){
    out(['error' => 'Gemini test failed for primary key: ' . $testRes['error']], 400);
   }
  }

  $curPass = $newPass ?: ($c['admin_password'] ?? 'admin123');
  $finalBuf = ($bufToken !== '') ? $bufToken : ($c['buffer_access_token'] ?? '');
  $finalPub = ($pubUrl !== '') ? $pubUrl : ($c['public_base_url'] ?? '');
  $finalMetaTok = ($metaToken !== '') ? $metaToken : ($c['meta_access_token'] ?? '');
  $finalMetaIg = ($metaIg !== '') ? $metaIg : ($c['meta_instagram_id'] ?? '');
  $finalMetaFb = ($metaFb !== '') ? $metaFb : ($c['meta_facebook_page_id'] ?? '');

  $postHlFont = isset($in['post_headline_font_size']) ? (int)$in['post_headline_font_size'] : (int)($c['post_headline_font_size'] ?? 0);
  $postDescFont = isset($in['post_description_font_size']) ? (int)$in['post_description_font_size'] : (int)($c['post_description_font_size'] ?? 0);
  $postLogoAlign = isset($in['post_logo_alignment']) ? trim((string)$in['post_logo_alignment']) : ($c['post_logo_alignment'] ?? 'top-right');
  $postFooterBranding = isset($in['post_footer_branding']) ? !empty($in['post_footer_branding']) : (isset($c['post_footer_branding']) ? !empty($c['post_footer_branding']) : true);
  $postHlCase = isset($in['post_headline_case']) ? trim((string)$in['post_headline_case']) : ($c['post_headline_case'] ?? 'uppercase');
  $postPalette = isset($in['post_color_palette']) ? trim((string)$in['post_color_palette']) : ($c['post_color_palette'] ?? 'gold-cyan');

  $configFile = dirname(__DIR__).'/config.php';
  $content = "<?php\nreturn [\n"
   . "  'admin_password' => " . var_export($curPass, true) . ",\n"
   . "  'gemini_api_keys' => " . var_export($finalKeys, true) . ",\n"
   . "  'gemini_api_key' => " . var_export($finalKeys[0] ?? '', true) . ",\n"
   . "  'buffer_access_token' => " . var_export($finalBuf, true) . ",\n"
   . "  'buffer_client_id' => " . var_export($c['buffer_client_id'] ?? '', true) . ",\n"
   . "  'buffer_client_secret' => " . var_export($c['buffer_client_secret'] ?? '', true) . ",\n"
   . "  'buffer_org_id' => " . var_export($c['buffer_org_id'] ?? '', true) . ",\n"
   . "  'public_base_url' => " . var_export($finalPub, true) . ",\n"
   . "  'email_notifications_enabled' => " . var_export($emailEnabled, true) . ",\n"
   . "  'notification_email' => " . var_export($notifyEmail, true) . ",\n"
   . "  'smtp_host' => " . var_export($smtpHost, true) . ",\n"
   . "  'smtp_port' => " . var_export($smtpPort, true) . ",\n"
   . "  'smtp_user' => " . var_export($smtpUser, true) . ",\n"
   . "  'smtp_pass' => " . var_export($smtpPass, true) . ",\n"
   . "  'smtp_encryption' => 'ssl',\n"
   . "  'meta_access_token' => " . var_export($finalMetaTok, true) . ",\n"
   . "  'meta_instagram_id' => " . var_export($finalMetaIg, true) . ",\n"
   . "  'meta_facebook_page_id' => " . var_export($finalMetaFb, true) . ",\n"
   . "  'post_headline_font_size' => " . var_export($postHlFont, true) . ",\n"
   . "  'post_description_font_size' => " . var_export($postDescFont, true) . ",\n"
   . "  'post_logo_alignment' => " . var_export($postLogoAlign, true) . ",\n"
   . "  'post_footer_branding' => " . var_export($postFooterBranding, true) . ",\n"
   . "  'post_headline_case' => " . var_export($postHlCase, true) . ",\n"
   . "  'post_color_palette' => " . var_export($postPalette, true) . ",\n"
   . "  'db_host' => " . var_export($c['db_host'] ?? 'localhost', true) . ",\n"
   . "  'db_name' => " . var_export($c['db_name'] ?? '', true) . ",\n"
   . "  'db_user' => " . var_export($c['db_user'] ?? '', true) . ",\n"
   . "  'db_pass' => " . var_export($c['db_pass'] ?? '', true) . ",\n"
   . "];\n";
  file_put_contents($configFile, $content, LOCK_EX);

  $c['admin_password'] = $curPass;
  $c['gemini_api_keys'] = $finalKeys;
  $c['gemini_api_key'] = $finalKeys[0] ?? '';
  $c['buffer_access_token'] = $finalBuf;
  $c['public_base_url'] = $finalPub;
  $c['email_notifications_enabled'] = $emailEnabled;
  $c['notification_email'] = $notifyEmail;
  $c['smtp_host'] = $smtpHost;
  $c['smtp_port'] = $smtpPort;
  $c['smtp_user'] = $smtpUser;
  $c['smtp_pass'] = $smtpPass;
  $c['meta_access_token'] = $finalMetaTok;
  $c['meta_instagram_id'] = $finalMetaIg;
  $c['meta_facebook_page_id'] = $finalMetaFb;
  $c['post_headline_font_size'] = $postHlFont;
  $c['post_description_font_size'] = $postDescFont;
  $c['post_logo_alignment'] = $postLogoAlign;
  $c['post_footer_branding'] = $postFooterBranding;
  $c['post_headline_case'] = $postHlCase;
  $c['post_color_palette'] = $postPalette;

  out([
   'ok' => true,
   'message' => 'Settings saved successfully! Loaded ' . count($finalKeys) . ' Gemini key(s).',
   'keys_count' => count($finalKeys),
   'gemini_keys' => $finalKeys
  ]);
 }
 if($action==='test_email'){
  csrf();
  $in = json_decode(file_get_contents('php://input'), true) ?: [];
  $testConfig = $c;
  if(!empty($in['notification_email'])) $testConfig['notification_email'] = trim((string)$in['notification_email']);
  if(!empty($in['smtp_host'])) $testConfig['smtp_host'] = trim((string)$in['smtp_host']);
  if(!empty($in['smtp_port'])) $testConfig['smtp_port'] = (int)$in['smtp_port'];
  if(!empty($in['smtp_user'])) $testConfig['smtp_user'] = trim((string)$in['smtp_user']);
  if(!empty($in['smtp_pass'])) $testConfig['smtp_pass'] = str_replace(' ', '', trim((string)$in['smtp_pass']));
  try {
   $res = sendTestNotificationEmail($testConfig);
   out(['ok' => true, 'message' => $res['message']]);
  } catch(Throwable $e) {
   out(['error' => 'SMTP Test Failed: ' . $e->getMessage()], 400);
  }
 }
 if($action==='draft'){
  csrf();$in=json_decode(file_get_contents('php://input'),true)?:[];
  $title=trim((string)($in['title']??''));$desc=trim((string)($in['description']??''));$url=(string)($in['url']??'');
  if(!$title||!$desc||!$url||strlen($title)>500||strlen($desc)>2000)out(['error'=>'Choose an article first'],422);
  $u=parse_url($url);if(!in_array(strtolower($u['host']??''),['fandomwire.com','www.fandomwire.com']))out(['error'=>'Invalid source URL'],422);

  $prompt = "You are an entertainment news graphic editor for VOX BULLETIN. Return a JSON object with keys: headline, description, caption.\n\n"
   . "CRITICAL BRANDING RULES:\n"
   . "- The news brand is VOX BULLETIN.\n"
   . "- STRICTLY FORBIDDEN: NEVER mention 'FandomWire', 'Fandom Wire', or 'fandomwire.com' anywhere in headline, description, or caption. If the source text mentions FandomWire, omit the name completely or use generic phrasing such as 'reports confirm', 'sources say', or direct reporting.\n\n"
   . "STRICT FORMAT REQUIREMENTS:\n"
   . "1. 'headline': 8-12 punchy uppercase words (55-80 characters). All-caps, concise, high impact.\n"
   . "2. 'description': Exactly 1 to 2 COMPLETE, well-formed sentences. STRICT LENGTH LIMIT: Exactly 25 to 35 words (strictly between 170 and 220 characters total). MUST fit neatly into 4 to 5 lines on Instagram portrait cards. Every sentence MUST be 100% grammatically complete and end with a full stop period (.) — NEVER truncate, never leave incomplete thoughts, and never end with an ellipsis (...).\n"
   . "3. 'caption': 300-600 characters ready to publish on Instagram with 4-6 relevant entertainment hashtags. MANDATORY BRANDING: The VERY FIRST hashtag MUST ALWAYS be #voxbulletin (e.g. #voxbulletin #EntertainmentNews #MovieNews). Do NOT include #FandomWire.\n\n"
   . "Source Title: " . $title . "\n"
   . "Source Excerpt: " . $desc . "\n"
   . "URL: " . $url;

  $gCall = callGeminiWithFailover($prompt, $c, ['json' => true]);
  if(!$gCall['ok']){
   out(['error' => 'Gemini draft generation failed', 'details' => $gCall['error'] . (!empty($gCall['details']) ? ' — ' . $gCall['details'] : '')], 502);
  }

  $txt = $gCall['text'] ?? '';
  $draft = json_decode($txt, true);
  if(!is_array($draft) && preg_match('/\{[\s\S]*\}/', $txt, $m)){
   $draft = json_decode($m[0], true);
  }
  if(!is_array($draft)||empty($draft['headline'])||empty($draft['description'])||empty($draft['caption'])){
   out(['error'=>'Gemini returned an unexpected response format'], 502);
  }

  $hlOut = cleanFandomWire(trim((string)$draft['headline']));
  $descOut = cleanFandomWire(trim((string)$draft['description']));
  $capOut = cleanFandomWire(trim((string)$draft['caption']));
  $descOut = preg_replace('/(\.\.\.|…)\s*$/', '.', $descOut);
  if(!str_ends_with($descOut, '.') && !str_ends_with($descOut, '!') && !str_ends_with($descOut, '?')){
   $descOut .= '.';
  }

  // Ensure #voxbulletin is ALWAYS the very first hashtag in the caption
  $cleanCap = preg_replace('/#voxbulletin\b/i', '', $capOut);
  $cleanCap = trim(preg_replace('/[ \t]+/', ' ', $cleanCap));
  if(preg_match('/#([a-zA-Z0-9_]+)/', $cleanCap, $matches, PREG_OFFSET_CAPTURE)){
    $pos = $matches[0][1];
    $capOut = substr($cleanCap, 0, $pos) . '#voxbulletin ' . substr($cleanCap, $pos);
  } else {
    $capOut = $cleanCap . "\n\n#voxbulletin";
  }
  $capOut = trim(preg_replace('/[ \t]{2,}/', ' ', $capOut));

  out([
   'draft' => [
    'headline' => mb_substr($hlOut, 0, 150),
    'description' => $descOut,
    'caption' => mb_substr($capOut, 0, 1500)
   ],
   'key_used' => $gCall['key_used'] ?? '',
   'failovers' => $gCall['failovers'] ?? 0
  ]);
 }
 if($action==='channels'){
  csrf();
  $rows=db()->query('SELECT platform, account_name, account_handle, avatar, remote_id, service_type, permissions, is_connected, updated_at FROM channels WHERE is_connected = 1')->fetchAll();
  $connected=[];
  foreach($rows as $r){
   $r['permissions']=json_decode($r['permissions']?:'[]',true);
   $connected[$r['platform']]=$r;
  }
  out([
   'connected'=>$connected,
   'count'=>count($connected),
   'has_buffer_token'=>!empty($c['buffer_access_token']),
   'has_meta_token'=>!empty($c['meta_access_token'])
  ]);
 }
 if($action==='buffer_sync'){
  csrf();
  $in=json_decode(file_get_contents('php://input'),true)?:[];
  $token=trim((string)($in['buffer_token']??''));
  if(!$token){
   $token=$c['buffer_access_token']??'';
  }
  if(!$token) out(['error'=>'Please provide a valid Buffer API Key.'],422);

  // 1. Query Buffer GraphQL for account organizations
  $gqlQuery1 = json_encode(['query' => 'query { account { id organizations { id name } } }']);
  $ch = curl_init('https://api.buffer.com');
  curl_setopt_array($ch, [
   CURLOPT_POST => true,
   CURLOPT_RETURNTRANSFER => true,
   CURLOPT_TIMEOUT => 25,
   CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Bearer '.$token, 'User-Agent: VoxBulletin/2.0'],
   CURLOPT_POSTFIELDS => $gqlQuery1
  ]);
  $raw1 = curl_exec($ch);
  curl_close($ch);

  $res1 = json_decode($raw1 ?: '', true);
  $orgs = $res1['data']['account']['organizations'] ?? [];
  if(empty($orgs)){
   $errMsg = $res1['errors'][0]['message'] ?? 'Could not authenticate with Buffer API. Check your API key.';
   out(['error' => 'Buffer authentication error: '.$errMsg], 400);
  }
  $orgId = $orgs[0]['id'];

  // 2. Query channels for the organization
  $gqlQuery2 = json_encode([
   'query' => 'query GetChannels($orgId: OrganizationId!) { channels(input: { organizationId: $orgId }) { id name displayName service avatar } }',
   'variables' => ['orgId' => $orgId]
  ]);
  $ch2 = curl_init('https://api.buffer.com');
  curl_setopt_array($ch2, [
   CURLOPT_POST => true,
   CURLOPT_RETURNTRANSFER => true,
   CURLOPT_TIMEOUT => 25,
   CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Bearer '.$token, 'User-Agent: VoxBulletin/2.0'],
   CURLOPT_POSTFIELDS => $gqlQuery2
  ]);
  $raw2 = curl_exec($ch2);
  curl_close($ch2);

  $res2 = json_decode($raw2 ?: '', true);
  $channels = $res2['data']['channels'] ?? [];
  if(empty($channels)){
   out(['error' => 'No connected channels found in your Buffer account. Please connect Instagram, Facebook, or X in Buffer first.'], 404);
  }

  // Update token & org_id in config.php
  $c['buffer_access_token'] = $token;
  $c['buffer_org_id'] = $orgId;
  $configFile = dirname(__DIR__).'/config.php';
  $content = "<?php
return [
"
   . "  'admin_password' => " . var_export($c['admin_password'] ?? 'admin123', true) . ",
"
   . "  'gemini_api_key' => " . var_export($c['gemini_api_key'] ?? '', true) . ",
"
   . "  'buffer_access_token' => " . var_export($token, true) . ",
"
   . "  'buffer_client_id' => " . var_export($c['buffer_client_id'] ?? '', true) . ",
"
   . "  'buffer_client_secret' => " . var_export($c['buffer_client_secret'] ?? '', true) . ",
"
   . "  'buffer_org_id' => " . var_export($orgId, true) . ",
"
   . "  'public_base_url' => " . var_export($c['public_base_url'] ?? '', true) . ",
"
   . "  'meta_access_token' => " . var_export($c['meta_access_token'] ?? '', true) . ",
"
   . "  'meta_instagram_id' => " . var_export($c['meta_instagram_id'] ?? '', true) . ",
"
   . "  'meta_facebook_page_id' => " . var_export($c['meta_facebook_page_id'] ?? '', true) . ",
"
   . "  'db_host' => " . var_export($c['db_host'] ?? 'localhost', true) . ",
"
   . "  'db_name' => " . var_export($c['db_name'] ?? '', true) . ",
"
   . "  'db_user' => " . var_export($c['db_user'] ?? '', true) . ",
"
   . "  'db_pass' => " . var_export($c['db_pass'] ?? '', true) . ",
"
   . "];
";
  file_put_contents($configFile, $content, LOCK_EX);

  $pdo = db();
  $synced = [];
  $perms = json_encode(['publish_content', 'manage_media', 'insights']);

  foreach($channels as $p){
   $svc = strtolower((string)($p['service'] ?? ''));
   if($svc === 'twitter') $svc = 'x';
   $remoteId = (string)($p['id'] ?? '');
   $accName = (string)($p['displayName'] ?? $p['name'] ?? 'Channel');
   $rawHandle = (string)($p['name'] ?? $p['displayName'] ?? 'channel');
   $accHandle = $rawHandle;
   if(!str_starts_with($accHandle, '@') && $svc !== 'facebook' && $svc !== 'pinterest'){
    $accHandle = '@' . $accHandle;
   }
   $avatar = (string)($p['avatar'] ?? '');
   $svcType = $svc;

   $chk = $pdo->prepare('SELECT id FROM channels WHERE remote_id=? OR platform=?');
   $chk->execute([$remoteId, $svc]);
   $existing = $chk->fetch();

   if($existing){
    $u = $pdo->prepare('UPDATE channels SET platform=?, account_name=?, account_handle=?, avatar=?, remote_id=?, service_type=?, permissions=?, access_token=?, is_connected=1, updated_at=CURRENT_TIMESTAMP WHERE id=?');
    $u->execute([$svc, $accName, $accHandle, $avatar, $remoteId, $svcType, $perms, $token, $existing['id']]);
   } else {
    $i = $pdo->prepare('INSERT INTO channels (platform, account_name, account_handle, avatar, remote_id, service_type, permissions, access_token, is_connected, updated_at) VALUES (?,?,?,?,?,?,?,?,1,CURRENT_TIMESTAMP)');
    $i->execute([$svc, $accName, $accHandle, $avatar, $remoteId, $svcType, $perms, $token]);
   }

   $synced[] = [
    'platform' => $svc,
    'account_name' => $accName,
    'account_handle' => $accHandle,
    'avatar' => $avatar,
    'remote_id' => $remoteId
   ];
  }

  out([
   'ok' => true,
   'message' => 'Successfully synced '.count($synced).' real social channel(s) from Buffer!',
   'synced' => $synced,
   'count' => count($synced)
  ]);
 }
 if($action==='meta_verify'){
  csrf();
  $in=json_decode(file_get_contents('php://input'),true)?:[];
  $token=trim((string)($in['access_token']??$c['meta_access_token']??''));
  $igId=trim((string)($in['instagram_id']??$c['meta_instagram_id']??''));
  if(!$token||!$igId)out(['error'=>'Both Meta Page Access Token and Instagram Account ID are required.'],422);

  $ch=curl_init('https://graph.facebook.com/v21.0/'.urlencode($igId).'?fields=id,username,name,profile_picture_url&access_token='.urlencode($token));
  curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>20]);
  $raw=curl_exec($ch);$code=curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);
  $res=json_decode($raw?:'',true);

  if($code!==200||empty($res['id'])){
   $msg=$res['error']['message']??('Meta Graph API returned HTTP '.$code);
   out(['error'=>'Meta verification failed: '.$msg],400);
  }

  $pdo=db();
  $accHandle='@'.($res['username']??'voxbulletin');
  $accName=$res['name']??'Instagram Business';
  $avatar=$res['profile_picture_url']??'';
  $perms=json_encode(['instagram_content_publish','instagram_basic']);

  $chk=$pdo->prepare('SELECT id FROM channels WHERE platform="instagram"');
  $chk->execute();
  $ex=$chk->fetch();
  if($ex){
   $u=$pdo->prepare('UPDATE channels SET account_name=?, account_handle=?, avatar=?, remote_id=?, service_type="meta_direct", permissions=?, access_token=?, is_connected=1, updated_at=CURRENT_TIMESTAMP WHERE id=?');
   $u->execute([$accName,$accHandle,$avatar,$igId,$perms,$token,$ex['id']]);
  } else {
   $i=$pdo->prepare('INSERT INTO channels (platform, account_name, account_handle, avatar, remote_id, service_type, permissions, access_token, is_connected, updated_at) VALUES ("instagram",?,?,?,?,?,?,"meta_direct",1,CURRENT_TIMESTAMP)');
   $i->execute([$accName,$accHandle,$avatar,$igId,$perms,$token]);
  }

  out(['ok'=>true,'message'=>'Verified and connected real Instagram account: '.$accHandle,'account'=>$res]);
 }
 if($action==='connect_channel'){
  csrf();
  $in=json_decode(file_get_contents('php://input'),true)?:[];
  $platform=trim((string)($in['platform']??''));
  $accName=trim((string)($in['account_name']??'Vox Bulletin'));
  $accHandle=trim((string)($in['account_handle']??'@voxbulletin'));
  $perms=json_encode($in['permissions']??['media_access','publish_content','manage_insights']);
  $token=trim((string)($in['access_token']??''));
  $remoteId=trim((string)($in['remote_id']??''));
  if(!$platform)out(['error'=>'Platform is required'],422);

  $pdo=db();
  $chk=$pdo->prepare('SELECT id FROM channels WHERE platform=?');
  $chk->execute([$platform]);
  if($chk->fetch()){
   $u=$pdo->prepare('UPDATE channels SET account_name=?, account_handle=?, permissions=?, access_token=?, remote_id=?, is_connected=1, updated_at=CURRENT_TIMESTAMP WHERE platform=?');
   $u->execute([$accName,$accHandle,$perms,$token,$remoteId,$platform]);
  } else {
   $i=$pdo->prepare('INSERT INTO channels (platform, account_name, account_handle, permissions, access_token, remote_id, is_connected, updated_at) VALUES (?,?,?,?,?,?,1,CURRENT_TIMESTAMP)');
   $i->execute([$platform,$accName,$accHandle,$perms,$token,$remoteId]);
  }
  out(['ok'=>true,'message'=>'Channel connected successfully!','platform'=>$platform,'handle'=>$accHandle]);
 }
 if($action==='disconnect_channel'){
  csrf();
  $in=json_decode(file_get_contents('php://input'),true)?:[];
  $platform=trim((string)($in['platform']??''));
  if(!$platform)out(['error'=>'Platform required'],422);
  $u=db()->prepare('UPDATE channels SET is_connected=0, updated_at=CURRENT_TIMESTAMP WHERE platform=?');
  $u->execute([$platform]);
  out(['ok'=>true,'message'=>'Channel disconnected']);
 }
 if($action==='publish'){
  csrf();
  $in=json_decode(file_get_contents('php://input'),true)?:[];
  $selectedChannels=(array)($in['channels']??[]);
  if(empty($selectedChannels))out(['error'=>'Select at least one connected channel to publish.'],422);

  $data=$in['image']??'';
  if(!preg_match('#^data:image/png;base64,([A-Za-z0-9+/=]+)$#',$data,$m))out(['error'=>'Invalid image data'],422);
  $bytes=base64_decode($m[1],true);
  if(!$bytes||strlen($bytes)>6500000||!getimagesizefromstring($bytes))out(['error'=>'Image too large or invalid'],422);
  $file=bin2hex(random_bytes(12)).'.png';
  $folder=dirname(__DIR__).'/storage/posts';
  if(!is_dir($folder))mkdir($folder,0755,true);
  if(file_put_contents($folder.'/'.$file,$bytes,LOCK_EX)===false)out(['error'=>'Could not save post image'],500);

  $url=(string)($in['url']??'');
  $sourceTitle=mb_substr((string)($in['source_title']??''),0,1500);
  $headline=mb_substr((string)($in['headline']??''),0,500);
  $description=mb_substr((string)($in['description']??''),0,2000);
  $caption=mb_substr((string)($in['caption']??''),0,8000);
  $captionX=trim((string)($in['caption_x']??''));
  $sourceImageUrl=mb_substr((string)($in['source_image_url']??''),0,2000);
  $channelStr=implode(',', $selectedChannels);

  // Determine public image URL (always use the generated branded poster, never raw source image!)
  $postImageUrl = getPublicPosterUrl($file, $bytes, $c['public_base_url'] ?? '');

  $results=[];
  $pdo=db();
  $chanStmt=$pdo->prepare('SELECT platform, account_name, account_handle, remote_id, access_token, service_type FROM channels WHERE platform=? AND is_connected=1');

  foreach($selectedChannels as $chKey){
   $chanStmt->execute([$chKey]);
   $chInfo=$chanStmt->fetch();
   if(!$chInfo){
    $results[]=[
     'platform'=>$chKey,
     'status'=>'error',
     'error'=>'Channel not connected in database.'
    ];
    continue;
   }

   $handle=$chInfo['account_handle'];
   $remoteId=$chInfo['remote_id'];
   $token=$chInfo['access_token'] ?: ($c['buffer_access_token'] ?? '');

   // Tailor post text strictly for platform constraints (Twitter/X strict 280-char limit)
   $textToSend = $caption;
   if($chKey === 'x'){
    if($captionX !== ''){
     $textToSend = mb_substr($captionX, 0, 280);
    } else {
     $tag = '#voxbulletin';
     $cleanHl = trim($headline);
     $cleanDesc = trim($description);
     $candidate = $cleanHl . "\n\n" . $cleanDesc . "\n\n" . $tag;
     if(mb_strlen($candidate) <= 280){
      $textToSend = $candidate;
     } else {
      $overhead = mb_strlen($cleanHl) + 2 + 5 + mb_strlen($tag);
      $availDesc = 280 - $overhead;
      if($availDesc >= 25){
       $trimmedDesc = mb_substr($cleanDesc, 0, $availDesc);
       $lastSpace = mb_strrpos($trimmedDesc, ' ');
       if($lastSpace !== false && $lastSpace > 10){
        $trimmedDesc = mb_substr($trimmedDesc, 0, $lastSpace);
       }
       $textToSend = $cleanHl . "\n\n" . $trimmedDesc . "...\n\n" . $tag;
      } else {
       $availHl = 280 - 5 - mb_strlen($tag);
       $trimmedHl = mb_substr($cleanHl, 0, $availHl);
       $lastSpace = mb_strrpos($trimmedHl, ' ');
       if($lastSpace !== false && $lastSpace > 10){
        $trimmedHl = mb_substr($trimmedHl, 0, $lastSpace);
       }
       $textToSend = $trimmedHl . "...\n\n" . $tag;
      }
     }
    }
    if(mb_strlen($textToSend) > 280){
     $textToSend = mb_substr($textToSend, 0, 277) . '...';
    }
   }

   // 1. REAL PUBLISH VIA BUFFER OFFICIAL GRAPHQL API
   if($remoteId && $token){
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

    if($chKey === 'instagram'){
     $postInput['metadata'] = [
      'instagram' => [
       'type' => 'post',
       'shouldShareToFeed' => true
      ]
     ];
    } else if($chKey === 'facebook'){
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
     CURLOPT_TIMEOUT => 40,
     CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Bearer '.$token, 'User-Agent: VoxBulletin/2.0'],
     CURLOPT_POSTFIELDS => $payload
    ]);
    $rawRes = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $bufRes = json_decode($rawRes ?: '', true);
    $createPost = $bufRes['data']['createPost'] ?? null;

    if($httpCode === 200 && !empty($createPost['post']['id'])){
     $upId = $createPost['post']['id'];
     $results[] = [
      'platform' => $chKey,
      'account_handle' => $handle,
      'status' => 'published',
      'via' => 'buffer_graphql',
      'update_id' => $upId,
      'post_url' => 'https://publish.buffer.com',
      'message' => 'Published live to '.$handle.' via Buffer!'
     ];
    } else {
     $errMsg = $createPost['message'] ?? $bufRes['errors'][0]['message'] ?? ('Buffer API Error (HTTP '.$httpCode.')');
     $results[] = [
      'platform' => $chKey,
      'account_handle' => $handle,
      'status' => 'error',
      'via' => 'buffer_graphql',
      'error' => $errMsg
     ];
    }
   }
   // 2. REAL PUBLISH VIA DIRECT META GRAPH API
   else if($chKey==='instagram' && !empty($c['meta_instagram_id']) && !empty($c['meta_access_token'])){
    $igId=$c['meta_instagram_id'];
    $metaToken=$c['meta_access_token'];

    $ch=curl_init('https://graph.facebook.com/v21.0/'.$igId.'/media');
    curl_setopt_array($ch,[
     CURLOPT_POST=>true,
     CURLOPT_POSTFIELDS=>http_build_query([
      'image_url'=>$postImageUrl,
      'caption'=>$caption,
      'access_token'=>$metaToken
     ]),
     CURLOPT_RETURNTRANSFER=>true,
     CURLOPT_TIMEOUT=>30
    ]);
    $cRes=json_decode(curl_exec($ch)?:'',true);
    curl_close($ch);

    if(!empty($cRes['id'])){
     $creationId=$cRes['id'];
     $ch2=curl_init('https://graph.facebook.com/v21.0/'.$igId.'/media_publish');
     curl_setopt_array($ch2,[
      CURLOPT_POST=>true,
      CURLOPT_POSTFIELDS=>http_build_query([
       'creation_id'=>$creationId,
       'access_token'=>$metaToken
      ]),
      CURLOPT_RETURNTRANSFER=>true,
      CURLOPT_TIMEOUT=>30
     ]);
     $pRes=json_decode(curl_exec($ch2)?:'',true);
     curl_close($ch2);

     if(!empty($pRes['id'])){
      $results[]=[
       'platform'=>'instagram',
       'account_handle'=>$handle,
       'status'=>'published',
       'via'=>'meta_graph_api',
       'post_id'=>$pRes['id'],
       'post_url'=>'https://www.instagram.com/p/'.$pRes['id'],
       'message'=>'Published directly to Instagram via Meta Graph API!'
      ];
     } else {
      $results[]=[
       'platform'=>'instagram',
       'account_handle'=>$handle,
       'status'=>'error',
       'error'=>'Instagram publish failed: '.($pRes['error']['message']??'Unknown error')
      ];
     }
    } else {
     $results[]=[
      'platform'=>'instagram',
      'account_handle'=>$handle,
      'status'=>'error',
      'error'=>'Instagram container error: '.($cRes['error']['message']??'Unknown error')
     ];
    }
   }
   // 3. NO REAL TOKEN CONNECTED
   else {
    $results[]=[
     'platform'=>$chKey,
     'account_handle'=>$handle,
     'status'=>'error',
     'error'=>'No real Buffer API key connected for '.$chKey.'.'
    ];
   }
  }

  // Insert post record with channels_published
  $stmt=$pdo->prepare('INSERT INTO posts(source_url,source_title,headline,description,caption,image_file,source_image_url,channels_published) VALUES(?,?,?,?,?,?,?,?)');
  $stmt->execute([$url,$sourceTitle,$headline,$description,$caption,$file,$sourceImageUrl,$channelStr]);
  $postId=$pdo->lastInsertId();

  // Determine overall status
  $anyPublished = false;
  foreach($results as $r){
   if($r['status'] === 'published') $anyPublished = true;
  }

  // Dispatch branded email notification if any channel published successfully
  if($anyPublished && !empty($c['email_notifications_enabled'])){
   sendPublishNotificationEmail([
    'headline' => $headline,
    'description' => $description,
    'channels' => $results,
    'image_url' => $postImageUrl,
    'article_title' => $sourceTitle,
    'article_url' => $url,
    'priority' => 'Manual Studio Publication',
    'published_at' => date('Y-m-d H:i:s')
   ], $c);
  }

  out([
   'ok'=>true,
   'id'=>$postId,
   'image_url'=>'storage/posts/'.$file,
   'channels_published'=>$selectedChannels,
   'results'=>$results,
   'message'=>$anyPublished ? 'Published successfully to social channels!' : 'Publish completed with errors (see channel details below).'
  ]);
 }
 if($action==='save'){
  csrf();$in=json_decode(file_get_contents('php://input'),true)?:[];
  $data=$in['image']??'';if(!preg_match('#^data:image/png;base64,([A-Za-z0-9+/=]+)$#',$data,$m))out(['error'=>'Invalid PNG image'],422);
  $bytes=base64_decode($m[1],true);if(!$bytes||strlen($bytes)>6500000||!getimagesizefromstring($bytes))out(['error'=>'Image too large or invalid'],422);
  $im=getimagesizefromstring($bytes);if($im[0]!==1080||$im[1]!==1350)out(['error'=>'Image must be 1080x1350'],422);
  $url=(string)($in['url']??'');if(!in_array(strtolower(parse_url($url,PHP_URL_HOST)??''),['fandomwire.com','www.fandomwire.com']))out(['error'=>'Invalid source'],422);
  $file=bin2hex(random_bytes(12)).'.png';$folder=dirname(__DIR__).'/storage/posts';if(!is_dir($folder))mkdir($folder,0755,true);
  if(file_put_contents($folder.'/'.$file,$bytes,LOCK_EX)===false)out(['error'=>'Could not save image'],500);
  $pdo=db();$stmt=$pdo->prepare('INSERT INTO posts(source_url,source_title,headline,description,caption,image_file,source_image_url) VALUES(?,?,?,?,?,?,?)');
  $stmt->execute([$url,mb_substr((string)($in['source_title']??''),0,1500),mb_substr((string)($in['headline']??''),0,500),mb_substr((string)($in['description']??''),0,2000),mb_substr((string)($in['caption']??''),0,8000),$file,mb_substr((string)($in['source_image_url']??''),0,2000)]);
  out(['ok'=>true,'id'=>$pdo->lastInsertId(),'image_url'=>'storage/posts/'.$file]);
 }
 if($action==='posts'){
  $rows=db()->query('SELECT id,source_url,source_title,headline,description,caption,image_file,channels_published,created_at FROM posts ORDER BY id DESC LIMIT 50')->fetchAll();out(['items'=>$rows]);
 }
 if($action==='delete_post'){
  csrf();
  $in=json_decode(file_get_contents('php://input'),true)?:[];
  $id=(int)($in['id']??0);
  if(!$id)out(['error'=>'Invalid post ID'],400);
  $pdo=db();
  $stmt=$pdo->prepare('SELECT image_file FROM posts WHERE id=?');
  $stmt->execute([$id]);
  $row=$stmt->fetch();
  if(!$row)out(['error'=>'Post not found'],404);
  if(!empty($row['image_file'])){
   $f=dirname(__DIR__).'/storage/posts/'.$row['image_file'];
   if(is_file($f))@unlink($f);
  }
  $del=$pdo->prepare('DELETE FROM posts WHERE id=?');
  $del->execute([$id]);
  out(['ok'=>true,'message'=>'Post #'.$id.' and its image deleted successfully.']);
 }
 if($action==='delete_posts'){
  csrf();
  $in=json_decode(file_get_contents('php://input'),true)?:[];
  $ids=array_filter(array_map('intval',(array)($in['ids']??[])));
  $deleteAll=!empty($in['all']);
  $pdo=db();
  $deletedFiles=0;
  $deletedRows=0;
  if($deleteAll){
   $rows=$pdo->query('SELECT id, image_file FROM posts')->fetchAll();
   foreach($rows as $r){
    if(!empty($r['image_file'])){
     $f=dirname(__DIR__).'/storage/posts/'.$r['image_file'];
     if(is_file($f)){@unlink($f);$deletedFiles++;}
    }
   }
   $deletedRows=$pdo->exec('DELETE FROM posts');
   out(['ok'=>true,'message'=>'All posts and image files deleted.','deleted_posts'=>$deletedRows,'deleted_files'=>$deletedFiles]);
  }
  if(empty($ids))out(['error'=>'No post IDs selected for deletion'],400);
  $inClause=implode(',',array_fill(0,count($ids),'?'));
  $stmt=$pdo->prepare("SELECT id, image_file FROM posts WHERE id IN ($inClause)");
  $stmt->execute($ids);
  $rows=$stmt->fetchAll();
  foreach($rows as $r){
   if(!empty($r['image_file'])){
    $f=dirname(__DIR__).'/storage/posts/'.$r['image_file'];
    if(is_file($f)){@unlink($f);$deletedFiles++;}
   }
  }
  $delStmt=$pdo->prepare("DELETE FROM posts WHERE id IN ($inClause)");
  $delStmt->execute($ids);
  $deletedRows=$delStmt->rowCount();
  out(['ok'=>true,'message'=>"Deleted $deletedRows post(s) and freed disk storage.",'deleted_posts'=>$deletedRows,'deleted_files'=>$deletedFiles]);
 }
 if($action==='cleanup_storage'){
  csrf();
  $days=7;
  $cutoff=date('Y-m-d H:i:s',strtotime("-$days days"));
  $pdo=db();
  $stmt=$pdo->prepare('SELECT id, image_file FROM posts WHERE created_at < ?');
  $stmt->execute([$cutoff]);
  $rows=$stmt->fetchAll();
  $deletedFiles=0;
  foreach($rows as $r){
   if(!empty($r['image_file'])){
    $f=dirname(__DIR__).'/storage/posts/'.$r['image_file'];
    if(is_file($f)){@unlink($f);$deletedFiles++;}
   }
  }
  $delStmt=$pdo->prepare('DELETE FROM posts WHERE created_at < ?');
  $delStmt->execute([$cutoff]);
  $deletedRows=$delStmt->rowCount();

  $folder=dirname(__DIR__).'/storage/posts';
  if(is_dir($folder)){
   foreach(glob($folder.'/*.png') as $imgFile){
    if(filemtime($imgFile)<(time()-$days*86400)){
     @unlink($imgFile);
     $deletedFiles++;
    }
   }
  }
  out(['ok'=>true,'message'=>"Weekly cleanup complete: Deleted $deletedRows post(s) older than 7 days and freed disk space.",'deleted_posts'=>$deletedRows,'deleted_files'=>$deletedFiles]);
 }
 out(['error'=>'Unknown action'],404);
}catch(Throwable $e){out(['error'=>'Operation failed: '.$e->getMessage()],500);}
