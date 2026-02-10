# Google AdSense Setup Guide

To start earning money, you need to replace the placeholder code with your real **Publisher ID** from Google.

## Step 1: Get your ID
1. Go to [Google AdSense](https://adsense.google.com).
2. Copy your **Publisher ID**. It looks like: `pub-1234567890123456`.

## Step 2: Update the Validation File
This proves to Google that you own the site.

1. Open the file: `public/ads.txt`
2. Replace the entire content with the line Google gives you.
   - It usually looks like: `google.com, pub-1234567890123456, DIRECT, f08c47fec0942fa0`

## Step 3: Update the Ad Script
This makes the banners appear.

1. Open: `templates/partials/_cookie_consent.html.twig`
2. Go to the bottom (line ~31).
3. Find: `client=ca-pub-0000000000000000`
4. Replace the zeros with your ID: `client=ca-pub-1234567890123456`

## Step 4: Update Ad Banners
Update the specific ad slots to track earnings correctly.

1. **Header Banner**: `templates/partials/_ad_header.html.twig`
   - Replace `data-ad-client="ca-pub-0000000000000000"` with your ID.
2. **Sidebar Banner**: `templates/partials/_ad_sidebar.html.twig`
   - Replace `data-ad-client="ca-pub-0000000000000000"` with your ID.
3. **In-Content Banner**: `templates/partials/_ad_content.html.twig`
   - Replace `data-ad-client="ca-pub-0000000000000000"` with your ID.

---

**That's it!** Once deployed to a real domain (e.g., `.com`), Google will check `ads.txt` and approve your site within a few days.
