import os, re, csv, io, json
from urllib.parse import urlparse
from flask import Flask, render_template, request, jsonify, Response, send_file
import requests
import trending_generator

app = Flask(__name__)
VERSION = os.getenv('GRAPH_API_VERSION', 'v27.0')

def page_id(value):
    value = (value or '').strip()
    if re.fullmatch(r'[A-Za-z0-9._-]{2,100}', value): return value
    parsed = urlparse(value)
    if parsed.scheme not in ('http', 'https') or parsed.hostname not in ('facebook.com', 'www.facebook.com', 'm.facebook.com'):
        raise ValueError('Enter a Facebook Page URL or Page ID.')
    path = parsed.path.strip('/').split('/')[0]
    if not re.fullmatch(r'[A-Za-z0-9._-]{2,100}', path or '') or path in ('profile.php', 'pages', 'groups', 'watch'):
        raise ValueError('Use a Page username URL such as https://www.facebook.com/screenrank or its numeric Page ID.')
    return path

def collect_images(post):
    found = []
    def add(url):
        if isinstance(url, str) and url.startswith('https://') and url not in found: found.append(url)
    def walk(obj):
        if not isinstance(obj, dict): return
        media = obj.get('media') or {}
        if isinstance(media, dict): add((media.get('image') or {}).get('src'))
        add(obj.get('picture'))
        for item in (obj.get('subattachments') or {}).get('data', []): walk(item)
    for attachment in (post.get('attachments') or {}).get('data', []): walk(attachment)
    if not found: add(post.get('full_picture'))
    return found

def normalize(post):
    caption = post.get('message') or post.get('story') or ''
    attachment = ((post.get('attachments') or {}).get('data') or [{}])[0]
    title = post.get('name') or attachment.get('title') or (caption.splitlines()[0][:130] if caption.strip() else 'Untitled post')
    return {
        'id': post.get('id', ''),
        'title': title,
        'caption': caption,
        'created_time': post.get('created_time', ''),
        'url': post.get('permalink_url', ''),
        'images': collect_images(post),
        'type': post.get('status_type') or attachment.get('type') or 'post'
    }

@app.get('/')
@app.get('/trending')
def home():
    return render_template('trending.html')

@app.get('/facebook')
def facebook_extractor():
    return render_template('index.html')

@app.get('/studio')
def studio():
    return render_template('studio.html')

@app.get('/api/settings/ai')
def get_ai_settings():
    g_keys = trending_generator.get_gemini_keys()
    g_key = g_keys[0] if g_keys else ""
    o_key = trending_generator.get_openai_key()
    g_masked = (g_key[:6] + "..." + g_key[-4:]) if len(g_key) > 10 else ("*" * len(g_key) if g_key else "")
    o_masked = (o_key[:8] + "..." + o_key[-4:]) if len(o_key) > 12 else ("*" * len(o_key) if o_key else "")
    return jsonify(
        gemini_masked=g_masked,
        gemini_has_key=bool(g_key),
        gemini_keys_count=len(g_keys),
        gemini_keys=g_keys,
        openai_masked=o_masked,
        openai_has_key=bool(o_key),
        active_provider=trending_generator.get_active_provider()
    )

@app.post('/api/settings/gemini')
def set_gemini_settings():
    body = request.get_json(silent=True) or {}
    new_keys = body.get('api_keys')
    if isinstance(new_keys, list) and new_keys:
        trending_generator.set_gemini_keys(new_keys)
        trending_generator.set_active_provider('gemini')
        return jsonify(success=True, message=f"Saved {len(new_keys)} Gemini keys with failover support.")

    new_key = (body.get('api_key') or '').strip()
    if not new_key:
        return jsonify(error="Gemini API key cannot be empty."), 400
    
    ok, msg = trending_generator.test_gemini_key(new_key)
    masked = (new_key[:6] + "..." + new_key[-4:]) if len(new_key) > 10 else "********"
    if ok:
        trending_generator.set_gemini_key(new_key)
        trending_generator.set_active_provider('gemini')
        return jsonify(success=True, message=msg, masked_key=masked)
    else:
        if body.get('force_save'):
            trending_generator.set_gemini_key(new_key)
            trending_generator.set_active_provider('gemini')
            return jsonify(success=True, saved=True, message="Saved successfully.", warning=msg, masked_key=masked)
        return jsonify(success=False, error=msg, masked_key=masked), 400

@app.post('/api/settings/openai')
def set_openai_settings():
    body = request.get_json(silent=True) or {}
    new_key = (body.get('api_key') or '').strip()
    if not new_key:
        return jsonify(error="API key cannot be empty."), 400
    
    ok, msg = trending_generator.test_openai_key(new_key)
    masked = (new_key[:8] + "..." + new_key[-4:]) if len(new_key) > 12 else "********"
    if ok:
        trending_generator.set_openai_key(new_key)
        trending_generator.set_active_provider('openai')
        return jsonify(success=True, message=msg, masked_key=masked)
    else:
        if body.get('force_save'):
            trending_generator.set_openai_key(new_key)
            trending_generator.set_active_provider('openai')
            return jsonify(success=True, saved=True, message="Saved successfully.", warning=msg, masked_key=masked)
        return jsonify(success=False, error=msg, masked_key=masked), 400

@app.post('/api/settings/active-provider')
def set_provider():
    body = request.get_json(silent=True) or {}
    p = body.get('provider', 'gemini')
    trending_generator.set_active_provider(p)
    return jsonify(success=True, active_provider=p)

@app.post('/api/ai-topic')
@app.post('/api/chatgpt-topic')
def api_ai_topic():
    body = request.get_json(silent=True) or {}
    provider = body.get('provider')
    data, err = trending_generator.fetch_ai_topic(provider=provider)
    if err:
        return jsonify(error=err), 400
    return jsonify(data)

@app.post('/api/render-post')
def api_render_post():
    try:
        body = request.get_json(silent=True) or {}
        img = trending_generator.create_graphic(
            image_source=body.get('image_url') or 'static/danny_boyle_bg.png',
            headline=body.get('headline') or '*DANNY BOYLE* TALKS INK, THE COMMON ARCHITECTURE OF HIS MOVIES AND MORE *28 YEARS LATER*',
            description=body.get('description', ''),
            brand=body.get('brand', 'VOX BULLETIN'),
            color_theme=body.get('color_theme', 'multicolor')
        )
        buf = io.BytesIO()
        img.save(buf, format='PNG')
        buf.seek(0)
        return send_file(buf, mimetype='image/png', as_attachment=True, download_name='vox-bulletin-post.png')
    except Exception as e:
        return jsonify(error=str(e)), 500

@app.post('/api/posts')
def posts():
    body = request.get_json(silent=True) or {}
    try:
        page = page_id(body.get('page', 'https://www.facebook.com/screenrank'))
        count = max(1, min(20, int(body.get('count', 2))))
    except (ValueError, TypeError) as e:
        return jsonify(error=str(e)), 400
    token = (body.get('token') or os.getenv('FACEBOOK_ACCESS_TOKEN', '')).strip()
    if not token:
        return jsonify(error='Access token required. Add a Meta Graph API token in Settings. Public Pages you do not manage require approved Page Public Content Access.'), 400
    fields = 'id,message,story,name,created_time,permalink_url,full_picture,status_type,attachments{title,type,media,subattachments}'
    try:
        r = requests.get(f'https://graph.facebook.com/{VERSION}/{page}/posts', params={'fields': fields, 'limit': count, 'access_token': token}, timeout=25)
        payload = r.json()
    except requests.RequestException:
        return jsonify(error='Unable to contact Meta Graph API. Check your connection and try again.'), 502
    except ValueError:
        return jsonify(error='Meta returned an unexpected response.'), 502
    if not r.ok:
        err = payload.get('error', {})
        code = err.get('code', 'unknown')
        message = err.get('message', 'Request failed')
        return jsonify(error=f'Meta API error ({code}): {message}', hint='For third-party public Pages, your Meta app needs approved Page Public Content Access. For Pages you manage, use a suitable Page token and permissions.'), r.status_code if r.status_code < 500 else 502
    data = [normalize(p) for p in payload.get('data', [])][:count]
    return jsonify(page=page, count=len(data), posts=data)

if __name__ == '__main__':
    app.run(host='127.0.0.1', port=int(os.getenv('PORT', '5000')), debug=False)
