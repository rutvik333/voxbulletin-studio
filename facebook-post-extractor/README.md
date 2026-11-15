# PostPilot — Facebook Post Extractor

A responsive local dashboard to retrieve the latest 2 (or up to 20) posts from a Facebook Page via the official Meta Graph API.

## Quick start

1. Install Python 3.10+.
2. In this folder run `pip install -r requirements.txt`.
3. Run `python app.py`.
4. Open `http://127.0.0.1:5000`.
5. Open **API Settings**, paste a valid Meta Graph API access token, and click **Fetch Posts**.

The page is preconfigured to `https://www.facebook.com/screenrank` and 2 posts.

## Facebook access requirements

Facebook does **not** generally permit reading arbitrary third-party public Page posts with an ordinary user/page token. For ScreenRank, if you do not manage that Page, your Meta app requires approved **Page Public Content Access**. For Pages you manage, obtain a suitable token and Page permissions such as `pages_read_engagement` and `pages_read_user_content` as required by the relevant endpoint. The app shows API permission errors rather than bypassing Facebook restrictions.

Meta docs: https://developers.facebook.com/docs/graph-api/reference/page/feed/

## Fields and limitations

Returns post ID, title (native title if available, otherwise first line of caption), full caption, created time, permalink, attachment image URLs, and type. Image URLs may expire or be limited by Meta. API image quality varies; `full_picture` may be capped. Reels are not included in this endpoint. Facebook may omit unavailable or restricted content.

## Security

The token is sent to the local Flask server to make the API request; it is not persisted by the app. Run locally or behind HTTPS and authentication if deployed. Do not expose a token in a publicly accessible frontend or share your `.env`/tokens. Optionally set `FACEBOOK_ACCESS_TOKEN` as a server environment variable instead of entering a token in the UI. Avoid running Flask's development server on the public internet.

## Branded Post Studio
Visit `/studio` or click **Create branded post** on a fetched post. Upload a source image and your own transparent PNG logo, edit the headline, highlight, category, date, and bottom brand name. Export a 1080px-wide PNG in portrait, square, or story format. A sample from the provided reference is included for layout testing; replace it with media you have rights to publish. Facebook CDN images may block browser CORS; download and upload the original image when that happens. The studio does not automatically publish posts or remove watermarks.
