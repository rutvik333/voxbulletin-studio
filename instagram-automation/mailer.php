<?php
/**
 * VoxBulletin Studio - Lightweight SMTP Mailer
 * Pure PHP socket implementation with SSL/TLS support.
 * Zero external dependencies (no Composer / PHPMailer required).
 */

function sendSmtpSocketMail($host, $port, $user, $pass, $to, $subject, $htmlBody, $fromName = 'VoxBulletin Content Studio') {
    $timeout = 15;
    $context = stream_context_create([
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ]);

    $prefix = ($port == 465) ? 'ssl://' : '';
    $socket = @stream_socket_client($prefix . $host . ':' . $port, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $context);
    if (!$socket) {
        throw new Exception("Could not connect to SMTP server $host:$port - $errstr ($errno)");
    }

    $read = function() use ($socket) {
        $data = '';
        while ($line = fgets($socket, 515)) {
            $data .= $line;
            if (substr($line, 3, 1) === ' ') break;
        }
        return $data;
    };

    $write = function($cmd, $expectedCode = 250) use ($socket, $read) {
        fputs($socket, $cmd . "\r\n");
        $response = $read();
        $code = substr($response, 0, 3);
        if ($expectedCode && $code != $expectedCode) {
            throw new Exception("SMTP error on '$cmd': " . trim($response));
        }
        return $response;
    };

    $read(); // Read initial banner
    $write("EHLO " . gethostname(), 250);

    if ($port == 587) {
        $write("STARTTLS", 220);
        stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        $write("EHLO " . gethostname(), 250);
    }

    $cleanPass = str_replace(' ', '', $pass);
    $write("AUTH LOGIN", 334);
    $write(base64_encode($user), 334);
    $write(base64_encode($cleanPass), 235);

    $write("MAIL FROM:<$user>", 250);
    $write("RCPT TO:<$to>", 250);
    $write("DATA", 354);

    $headers = [
        "MIME-Version: 1.0",
        "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <$user>",
        "To: <$to>",
        "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=",
        "Date: " . date('r'),
        "Content-Type: text/html; charset=UTF-8",
        "Content-Transfer-Encoding: base64"
    ];

    $emailContent = implode("\r\n", $headers) . "\r\n\r\n" . chunk_split(base64_encode($htmlBody)) . "\r\n.";
    $write($emailContent, 250);
    $write("QUIT", 221);
    fclose($socket);
    return true;
}

/**
 * Formats and sends a rich HTML email notification when a post is published.
 */
function sendPublishNotificationEmail($postData, $config) {
    if (empty($config['email_notifications_enabled']) && empty($config['smtp_user'])) {
        return ['ok' => false, 'skipped' => true, 'reason' => 'Email notifications disabled'];
    }

    $smtpUser = trim((string)($config['smtp_user'] ?? ''));
    $smtpPass = trim((string)($config['smtp_pass'] ?? ''));
    $smtpHost = trim((string)($config['smtp_host'] ?? 'smtp.gmail.com'));
    $smtpPort = (int)($config['smtp_port'] ?? 465);
    $recipient = trim((string)($config['notification_email'] ?? $smtpUser));

    if (!$smtpUser || !$smtpPass || !$recipient) {
        return ['ok' => false, 'reason' => 'Missing SMTP credentials'];
    }

    $headline = $postData['headline'] ?? 'Untitled Post';
    $desc = $postData['description'] ?? '';
    $channels = (array)($postData['channels'] ?? []);
    $imageUrl = $postData['image_url'] ?? '';
    $priority = $postData['priority'] ?? 'Standard Publication';
    $sourceTitle = $postData['article_title'] ?? $headline;
    $sourceUrl = $postData['article_url'] ?? '';
    $timestamp = $postData['published_at'] ?? date('Y-m-d H:i:s');

    // Build channel badges HTML
    $channelsHtml = '';
    foreach ($channels as $ch) {
        $name = is_array($ch) ? ($ch['platform'] ?? 'Social') : $ch;
        $handle = is_array($ch) ? ($ch['handle'] ?? '') : '';
        $bg = '#132b45';
        $border = '#234a70';
        $color = '#38bdf8';
        if (stripos($name, 'instagram') !== false) {
            $bg = '#3b122d'; $border = '#6e1d52'; $color = '#f472b6';
        } elseif (stripos($name, 'facebook') !== false) {
            $bg = '#122544'; $border = '#1d3e75'; $color = '#60a5fa';
        } elseif (stripos($name, 'x') !== false || stripos($name, 'twitter') !== false) {
            $bg = '#1e293b'; $border = '#334155'; $color = '#f8fafc';
        }
        $channelsHtml .= '<span style="display:inline-block;background:' . $bg . ';border:1px solid ' . $border . ';color:' . $color . ';font-size:12px;font-weight:700;padding:5px 12px;border-radius:20px;margin-right:6px;margin-bottom:6px;">'
            . htmlspecialchars(strtoupper($name)) . ($handle ? ' (' . htmlspecialchars($handle) . ')' : '') . '</span>';
    }

    $imageSection = '';
    if ($imageUrl) {
        $imageSection = '
        <div style="margin:20px 0;text-align:center;background:#060b17;padding:16px;border-radius:10px;border:1px solid #1a2942">
          <img src="' . htmlspecialchars($imageUrl) . '" alt="Published Graphic" style="max-width:100%;max-height:480px;border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,0.5);display:inline-block;" />
          <div style="margin-top:12px;">
            <a href="' . htmlspecialchars($imageUrl) . '" target="_blank" style="display:inline-block;background:#ffd12a;color:#001226;text-decoration:none;font-weight:800;font-size:13px;padding:8px 18px;border-radius:6px;">View Full 1080×1350 Graphic ↗</a>
          </div>
        </div>';
    }

    $sourceSection = '';
    if ($sourceUrl) {
        $sourceSection = '<div style="margin-top:12px;font-size:12px;color:#94a3b8">Source Article: <a href="' . htmlspecialchars($sourceUrl) . '" target="_blank" style="color:#38bdf8;text-decoration:underline;">' . htmlspecialchars($sourceTitle) . ' ↗</a></div>';
    }

    $subject = "🚀 [VoxBulletin] Published: " . mb_substr($headline, 0, 70);

    $htmlBody = '
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="margin:0;padding:24px 12px;background:#030712;font-family:\'Segoe UI\',-apple-system,BlinkMacSystemFont,Roboto,Helvetica,Arial,sans-serif;color:#e2e8f0;line-height:1.6;">
  <div style="max-width:620px;margin:0 auto;background:#0d1829;border:1px solid #20314d;border-radius:14px;overflow:hidden;box-shadow:0 12px 36px rgba(0,0,0,0.6);">
    <!-- Brand Header -->
    <div style="background:linear-gradient(135deg, #091322 0%, #112238 100%);padding:22px 26px;border-bottom:1px solid #20314d;display:flex;justify-content:space-between;align-items:center;">
      <div>
        <div style="font-size:22px;font-weight:900;letter-spacing:1px;color:#ffffff;">VOX <span style="color:#ffd12a;">BULLETIN</span></div>
        <div style="font-size:10px;font-weight:700;letter-spacing:2px;color:#64748b;margin-top:2px;">CONTENT STUDIO</div>
      </div>
      <div style="text-align:right;">
        <span style="background:#14532d;color:#86efac;border:1px solid #16a34a;font-size:11px;font-weight:700;padding:4px 10px;border-radius:14px;">● PUBLISHED LIVE</span>
      </div>
    </div>

    <!-- Body Content -->
    <div style="padding:26px;">
      <!-- Priority category tag -->
      <div style="display:inline-block;background:#1e293b;border:1px solid #334155;color:#94a3b8;font-size:11px;font-weight:700;padding:3px 10px;border-radius:6px;margin-bottom:14px;">
        ' . htmlspecialchars($priority) . '
      </div>

      <!-- Main Headline -->
      <h1 style="font-size:22px;font-weight:900;line-height:1.3;color:#ffffff;margin:0 0 14px;letter-spacing:-0.2px;">
        ' . htmlspecialchars(strtoupper($headline)) . '
      </h1>

      <!-- Description / Excerpt -->
      <div style="background:#111e33;border-left:3px solid #ffd12a;padding:12px 16px;border-radius:0 8px 8px 0;color:#cbd5e1;font-size:14px;margin-bottom:20px;">
        ' . nl2br(htmlspecialchars($desc)) . '
      </div>

      <!-- Published Channels -->
      <div style="margin-bottom:18px;">
        <div style="font-size:11px;font-weight:700;color:#94a3b8;letter-spacing:1px;margin-bottom:8px;">PUBLISHED TO CHANNELS:</div>
        <div>' . $channelsHtml . '</div>
      </div>

      <!-- Image preview -->
      ' . $imageSection . '

      <!-- Timestamp and metadata -->
      <div style="margin-top:20px;padding-top:16px;border-top:1px solid #1c2c47;font-size:12px;color:#64748b;display:flex;justify-content:space-between;flex-wrap:wrap;gap:8px;">
        <div><b>Published At:</b> ' . htmlspecialchars($timestamp) . '</div>
        <div><b>Engine:</b> Autonomous 3x/Day Scheduler</div>
      </div>
      ' . $sourceSection . '
    </div>

    <!-- Footer -->
    <div style="background:#091322;padding:16px 26px;border-top:1px solid #1c2c47;text-align:center;font-size:11px;color:#64748b;">
      You received this because email notifications are enabled in your VoxBulletin Content Studio settings.
    </div>
  </div>
</body>
</html>';

    try {
        sendSmtpSocketMail($smtpHost, $smtpPort, $smtpUser, $smtpPass, $recipient, $subject, $htmlBody, 'VoxBulletin Studio');
        return ['ok' => true, 'recipient' => $recipient];
    } catch (Throwable $e) {
        error_log("[VoxBulletin Mailer Error] " . $e->getMessage());
        return ['ok' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Sends an instant test email to verify SMTP configuration.
 */
function sendTestNotificationEmail($config) {
    $smtpUser = trim((string)($config['smtp_user'] ?? ''));
    $smtpPass = trim((string)($config['smtp_pass'] ?? ''));
    $smtpHost = trim((string)($config['smtp_host'] ?? 'smtp.gmail.com'));
    $smtpPort = (int)($config['smtp_port'] ?? 465);
    $recipient = trim((string)($config['notification_email'] ?? $smtpUser));

    if (!$smtpUser || !$smtpPass) {
        throw new Exception("Missing Gmail username or 16-character App Password.");
    }

    $subject = "🧪 [VoxBulletin] Gmail SMTP Connected Successfully!";
    $body = '
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="margin:0;padding:24px 12px;background:#030712;font-family:\'Segoe UI\',-apple-system,sans-serif;color:#e2e8f0;">
  <div style="max-width:560px;margin:0 auto;background:#0d1829;border:1px solid #20314d;border-radius:14px;padding:26px;box-shadow:0 12px 36px rgba(0,0,0,0.6);">
    <div style="font-size:22px;font-weight:900;color:#fff;margin-bottom:4px;">VOX <span style="color:#ffd12a;">BULLETIN</span></div>
    <div style="font-size:11px;font-weight:700;letter-spacing:2px;color:#64748b;margin-bottom:20px;">CONTENT STUDIO</div>
    <div style="background:#14532d;border:1px solid #16a34a;color:#86efac;padding:12px 16px;border-radius:8px;font-weight:700;font-size:14px;margin-bottom:16px;">
      ✓ Gmail SMTP Connection Verified & Working!
    </div>
    <p style="font-size:14px;color:#cbd5e1;line-height:1.6;">
      Your Gmail SMTP connection to <b>' . htmlspecialchars($smtpHost) . ':' . $smtpPort . '</b> is successfully configured.
    </p>
    <p style="font-size:14px;color:#94a3b8;line-height:1.6;">
      Every time an entertainment post is published to <b>Instagram, Facebook, or X</b> (both manually or via the 3x/day autonomous scheduler), you will receive a branded summary email with the post graphic and copy.
    </p>
    <div style="margin-top:20px;padding-top:14px;border-top:1px solid #1c2c47;font-size:12px;color:#64748b;">
      Recipient: ' . htmlspecialchars($recipient) . ' &bull; Time: ' . date('Y-m-d H:i:s') . '
    </div>
  </div>
</body>
</html>';

    sendSmtpSocketMail($smtpHost, $smtpPort, $smtpUser, $smtpPass, $recipient, $subject, $body, 'VoxBulletin Studio');
    return ['ok' => true, 'message' => "Test email successfully sent to $recipient! Check your inbox."];
}
