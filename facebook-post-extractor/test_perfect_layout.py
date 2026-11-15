import os
import re
from PIL import Image, ImageDraw, ImageFont

FONTS_DIR = r"C:\Users\Rutvik Pagi\Documents\instagram\facebook-post-extractor\static\fonts"
FONT_HL = os.path.join(FONTS_DIR, "BarlowCondensed-ExtraBold.ttf")
FONT_DESC = os.path.join(FONTS_DIR, "Inter-Bold.ttf")
FONT_FOOTER = r"C:\Windows\Fonts\segoeuib.ttf" if os.path.exists(r"C:\Windows\Fonts\segoeuib.ttf") else FONT_DESC

# Vibrant color palette for headline highlights
COLOR_PALETTE = [
    "#FFD600",  # Electric Yellow
    "#00E676",  # Neon Green
    "#00D2FF",  # Electric Cyan / Blue
    "#FF9100",  # Vibrant Orange
    "#FF3366",  # Hot Coral / Pink
]

def auto_highlight(headline):
    """Automatically wraps 2-3 prominent word clusters in asterisks if none present."""
    if '*' in headline:
        return headline
    
    words = headline.split()
    if len(words) <= 4:
        return f"*{headline}*"
    
    # Check for numbers + word (e.g. '28 YEARS LATER')
    # Or highlight first 2-3 words and last 2-4 words
    # Example: DANNY BOYLE (first 2 words) and DEADLINE LEGEND AWARD (last 3 words)
    if len(words) >= 8:
        p1 = " ".join(words[:2])
        mid = " ".join(words[2:-3])
        p2 = " ".join(words[-3:])
        return f"*{p1}* {mid} *{p2}*"
    else:
        p1 = " ".join(words[:2])
        rest = " ".join(words[2:])
        return f"*{p1}* *{rest}*"

def parse_tokens_multicolor(headline, chosen_colors=None):
    """
    Parses headline text into tokens with specific colors.
    Alternates through chosen_colors for each highlighted segment!
    """
    colors = chosen_colors or COLOR_PALETTE
    headline = auto_highlight(headline).strip().upper()

    tokens = []
    parts = headline.split('*')
    color_idx = 0

    for i, part in enumerate(parts):
        is_hl = (i % 2 == 1)
        words = part.split()
        if is_hl:
            hl_color = colors[color_idx % len(colors)]
            color_idx += 1
            for w in words:
                tokens.append((w, hl_color))
        else:
            for w in words:
                tokens.append((w, "#FFFFFF"))
                
    return tokens

def draw_footer_line(canvas, text="V O X   B U L L E T I N", y_pos=1290, width=1080):
    draw = ImageDraw.Draw(canvas)
    font_footer = ImageFont.truetype(FONT_FOOTER, 20)
    
    # Spaced brand text
    spaced = "   ".join(text.split())
    bb = draw.textbbox((0, 0), spaced, font=font_footer)
    tw = bb[2] - bb[0]
    th = bb[3] - bb[1]
    tx = (width - tw) // 2
    
    # Text in crisp white
    draw.text((tx, y_pos), spaced, fill=(255, 255, 255, 240), font=font_footer)
    
    # Divider lines with subtle fade
    line_len = 160
    gap = 26
    cy = y_pos + th // 2 + 1
    
    # Left line
    x_left_end = tx - gap
    x_left_start = max(40, x_left_end - line_len)
    for x in range(x_left_start, x_left_end):
        p = (x - x_left_start) / max(1, (x_left_end - x_left_start))
        alpha = int(25 + 215 * p)
        canvas.putpixel((x, cy), (255, 255, 255, alpha))
        canvas.putpixel((x, cy + 1), (255, 255, 255, int(alpha * 0.7)))

    # Right line
    x_right_start = tx + tw + gap
    x_right_end = min(width - 40, x_right_start + line_len)
    for x in range(x_right_start, x_right_end):
        p = 1.0 - (x - x_right_start) / max(1, (x_right_end - x_right_start))
        alpha = int(25 + 215 * p)
        canvas.putpixel((x, cy), (255, 255, 255, alpha))
        canvas.putpixel((x, cy + 1), (255, 255, 255, int(alpha * 0.7)))

def render_post_card(image_path, headline, description, output_path, highlight_mode="multicolor", width=1080, height=1350):
    # Base deep oceanic navy / charcoal
    bg_color = (0, 18, 38, 255) # #001226
    canvas = Image.new("RGBA", (width, height), bg_color)
    
    # 1. Background Image (upper 58% of canvas)
    if os.path.exists(image_path):
        bg = Image.open(image_path).convert("RGBA")
        img_target_h = int(height * 0.58)
        scale = max(width / bg.width, img_target_h / bg.height)
        nw, nh = int(bg.width * scale), int(bg.height * scale)
        bg = bg.resize((nw, nh), Image.Resampling.LANCZOS)
        crop_x = (nw - width) // 2
        bg = bg.crop((crop_x, 0, crop_x + width, img_target_h))
        canvas.paste(bg, (0, 0))

    # 2. Smooth gradient fade starting around 35% down to 55%
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

    # 3. Dynamic Headline & Typography Sizing
    words = headline.split()
    word_count = len(words)
    
    # Scale font size dynamically based on length
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

    font_hl = ImageFont.truetype(FONT_HL, hl_size)
    font_desc = ImageFont.truetype(FONT_DESC, desc_size)

    # Parse tokens with colors
    tokens = parse_tokens_multicolor(headline)

    # Wrap into lines
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

    # Wrap description
    desc_lines = []
    if description:
        d_words = description.upper().split()
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
    total_desc_h = len(desc_lines[:4]) * desc_line_h

    # Total content height
    total_content_h = total_hl_h + (gap if description else 0) + total_desc_h

    # 4. Spacing & Balancing:
    # Available area is from grad_end (approx 52% of h) to footer (h - 90)
    avail_top = int(height * 0.48)
    avail_bottom = height - 90
    avail_space = avail_bottom - avail_top

    # Center content vertically in the available dark space
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

    # 5. Draw Footer Line at bottom center
    draw_footer_line(canvas, text="V O X   B U L L E T I N", y_pos=height - 65, width=width)

    canvas.save(output_path)
    print(f"Post saved: {output_path} (Headline lines: {len(lines)}, Start Y: {start_y})")

if __name__ == "__main__":
    static_dir = r"C:\Users\Rutvik Pagi\Documents\instagram\facebook-post-extractor\static"
    
    # 1. Danny Boyle story (the one from Image 1) with green, blue, yellow colors
    render_post_card(
        image_path=os.path.join(static_dir, "sample-photo.png"),
        headline="*DANNY BOYLE* TALKS INK, THE COMMON ARCHITECTURE OF HIS MOVIES AND MORE *28 YEARS LATER* AS HE RECEIVES THE *DEADLINE LEGEND AWARD*",
        description="Danny Boyle says he used to believe that he never made the same film twice, thus a filmography that includes hit movies across genres like Shallow Grave, Trainspotting, 127 Hours, The Beach, 28 Days Later and Slumdog Millionaire.",
        output_path=os.path.join(static_dir, "danny_boyle_perfect.png")
    )
    
    # 2. Short story with zero bottom gap
    render_post_card(
        image_path=os.path.join(static_dir, "civil_war_bg.png"),
        headline="*CHRIS PRATT* CONFIRMS *GUARDIANS 4* TALKS WITH *MARVEL STUDIOS*!",
        description="Chris Pratt says he would love to return as Star-Lord under James Gunn's vision.",
        output_path=os.path.join(static_dir, "short_story_perfect.png")
    )

