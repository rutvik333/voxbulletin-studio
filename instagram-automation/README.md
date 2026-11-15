# Vox Bulletin Studio — Hostinger PHP/MySQL Edition

A deployable PHP dashboard to discover FandomWire entertainment headlines, inspect article metadata, create an original Gemini-written draft, render a 1080×1350 Vox Bulletin poster in the browser, and save PNGs and captions to Hostinger MySQL + disk.

## Requirements
- Hostinger PHP hosting with PHP 8.1+ and cURL, DOM, PDO MySQL, mbstring extensions
- MySQL database and phpMyAdmin
- HTTPS domain or subdomain
- Google Gemini API key with access to `gemini-2.5-flash`
- Permission to use any images you upload or choose from an article

## Deployment (about 15 minutes after credentials are ready)
1. In Hostinger hPanel, create a subdomain (e.g. `studio.example.com`) and note its document root. Enable SSL.
2. Create a MySQL database and user. Open phpMyAdmin and import `schema.sql`.
3. Copy `config.example.php` to `config.php`. Fill in your own admin password, a **new** Gemini key, and Hostinger MySQL credentials. **Never paste secrets into the frontend.**
4. Upload `index.php`, `api/`, `storage/`, `.htaccess`, and `config.php` to the subdomain's document root. Do not upload `config.example.php`, `schema.sql` or this README if you can avoid it.
5. Ensure `storage/posts/` is writable by PHP (typically directory permission 755/775 depending on server ownership). Do not set 777.
6. Open your subdomain in a browser, sign in, and click **Fetch latest news**.
7. Select an article. Click **Generate with Gemini** to produce an original draft. Read the source and verify the claims before publishing.
8. Upload an image that you have rights to use. You can try **Try source image**, but it only works for image URLs hosted on `fandomwire.com` or its subdomains. Article images may have separate copyrights; do not assume permission.
9. Optionally upload your logo. Adjust the headline and description and click **Refresh preview**.
10. Click **Download PNG** or **Save to Hostinger**. Saved posts appear in **Post Library**.

## What this version does
- Scrapes public headline links from the supplied FandomWire news category page, if the site's layout and access controls allow it.
- Retrieves `og:title`, `og:image`, publication time and meta description for a selected article.
- Sends a limited headline/excerpt to Gemini to create a new draft (not a fact-check).
- Creates a sharp 1080×1350 PNG with the user's requested navy-gradient, bold text and bottom branding.
- Stores draft metadata in MySQL and generated images on Hostinger.
- Includes password login, CSRF protection, server-side API key, and a basic authenticated dashboard.

## Important limitations / production hardening
- **Manual MVP, not automatic scheduled posting.** The previous twice-daily automation can be added separately with GitHub Actions and a secure server-to-server endpoint. This version does not schedule or publish to social networks.
- **No AI image generation** is included: Gemini generates text; upload your own permitted photo or load an eligible article image. Generating original AI backgrounds would require an image-capable model and a separate workflow.
- FandomWire markup, availability, robots rules and terms can change. Check permissions before sustained crawling. No paywall or bot-block bypass is implemented.
- The Gemini response is based only on supplied headline and excerpt, so human verification is essential.
- For production internet exposure, add login rate limiting at the host/firewall, CSP, backup/retention, image copyright review, session cookie security, and move `config.php` outside the public web root where possible.
- The optional image proxy allows only HTTPS `fandomwire.com` subdomains and blocks redirects; some image CDNs will not work.
- Fonts are loaded from Google Fonts in the browser. For a fully offline setup, self-host properly licensed font files.
- PHP's `upload_max_filesize` does not apply to JSON base64 upload; reverse proxy/PHP `post_max_size` must accommodate ~6 MB PNG payloads.

## Security notice
If an API key was pasted into a chat, screenshot, issue, or other shared location, revoke/rotate it in Google AI Studio before use. Do not reuse that exposed key.
