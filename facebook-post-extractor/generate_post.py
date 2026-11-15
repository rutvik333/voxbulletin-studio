"""
VOX BULLETIN — Entertainment News Post Generator (CLI)
Usage:
    python generate_post.py --auto
    python generate_post.py --color green
    python generate_post.py --headline "*CHRIS PRATT* TALKS *GUARDIANS 4* WITH *MARVEL*!"
"""

import os
import sys
import argparse
import trending_generator

def main():
    parser = argparse.ArgumentParser(description="Generate Instagram-ready entertainment news graphic (Vox Bulletin style)")
    parser.add_argument("--headline", help="Headline text (wrap words in *asterisks* for highlights)", default=None)
    parser.add_argument("--desc", help="Description / summary text", default=None)
    parser.add_argument("--image", help="Image URL or local file path", default=None)
    parser.add_argument("--brand", help="Brand name at footer", default="VOX BULLETIN")
    parser.add_argument("--color", help="Highlight color: multicolor, green, blue, yellow, orange, pink", default="multicolor")
    parser.add_argument("--output", help="Output PNG path", default="vox-bulletin-post.png")
    parser.add_argument("--auto", action="store_true", help="Automatically pull latest trending entertainment story")

    args = parser.parse_args()

    headline = args.headline
    desc = args.desc
    image_src = args.image
    brand = args.brand
    color = args.color

    if not headline or args.auto:
        print("🔍 Fetching live trending entertainment news...")
        stories = trending_generator.fetch_feed_topics(limit=5)
        if not stories:
            print("⚠️ Could not fetch feeds. Using default sample story.")
            headline = "*DANNY BOYLE* TALKS INK, THE COMMON ARCHITECTURE OF HIS MOVIES AND MORE *28 YEARS LATER* AS HE RECEIVES THE *DEADLINE LEGEND AWARD*"
            desc = "DANNY BOYLE SAYS HE USED TO BELIEVE THAT HE NEVER MADE THE SAME FILM TWICE, THUS A FILMOGRAPHY THAT INCLUDES HIT MOVIES ACROSS GENRES."
            image_src = os.path.join(trending_generator.STATIC_DIR, "danny_boyle_bg.png")
        else:
            story = stories[0]
            print(f"📰 Top Story ({story['source']}): {story['raw_title']}")
            headline = story['headline']
            desc = story['description']
            image_src = story['image_url']

    print(f"\n🎨 Rendering Vox Bulletin Graphic:")
    print(f"   Brand:       {brand}")
    print(f"   Headline:    {headline}")
    print(f"   Color Theme: {color}")
    print(f"   Description: {desc}")
    print(f"   Image:       {image_src}")

    canvas = trending_generator.create_graphic(
        image_source=image_src,
        headline=headline,
        description=desc or "",
        brand=brand,
        color_theme=color
    )

    out_file = os.path.abspath(args.output)
    canvas.save(out_file)
    print(f"\n✅ Ready-to-post graphic saved successfully at:")
    print(f"   {out_file}\n")

if __name__ == "__main__":
    main()
