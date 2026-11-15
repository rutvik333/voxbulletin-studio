import os
import requests
from PIL import Image, ImageDraw, ImageFont

fonts_dir = r"C:\Users\Rutvik Pagi\Documents\instagram\facebook-post-extractor\static\fonts"
font_hl_path = os.path.join(fonts_dir, "BarlowCondensed-ExtraBold.ttf")
font_desc_path = os.path.join(fonts_dir, "Inter-Bold.ttf")
font_pill_path = os.path.join(fonts_dir, "BarlowCondensed-ExtraBold.ttf")

w, h = 1080, 1440
bg_color = (14, 15, 19, 255) # #0e0f13

canvas = Image.new("RGBA", (w, h), bg_color)

# Try loading background image
bg_path = r"C:\Users\Rutvik Pagi\Documents\instagram\facebook-post-extractor\static\sample-photo.png"
bg = Image.open(bg_path).convert("RGBA")

# Target height for top image area ~52% of canvas
img_target_h = int(h * 0.52)
scale = max(w / bg.width, img_target_h / bg.height)
nw, nh = int(bg.width * scale), int(bg.height * scale)
bg = bg.resize((nw, nh), Image.Resampling.LANCZOS)
crop_x = (nw - w) // 2
bg = bg.crop((crop_x, 0, crop_x + w, img_target_h))

canvas.paste(bg, (0, 0))

# Smooth gradient fading from 32% to 50% into solid #0e0f13
overlay = Image.new("RGBA", (w, h), (0, 0, 0, 0))
draw_ov = ImageDraw.Draw(overlay)

grad_start = int(h * 0.30)
grad_end = int(h * 0.50)

for y in range(grad_start, h):
    if y < grad_end:
        p = (y - grad_start) / (grad_end - grad_start)
        alpha = int(255 * (p ** 2.2))
    else:
        alpha = 255
    draw_ov.line([(0, y), (w, y)], fill=(14, 15, 19, alpha))

canvas = Image.alpha_composite(canvas, overlay)
draw = ImageDraw.Draw(canvas)

# Pill badge: VOXBULLETIN
pill_text = "VOXBULLETIN"
font_pill = ImageFont.truetype(font_pill_path, 26)
pill_bb = draw.textbbox((0, 0), pill_text, font=font_pill)
pw = pill_bb[2] - pill_bb[0]
ph = pill_bb[3] - pill_bb[1]

pill_pad_x = 24
pill_pad_y = 10
pill_w = pw + pill_pad_x * 2
pill_h = ph + pill_pad_y * 2
pill_x = (w - pill_w) // 2
pill_y = int(h * 0.51)

draw.rounded_rectangle(
    [(pill_x, pill_y), (pill_x + pill_w, pill_y + pill_h)],
    radius=pill_h // 2,
    fill=(25, 30, 42, 240),
    outline=(60, 72, 98, 200),
    width=2
)
draw.text((pill_x + pill_pad_x, pill_y + pill_pad_y - 2), pill_text, fill="#F59E0B", font=font_pill)

# Headline
headline_lines = [
    [("CHRIS EVANS ADMITS HE ", False), ("PERSONALLY", True)],
    [("WOULD HAVE CHOSEN ", False), ("TEAM IRON MAN", True)],
    [("OVER ", False), ("CAPTAIN AMERICA ", True), ("IN THEIR", False)],
    [("LEGENDARY ", False), ("CIVIL WAR ", True), ("DEBATE!", False)]
]

font_hl = ImageFont.truetype(font_hl_path, 66)
y_hl = pill_y + pill_h + 30

for line_parts in headline_lines:
    total_lw = 0
    part_widths = []
    for text, hl in line_parts:
        bb = draw.textbbox((0, 0), text, font=font_hl)
        lw = bb[2] - bb[0]
        part_widths.append(lw)
        total_lw += lw
    
    cur_x = (w - total_lw) // 2
    for (text, hl), lw in zip(line_parts, part_widths):
        color = "#F59E0B" if hl else "#FFFFFF"
        draw.text((cur_x, y_hl), text, fill=color, font=font_hl)
        cur_x += lw
    y_hl += 74

# Description
desc = "CHRIS EVANS SURPRISED MCU FANS BY ADMITTING HIS TRUE ALLEGIANCE IN THE 'CIVIL WAR' DEBATE: \"ME PERSONALLY, I MIGHT BE TEAM IRON MAN. I KNOW THAT'S PROBABLY CONTROVERSIAL TO SAY.\""
font_desc = ImageFont.truetype(font_desc_path, 21)

y_desc = y_hl + 30
words = desc.split()
desc_lines = []
cur = []
for wd in words:
    test = " ".join(cur + [wd])
    bb = draw.textbbox((0, 0), test, font=font_desc)
    if bb[2] - bb[0] > (w - 180):
        if cur:
            desc_lines.append(" ".join(cur))
        cur = [wd]
    else:
        cur.append(wd)
if cur:
    desc_lines.append(" ".join(cur))

for d_line in desc_lines[:4]:
    bb = draw.textbbox((0, 0), d_line, font=font_desc)
    dw = bb[2] - bb[0]
    draw.text(((w - dw) // 2, y_desc), d_line, fill="#9CA3AF", font=font_desc)
    y_desc += 32

out = r"C:\Users\Rutvik Pagi\Documents\instagram\facebook-post-extractor\static\test_new_style_output.png"
canvas.save(out)
print("New style output saved to:", out)

