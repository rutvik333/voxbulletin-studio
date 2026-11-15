import os
import re
import html
import json
import requests
import feedparser
from io import BytesIO
from PIL import Image, ImageDraw, ImageFont

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
CONFIG_PATH = os.path.join(BASE_DIR, "config.json")
DEFAULT_KEY = os.getenv("OPENAI_API_KEY", "")

def get_openai_key():
    if os.path.exists(CONFIG_PATH):
        try:
            with open(CONFIG_PATH, "r", encoding="utf-8") as f:
                data = json.load(f)
                if data.get("openai_api_key"):
                    return data["openai_api_key"].strip()
        except Exception:
            pass
    return os.getenv("OPENAI_API_KEY", DEFAULT_KEY).strip()

def set_openai_key(new_key):
    config = {}
    if os.path.exists(CONFIG_PATH):
        try:
            with open(CONFIG_PATH, "r", encoding="utf-8") as f:
                config = json.load(f)
        except Exception:
            config = {}
    config["openai_api_key"] = new_key.strip()
    with open(CONFIG_PATH, "w", encoding="utf-8") as f:
        json.dump(config, f, indent=2)

def get_gemini_keys():
    keys = []
    if os.path.exists(CONFIG_PATH):
        try:
            with open(CONFIG_PATH, "r", encoding="utf-8") as f:
                data = json.load(f)
                if data.get("gemini_api_keys") and isinstance(data["gemini_api_keys"], list):
                    keys = [k.strip() for k in data["gemini_api_keys"] if k and k.strip()]
                elif data.get("gemini_api_key"):
                    keys = [data["gemini_api_key"].strip()]
        except Exception:
            pass
    if not keys:
        # Check parent instagram-automation config.php
        php_cfg = os.path.join(os.path.dirname(os.path.dirname(__file__)), "instagram-automation", "config.php")
        if os.path.exists(php_cfg):
            try:
                with open(php_cfg, "r", encoding="utf-8") as f:
                    content = f.read()
                    import re
                    m_arr = re.findall(r"'gemini_api_keys'\s*=>\s*\[([^\]]+)\]", content)
                    if m_arr:
                        keys = [k.strip().strip("'\"") for k in re.findall(r"['\"]([^'\"]+)['\"]", m_arr[0])]
                    if not keys:
                        m_single = re.search(r"'gemini_api_key'\s*=>\s*['\"]([^'\"]+)['\"]", content)
                        if m_single:
                            keys = [m_single.group(1).strip()]
            except Exception:
                pass
    if not keys:
        env_k = os.getenv("GEMINI_API_KEY", "").strip()
        if env_k:
            keys = [env_k]
    return [k for k in keys if k and not k.startswith("PASTE_")]

def get_gemini_key():
    keys = get_gemini_keys()
    return keys[0] if keys else ""

def set_gemini_key(new_key):
    config = {}
    if os.path.exists(CONFIG_PATH):
        try:
            with open(CONFIG_PATH, "r", encoding="utf-8") as f:
                config = json.load(f)
        except Exception:
            config = {}
    config["gemini_api_key"] = new_key.strip()
    if "gemini_api_keys" in config and isinstance(config["gemini_api_keys"], list):
        if new_key.strip() not in config["gemini_api_keys"]:
            config["gemini_api_keys"].insert(0, new_key.strip())
    else:
        config["gemini_api_keys"] = [new_key.strip()]
    with open(CONFIG_PATH, "w", encoding="utf-8") as f:
        json.dump(config, f, indent=2)

def set_gemini_keys(keys_list):
    config = {}
    if os.path.exists(CONFIG_PATH):
        try:
            with open(CONFIG_PATH, "r", encoding="utf-8") as f:
                config = json.load(f)
        except Exception:
            config = {}
    clean_keys = [k.strip() for k in keys_list if k and k.strip() and not k.startswith("PASTE_")]
    config["gemini_api_keys"] = clean_keys
    config["gemini_api_key"] = clean_keys[0] if clean_keys else ""
    with open(CONFIG_PATH, "w", encoding="utf-8") as f:
        json.dump(config, f, indent=2)

def test_gemini_key(key):
    key = (key or "").strip()
    if not key:
        return False, "Gemini API key cannot be empty."
    url = f"https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={key}"
    payload = {
        "contents": [{"parts": [{"text": "ping"}]}],
        "generationConfig": {"maxOutputTokens": 2}
    }
    try:
        r = requests.post(url, json=payload, timeout=10)
        res = r.json()
        if r.ok:
            return True, "Gemini API Key is valid and active!"
        err = res.get("error", {}).get("message", "Validation failed.")
        return False, f"Gemini Error: {err}"
    except Exception as e:
        return False, f"Connection error: {e}"

def get_active_provider():
    if os.path.exists(CONFIG_PATH):
        try:
            with open(CONFIG_PATH, "r", encoding="utf-8") as f:
                data = json.load(f)
                if data.get("active_provider"):
                    return data["active_provider"]
        except Exception:
            pass
    # If Gemini key exists, default to Gemini, otherwise check OpenAI
    if get_gemini_key():
        return "gemini"
    return "gemini"

def set_active_provider(provider):
    config = {}
    if os.path.exists(CONFIG_PATH):
        try:
            with open(CONFIG_PATH, "r", encoding="utf-8") as f:
                config = json.load(f)
        except Exception:
            config = {}
    config["active_provider"] = provider
    with open(CONFIG_PATH, "w", encoding="utf-8") as f:
        json.dump(config, f, indent=2)

def fetch_gemini_topic(api_key=None, model="gemini-2.5-flash"):
    """Generates an entertainment news post via Google Gemini API with automatic multi-key failover."""
    keys = [api_key.strip()] if api_key else get_gemini_keys()
    if not keys:
        return None, "No Gemini API key configured. Click '⚙ API Settings' to add your free Gemini key."

    prompt = (
        "You are an entertainment news graphic editor for VOX BULLETIN. "
        "Generate 1 hot, breaking, viral movie, Marvel, DC, or celebrity entertainment story right now. "
        "Return ONLY a JSON object with: "
        "1. 'headline': 10-16 word punchy ALL-CAPS news headline. Wrap 2-3 key impact phrases in asterisks for multi-color highlighting (e.g. '*CHRIS EVANS* WOULD HAVE CHOSEN *TEAM IRON MAN* OVER *CAPTAIN AMERICA* IN CIVIL WAR!'). "
        "2. 'description': 1-2 sentence uppercase crisp description (max 140 chars). "
        "3. 'brand': 'VOX BULLETIN'. "
        "4. 'image_query': search query to find the best movie still."
    )
    payload = {
        "contents": [{"parts": [{"text": prompt}]}],
        "generationConfig": {
            "responseMimeType": "application/json",
            "temperature": 0.8
        }
    }

    errors = []
    for idx, key in enumerate(keys):
        url = f"https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent?key={key}"
        try:
            r = requests.post(url, json=payload, timeout=20)
            if r.ok:
                candidates = r.json().get("candidates", [])
                if candidates:
                    raw_text = candidates[0].get("content", {}).get("parts", [{}])[0].get("text", "")
                    data = json.loads(raw_text)
                    data['key_index'] = idx
                    data['failovers'] = idx
                    return data, None
            
            err = r.json().get("error", {}).get("message", r.text) if r.content else f"HTTP {r.status_code}"
            errors.append(f"Key #{idx+1}: {err}")
        except Exception as e:
            errors.append(f"Key #{idx+1}: {e}")

    return None, f"All Gemini keys ({len(keys)}) failed: " + " | ".join(errors)

def fetch_ai_topic(provider=None):
    """Dynamically routes to active AI provider (Gemini or OpenAI)."""
    p = provider or get_active_provider()
    if p == "gemini":
        data, err = fetch_gemini_topic()
        if not err:
            data['provider'] = 'Gemini (Free)'
            return data, None
        # If Gemini failed because key missing, try OpenAI
        if get_openai_key() and "No Gemini API key" in err:
            d2, e2 = fetch_chatgpt_topic()
            if not e2:
                d2['provider'] = 'OpenAI'
                return d2, None
        return None, err
    else:
        data, err = fetch_chatgpt_topic()
        if not err:
            data['provider'] = 'OpenAI'
            return data, None
        # If OpenAI failed (e.g. 429), try Gemini if available
        if get_gemini_key():
            d2, e2 = fetch_gemini_topic()
            if not e2:
                d2['provider'] = 'Gemini (Free)'
                return d2, None
        return None, err

STATIC_DIR = os.path.join(BASE_DIR, "static")
FONTS_DIR = os.path.join(STATIC_DIR, "fonts")
os.makedirs(FONTS_DIR, exist_ok=True)

# High-impact fonts
FONT_HEADLINE_PATH = os.path.join(FONTS_DIR, "BarlowCondensed-ExtraBold.ttf")
FONT_DESC_PATH = os.path.join(FONTS_DIR, "Inter-Bold.ttf")
FONT_FOOTER_PATH = r"C:\Windows\Fonts\segoeuib.ttf" if os.path.exists(r"C:\Windows\Fonts\segoeuib.ttf") else FONT_DESC_PATH

# Vibrant color palette for headline highlights (yellow, green, blue, orange, pink)
COLOR_PALETTE = [
    "#FFD600",  # Electric Yellow
    "#00E676",  # Neon Green
    "#00D2FF",  # Cyber Cyan / Electric Blue
    "#FF9100",  # Vibrant Tangerine / Orange
    "#FF3377",  # Hot Pink
    "#C084FC",  # Electric Violet
    "#22D3EE",  # Aqua Cyan
    "#A3E635",  # Lime Green
    "#FBBF24",  # Amber Gold
]

COLOR_BG = (0, 18, 38, 255) # Deep oceanic navy / black (#001226)

FEEDS = [
    ("Deadline Hollywood", "https://deadline.com/v/film/feed/"),
    ("Variety", "https://variety.com/v/film/feed/"),
    ("Collider", "https://collider.com/feed/category/movie-news/"),
    ("ScreenRant", "https://screenrant.com/feed/movie-news/"),
]

def clean_text(text):
    if not text:
        return ""
    text = html.unescape(text)
    text = re.sub(r'<[^>]+>', '', text)
    text = re.sub(r'\s+', ' ', text).strip()
    return text

def auto_highlight(headline):
    """Automatically wraps 2-3 prominent word clusters in asterisks if none present."""
    if '*' in headline:
        return headline
    
    words = headline.split()
    if len(words) <= 4:
        return f"*{headline}*"
    
    if len(words) >= 8:
        p1 = " ".join(words[:2])
        mid = " ".join(words[2:-3])
        p2 = " ".join(words[-3:])
        return f"*{p1}* {mid} *{p2}*"
    else:
        p1 = " ".join(words[:2])
        rest = " ".join(words[2:])
        return f"*{p1}* *{rest}*"

def parse_tokens(headline, color_theme="multicolor"):
    """
    Parses headline text into (word, color) tokens.
    Supports multicolor cycling across highlighted phrases!
    """
    headline = auto_highlight(headline).strip().upper()
    tokens = []
    parts = headline.split('*')
    color_idx = 0

    single_color = None
    if color_theme and color_theme.lower() in ('yellow', 'green', 'blue', 'orange', 'pink'):
        cmap = {
            'yellow': '#FFD600',
            'green':  '#00E676',
            'blue':   '#00D2FF',
            'orange': '#FF9100',
            'pink':   '#FF3366'
        }
        single_color = cmap[color_theme.lower()]

    for i, part in enumerate(parts):
        is_hl = (i % 2 == 1)
        words = part.split()
        if is_hl:
            color = single_color if single_color else COLOR_PALETTE[color_idx % len(COLOR_PALETTE)]
            color_idx += 1
            for w in words:
                tokens.append((w, color))
        else:
            for w in words:
                tokens.append((w, "#FFFFFF"))

    return tokens

def draw_footer_brand(canvas, text="VOX BULLETIN", y_pos=1285, width=1080):
    """Renders the centered VOX BULLETIN with glowing horizontal hairline dividers."""
    draw = ImageDraw.Draw(canvas)
    font_footer = ImageFont.truetype(FONT_FOOTER_PATH, 20)
    
    spaced = "   ".join(text.upper().split())
    bb = draw.textbbox((0, 0), spaced, font=font_footer)
    tw = bb[2] - bb[0]
    th = bb[3] - bb[1]
    tx = (width - tw) // 2
    
    # White brand text
    draw.text((tx, y_pos), spaced, fill=(255, 255, 255, 240), font=font_footer)
    
    # Divider lines
    line_len = 160
    gap = 26
    cy = y_pos + th // 2 + 1
    
    # Left line fading towards outside
    x_left_end = tx - gap
    x_left_start = max(40, x_left_end - line_len)
    for x in range(x_left_start, x_left_end):
        p = (x - x_left_start) / max(1, (x_left_end - x_left_start))
        alpha = int(25 + 215 * p)
        canvas.putpixel((x, cy), (255, 255, 255, alpha))
        canvas.putpixel((x, cy + 1), (255, 255, 255, int(alpha * 0.7)))

    # Right line fading towards outside
    x_right_start = tx + tw + gap
    x_right_end = min(width - 40, x_right_start + line_len)
    for x in range(x_right_start, x_right_end):
        p = 1.0 - (x - x_right_start) / max(1, (x_right_end - x_right_start))
        alpha = int(25 + 215 * p)
        canvas.putpixel((x, cy), (255, 255, 255, alpha))
        canvas.putpixel((x, cy + 1), (255, 255, 255, int(alpha * 0.7)))

def fetch_feed_topics(limit=8):
    """Fetches real-time entertainment trending topics with high-res images."""
    stories = []
    headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'}

    for source_name, feed_url in FEEDS:
        try:
            resp = requests.get(feed_url, headers=headers, timeout=10)
            feed = feedparser.parse(resp.content)
            for entry in feed.entries[:4]:
                title = clean_text(entry.title)
                clean_title = re.sub(r'[\–\—\-]\s*[A-Z0-9\s,.\']+$', '', title).strip()
                clean_title = re.sub(r'^\[.*?\]\s*', '', clean_title).strip()
                clean_title = re.sub(r'[\'\"‘’“”]', '', clean_title)

                summary = clean_text(entry.get('summary', ''))
                if len(summary) > 160:
                    dot = summary.find('.', 100)
                    summary = summary[:dot+1] if dot != -1 else summary[:150] + '...'

                image_url = None
                if 'media_content' in entry and len(entry.media_content) > 0:
                    image_url = entry.media_content[0].get('url')
                if not image_url:
                    for link in entry.get('links', []):
                        if 'image' in link.get('type', ''):
                            image_url = link.get('href')
                            break
                if not image_url and 'enclosures' in entry:
                    for enc in entry.enclosures:
                        if 'image' in enc.get('type', ''):
                            image_url = enc.get('href')
                            break

                stories.append({
                    'source': source_name,
                    'raw_title': title,
                    'headline': auto_highlight(clean_title),
                    'description': summary.upper(),
                    'image_url': image_url or '/static/danny_boyle_bg.png',
                    'url': entry.get('link', '')
                })
        except Exception as e:
            print(f"Error fetching {source_name}: {e}")

    seen = set()
    unique_stories = []
    for s in stories:
        if s['headline'] not in seen:
            seen.add(s['headline'])
            unique_stories.append(s)
            if len(unique_stories) >= limit:
                break

    return unique_stories

def fetch_chatgpt_topic(api_key=None):
    """Generates an entertainment news post via OpenAI API."""
    key = (api_key or get_openai_key()).strip()
    if not key:
        return None, "No OpenAI API key provided."

    try:
        from openai import OpenAI
        client = OpenAI(api_key=key)
        res = client.chat.completions.create(
            model="gpt-4o-mini",
            messages=[
                {
                    "role": "system",
                    "content": (
                        "You are an entertainment news graphic editor for VOX BULLETIN. Return a JSON object with: "
                        "1. 'headline': 10-16 word punchy ALL-CAPS news headline. Wrap 2-3 key impact phrases in asterisks for multi-color highlighting (e.g. '*CHRIS EVANS* WOULD HAVE CHOSEN *TEAM IRON MAN* OVER *CAPTAIN AMERICA* IN CIVIL WAR!'). "
                        "2. 'description': 1-2 sentence uppercase crisp description (max 140 chars). "
                        "3. 'brand': 'VOX BULLETIN'. "
                        "4. 'image_query': search query to find the best movie still."
                    )
                },
                {
                    "role": "user",
                    "content": "Create 1 viral trending movie / entertainment news post right now."
                }
            ],
            response_format={"type": "json_object"},
            timeout=15
        )
        import json
        data = json.loads(res.choices[0].message.content)
        return data, None
    except Exception as e:
        return None, str(e)

def create_graphic(image_source, headline, description="", brand="VOX BULLETIN", color_theme="multicolor", width=1080, height=1350):
    """
    Renders an Instagram-ready social graphic matching the updated Vox Bulletin design:
    - Background image across upper ~58% of canvas
    - Seamless dark gradient fading into solid dark navy/charcoal (#001226)
    - Dynamic multi-colored headline (Green, Yellow, Blue, Orange, Pink)
    - Centered uppercase muted description
    - Bottom centered VOX BULLETIN with glowing hairline dividers
    - Dynamically balanced vertical spacing with zero awkward empty gaps
    """
    canvas = Image.new("RGBA", (width, height), COLOR_BG)

    # 1. Load Background Image
    bg = None
    if isinstance(image_source, str):
        if image_source.startswith("http://") or image_source.startswith("https://"):
            try:
                resp = requests.get(image_source, timeout=12)
                bg = Image.open(BytesIO(resp.content)).convert("RGBA")
            except Exception as e:
                print("Failed loading image URL:", e)
        elif os.path.exists(image_source):
            bg = Image.open(image_source).convert("RGBA")
    elif isinstance(image_source, Image.Image):
        bg = image_source.convert("RGBA")

    if bg is None:
        default_img = os.path.join(STATIC_DIR, "danny_boyle_bg.png")
        if not os.path.exists(default_img):
            default_img = os.path.join(STATIC_DIR, "sample-photo.png")
        bg = Image.open(default_img).convert("RGBA")

    # Fit image to upper ~58% of canvas
    img_target_h = int(height * 0.58)
    scale = max(width / bg.width, img_target_h / bg.height)
    nw, nh = int(bg.width * scale), int(bg.height * scale)
    bg = bg.resize((nw, nh), Image.Resampling.LANCZOS)
    crop_x = (nw - width) // 2
    bg = bg.crop((crop_x, 0, crop_x + width, img_target_h))

    canvas.paste(bg, (0, 0))

    # 2. Smooth gradient fade starting around 34% down to 54%
    overlay = Image.new("RGBA", (width, height), (0, 0, 0, 0))
    draw_ov = ImageDraw.Draw(overlay)
    grad_start = int(height * 0.34)
    grad_end = int(height * 0.54)
    for y in range(grad_start, height):
        if y < grad_end:
            p = (y - grad_start) / (grad_end - grad_start)
            alpha = int(255 * (p ** 2.0))
        else:
            alpha = 255
        draw_ov.line([(0, y), (width, y)], fill=(0, 18, 38, alpha))

    canvas = Image.alpha_composite(canvas, overlay)
    draw = ImageDraw.Draw(canvas)

    # 3. Dynamic Headline Sizing based on length
    clean_hl = clean_text(headline).replace('*', '').strip()
    words = clean_hl.split()
    word_count = len(words)

    if word_count <= 8:
        hl_size = 76
        desc_size = 24
        gap = 36
    elif word_count <= 14:
        hl_size = 68
        desc_size = 22
        gap = 30
    else:
        hl_size = 60
        desc_size = 20
        gap = 24

    font_hl_path = FONT_HEADLINE_PATH if os.path.exists(FONT_HEADLINE_PATH) else r"C:\Windows\Fonts\ArialNovaCond-Bold.ttf"
    if not os.path.exists(font_hl_path):
        font_hl_path = r"C:\Windows\Fonts\ARIALNB.TTF"
    if not os.path.exists(font_hl_path):
        font_hl_path = r"C:\Windows\Fonts\impact.ttf"

    font_desc_path = FONT_DESC_PATH if os.path.exists(FONT_DESC_PATH) else r"C:\Windows\Fonts\segoeui.ttf"
    if not os.path.exists(font_desc_path):
        font_desc_path = r"C:\Windows\Fonts\arial.ttf"

    font_hl = ImageFont.truetype(font_hl_path, hl_size)
    font_desc = ImageFont.truetype(font_desc_path, desc_size)

    # 4. Token Parsing & Line Wrapping
    tokens = parse_tokens(headline, color_theme=color_theme)
    max_line_w = width - 120
    space_w = draw.textbbox((0, 0), " ", font=font_hl)[2] - draw.textbbox((0, 0), " ", font=font_hl)[0]

    lines = []
    cur_line = []
    cur_w = 0

    for word, color in tokens:
        w_bb = draw.textbbox((0, 0), word, font=font_hl)
        w_len = w_bb[2] - w_bb[0]
        test_w = cur_w + (space_w if cur_line else 0) + w_len
        if test_w > max_line_w and cur_line:
            lines.append(cur_line)
            cur_line = [(word, color, w_len)]
            cur_w = w_len
        else:
            cur_line.append((word, color, w_len))
            cur_w = test_w
    if cur_line:
        lines.append(cur_line)

    line_h = int(hl_size * 1.15)
    total_hl_h = len(lines) * line_h

    # 5. Wrap Description
    desc_lines = []
    if description:
        d_words = clean_text(description).upper().split()
        d_cur = []
        for wd in d_words:
            test = " ".join(d_cur + [wd])
            bb = draw.textbbox((0, 0), test, font=font_desc)
            if bb[2] - bb[0] > (width - 160):
                if d_cur: desc_lines.append(" ".join(d_cur))
                d_cur = [wd]
            else:
                d_cur.append(wd)
        if d_cur: desc_lines.append(" ".join(d_cur))

    desc_line_h = int(desc_size * 1.5)
    total_desc_h = len(desc_lines[:5]) * desc_line_h

    total_content_h = total_hl_h + (gap if description else 0) + total_desc_h

    # 6. Perfect Dynamic Vertical Balancing
    avail_top = int(height * 0.47)
    avail_bottom = height - 90
    avail_space = avail_bottom - avail_top

    # Center content vertically within the available dark lower zone
    start_y = avail_top + max(10, (avail_space - total_content_h) // 2)

    # Render Headline
    y_hl = start_y
    for line_tokens in lines:
        line_total_w = sum(t[2] for t in line_tokens) + space_w * (len(line_tokens) - 1)
        cur_x = (width - line_total_w) // 2
        for word, color, w_len in line_tokens:
            draw.text((cur_x, y_hl), word, fill=color, font=font_hl)
            cur_x += w_len + space_w
        y_hl += line_h

    # Render Description
    y_desc = y_hl + gap
    for d_line in desc_lines[:4]:
        bb = draw.textbbox((0, 0), d_line, font=font_desc)
        dw = bb[2] - bb[0]
        draw.text(((width - dw) // 2, y_desc), d_line, fill="#9CA3AF", font=font_desc)
        y_desc += desc_line_h

    # 7. Render VOX BULLETIN Bottom Footer Line
    draw_footer_brand(canvas, text=brand or "VOX BULLETIN", y_pos=height - 65, width=width)

    return canvas
