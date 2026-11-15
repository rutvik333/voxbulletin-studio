# Vox Bulletin Studio 🚀

Automated entertainment news discovery, AI drafting, high-impact social graphic generator, and real multi-channel cross-publishing studio.

---

## 🌟 Features

- **Automated News Discovery**: Real-time RSS ingestion from entertainment feeds with sanitized headlines and clean summaries.
- **Gemini AI Drafting**: 1-click drafting using Google Gemini API to produce viral headlines, concise excerpts, and platform-optimized copy.
- **Cinematic Canvas Graphic Studio**: 
  - 1080 × 1350 Instagram & Facebook optimized dimensions.
  - Multi-color headline highlighting (Electric Yellow, Sky Blue, Sunset Orange, Neon Green, etc.).
  - 3D Globe badge logo integration with translucent backing pill.
  - Seamless gradient fade and glowing footer brand dividers.
- **Multi-Channel Buffer Publishing**:
  - **Instagram (`@voxbulletin`)**: Full-length multi-paragraph captions + high-resolution poster graphics.
  - **Facebook (`Vox Bulletins`)**: Full-length captions + graphic posts.
  - **X / Twitter (`@voxbulletins`)**: Dedicated character-limited post editor with live `0 / 280` character counter and automatic smart word-boundary formatting.
- **Fast CDN Bridge**: Seamlessly uploads local graphics to a high-speed public CDN so Buffer can dispatch live posts directly from localhost or Hostinger deployments.

---

## 📁 Repository Structure

```
├── instagram-automation/        # Vox Bulletin Studio (PHP, JavaScript Canvas, Buffer & Gemini APIs)
│   ├── api/index.php            # Backend API (News proxy, Gemini draft, Buffer GraphQL publishing)
│   ├── index.php                # Complete frontend Studio UI with live preview and cross-poster
│   ├── config.example.php       # Configuration template (Copy to config.php)
│   ├── brand-globe.png          # High-resolution 3D Globe brand logo
│   └── schema.sql               # SQLite / MySQL database schema
├── facebook-post-extractor/     # Python Flask news extraction service
└── .gitignore                   # Protects API keys and local databases
```

---

## ⚙️ Quick Start

### 1. Configure Secrets
Copy the example config inside `instagram-automation/`:
```bash
cp instagram-automation/config.example.php instagram-automation/config.php
```
Edit `config.php` with your credentials:
```php
return [
  'admin_password' => 'admin123',
  'gemini_api_key' => 'YOUR_GEMINI_API_KEY',
  'buffer_access_token' => 'YOUR_BUFFER_ACCESS_TOKEN',
  'buffer_client_id' => 'YOUR_BUFFER_CLIENT_ID',
  'buffer_client_secret' => 'YOUR_BUFFER_CLIENT_SECRET',
  'buffer_org_id' => 'YOUR_BUFFER_ORG_ID',
];
```

### 2. Start Local PHP Server
```bash
cd instagram-automation
php -S 127.0.0.1:8000
```
Open `http://127.0.0.1:8000` in your browser. Default password is `admin123`.

---

## 🔒 Security
Real credentials, database files (`*.sqlite`), and generated storage images are excluded via `.gitignore` to ensure security. Never commit `config.php` to version control.
