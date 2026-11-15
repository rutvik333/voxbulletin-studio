<?php
// Copy to config.php. Never commit your real credentials or API keys to GitHub.
return [
  'admin_password' => 'admin123',
  'gemini_api_keys' => [
    'YOUR_PRIMARY_GEMINI_API_KEY',
    'YOUR_BACKUP_GEMINI_KEY_2',
  ],
  'gemini_api_key' => 'YOUR_PRIMARY_GEMINI_API_KEY',
  'buffer_access_token' => 'YOUR_BUFFER_ACCESS_TOKEN',
  'buffer_client_id' => 'YOUR_BUFFER_CLIENT_ID',
  'buffer_client_secret' => 'YOUR_BUFFER_CLIENT_SECRET',
  'buffer_org_id' => 'YOUR_BUFFER_ORGANIZATION_ID',
  'public_base_url' => '', // e.g. https://yourdomain.com when deployed on Hostinger
  'email_notifications_enabled' => false,
  'notification_email' => 'your-email@gmail.com',
  'smtp_host' => 'smtp.gmail.com',
  'smtp_port' => 465,
  'smtp_user' => 'your-email@gmail.com',
  'smtp_pass' => 'YOUR_GMAIL_16_CHAR_APP_PASSWORD',
  'smtp_encryption' => 'ssl',
  'db_host' => 'localhost',
  'db_name' => '',
  'db_user' => '',
  'db_pass' => '',
];
