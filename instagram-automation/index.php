<?php
session_start();
$authed=!empty($_SESSION['logged_in']);
$csrf=$_SESSION['csrf']??'';
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Vox Bulletin Studio</title><link rel="icon" type="image/x-icon" href="favicon.ico"><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@700;800;900&family=DM+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"><style>
:root{font-family:'DM Sans',Arial,sans-serif;color:#e9eef9;background:#080e1c}*{box-sizing:border-box}body{margin:0}button,input,textarea{font:inherit}button{cursor:pointer}button:disabled{opacity:.5;cursor:not-allowed}.shell{display:grid;grid-template-columns:235px minmax(0,1fr);min-height:100vh}.sidebar{border-right:1px solid #233048;background:#0b1425;padding:28px 20px}.brand{font-family:'Barlow Condensed';font-size:30px;font-weight:900;letter-spacing:1.5px;color:#fff}.brand span{color:#ffd12a}.sub{color:#8291aa;font-size:12px;letter-spacing:2px}.nav{margin-top:48px}.nav button{width:100%;background:transparent;border:0;color:#a9b7d0;text-align:left;padding:14px;border-radius:10px;margin-bottom:5px;font-weight:600}.nav button.active,.nav button:hover{background:#172641;color:white}.main{padding:34px;max-width:1500px;width:100%;margin:auto}.top{display:flex;justify-content:space-between;align-items:center;gap:15px;flex-wrap:wrap}.top h1{font-size:28px;margin:0 0 5px}.muted{color:#91a1ba;font-size:13px}.pill{padding:9px 13px;background:#123528;border:1px solid #245a45;color:#71e5aa;border-radius:30px;font-size:12px;font-weight:700}.layout{display:grid;grid-template-columns:minmax(320px,1fr) minmax(340px,1fr);gap:24px;margin-top:28px}.panel{border:1px solid #24324b;background:#101b2d;border-radius:18px;padding:23px;min-width:0}.panel h2{margin:0 0 8px;font-size:17px}.panel .muted{margin-bottom:20px}.field{margin:16px 0}label{display:block;font-size:12px;font-weight:700;color:#a6b5cd;margin-bottom:8px}input,textarea,select{width:100%;border:1px solid #33435d;background:#0b1527;color:#f1f5ff;border-radius:10px;padding:12px;outline:none}textarea{resize:vertical;min-height:86px}input:focus,textarea:focus{border-color:#5784ff}.btn{border:0;border-radius:10px;background:#f6c92e;color:#101726;font-weight:800;padding:12px 18px}.btn.alt{background:#24334c;color:#e9f1ff}.btn.blue{background:#447aff;color:white}.btn.small{padding:8px 11px;font-size:12px}.actions{display:flex;gap:9px;flex-wrap:wrap}.stories{max-height:480px;overflow-y:auto;display:grid;gap:10px;margin:14px 0;padding-right:4px}.story{display:flex;align-items:center;gap:13px;padding:10px 12px;background:#0b1527;border:1px solid #24344d;border-radius:12px;text-align:left;color:#dce5f7;cursor:pointer;transition:all .18s ease;width:100%}.story:hover,.story.selected{border-color:#f6c92e;background:#13223b}.story-thumb{width:68px;height:68px;border-radius:8px;object-fit:cover;background:#16243a;flex-shrink:0;border:1px solid #273a56}.story-body{flex:1;min-width:0}.story-meta{display:flex;align-items:center;gap:8px;margin-bottom:4px}.story .idx{color:#f6c92e;font-size:11px;font-weight:700}.story-date{font-size:10px;font-weight:700;color:#71e5aa;background:#102e21;border:1px solid #1c523b;padding:2px 7px;border-radius:10px}.story-title{font-size:13px;font-weight:600;color:#e7effc;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}.preview-wrap{background:#080f1d;border:1px solid #2d3b55;border-radius:14px;padding:15px;display:flex;justify-content:center}.preview-wrap canvas{width:min(100%,380px);height:auto;aspect-ratio:4/5;box-shadow:0 12px 35px #0006}.notice{margin-top:18px;background:#132b40;border:1px solid #285372;color:#bde3fa;border-radius:10px;padding:12px;font-size:13px;line-height:1.5}.error{background:#3c1d28;border-color:#79354a;color:#ffc6d0}.hide{display:none!important}.login{max-width:430px;margin:12vh auto;background:#101b2d;border:1px solid #283a53;padding:35px;border-radius:18px}.login h1{margin:5px 0}.gridposts{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:17px;margin-top:20px}
.post{border:1px solid #2d3b55;border-radius:12px;overflow:hidden;background:#101b2d;position:relative;transition:border-color .15s,box-shadow .15s;display:flex;flex-direction:column}
.post.selected{border-color:#38bdf8;box-shadow:0 0 0 2px rgba(56,189,248,.35)}
.post img{width:100%;aspect-ratio:4/5;object-fit:cover;display:block}
.post>div{padding:14px;display:flex;flex-direction:column;flex:1}
.post h3{font-size:15px;margin:0 0 10px;line-height:1.35}
.post a{color:#ffd12a;font-size:12px;font-weight:600}
.post-check-wrap{position:absolute;top:10px;left:10px;z-index:4;background:rgba(8,14,28,.82);backdrop-filter:blur(6px);padding:6px;border-radius:8px;border:1px solid rgba(255,255,255,.2);display:flex;align-items:center;cursor:pointer}
.post-check-wrap input{cursor:pointer;width:18px;height:18px;accent-color:#38bdf8;margin:0}
.post-card-footer{display:flex;justify-content:space-between;align-items:center;margin-top:auto;padding-top:12px;border-top:1px solid #1c2c47}
.btn-del-single{background:transparent;border:1px solid rgba(239,68,68,.4);color:#f87171;padding:4px 8px;border-radius:6px;font-size:11px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:4px;transition:all .15s}
.btn-del-single:hover{background:#ef4444;color:#fff;border-color:#ef4444}
.btn-danger{background:#7f1d1d;border:1px solid #b91c1c;color:#fca5a5;font-weight:700;padding:7px 14px;border-radius:7px;cursor:pointer;display:inline-flex;align-items:center;gap:6px;font-size:13px;transition:all .15s}
.btn-danger:hover:not(:disabled){background:#991b1b;color:#fff}
.btn-danger:disabled{opacity:.4;cursor:not-allowed}
.library-toolbar{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-top:20px;padding:12px 16px;background:#0f1a2e;border:1px solid #20314d;border-radius:10px}.status{margin:15px 0;font-size:13px;color:#a9c3e9;min-height:20px}.inline{display:flex;gap:8px;align-items:center}.inline input{flex:1}.divider{height:1px;background:#24344d;margin:20px 0}@media(max-width:1000px){.layout{grid-template-columns:1fr}.shell{grid-template-columns:1fr}.sidebar{padding:15px 20px;display:flex;align-items:center;justify-content:space-between}.nav{margin:0;display:flex}.nav button{margin:0}.main{padding:20px}}

/* Modals */
.modal-overlay{position:fixed;inset:0;background:rgba(4,8,18,0.85);backdrop-filter:blur(6px);z-index:999;display:flex;align-items:center;justify-content:center;padding:16px}
.modal-card{background:#101b2d;border:1px solid #283a55;border-radius:20px;width:100%;max-width:760px;max-height:92vh;overflow-y:auto;box-shadow:0 25px 60px rgba(0,0,0,0.6);position:relative}
.modal-header{padding:22px 26px 16px;border-bottom:1px solid #1f2e46;display:flex;justify-content:space-between;align-items:center}
.modal-header h2{margin:0;font-size:20px;font-weight:700}
.modal-close{background:transparent;border:0;color:#8e9fb9;font-size:22px;cursor:pointer;padding:4px 8px;border-radius:8px;line-height:1}
.modal-close:hover{color:#fff;background:#1a283f}
.modal-body{padding:24px 26px}

/* Channels Grid (matching Image 1) */
.channels-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-top:14px}
@media(max-width:720px){.channels-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:480px){.channels-grid{grid-template-columns:1fr}}
.channel-btn{background:#0b1527;border:1px solid #22324b;border-radius:14px;padding:20px 14px;display:flex;flex-direction:column;align-items:center;text-align:center;cursor:pointer;transition:all .18s ease;position:relative;color:#f0f4fc;width:100%}
.channel-btn:hover{border-color:#5784ff;background:#13223b;transform:translateY(-2px)}
.channel-btn.connected{border-color:#2a6349;background:#0d231a}
.channel-badge-new{position:absolute;top:10px;right:10px;background:#ff6719;color:#fff;font-size:10px;font-weight:800;padding:2px 7px;border-radius:8px;letter-spacing:0.5px}
.channel-badge-connected{position:absolute;top:10px;right:10px;background:#123e2b;border:1px solid #256649;color:#75e8b0;font-size:10px;font-weight:800;padding:2px 7px;border-radius:8px}
.channel-icon-wrap{width:54px;height:54px;border-radius:14px;display:flex;align-items:center;justify-content:center;margin-bottom:12px;flex-shrink:0}
.channel-name{font-size:15px;font-weight:700;margin-bottom:3px}
.channel-sub{font-size:11px;color:#8b9bb2}

/* Consent Modal (matching Images 2, 3, 4, 5) */
.consent-wrap{max-width:540px;margin:auto}
.consent-brand-row{display:flex;align-items:center;justify-content:center;gap:12px;margin-bottom:18px}
.consent-box{background:#0b1527;border:1px solid #24344d;border-radius:14px;padding:16px 20px;margin-bottom:18px}
.consent-item{display:flex;align-items:center;justify-content:space-between;gap:15px;padding:11px 0;border-bottom:1px solid #1a283e}
.consent-item:last-child{border-bottom:0}
.consent-item-label{font-size:13px;font-weight:600;color:#dce5f7}
.consent-item-sub{font-size:11px;color:#8596b0}

/* Switch */
.switch{position:relative;display:inline-block;width:44px;height:24px;flex-shrink:0}
.switch input{opacity:0;width:0;height:0}
.slider{position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background-color:#24334c;transition:.2s;border-radius:24px}
.slider:before{position:absolute;content:"";height:18px;width:18px;left:3px;bottom:3px;background-color:white;transition:.2s;border-radius:50%}
input:checked + .slider{background-color:#447aff}
input:checked + .slider:before{transform:translateX(20px)}

/* Cross-post selection in Create Page */
.crosspost-box{background:#0b1527;border:1px solid #24344d;border-radius:14px;padding:18px;margin:18px 0}
.crosspost-title{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px}
.crosspost-title h3{margin:0;font-size:14px;font-weight:700;color:#e9f0fd;display:flex;align-items:center;gap:8px}
.crosspost-list{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:10px}
.crosspost-item{display:flex;align-items:center;gap:10px;padding:10px 12px;background:#101d32;border:1px solid #23344e;border-radius:10px;cursor:pointer;transition:all .15s}
.crosspost-item:hover{border-color:#447aff}
.crosspost-item input[type="checkbox"]{width:18px;height:18px;accent-color:#447aff;cursor:pointer;flex-shrink:0}
.crosspost-item-info{flex:1;min-width:0}
.crosspost-item-name{font-size:12px;font-weight:700;color:#f0f4fc}
.crosspost-item-handle{font-size:11px;color:#8b9bb2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.btn-publish-all{background:linear-gradient(135deg,#00c853,#00e676);color:#041a0d;font-weight:900;font-size:14px;padding:13px 20px;border:0;border-radius:10px;display:inline-flex;align-items:center;gap:8px;box-shadow:0 8px 24px rgba(0,230,118,0.22);transition:all .18s ease;cursor:pointer}
.btn-publish-all:hover{filter:brightness(1.08);transform:translateY(-1px);box-shadow:0 12px 28px rgba(0,230,118,0.32)}
.pub-results{margin-top:14px;padding:14px;background:#0d231a;border:1px solid #1e553c;border-radius:12px;font-size:13px;color:#a3f3cc}
.pub-result-row{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:6px}
.pub-result-row:last-child{margin-bottom:0}
.pub-result-row a{color:#ffd12a;text-decoration:underline;font-weight:600}
.channel-conn-card{display:flex;align-items:center;justify-content:space-between;padding:14px 16px;background:#0b1527;border:1px solid #24344d;border-radius:12px;margin-bottom:10px}
.pill-ch{display:inline-block;padding:2px 8px;border-radius:6px;font-size:10px;font-weight:700;background:#1a2a44;color:#a8c4ee;margin-right:4px;margin-top:4px}


.buffer-sync-banner{background:linear-gradient(135deg,#0c1d38 0%,#132b52 100%);border:1px solid #3b82f6;border-radius:12px;padding:16px;margin-bottom:16px}
.avatar-img{width:28px;height:28px;border-radius:50%;object-fit:cover;border:1px solid rgba(255,255,255,0.2)}
.live-api-tag{font-size:10px;padding:2px 8px;border-radius:4px;font-weight:700;letter-spacing:0.5px;background:#103b2b;color:#4ade80;border:1px solid #166534}
.key-card{background:#0c192c;border:1px solid #1e3556;border-radius:12px;padding:12px 14px;transition:all .15s ease}
.key-card:hover{border-color:#38bdf8}
.key-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px}
.key-badge{font-size:11px;font-weight:700;padding:3px 9px;border-radius:5px;background:#1a365d;color:#7dd3fc;border:1px solid #2b6cb0;letter-spacing:0.3px}
.key-badge.primary{background:#14532d;color:#86efac;border-color:#16a34a}
.key-actions{display:flex;gap:6px;align-items:center}
</style></head><body>
<div id="login" class="login <?= $authed?'hide':'' ?>"><div class="brand">VOX <span>BULLETIN</span></div><p class="sub">CONTENT STUDIO</p><h1>Welcome back</h1><p class="muted">Sign in to your private news creation dashboard.</p><form id="loginForm"><div class="field"><label>ADMIN PASSWORD</label><input id="password" type="password" required autocomplete="current-password"></div><button class="btn" style="width:100%">Sign in</button></form><div id="loginErr" class="status"></div></div>
<div id="app" class="shell <?= $authed?'':'hide' ?>"><aside class="sidebar"><div><div class="brand">VOX <span>BULLETIN</span></div><div class="sub">CONTENT STUDIO</div></div><div class="nav"><button id="navCreate" class="active" onclick="showPage('create')">✦ Create Post</button><button id="navLibrary" onclick="showPage('library')">▣ Post Library</button><button id="navAutomation" onclick="showPage('automation')">⚡ 3x/Day Auto-Post</button><button id="navSettings" onclick="showPage('settings')">⚙ API Settings</button><button onclick="logout()">↪ Logout</button></div></aside><main class="main"><div class="top"><div><h1 id="pageTitle">Create news post</h1><div class="muted">Discover, design and save social-ready entertainment posts.</div></div><div style="display:flex;gap:10px;align-items:center"><button class="btn alt small" onclick="showPage('settings')">⚙ Settings</button><span id="topPill" class="pill">● TODAY'S FEED</span></div></div><div id="createPage" class="layout"><section><div class="panel"><h2>01 / Discover news</h2><div class="muted">Today's top entertainment headlines with thumbnails. Select any story to auto-apply.</div><button type="button" class="btn blue" onclick="fetchNews()" id="fetchBtn">↻ Fetch today's news</button><div class="status" id="newsStatus">Click fetch to load articles.</div><div id="stories" class="stories"></div></div><div class="panel" style="margin-top:20px"><div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px"><h2>02 / Generate content</h2><span class="pill" style="background:#132b40;color:#38bdf8;border:1px solid #285372;font-size:11px;font-weight:700">DRAFT ONLY • DOES NOT PUBLISH</span></div><div class="muted">Gemini writes an original draft from the selected source excerpt. <strong>This does NOT publish to social media</strong> — publishing only happens when you explicitly click the green "🚀 Publish to Selected Channels" button in Section 03.</div><div class="field"><label>ORIGINAL ARTICLE TITLE</label><textarea id="sourceTitle" rows="2" readonly></textarea></div><div class="field"><label>POST HEADLINE</label><textarea id="headline" rows="3" placeholder="Your post headline"></textarea></div><div class="field"><label>SHORT DESCRIPTION</label><textarea id="description" rows="3" placeholder="Short summary of the news"></textarea></div><div class="field"><label>INSTAGRAM / FACEBOOK CAPTION (FULL STORY)</label><textarea id="caption" rows="4" placeholder="Ready-to-publish detailed caption for Instagram & Facebook"></textarea></div><div class="field"><div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px"><label style="margin-bottom:0">X / TWITTER POST <span style="font-size:11px;font-weight:400;color:#94a3b8">(STRICT 280-CHAR LIMIT)</span></label><span id="xCharCounter" style="font-size:12px;font-weight:700;color:#38bdf8">0 / 280</span></div><textarea id="captionX" rows="3" maxlength="280" placeholder="Concise post tailored for X / Twitter with #voxbulletin"></textarea><div style="display:flex;justify-content:space-between;align-items:center;margin-top:4px"><p class="muted" style="margin:0;font-size:11px">Auto-optimized for Twitter/X's 280-char limit with #voxbulletin.</p><button type="button" class="btn alt small" style="padding:2px 8px;font-size:11px" onclick="syncTwitterPost()">↻ Re-sync for X</button></div></div><div class="actions"><button type="button" class="btn blue" onclick="generate(event)" id="generateBtn">✦ Generate with Gemini (Draft Only)</button><button type="button" class="btn alt" onclick="render()">Refresh preview</button></div></div></section><section><div class="panel"><h2>03 / Design & export</h2><div class="muted">1080 × 1350 · Cinematic navy · Bold headline · Vox Bulletin branding</div><div class="preview-wrap"><canvas id="poster" width="1080" height="1350"></canvas></div>
<div class="quick-design-toolbar" style="background:#09192e;border:1px solid #1c3d69;border-radius:10px;padding:12px 14px;margin-bottom:16px">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
    <span style="font-size:12px;font-weight:700;color:#cbd5e1;display:flex;align-items:center;gap:6px"><span>🎨</span> QUICK DESIGN &amp; TYPOGRAPHY</span>
    <button type="button" class="btn alt small" style="padding:2px 8px;font-size:11px" onclick="showPage('settings')">⚙ Open All Settings</button>
  </div>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;font-size:12px">
    <div>
      <label style="font-size:11px;color:#94a3b8;margin-bottom:3px;display:block">TITLE FONT SIZE</label>
      <select id="quickHlFontSize" style="padding:5px 8px;font-size:12px;background:#0d1e36;color:#fff;border:1px solid #285372;border-radius:6px;width:100%" onchange="setQuickHlFont(this.value)">
        <option value="0">Auto (Dynamic 68px)</option>
        <option value="56">Small (56px)</option>
        <option value="64">Medium (64px)</option>
        <option value="72">Large (72px)</option>
        <option value="80">Extra Large (80px)</option>
      </select>
    </div>
    <div>
      <label style="font-size:11px;color:#94a3b8;margin-bottom:3px;display:block">DESC FONT SIZE</label>
      <select id="quickDescFontSize" style="padding:5px 8px;font-size:12px;background:#0d1e36;color:#fff;border:1px solid #285372;border-radius:6px;width:100%" onchange="setQuickDescFont(this.value)">
        <option value="0">Auto (Default 24px)</option>
        <option value="20">Compact (20px)</option>
        <option value="24">Standard (24px)</option>
        <option value="28">Medium (28px)</option>
        <option value="32">Large (32px)</option>
      </select>
    </div>
    <div>
      <label style="font-size:11px;color:#94a3b8;margin-bottom:3px;display:block">TOP LOGO POSITION</label>
      <select id="quickLogoAlign" style="padding:5px 8px;font-size:12px;background:#0d1e36;color:#fff;border:1px solid #285372;border-radius:6px;width:100%" onchange="setQuickLogoAlign(this.value)">
        <option value="top-right">Top Right (Default)</option>
        <option value="top-left">Top Left</option>
        <option value="top-center">Top Center</option>
        <option value="hidden">Hidden</option>
      </select>
    </div>
    <div>
      <label style="font-size:11px;color:#94a3b8;margin-bottom:3px;display:block">BOTTOM "VOX BULLETIN" BAR</label>
      <select id="quickFooterBrand" style="padding:5px 8px;font-size:12px;background:#0d1e36;color:#fff;border:1px solid #285372;border-radius:6px;width:100%" onchange="setQuickFooterBrand(this.value)">
        <option value="1">Visible (Dividers + Brand)</option>
        <option value="0">Hidden (Clean Minimal)</option>
      </select>
    </div>
  </div>
</div><div class="field"><label>TOPIC IMAGE (AUTO-APPLIED OR CUSTOM)</label><input type="file" id="photoInput" accept="image/png,image/jpeg,image/webp"><p class="muted">Topic image is automatically applied when selecting an article. You can also upload your own.</p><button type="button" class="btn alt small" onclick="loadSourceImage()">Reload source image</button></div><div class="field"><label>BRAND LOGO (OPTIONAL)</label><input type="file" id="logoInput" accept="image/png,image/jpeg,image/webp"></div>
<div class="crosspost-box">
  <div class="crosspost-title">
    <h3><span>🚀</span> Cross-Post to Social Accounts</h3>
    <button type="button" class="btn alt small" onclick="openChannelModal()">+ Connect Channels</button>
  </div>
  <p class="muted" style="margin:0 0 12px;font-size:12px">Check the social accounts you want to post this graphic and caption to:</p>
  <div id="crosspostList" class="crosspost-list"></div>
  <div id="pubStatus" class="notice hide" style="margin-top:14px"></div>
</div>
<div class="actions">
  <button type="button" class="btn-publish-all" onclick="publishSelected(event)" id="pubBtn">🚀 Publish to Selected Channels</button>
  <button type="button" class="btn" onclick="download()">↓ Download PNG</button>
  <button type="button" class="btn alt" onclick="shuffleTheme()">🎨 Shuffle Colors</button>
  <button type="button" class="btn alt" onclick="save()" id="saveBtn">💾 Save to Library</button>
</div>
<div id="status" class="notice">Select any news story from the list — title, description, and hero image will auto-apply instantly!</div></div></section></div><div id="libraryPage" class="hide">
  <div class="library-toolbar">
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
      <button type="button" class="btn blue small" onclick="loadPosts()">↻ Refresh library</button>
      <button type="button" class="btn alt small" onclick="toggleSelectAllPosts()" id="selectAllBtn">Select All</button>
      <button type="button" class="btn-danger" onclick="deleteSelectedPosts()" id="deleteSelectedBtn" disabled>
        🗑 Delete Selected (<span id="selectedCount">0</span>)
      </button>
    </div>
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
      <span id="postCountBadge" class="pill" style="font-size:12px;background:#152642;border-color:#233a61;color:#93c5fd">0 posts</span>
      <button type="button" class="btn alt small" onclick="cleanupWeeklyPosts()" title="Clean posts and images older than 7 days to free up disk storage">
        🧹 Run 7-Day Storage Cleanup
      </button>
    </div>
  </div>
  <div id="libraryStatus" class="notice hide" style="margin-top:14px"></div>
  <div id="postGrid" class="gridposts"></div>
</div><div id="automationPage" class="hide">
  <div class="panel" style="max-width:880px;margin:24px auto">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px">
      <div>
        <h2 style="font-size:22px;margin:0 0 6px">⚡ 3x/Day Autonomous Publishing Engine</h2>
        <div class="muted">Fully automated workflow: Fetches today's news &rarr; Prioritizes Marvel & DC &rarr; Gemini AI rewrite &rarr; Generates 1080×1350 branded graphic &rarr; Publishes live to Instagram, Facebook & X.</div>
      </div>
      <span class="pill" style="background:#103b2b;color:#4ade80;border:1px solid #166534">● SCHEDULER ACTIVE</span>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));gap:14px;margin-top:20px">
      <div style="background:#091c34;border:1px solid #1a3962;border-radius:10px;padding:16px">
        <div style="display:flex;align-items:center;gap:10px">
          <span style="font-size:24px">🌅</span>
          <div>
            <div style="font-weight:800;font-size:14px;color:#ffd12a">SLOT 01 / MORNING</div>
            <div style="font-size:18px;font-weight:800;color:#fff;margin-top:2px">9:00 AM</div>
          </div>
        </div>
        <div class="muted" style="font-size:11px;margin-top:8px">Top Marvel/DC breaking news from the morning feed.</div>
      </div>

      <div style="background:#091c34;border:1px solid #1a3962;border-radius:10px;padding:16px">
        <div style="display:flex;align-items:center;gap:10px">
          <span style="font-size:24px">☀️</span>
          <div>
            <div style="font-weight:800;font-size:14px;color:#00d2ff">SLOT 02 / AFTERNOON</div>
            <div style="font-size:18px;font-weight:800;color:#fff;margin-top:2px">2:00 PM</div>
          </div>
        </div>
        <div class="muted" style="font-size:11px;margin-top:8px">Midday trending entertainment & franchise updates.</div>
      </div>

      <div style="background:#091c34;border:1px solid #1a3962;border-radius:10px;padding:16px">
        <div style="display:flex;align-items:center;gap:10px">
          <span style="font-size:24px">🌙</span>
          <div>
            <div style="font-weight:800;font-size:14px;color:#f472b6">SLOT 03 / NIGHT</div>
            <div style="font-size:18px;font-weight:800;color:#fff;margin-top:2px">8:00 PM</div>
          </div>
        </div>
        <div class="muted" style="font-size:11px;margin-top:8px">Prime-time evening blockbuster & trailer highlights.</div>
      </div>
    </div>

    <div style="background:#051224;border:1px solid #1e3a5f;border-radius:10px;padding:18px;margin-top:20px">
      <div style="font-weight:800;font-size:13px;letter-spacing:0.5px;color:#fff;margin-bottom:10px">🎯 PRIORITY SCORING & ZERO-DUPLICATE GUARD</div>
      <div style="display:flex;flex-direction:column;gap:8px;font-size:12px;color:#94a3b8">
        <div><b style="color:#ffd12a">Tier 1 — Marvel & DC Universe (Score 100):</b> Matches Marvel, MCU, Avengers, Spider-Man, Deadpool, Wolverine, Batman, Superman, DCU, etc.</div>
        <div><b style="color:#00d2ff">Tier 2 — Major Franchises (Score 50):</b> Star Wars, Dragon Ball, Anime, GTA 6, Gaming, Lord of the Rings, HBO/Netflix hits.</div>
        <div><b style="color:#71e5aa">Tier 3 — General Entertainment (Score 10):</b> Selected only when no unscored Tier 1/2 articles remain.</div>
        <div><b style="color:#f87171">🛡 Zero-Duplicate Guarantee:</b> Every published article URL is permanently recorded in your database; previously published stories are automatically skipped.</div>
      </div>
    </div>

    <div style="display:flex;align-items:center;gap:12px;margin-top:20px;flex-wrap:wrap">
      <button class="btn blue" style="padding:10px 22px;font-size:14px;font-weight:700" onclick="runAutomationCycle()" id="autoRunBtn">⚡ Run Automation Cycle Now</button>
      <button class="btn alt" onclick="loadAutomationLogs()">↻ Refresh Logs</button>
      <div id="autoRunStatus" class="muted" style="font-size:12px">Ready. Click to trigger an immediate automated fetch, draft, and publish.</div>
    </div>

    <div style="background:#0a1729;border:1px solid #1c3558;border-radius:10px;padding:16px;margin-top:24px">
      <div style="font-weight:700;font-size:13px;color:#fff;display:flex;align-items:center;gap:8px">
        <span>⚙</span> How to Enable 3x/Day Cron on Hostinger
      </div>
      <div class="muted" style="font-size:12px;margin-top:6px;line-height:1.5">
        In Hostinger hPanel &rarr; <b>Advanced</b> &rarr; <b>Cron Jobs</b>, add a custom cron job:
        <div style="background:#040c18;padding:8px 12px;border-radius:6px;font-family:monospace;color:#ffd12a;margin-top:6px;user-select:all">
          0 9,14,20 * * * /usr/bin/php /home/username/public_html/cron.php
        </div>
      </div>
    </div>

    <div style="margin-top:28px">
      <h3 style="font-size:16px;color:#fff;margin-bottom:12px">📋 Automation Execution History</h3>
      <div id="automationLogsList" style="display:flex;flex-direction:column;gap:10px">Loading history...</div>
    </div>
  </div>
</div><div id="settingsPage" class="hide">

<!-- Section 1: AI Settings & Multi-Key Failover Pool -->
<div class="panel" style="max-width:760px;margin:24px auto">
  <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px">
    <div>
      <h2 style="font-size:20px;margin:0 0 5px">⚙ Google Gemini Multi-Key Pool</h2>
      <div class="muted">Add multiple Gemini API keys. If any key exceeds its daily limit, VoxBulletin automatically fails over to the next key!</div>
    </div>
    <span id="geminiPill" class="pill">● CHECKING...</span>
  </div>
  <div class="divider"></div>

  <div style="background:#09192e;border:1px solid #1c3d69;border-radius:10px;padding:14px 16px;margin-bottom:20px;display:flex;align-items:flex-start;gap:12px">
    <div style="font-size:22px;line-height:1">🛡</div>
    <div style="font-size:12px;line-height:1.5;color:#9fb5d6">
      <b style="color:#ffd12a">Daily Quota Protection Active:</b> Free Google AI Studio accounts provide a daily request limit per key. 
      Add 2 or more free keys below. If Key #1 hits daily quota (HTTP 429 / <code>RESOURCE_EXHAUSTED</code>), both manual studio drafting and autonomous 3x/day publishing automatically roll over to Key #2 without interruption!
      <div style="margin-top:6px">
        <a href="https://aistudio.google.com/app/apikey" target="_blank" rel="noopener" style="color:#38bdf8;text-decoration:underline;font-weight:600">Get additional free keys at Google AI Studio ↗</a>
      </div>
    </div>
  </div>

  <form id="settingsForm" onsubmit="saveSettings(event)">
    <div style="margin-bottom:12px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
      <label style="margin:0;font-size:13px;color:#cbd5e1;font-weight:700">CONFIGURED GEMINI API KEYS (<span id="keyPoolCount" style="color:#ffd12a;font-weight:700">0</span> Active in Pool)</label>
      <span style="font-size:11px;color:#64748b">Takes all available non-empty keys (Slots 1–5)</span>
    </div>

    <!-- 5 Explicit Key Slots -->
    <div style="display:flex;flex-direction:column;gap:12px;margin-bottom:18px">
      <!-- Slot 0: Primary -->
      <div class="key-card">
        <div class="key-header">
          <div style="display:flex;align-items:center;gap:8px">
            <span class="key-badge primary">KEY #1 (PRIMARY)</span>
            <span id="keyStatus_0" style="font-size:11px;color:#94a3b8"></span>
          </div>
          <div class="key-actions">
            <button type="button" class="btn alt small" style="padding:3px 8px;font-size:11px" onclick="testSingleKey(0)">⚡ Test Quota</button>
          </div>
        </div>
        <div class="inline">
          <input type="password" id="geminiKey_0" placeholder="Paste Primary Gemini API Key (e.g. AIzaSy... or AQ.Ab8...)" oninput="updateKeyPoolCount()">
          <button type="button" class="btn alt small" onclick="toggleSlotVis(0, this)">SHOW</button>
        </div>
      </div>

      <!-- Slot 1: Backup 1 -->
      <div class="key-card">
        <div class="key-header">
          <div style="display:flex;align-items:center;gap:8px">
            <span class="key-badge">KEY #2 (BACKUP / FAILOVER #1)</span>
            <span id="keyStatus_1" style="font-size:11px;color:#94a3b8"></span>
          </div>
          <div class="key-actions">
            <button type="button" class="btn alt small" style="padding:3px 8px;font-size:11px" onclick="testSingleKey(1)">⚡ Test Quota</button>
          </div>
        </div>
        <div class="inline">
          <input type="password" id="geminiKey_1" placeholder="Paste Backup Gemini Key #2 (Optional - leave blank if not using)" oninput="updateKeyPoolCount()">
          <button type="button" class="btn alt small" onclick="toggleSlotVis(1, this)">SHOW</button>
        </div>
      </div>

      <!-- Slot 2: Backup 2 -->
      <div class="key-card">
        <div class="key-header">
          <div style="display:flex;align-items:center;gap:8px">
            <span class="key-badge">KEY #3 (BACKUP / FAILOVER #2)</span>
            <span id="keyStatus_2" style="font-size:11px;color:#94a3b8"></span>
          </div>
          <div class="key-actions">
            <button type="button" class="btn alt small" style="padding:3px 8px;font-size:11px" onclick="testSingleKey(2)">⚡ Test Quota</button>
          </div>
        </div>
        <div class="inline">
          <input type="password" id="geminiKey_2" placeholder="Paste Backup Gemini Key #3 (Optional - leave blank if not using)" oninput="updateKeyPoolCount()">
          <button type="button" class="btn alt small" onclick="toggleSlotVis(2, this)">SHOW</button>
        </div>
      </div>

      <!-- Slot 3: Backup 3 -->
      <div class="key-card">
        <div class="key-header">
          <div style="display:flex;align-items:center;gap:8px">
            <span class="key-badge">KEY #4 (BACKUP / FAILOVER #3)</span>
            <span id="keyStatus_3" style="font-size:11px;color:#94a3b8"></span>
          </div>
          <div class="key-actions">
            <button type="button" class="btn alt small" style="padding:3px 8px;font-size:11px" onclick="testSingleKey(3)">⚡ Test Quota</button>
          </div>
        </div>
        <div class="inline">
          <input type="password" id="geminiKey_3" placeholder="Paste Backup Gemini Key #4 (Optional - leave blank if not using)" oninput="updateKeyPoolCount()">
          <button type="button" class="btn alt small" onclick="toggleSlotVis(3, this)">SHOW</button>
        </div>
      </div>

      <!-- Slot 4: Backup 4 -->
      <div class="key-card">
        <div class="key-header">
          <div style="display:flex;align-items:center;gap:8px">
            <span class="key-badge">KEY #5 (BACKUP / FAILOVER #4)</span>
            <span id="keyStatus_4" style="font-size:11px;color:#94a3b8"></span>
          </div>
          <div class="key-actions">
            <button type="button" class="btn alt small" style="padding:3px 8px;font-size:11px" onclick="testSingleKey(4)">⚡ Test Quota</button>
          </div>
        </div>
        <div class="inline">
          <input type="password" id="geminiKey_4" placeholder="Paste Backup Gemini Key #5 (Optional - leave blank if not using)" oninput="updateKeyPoolCount()">
          <button type="button" class="btn alt small" onclick="toggleSlotVis(4, this)">SHOW</button>
        </div>
      </div>
    </div>

    <div class="field" style="margin-top:20px;border-top:1px solid #1e3556;padding-top:18px">
      <label>ADMIN PASSWORD</label>
      <input id="newAdminPassword" type="password" placeholder="Leave blank to keep current password">
      <p class="muted" style="margin-top:5px">Default local password is <code>admin123</code></p>
    </div>

    <div class="actions" style="margin-top:24px">
      <button type="submit" class="btn" id="saveSettingsBtn">✓ Save All Keys & Settings</button>
      <button type="button" class="btn alt" onclick="showPage('create')">Back to Studio</button>
    </div>
    <div id="settingsMsg" class="status"></div>
  </form>
</div>

<!-- Section 2: Buffer Real Account Connection (Recommended) -->
<div class="panel" style="max-width:760px;margin:24px auto;border-color:#2a4365">
  <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px">
    <div style="display:flex;align-items:center;gap:12px">
      <div style="width:36px;height:36px;background:#2b6cb0;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:20px">⚡</div>
      <div>
        <h2 style="font-size:20px;margin:0 0 4px">Buffer Real API Connection (Recommended)</h2>
        <div class="muted">Connect your live Instagram (@voxbulletin), Facebook, X, and Pinterest accounts directly via Buffer API.</div>
      </div>
    </div>
    <span id="bufferPill" class="pill">● NOT CONNECTED</span>
  </div>
  <div class="divider"></div>

  <p class="muted" style="font-size:13px;line-height:1.6;margin-top:0">
    Buffer handles verified multi-platform publishing. Since you already linked <b>@voxbulletin</b> and <b>@voxbulletins</b> in Buffer, entering your Buffer Access Token lets VoxBulletin Studio publish live directly to all your connected channels!
  </p>

  <form id="bufferForm" onsubmit="saveBufferSettings(event)">
    <div class="field">
      <label>BUFFER ACCESS TOKEN</label>
      <div class="inline">
        <input id="bufferTokenInput" type="password" placeholder="Paste your Buffer Access Token (1/...)">
        <button type="button" class="btn alt small" id="toggleBufferKey" onclick="toggleKeyVis('bufferTokenInput', 'toggleBufferKey')">SHOW</button>
      </div>
      <p class="muted" style="margin-top:8px">
        Generate your permanent Buffer Access Token in 1 click at: 
        <a href="https://buffer.com/developers/api/manage" target="_blank" rel="noopener" style="color:#ffd12a;text-decoration:underline;font-weight:600">buffer.com/developers/api/manage ↗</a>
      </p>
    </div>

    <div class="field" style="margin-top:14px">
      <label>HOSTINGER / PUBLIC BASE URL (OPTIONAL)</label>
      <input id="publicBaseUrlInput" type="text" placeholder="https://voxbulletin.com (or leave empty for automatic cloud image hosting)">
      <p class="muted" style="margin-top:5px;font-size:12px">Optional: If hosted on Hostinger, enter your domain URL here. If left empty, images are automatically uploaded via secure cloud CDN for Buffer publishing.</p>
    </div>

    <div class="actions" style="margin-top:18px">
      <button type="button" class="btn blue" onclick="syncBufferNow()" id="syncBufferBtn">⚡ Connect & Sync Real Channels</button>
      <button type="submit" class="btn alt">Save Settings</button>
    </div>
    <div id="bufferMsg" class="status"></div>
  </form>
</div>

<!-- Section 3: Post Email Notifications (Gmail SMTP) -->
<div class="panel" style="max-width:760px;margin:24px auto">
  <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px">
    <div style="display:flex;align-items:center;gap:12px">
      <div style="width:36px;height:36px;background:#991b1b;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:18px">📧</div>
      <div>
        <h2 style="font-size:20px;margin:0 0 4px">Post Email Notifications (Gmail SMTP)</h2>
        <div class="muted">Receive instant branded email notifications whenever a post is published live.</div>
      </div>
    </div>
    <span id="smtpPill" class="pill">● ACTIVE &amp; READY</span>
  </div>
  <div class="divider"></div>

  <form id="emailSettingsForm" onsubmit="saveEmailSettings(event)">
    <div style="margin-bottom:14px;background:#0d1829;padding:12px 16px;border-radius:8px;border:1px solid #1c2c47">
      <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-weight:700;font-size:14px;color:#fff">
        <input type="checkbox" id="emailNotifyToggle" style="width:18px;height:18px;accent-color:#38bdf8" checked>
        Send me an email notification whenever a post is published (Manual &amp; 3x/Day Cron)
      </label>
    </div>

    <div class="field">
      <label>RECIPIENT EMAIL ADDRESS</label>
      <input id="notifyEmailInput" type="email" placeholder="bulletinvox@gmail.com" value="bulletinvox@gmail.com">
      <p class="muted" style="margin-top:5px;font-size:12px">The email inbox where you want to receive publication notifications.</p>
    </div>

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:12px">
      <div class="field">
        <label>GMAIL SMTP SERVER</label>
        <input id="smtpHostInput" type="text" placeholder="smtp.gmail.com" value="smtp.gmail.com">
      </div>
      <div class="field">
        <label>SMTP PORT</label>
        <input id="smtpPortInput" type="number" placeholder="465" value="465">
      </div>
    </div>

    <div class="field">
      <label>GMAIL SENDER ADDRESS</label>
      <input id="smtpUserInput" type="email" placeholder="bulletinvox@gmail.com" value="bulletinvox@gmail.com">
    </div>

    <div class="field">
      <label>GMAIL 16-CHARACTER APP PASSWORD</label>
      <div class="inline">
        <input id="smtpPassInput" type="password" placeholder="•••••••••••••••• (Saved)">
        <button type="button" class="btn alt small" id="toggleSmtpPass" onclick="toggleKeyVis('smtpPassInput', 'toggleSmtpPass')">SHOW</button>
      </div>
      <p class="muted" style="margin-top:6px;font-size:11px">Generated in Google Account &rarr; Security &rarr; 2-Step Verification &rarr; App Passwords.</p>
    </div>

    <div class="actions" style="margin-top:18px">
      <button type="button" class="btn blue" onclick="sendTestEmailNow()" id="testEmailBtn">✉ Send Test Email</button>
      <button type="submit" class="btn alt">Save Email Settings</button>
    </div>
    <div id="emailMsg" class="status"></div>
  </form>
</div>

<!-- Section 4: Connected Channels List -->
<div class="panel" style="max-width:760px;margin:24px auto">
  <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px">
    <div>
      <h2 style="font-size:20px;margin:0 0 5px">📡 Connected Real Social Channels</h2>
      <div class="muted">Authorized live channels ready for 1-click publishing.</div>
    </div>
    <button type="button" class="btn blue small" onclick="openChannelModal()">+ Connect a Channel</button>
  </div>
  <div class="divider"></div>
  <div id="connectedChannelsList"></div>
</div>

<!-- Section 5: Poster Graphic & Typography Design Settings -->
<div class="panel" style="max-width:760px;margin:24px auto">
  <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px">
    <div style="display:flex;align-items:center;gap:12px">
      <div style="width:36px;height:36px;background:#1e3a8a;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:18px">🎨</div>
      <div>
        <h2 style="font-size:20px;margin:0 0 4px">Poster Graphic &amp; Typography Design Settings</h2>
        <div class="muted">Manage font sizes, logo placement, brand visibility, and color styling for both studio posts and 3x/day automated graphics.</div>
      </div>
    </div>
    <span id="designPill" class="pill" style="background:#132b40;color:#38bdf8;border:1px solid #285372">● ACTIVE DEFAULTS</span>
  </div>
  <div class="divider"></div>

  <form id="designSettingsForm" onsubmit="saveDesignSettings(event)">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
      <!-- Title Font Size -->
      <div class="field" style="margin-bottom:0">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
          <label style="margin:0;font-weight:700">POST TITLE FONT SIZE</label>
          <span id="hlFontSizeBadge" style="font-size:12px;font-weight:700;color:#ffd12a">Auto (Dynamic)</span>
        </div>
        <div style="display:flex;align-items:center;gap:10px">
          <input type="range" id="settingHlFontSize" min="0" max="84" step="2" value="0" style="flex:1" oninput="onSettingHlSlider(this.value)">
          <select id="settingHlFontSizePreset" style="width:130px;padding:6px;font-size:12px" onchange="applyHlPreset(this.value)">
            <option value="0">Auto (Default)</option>
            <option value="56">Small (56px)</option>
            <option value="64">Medium (64px)</option>
            <option value="72">Large (72px)</option>
            <option value="80">Extra Large (80px)</option>
          </select>
        </div>
        <p class="muted" style="margin-top:4px;font-size:11px">Auto automatically balances between 62px–74px depending on headline word count.</p>
      </div>

      <!-- Description Font Size -->
      <div class="field" style="margin-bottom:0">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
          <label style="margin:0;font-weight:700">DESCRIPTION FONT SIZE</label>
          <span id="descFontSizeBadge" style="font-size:12px;font-weight:700;color:#ffd12a">Auto (24px)</span>
        </div>
        <div style="display:flex;align-items:center;gap:10px">
          <input type="range" id="settingDescFontSize" min="0" max="36" step="1" value="0" style="flex:1" oninput="onSettingDescSlider(this.value)">
          <select id="settingDescFontSizePreset" style="width:130px;padding:6px;font-size:12px" onchange="applyDescPreset(this.value)">
            <option value="0">Auto (Default)</option>
            <option value="20">Compact (20px)</option>
            <option value="24">Standard (24px)</option>
            <option value="28">Medium (28px)</option>
            <option value="32">Large (32px)</option>
          </select>
        </div>
        <p class="muted" style="margin-top:4px;font-size:11px">Controls the legibility of the 1–2 sentence summary below the title.</p>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
      <!-- Top Logo Alignment -->
      <div class="field" style="margin-bottom:0">
        <label style="font-weight:700">TOP 3D GLOBE LOGO ALIGNMENT</label>
        <select id="settingLogoAlignment" onchange="onDesignInputChange()">
          <option value="top-right">Top Right (Default with VoxBulletin Pill)</option>
          <option value="top-left">Top Left (With VoxBulletin Pill)</option>
          <option value="top-center">Top Center (Centered Logo)</option>
          <option value="hidden">Hide Logo Completely</option>
        </select>
        <p class="muted" style="margin-top:4px;font-size:11px">Position of the translucent 3D Globe + VoxBulletin branding badge.</p>
      </div>

      <!-- Bottom Branding Visibility -->
      <div class="field" style="margin-bottom:0">
        <label style="font-weight:700">BOTTOM "VOX BULLETIN" DIVIDER BAR</label>
        <select id="settingFooterBranding" onchange="onDesignInputChange()">
          <option value="1">Visible (Default: Dividers + VOX BULLETIN)</option>
          <option value="0">Hidden (Clean bottom without dividers)</option>
        </select>
        <p class="muted" style="margin-top:4px;font-size:11px">Show or hide the lower brand separator at the bottom of the poster.</p>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">
      <!-- Headline Casing -->
      <div class="field" style="margin-bottom:0">
        <label style="font-weight:700">HEADLINE TEXT CASING</label>
        <select id="settingHeadlineCase" onchange="onDesignInputChange()">
          <option value="uppercase">ALL-CAPS (High Impact - Default)</option>
          <option value="titlecase">Title Case (First Letters Capitalized)</option>
          <option value="original">Original / As Written (Sentence Case)</option>
        </select>
      </div>

      <!-- Headline Palette Theme -->
      <div class="field" style="margin-bottom:0">
        <label style="font-weight:700">HEADLINE COLOR PALETTE THEME</label>
        <select id="settingColorPalette" onchange="onDesignInputChange()">
          <option value="gold-cyan">Electric Yellow &amp; Sky Blue (#ffd12a, #00d2ff)</option>
          <option value="orange-cyan">Sunset Orange &amp; Sky Blue (#ff9100, #00d2ff)</option>
          <option value="green-gold">Neon Green &amp; Yellow (#00e676, #ffd12a)</option>
          <option value="pink-cyan">Hot Pink &amp; Sky Blue (#f472b6, #00d2ff)</option>
          <option value="violet-cyan">Electric Violet &amp; Sky Blue (#c084fc, #00d2ff)</option>
          <option value="white">Pure Minimal White (#ffffff, #ffffff)</option>
          <option value="auto">Dynamic / Auto per story (Cycle vibrant duos)</option>
        </select>
      </div>
    </div>

    <div class="actions" style="margin-top:16px">
      <button type="submit" class="btn blue" id="saveDesignBtn">💾 Save Graphic Design Settings</button>
      <button type="button" class="btn alt" onclick="resetDesignSettingsDefaults()">↺ Reset to Factory Defaults</button>
    </div>
    <div id="designMsg" class="status"></div>
  </form>
</div>

</div>
</main></div>
<script>
let csrf=<?=json_encode($csrf)?>,articles=[],current=null,photo=null,logo=null,sourceImage='',sourceUrl='';
const brandGlobeImg = new Image();
brandGlobeImg.src = 'brand-globe.png';
brandGlobeImg.onerror = () => { brandGlobeImg.src = 'image.png'; };
brandGlobeImg.onload = () => { if(typeof render === 'function') render(); };
const COLOR_NAMES = {
  '#ffd12a': 'Electric Yellow',
  '#00d2ff': 'Sky Blue',
  '#ff9100': 'Sunset Orange',
  '#00e676': 'Neon Green',
  '#f472b6': 'Hot Pink',
  '#c084fc': 'Electric Violet',
  '#22d3ee': 'Aqua Cyan',
  '#ff7b7b': 'Vibrant Coral',
  '#a3e635': 'Lime Green',
  '#fbbf24': 'Amber Gold'
};

const THEME_PALETTES = [
  // 1. Classic High-Impact Duos
  ['#ffd12a', '#00d2ff'], // Yellow & Sky Blue
  ['#ff9100', '#00d2ff'], // Sunset Orange & Sky Blue
  ['#00d2ff', '#ffd12a'], // Sky Blue & Yellow
  ['#00d2ff', '#ff9100'], // Sky Blue & Sunset Orange
  ['#ffd12a', '#ff9100'], // Yellow & Sunset Orange
  ['#ff9100', '#ffd12a'], // Sunset Orange & Yellow

  // 2. Neon Mint & Lime Green Combinations (Super high luminance)
  ['#00e676', '#ffd12a'], // Neon Green & Yellow
  ['#00e676', '#00d2ff'], // Neon Green & Sky Blue
  ['#ffd12a', '#00e676'], // Yellow & Neon Green
  ['#00d2ff', '#00e676'], // Sky Blue & Neon Green
  ['#ff9100', '#00e676'], // Sunset Orange & Neon Green
  ['#00e676', '#ff9100'], // Neon Green & Sunset Orange
  ['#a3e635', '#00d2ff'], // Lime Green & Sky Blue
  ['#a3e635', '#ffd12a'], // Lime Green & Yellow

  // 3. Hot Pink & Magenta Accents (Vibrant & Stylish)
  ['#f472b6', '#00d2ff'], // Hot Pink & Sky Blue
  ['#f472b6', '#ffd12a'], // Hot Pink & Yellow
  ['#00d2ff', '#f472b6'], // Sky Blue & Hot Pink
  ['#ffd12a', '#f472b6'], // Yellow & Hot Pink
  ['#f472b6', '#00e676'], // Hot Pink & Neon Green
  ['#00e676', '#f472b6'], // Neon Green & Hot Pink
  ['#a3e635', '#f472b6'], // Lime Green & Hot Pink

  // 4. Electric Violet, Aqua Cyan & Coral
  ['#c084fc', '#ffd12a'], // Electric Violet & Yellow
  ['#c084fc', '#00d2ff'], // Electric Violet & Sky Blue
  ['#c084fc', '#00e676'], // Electric Violet & Neon Green
  ['#22d3ee', '#ff9100'], // Aqua Cyan & Sunset Orange
  ['#22d3ee', '#f472b6'], // Aqua Cyan & Hot Pink
  ['#22d3ee', '#ffd12a'], // Aqua Cyan & Yellow
  ['#ff7b7b', '#00d2ff'], // Coral & Sky Blue
  ['#ff7b7b', '#00e676'], // Coral & Neon Green
  ['#fbbf24', '#00d2ff'], // Amber Gold & Sky Blue
  ['#fbbf24', '#f472b6']  // Amber Gold & Hot Pink
];
let currentTheme = ['#ffd12a', '#00d2ff'];
let colorA = currentTheme[0], colorB = currentTheme[1];

let postDesignConfig = {
  headlineFontSize: 0,
  descriptionFontSize: 0,
  logoAlignment: 'top-right',
  footerBranding: true,
  headlineCase: 'uppercase',
  colorPalette: 'gold-cyan'
};

const PALETTE_MAP = {
  'gold-cyan': ['#ffd12a', '#00d2ff'],
  'orange-cyan': ['#ff9100', '#00d2ff'],
  'green-gold': ['#00e676', '#ffd12a'],
  'pink-cyan': ['#f472b6', '#00d2ff'],
  'violet-cyan': ['#c084fc', '#00d2ff'],
  'white': ['#ffffff', '#ffffff']
};

function toTitleCase(str) {
  if(!str) return '';
  return str.toLowerCase().replace(/(?:^|\s|\/|-)\w/g, m => m.toUpperCase());
}

function applyDesignPalette(key) {
  if (key && PALETTE_MAP[key]) {
    currentTheme = PALETTE_MAP[key];
    colorA = currentTheme[0];
    colorB = currentTheme[1];
  }
}

function cleanSourceText(text){
  if(!text) return '';
  return text
    .replace(/\s*[-–—|]\s*Fandom\s*Wire.*$/i, '')
    .replace(/\b(This post belongs to and first appeared on|The post .* appeared first on).*$/i, '')
    .replace(/\b(according to|per|as reported by|via)\s+Fandom\s*Wire,?\s*/gi, '')
    .replace(/\bFandom\s*Wire\s+(reports|reported|reveals|revealed|claims|claimed|stated)\s+(that\s+)?/gi, 'Reports indicate ')
    .replace(/#?Fandom\s*Wire(\.com)?\b/gi, '')
    .replace(/\s*\[…\]\s*$/,'...')
    .replace(/\s*\[\.\.\.\]\s*$/,'...')
    .replace(/\s+/g, ' ')
    .trim();
}

function formatTwitterPost(hl, desc){
  const tag = '#voxbulletin';
  const cleanHl = (hl || '').trim();
  const cleanDesc = (desc || '').trim();
  if(!cleanHl && !cleanDesc) return tag;
  if(!cleanDesc) {
    const candidate = cleanHl + '\n\n' + tag;
    return candidate.length <= 280 ? candidate : (cleanHl.slice(0, 280 - tag.length - 5) + '...\n' + tag);
  }
  const candidate = cleanHl + '\n\n' + cleanDesc + '\n\n' + tag;
  if(candidate.length <= 280) return candidate;

  // Truncate description at word boundary to strictly preserve <= 280 chars
  const overhead = cleanHl.length + 2 + 5 + tag.length;
  const availDesc = 280 - overhead;
  if(availDesc >= 25){
    let trimmedDesc = cleanDesc.slice(0, availDesc);
    const lastSpace = trimmedDesc.lastIndexOf(' ');
    if(lastSpace > 15) trimmedDesc = trimmedDesc.slice(0, lastSpace);
    return cleanHl + '\n\n' + trimmedDesc + '...\n\n' + tag;
  }
  const availHl = 280 - 5 - tag.length;
  let trimmedHl = cleanHl.slice(0, availHl);
  const lastSpace = trimmedHl.lastIndexOf(' ');
  if(lastSpace > 15) trimmedHl = trimmedHl.slice(0, lastSpace);
  return trimmedHl + '...\n\n' + tag;
}

function updateXCounter(){
  const el = $('captionX');
  const countEl = $('xCharCounter');
  if(!el || !countEl) return;
  const len = el.value.length;
  countEl.textContent = len + ' / 280';
  if(len > 280){
    countEl.style.color = '#ff6b6b';
  } else if(len >= 260){
    countEl.style.color = '#facc15';
  } else {
    countEl.style.color = '#38bdf8';
  }
}

function syncTwitterPost(){
  const hl = $('headline') ? $('headline').value : '';
  const desc = $('description') ? $('description').value : '';
  if($('captionX')){
    $('captionX').value = formatTwitterPost(hl, desc);
    updateXCounter();
  }
}

function pickThemeForArticle(title){
  if(!title) return THEME_PALETTES[0];
  let hash = 0;
  for(let i=0; i<title.length; i++) hash = ((hash << 5) - hash) + title.charCodeAt(i);
  currentTheme = THEME_PALETTES[Math.abs(hash) % THEME_PALETTES.length];
  colorA = currentTheme[0];
  colorB = currentTheme[1];
  return currentTheme;
}

function shuffleTheme(){
  let nextIdx;
  do {
    nextIdx = Math.floor(Math.random() * THEME_PALETTES.length);
  } while(THEME_PALETTES[nextIdx][0] === currentTheme[0] && THEME_PALETTES[nextIdx][1] === currentTheme[1]);
  currentTheme = THEME_PALETTES[nextIdx];
  colorA = currentTheme[0];
  colorB = currentTheme[1];
  render();
  const nameA = COLOR_NAMES[currentTheme[0]] || currentTheme[0];
  const nameB = COLOR_NAMES[currentTheme[1]] || currentTheme[1];
  status('🎨 Theme colors: ' + nameA + ' & ' + nameB + ' (Palette ' + (nextIdx + 1) + '/' + THEME_PALETTES.length + ')');
}

function drawRoundedRect(ctx, x, y, w, h, r){
  if(ctx.roundRect){
    ctx.beginPath();
    ctx.roundRect(x, y, w, h, r);
  }else{
    ctx.beginPath();
    ctx.moveTo(x + r, y);
    ctx.arcTo(x + w, y, x + w, y + h, r);
    ctx.arcTo(x + w, y + h, x, y + h, r);
    ctx.arcTo(x, y + h, x, y, r);
    ctx.arcTo(x, y, x + w, y, r);
    ctx.closePath();
  }
}
const $=id=>document.getElementById(id);
async function req(action,options={}){
  const r=await fetch('api/index.php?action='+action,{credentials:'same-origin',headers:{...(options.body?{'Content-Type':'application/json'}:{}),'X-CSRF-Token':csrf},...options});
  let j;
  const rawText = await r.text();
  try {
    j = JSON.parse(rawText);
  } catch (err) {
    if (r.status === 504) throw Error('Server Gateway Timeout (HTTP 504). The AI request took too long. We have optimized model failover — please try again.');
    if (r.status === 502) throw Error('Server Bad Gateway (HTTP 502). Please check server logs and try again.');
    throw Error('Server returned unexpected response (HTTP ' + r.status + ').');
  }
  if(!r.ok)throw Error((j && j.error) ? (j.error + (j.details ? ' — ' + j.details : '')) : ('Request failed with HTTP ' + r.status));
  return j;
}
$('loginForm').onsubmit=async e=>{e.preventDefault();try{const j=await req('login',{method:'POST',body:JSON.stringify({password:$('password').value})});csrf=j.csrf;$('login').classList.add('hide');$('app').classList.remove('hide');loadSettings();loadChannels();fetchNews();render()}catch(e){$('loginErr').textContent=e.message}};
async function logout(){await req('logout',{method:'POST'});location.reload()}

// --- Social Channels & Multi-Channel Cross-Posting System ---
const PLATFORMS_META = {
  instagram: {
    name: 'Instagram',
    sub: 'Business, Creator, or Personal',
    defaultHandle: '@voxbulletin',
    svg: '<svg width="28" height="28" viewBox="0 0 24 24" fill="none"><rect width="24" height="24" rx="6" fill="url(#igG)"/><path d="M12 7a5 5 0 100 10 5 5 0 000-10zm0 8.2a3.2 3.2 0 110-6.4 3.2 3.2 0 010 6.4zm5.2-8.5a1.1 1.1 0 11-2.2 0 1.1 1.1 0 012.2 0z" fill="#fff"/><defs><linearGradient id="igG" x1="2" y1="2" x2="22" y2="22"><stop stop-color="#fd5949"/><stop offset=".5" stop-color="#d6249f"/><stop offset="1" stop-color="#285AEB"/></linearGradient></defs></svg>'
  },
  threads: {
    name: 'Threads',
    sub: 'Profile',
    defaultHandle: '@voxbulletin',
    svg: '<svg width="28" height="28" viewBox="0 0 24 24"><rect width="24" height="24" rx="6" fill="#000"/><path d="M14.5 11.8c-.1-.4-.3-.8-.5-1.1-.5-.7-1.2-1.1-2-1.1-.9 0-1.7.5-2.1 1.3-.3.6-.4 1.3-.4 2.1 0 1.5.8 2.6 2.3 2.6.9 0 1.6-.3 2-1 .2-.3.3-.7.4-1.2h1.6c-.1.9-.5 1.7-1.1 2.3-.8.8-2 1.2-3.3 1.2-1.7 0-3.1-.7-4-2-.8-1.1-1.1-2.6-1.1-4.2s.5-3 1.4-4.1c1-1.1 2.5-1.7 4.2-1.7 1.8 0 3.2.7 4.2 1.9.9 1.1 1.4 2.6 1.4 4.3 0 1.8-.4 3.4-1.2 4.7-.9 1.4-2.2 2.3-3.8 2.8-.7.2-1.4.3-2.1.3-1.7 0-3.3-.6-4.5-1.7l1.1-1.3c1 .9 2.2 1.4 3.5 1.4 1.3 0 2.3-.7 3-1.8.6-1 1-2.4 1-3.9 0-1.4-.4-2.5-1.1-3.3-.7-.8-1.7-1.3-3-1.3-1.3 0-2.4.5-3.1 1.3-.7.9-1.1 2-1.1 3.4 0 1.3.2 2.4.8 3.2.6.9 1.5 1.3 2.6 1.3.8 0 1.4-.2 1.9-.6.4-.4.6-.9.7-1.5z" fill="#fff"/></svg>'
  },
  linkedin: {
    name: 'LinkedIn',
    sub: 'Page or Profile',
    defaultHandle: 'Vox Bulletin',
    svg: '<svg width="28" height="28" viewBox="0 0 24 24"><rect width="24" height="24" rx="6" fill="#0A66C2"/><path d="M7.5 9h-2v7.5h2V9zm-1-1.3a1.2 1.2 0 100-2.4 1.2 1.2 0 000 2.4zM18.5 12.8c0-2.1-1.1-3.1-2.6-3.1-1.2 0-1.8.7-2.1 1.2V9h-2v7.5h2v-4.2c0-1.1.2-2.2 1.6-2.2 1.4 0 1.4 1.3 1.4 2.3v4.1h2v-4.2z" fill="#fff"/></svg>'
  },
  facebook: {
    name: 'Facebook',
    sub: 'Page or Group',
    defaultHandle: 'Vox Bulletin Page',
    svg: '<svg width="28" height="28" viewBox="0 0 24 24"><rect width="24" height="24" rx="6" fill="#1877F2"/><path d="M14 12.5h2l.5-2.5H14v-1.6c0-.7.3-1.4 1.4-1.4H16.5V4.8s-1-.2-2-.2c-2 0-3.5 1.2-3.5 3.5V10H9v2.5h2v6.5h3v-6.5z" fill="#fff"/></svg>'
  },
  bluesky: {
    name: 'Bluesky',
    sub: 'Profile',
    defaultHandle: 'voxbulletin.bsky.social',
    svg: '<svg width="28" height="28" viewBox="0 0 24 24"><rect width="24" height="24" rx="6" fill="#0285FF"/><path d="M12 10.8c1.8-2.6 3.6-4.6 5.8-4.8 2.2-.2 3.2 1.5 2.2 4.2-.8 2.1-2.4 3.2-3.9 3.5 2.2.4 4.5 1.5 3.3 4.5-1.1 2.8-3.9 1.7-6.2-.3-.5-.4-.9-.8-1.2-1.1-.3.3-.7.7-1.2 1.1-2.3 2-5.1 3.1-6.2.3-1.2-3 1.1-4.1 3.3-4.5-1.5-.3-3.1-1.4-3.9-3.5-1-2.7 0-4.4 2.2-4.2 2.2.2 4 2.2 5.8 4.8l.1-.3z" fill="#fff"/></svg>'
  },
  substack: {
    name: 'Substack',
    sub: 'Profile',
    badge: 'New',
    defaultHandle: 'voxbulletin.substack.com',
    svg: '<svg width="28" height="28" viewBox="0 0 24 24"><rect width="24" height="24" rx="6" fill="#FF6719"/><path d="M6 6h12v2H6V6zm0 3.5h12v2H6v-2zm0 3.5h12v7l-6-3.4-6 3.4V13z" fill="#fff"/></svg>'
  },
  youtube: {
    name: 'YouTube',
    sub: 'Channel',
    defaultHandle: 'Vox Bulletin Channel',
    svg: '<svg width="28" height="28" viewBox="0 0 24 24"><rect width="24" height="24" rx="6" fill="#FF0000"/><path d="M18.8 8.2a2.3 2.3 0 00-1.6-1.6C15.8 6.2 12 6.2 12 6.2s-3.8 0-5.2.4a2.3 2.3 0 00-1.6 1.6C4.8 9.6 4.8 12 4.8 12s0 2.4.4 3.8a2.3 2.3 0 001.6 1.6c1.4.4 5.2.4 5.2.4s3.8 0 5.2-.4a2.3 2.3 0 001.6-1.6c.4-1.4.4-3.8.4-3.8s0-2.4-.4-3.8zM10.5 14.5v-5l4.3 2.5-4.3 2.5z" fill="#fff"/></svg>'
  },
  tiktok: {
    name: 'TikTok',
    sub: 'Business or Personal',
    defaultHandle: '@voxbulletin',
    svg: '<svg width="28" height="28" viewBox="0 0 24 24"><rect width="24" height="24" rx="6" fill="#000"/><path d="M15.5 6.2c.4.8 1.1 1.5 2 1.8v2.3c-1 0-1.9-.3-2.7-.9v5.3c0 2.3-1.9 4.2-4.2 4.2-2.3 0-4.2-1.9-4.2-4.2s1.9-4.2 4.2-4.2c.4 0 .8.1 1.2.2V13c-.4-.1-.8-.2-1.2-.2-1.1 0-2 1-2 2.1s.9 2.1 2 2.1c1.2 0 2.1-.9 2.1-2.1V4.5h2.8c0 .6.3 1.2.7 1.7z" fill="#fff"/></svg>'
  },
  mastodon: {
    name: 'Mastodon',
    sub: 'Profile',
    defaultHandle: '@voxbulletin@mastodon.social',
    svg: '<svg width="28" height="28" viewBox="0 0 24 24"><rect width="24" height="24" rx="6" fill="#6364FF"/><path d="M18.8 11.2c0-3.3-2.1-4.3-2.1-4.3-1.1-.5-3-.8-5.2-.8s-4.1.3-5.2.8c0 0-2.1 1-2.1 4.3 0 2.4.2 5.2.2 6.5 0 .9.7 1.8 1.8 2 1.5.2 3.1.2 4.7 0 .5-.1 1.2-.3 1.2-.3l-.1-1.3s-.5.1-1.1.2c-1.3.1-2.7 0-2.8-.7 0-.2 0-.4.1-.6 1.3.3 2.7.5 4.1.5s2.8-.2 4.1-.5c1.4-.4 1.7-1.7 1.8-2.7.1-1.3.2-4.1.2-5.1zm-3-1.6v4.6h-1.6v-4.4c0-.9-.4-1.4-1.2-1.4-.9 0-1.3.6-1.3 1.7v2.5h-1.5v-2.5c0-1.1-.4-1.7-1.3-1.7-.8 0-1.2.5-1.2 1.4v4.4H6.2V9.6c0-1 .3-1.7.8-2.2.6-.5 1.3-.8 2.2-.8 1 0 1.8.4 2.3 1.2.5-.8 1.3-1.2 2.3-1.2.9 0 1.7.3 2.2.8.6.5.8 1.2.8 2.2z" fill="#fff"/></svg>'
  },
  x: {
    name: 'X (Twitter)',
    sub: 'Profile',
    defaultHandle: '@voxbulletins',
    svg: '<svg width="28" height="28" viewBox="0 0 24 24"><rect width="24" height="24" rx="6" fill="#000"/><path d="M16.9 6h2.2l-4.8 5.5L20 18h-4.4l-3.5-4.5L8.2 18H6l5.2-5.9L5.6 6H10l3.1 4.1L16.9 6zm-.8 10.7h1.2L9.9 7.2H8.6l7.5 9.5z" fill="#fff"/></svg>'
  },
  pinterest: {
    name: 'Pinterest',
    sub: 'Profile or Board',
    defaultHandle: 'Vox Bulletin',
    svg: '<svg width="28" height="28" viewBox="0 0 24 24"><rect width="24" height="24" rx="6" fill="#E60023"/><path d="M12 4.5A7.5 7.5 0 007.4 18c.2-.6.4-1.5.1-2.2l-.7-3c.2-.5.8-1 1.5-1 1 0 1.8.8 1.8 2 0 1.3-.8 3.2-1.2 5-.3 1.3.7 2.4 2 2.4 2.4 0 4.2-2.5 4.2-6.1 0-3.2-2.3-5.4-5.6-5.4-3.8 0-6 2.8-6 5.8 0 1.1.4 2.3 1 3 .1.1.1.2.1.3l-.3 1.3c0 .2-.2.2-.4.1-1.3-.6-2.1-2.5-2.1-4 0-3.3 2.4-6.3 6.9-6.3 3.6 0 6.4 2.6 6.4 6 0 3.6-2.3 6.5-5.4 6.5-1.1 0-2.1-.6-2.4-1.2l-.7 2.6c-.3 1-.9 2.2-1.4 3 1 .3 2.1.5 3.3.5a7.5 7.5 0 007.5-7.5 7.5 7.5 0 00-7.5-7.5z" fill="#fff"/></svg>'
  }
};

function renderPlatformIcon(container, k, avatarUrl, size){
  container.replaceChildren();
  if(avatarUrl){
    const img = document.createElement('img');
    img.className = 'avatar-img';
    img.style.width = (size || 28) + 'px';
    img.style.height = (size || 28) + 'px';
    img.src = avatarUrl;
    img.onerror = () => {
      container.innerHTML = (PLATFORMS_META[k] || {}).svg || '📡';
    };
    container.append(img);
  } else {
    let svg = (PLATFORMS_META[k] || {}).svg || '📡';
    if(size && size !== 28){
      svg = svg.replace(/width="28"/g, 'width="' + size + '"').replace(/height="28"/g, 'height="' + size + '"');
    }
    container.innerHTML = svg;
  }
}

let connectedChannels = {};
let selectedPlatformForAuth = null;

function openChannelModal(){
  $('channelModal').classList.remove('hide');
  backToChannelsGrid();
  renderChannelGrid();
  // Autofill quickBufferToken if available
  const curBuf = $('bufferTokenInput') ? $('bufferTokenInput').value : '';
  if(curBuf && $('quickBufferToken')) $('quickBufferToken').value = curBuf;
}

function closeChannelModal(){
  $('channelModal').classList.add('hide');
}

function renderChannelGrid(){
  const c = $('channelsGridContainer');
  c.replaceChildren();
  Object.keys(PLATFORMS_META).forEach(k => {
    const p = PLATFORMS_META[k];
    const isConn = !!connectedChannels[k];
    const chData = connectedChannels[k];
    const btn = document.createElement('div');
    btn.className = 'channel-btn' + (isConn ? ' connected' : '');
    
    if (p.badge) {
      const b = document.createElement('span');
      b.className = 'channel-badge-new';
      b.textContent = p.badge;
      btn.append(b);
    } else if (isConn) {
      const b = document.createElement('span');
      b.className = 'channel-badge-connected';
      b.textContent = '✓ CONNECTED';
      btn.append(b);
    }

    const iconWrap = document.createElement('div');
    iconWrap.className = 'channel-icon-wrap';
    renderPlatformIcon(iconWrap, k, chData ? chData.avatar : '', 28);

    const name = document.createElement('div');
    name.className = 'channel-name';
    name.textContent = p.name;

    const sub = document.createElement('div');
    sub.className = 'channel-sub';
    sub.textContent = isConn ? (chData.account_handle || p.sub) : p.sub;

    btn.append(iconWrap, name, sub);
    btn.onclick = () => showConsentStep(k);
    c.append(btn);
  });
}

function showConsentStep(k){
  selectedPlatformForAuth = k;
  const p = PLATFORMS_META[k];
  const existing = connectedChannels[k];

  $('channelGridStep').classList.add('hide');
  $('channelConsentStep').classList.remove('hide');
  $('channelModalTitle').textContent = 'Authorize ' + p.name;
  $('consentPlatformTitle').textContent = p.name;
  
  if(existing && existing.avatar){
    $('consentIconWrap').innerHTML = '<img class="avatar-img" style="width:48px;height:48px" src="' + existing.avatar + '" alt="">';
  } else {
    $('consentIconWrap').innerHTML = p.svg;
  }
  
  const handle = existing ? existing.account_handle : p.defaultHandle;
  $('consentHandle').value = handle;
  $('consentHeading').innerHTML = '<b style="color:#5784ff">VoxBulletin Studio</b> is requesting live access to: <b>' + handle + '</b>';
  $('consentStatus').textContent = '';
  $('consentToken').value = existing ? (existing.access_token || '') : '';
}

function backToChannelsGrid(){
  $('channelModalTitle').textContent = 'Connect a New Channel';
  $('channelGridStep').classList.remove('hide');
  $('channelConsentStep').classList.add('hide');
  renderChannelGrid();
}

async function quickBufferSync(){
  const tok = $('quickBufferToken').value.trim();
  const st = $('quickSyncStatus');
  const btn = $('quickSyncBtn');
  if(!tok){
    st.textContent = 'Please paste your Buffer Access Token.';
    st.style.color = '#ffb3ba';
    return;
  }
  btn.disabled = true;
  btn.textContent = '⚡ Connecting to Buffer...';
  st.textContent = 'Contacting Buffer API (api.bufferapp.com)...';
  st.style.color = '#71e5aa';

  try{
    const res = await req('buffer_sync', {
      method: 'POST',
      body: JSON.stringify({ buffer_token: tok })
    });
    st.textContent = '✓ ' + res.message;
    st.style.color = '#71e5aa';
    if($('bufferTokenInput')) $('bufferTokenInput').value = tok;
    await loadChannels();
    await loadSettings();
    setTimeout(() => {
      closeChannelModal();
      status('✓ Successfully synced ' + res.count + ' real channel(s) from Buffer!');
    }, 900);
  }catch(err){
    st.textContent = '✗ ' + err.message;
    st.style.color = '#ffb3ba';
  }finally{
    btn.disabled = false;
    btn.textContent = '⚡ Sync Real Channels Now';
  }
}

async function submitChannelAuth(e){
  e.preventDefault();
  if(!selectedPlatformForAuth) return;
  const b = $('consentSubmitBtn');
  b.disabled = true;
  $('consentStatus').textContent = 'Connecting and verifying credentials...';
  $('consentStatus').style.color = '#71e5aa';

  try{
    const handle = $('consentHandle').value.trim() || PLATFORMS_META[selectedPlatformForAuth].defaultHandle;
    const token = $('consentToken').value.trim();
    const perms = ['view_profile'];
    if($('permPublish').checked) perms.push('publish_content');
    if($('permComments').checked) perms.push('manage_comments');
    if($('permInsights').checked) perms.push('manage_insights');

    // If Instagram and token provided, try meta_verify
    if(selectedPlatformForAuth === 'instagram' && token && token.startsWith('EAA')){
      const igId = prompt('Enter your Instagram Business Account ID for direct Meta API:', '');
      if(igId){
        const vRes = await req('meta_verify', {
          method: 'POST',
          body: JSON.stringify({ access_token: token, instagram_id: igId })
        });
        $('consentStatus').textContent = '✓ ' + vRes.message;
        await loadChannels();
        setTimeout(() => { closeChannelModal(); status('✓ Connected Instagram via Meta Graph API!'); }, 600);
        return;
      }
    }

    const res = await req('connect_channel', {
      method: 'POST',
      body: JSON.stringify({
        platform: selectedPlatformForAuth,
        account_name: PLATFORMS_META[selectedPlatformForAuth].name,
        account_handle: handle,
        permissions: perms,
        access_token: token
      })
    });

    $('consentStatus').textContent = '✓ ' + res.message;
    await loadChannels();
    setTimeout(() => {
      closeChannelModal();
      status('✓ ' + PLATFORMS_META[selectedPlatformForAuth].name + ' (' + handle + ') authorized and connected!');
    }, 600);
  }catch(err){
    $('consentStatus').textContent = err.message;
    $('consentStatus').style.color = '#ffb3ba';
  }finally{
    b.disabled = false;
  }
}

async function disconnectChannel(platformId){
  if(!confirm('Disconnect ' + (PLATFORMS_META[platformId] ? PLATFORMS_META[platformId].name : platformId) + '?')) return;
  try{
    await req('disconnect_channel', {
      method: 'POST',
      body: JSON.stringify({ platform: platformId })
    });
    await loadChannels();
    status('Channel disconnected.');
  }catch(err){
    alert(err.message);
  }
}

async function loadChannels(){
  try{
    const res = await req('channels');
    connectedChannels = res.connected || {};

    // 1. Render in Settings Page
    const listEl = $('connectedChannelsList');
    if(listEl){
      listEl.replaceChildren();
      const keys = Object.keys(connectedChannels);
      if(!keys.length){
        listEl.innerHTML = '<p class="muted" style="margin:12px 0">No social media channels connected yet. Click "+ Connect a Channel" or use the 1-Click Buffer Sync above to link your accounts.</p>';
      } else {
        keys.forEach(k => {
          const ch = connectedChannels[k];
          const meta = PLATFORMS_META[k] || { name: k, svg: '📡' };
          const card = document.createElement('div');
          card.className = 'channel-conn-card';
          
          const left = document.createElement('div');
          left.style.display = 'flex';
          left.style.alignItems = 'center';
          left.style.gap = '12px';

          const icon = document.createElement('div');
          renderPlatformIcon(icon, k, ch.avatar, 28);

          const info = document.createElement('div');
          const isBuffer = !!ch.remote_id;
          const badgeText = isBuffer ? '● BUFFER API' : '● LIVE API';
          info.innerHTML = '<div style="font-weight:700;font-size:14px;color:#f0f4fc">' + meta.name + 
            ' <span class="live-api-tag">' + badgeText + '</span></div>' +
            '<div class="muted" style="font-size:12px">' + ch.account_handle + ' &middot; Content Publishing Active</div>';

          left.append(icon, info);

          const right = document.createElement('div');
          const disBtn = document.createElement('button');
          disBtn.className = 'btn alt small';
          disBtn.textContent = 'Disconnect';
          disBtn.onclick = () => disconnectChannel(k);
          right.append(disBtn);

          card.append(left, right);
          listEl.append(card);
        });
      }
    }

    // 2. Render in Create Page (Cross-Post Checkboxes)
    const cpList = $('crosspostList');
    if(cpList){
      cpList.replaceChildren();
      const keys = Object.keys(connectedChannels);
      if(!keys.length){
        cpList.innerHTML = '<div style="grid-column:1/-1;color:#8ba0be;font-size:12px">No social accounts connected yet. <button type="button" class="btn alt small" onclick="openChannelModal()" style="margin-left:8px">+ Connect Channels</button></div>';
      } else {
        keys.forEach(k => {
          const ch = connectedChannels[k];
          const meta = PLATFORMS_META[k] || { name: k, svg: '📡' };
          const item = document.createElement('label');
          item.className = 'crosspost-item';

          const cb = document.createElement('input');
          cb.type = 'checkbox';
          cb.name = 'crosspost';
          cb.value = k;
          cb.checked = true;

          const icon = document.createElement('div');
          icon.style.width = '24px';
          icon.style.height = '24px';
          icon.style.flexShrink = '0';
          renderPlatformIcon(icon, k, ch.avatar, 24);

          const info = document.createElement('div');
          info.className = 'crosspost-item-info';
          info.innerHTML = '<div class="crosspost-item-name">' + meta.name + '</div><div class="crosspost-item-handle">' + ch.account_handle + '</div>';

          item.append(cb, icon, info);
          cpList.append(item);
        });
      }
    }
  }catch(e){
    console.error('loadChannels error:', e);
  }
}

async function publishSelected(evt){
  if(evt && evt.preventDefault) evt.preventDefault();
  if(!current) return status('Select a source story first.', true);
  if(!photo) return status('Please add or load a topic image before publishing.', true);
  if(!$('headline').value.trim() || !$('description').value.trim()) return status('Headline and description are required.', true);

  const checkedBoxes = Array.from(document.querySelectorAll('input[name="crosspost"]:checked'));
  const selectedChannels = checkedBoxes.map(b => b.value);

  if(!selectedChannels.length){
    return status('Select at least one connected social account to publish.', true);
  }

  const channelNames = selectedChannels.map(k => (PLATFORMS_META[k] || {}).name || k).join(', ');
  const confirmed = confirm('⚠️ PUBLISH TO SOCIAL MEDIA CONFIRMATION:\n\nAre you sure you want to publish this post live now to:\n• ' + selectedChannels.map(k => (PLATFORMS_META[k] || {}).name || k).join('\n• ') + '\n\nClick OK to publish live immediately, or Cancel to keep editing.');
  if(!confirmed){
    status('Publishing cancelled. Your post has NOT been published.');
    return;
  }

  const btn = $('pubBtn');
  btn.disabled = true;
  btn.textContent = '🚀 Publishing to ' + selectedChannels.length + ' Channel(s)...';
  status('Publishing post and caption to ' + selectedChannels.map(k => PLATFORMS_META[k] ? PLATFORMS_META[k].name : k).join(', ') + '...');

  const pubStatus = $('pubStatus');
  pubStatus.classList.remove('hide');
  pubStatus.replaceChildren();
  pubStatus.innerHTML = '<div style="display:flex;align-items:center;gap:8px"><span style="animation:spin 1s linear infinite">&#9696;</span> Dispatching live API posts...</div>';

  try{
    render();
    const payload = {
      image: $('poster').toDataURL('image/png'),
      headline: $('headline').value,
      description: $('description').value,
      caption: $('caption').value,
      caption_x: $('captionX') ? $('captionX').value : '',
      url: current.url,
      source_title: current.title,
      source_image_url: sourceImage,
      channels: selectedChannels
    };

    const res = await req('publish', {
      method: 'POST',
      body: JSON.stringify(payload)
    });

    pubStatus.replaceChildren();
    const header = document.createElement('div');
    header.style.fontWeight = '700';
    header.style.marginBottom = '8px';
    header.style.color = '#71e5aa';
    header.textContent = res.message || 'Publishing finished';
    pubStatus.append(header);

    (res.results || []).forEach(r => {
      const row = document.createElement('div');
      row.className = 'pub-result-row';
      const pName = (PLATFORMS_META[r.platform] || {}).name || r.platform;
      if(r.status === 'published'){
        row.innerHTML = '<span><b style="color:#71e5aa">✓ ' + pName + '</b> (' + (r.account_handle || '') + ')</span>' +
          (r.post_url ? '<a href="' + r.post_url + '" target="_blank" rel="noopener">View Post &#8599;</a>' : '<span class="muted">' + (r.message || 'Queued') + '</span>');
      } else {
        row.innerHTML = '<span><b style="color:#ffb3ba">✗ ' + pName + '</b></span><span style="color:#ffb3ba;font-size:12px">' + (r.error || 'Failed to publish') + '</span>';
      }
      pubStatus.append(row);
    });

    status('✓ Completed! Saved to Post Library (# ' + res.id + ')');
  }catch(err){
    pubStatus.innerHTML = '<div style="color:#ffb3ba">&#10006; ' + err.message + '</div>';
    status(err.message, true);
  }finally{
    btn.disabled = false;
    btn.textContent = '🚀 Publish to Selected Channels';
  }
}

function showPage(page){
  $('createPage').classList.toggle('hide',page!=='create');
  $('libraryPage').classList.toggle('hide',page!=='library');
  $('automationPage').classList.toggle('hide',page!=='automation');
  $('settingsPage').classList.toggle('hide',page!=='settings');
  $('pageTitle').textContent=page==='create'?'Create news post':(page==='library'?'Saved posts':(page==='automation'?'⚡ 3x/Day Automation Engine':'AI & Studio Settings'));
  $('navCreate').classList.toggle('active',page==='create');
  $('navLibrary').classList.toggle('active',page==='library');
  $('navAutomation').classList.toggle('active',page==='automation');
  $('navSettings').classList.toggle('active',page==='settings');
  if(page==='library')loadPosts();
  if(page==='automation')loadAutomationLogs();
  if(page==='settings'){loadSettings();loadChannels();}
}

async function loadAutomationLogs(){
  const container = $('automationLogsList');
  if(!container) return;
  container.innerHTML = '<div class="muted">Loading automation history...</div>';
  try{
    const res = await req('cron_logs');
    const logs = res.logs || [];
    if(!logs.length){
      container.innerHTML = '<div class="muted">No automation runs recorded yet. Click "Run Automation Cycle Now" above to trigger your first run!</div>';
      return;
    }
    container.replaceChildren();
    logs.forEach(l => {
      const card = document.createElement('div');
      card.style.background = '#091c34';
      card.style.border = '1px solid #1a3962';
      card.style.borderRadius = '8px';
      card.style.padding = '12px 16px';
      card.style.display = 'flex';
      card.style.justifyContent = 'space-between';
      card.style.alignItems = 'center';
      card.style.flexWrap = 'wrap';
      card.style.gap = '12px';

      const left = document.createElement('div');
      left.style.display = 'flex';
      left.style.flexDirection = 'column';
      left.style.gap = '4px';

      const catBadge = document.createElement('span');
      catBadge.style.fontSize = '10px';
      catBadge.style.fontWeight = '800';
      catBadge.style.padding = '2px 8px';
      catBadge.style.borderRadius = '4px';
      catBadge.style.display = 'inline-block';
      catBadge.style.width = 'fit-content';
      if(l.priority_score >= 90){
        catBadge.style.background = '#381f15';
        catBadge.style.border = '1px solid #783510';
        catBadge.style.color = '#ffd12a';
      } else if(l.priority_score >= 50){
        catBadge.style.background = '#0c2e47';
        catBadge.style.border = '1px solid #0369a1';
        catBadge.style.color = '#38bdf8';
      } else {
        catBadge.style.background = '#132b49';
        catBadge.style.border = '1px solid #1e3a5f';
        catBadge.style.color = '#94a3b8';
      }
      catBadge.textContent = (l.priority_category || 'General') + ' (Score ' + (l.priority_score || 0) + ')';

      const title = document.createElement('div');
      title.style.fontWeight = '700';
      title.style.fontSize = '14px';
      title.style.color = '#fff';
      title.textContent = l.article_title || l.headline || 'Automated Run';

      const meta = document.createElement('div');
      meta.className = 'muted';
      meta.style.fontSize = '11px';
      meta.textContent = l.created_at + ' • ' + (l.message || '');

      left.append(catBadge, title, meta);

      const right = document.createElement('div');
      right.style.display = 'flex';
      right.style.alignItems = 'center';
      right.style.gap = '10px';

      if(l.channels_published){
        l.channels_published.split(',').filter(Boolean).forEach(ch => {
          const pill = document.createElement('span');
          pill.className = 'pill-ch';
          pill.textContent = (PLATFORMS_META[ch]||{}).name || ch;
          right.append(pill);
        });
      }

      const statusTag = document.createElement('span');
      statusTag.style.fontSize = '11px';
      statusTag.style.fontWeight = '700';
      statusTag.style.padding = '3px 8px';
      statusTag.style.borderRadius = '4px';
      if(l.status === 'success'){
        statusTag.style.background = '#103b2b';
        statusTag.style.color = '#4ade80';
        statusTag.textContent = '✓ Published';
      } else if(l.status === 'skipped'){
        statusTag.style.background = '#282b15';
        statusTag.style.color = '#facc15';
        statusTag.textContent = '⚡ Skipped (No Dupes)';
      } else {
        statusTag.style.background = '#3b1515';
        statusTag.style.color = '#f87171';
        statusTag.textContent = '✗ ' + (l.status || 'Failed');
      }
      right.append(statusTag);

      card.append(left, right);
      container.append(card);
    });
  }catch(err){
    container.innerHTML = '<div style="color:#ffb3ba">Error loading logs: ' + err.message + '</div>';
  }
}

async function runAutomationCycle(){
  const btn = $('autoRunBtn');
  const statusEl = $('autoRunStatus');
  btn.disabled = true;
  btn.textContent = '⚡ Running Automation Cycle...';
  statusEl.style.color = '#ffd12a';
  statusEl.textContent = 'Fetching today\'s news -> Checking Marvel/DC priority -> Gemini AI rewrite -> Rendering 1080x1350 graphic -> Publishing to Instagram, Facebook & X...';

  try{
    const res = await req('cron');
    statusEl.style.color = '#71e5aa';
    if(res.status === 'skipped'){
      statusEl.textContent = '✓ Run complete: ' + res.message;
    } else {
      statusEl.textContent = '✓ Success! Picked: "' + (res.selected_title || '') + '" (' + (res.priority_category || '') + ') and published across ' + (res.channels_published ? res.channels_published.join(', ') : 'all channels') + '!';
    }
    loadAutomationLogs();
    status('✓ Automation cycle completed successfully!');
  }catch(err){
    statusEl.style.color = '#ffb3ba';
    statusEl.textContent = 'Automation error: ' + err.message;
    status(err.message, true);
  }finally{
    btn.disabled = false;
    btn.textContent = '⚡ Run Automation Cycle Now';
  }
}

function toggleSlotVis(idx, btn){
  const inp = $('geminiKey_' + idx);
  if(!inp) return;
  inp.type = inp.type === 'password' ? 'text' : 'password';
  btn.textContent = inp.type === 'password' ? 'SHOW' : 'HIDE';
}

function updateKeyPoolCount(){
  let count = 0;
  for(let i = 0; i < 5; i++){
    const inp = $('geminiKey_' + i);
    if(inp && inp.value.trim()) count++;
  }
  if($('keyPoolCount')) $('keyPoolCount').textContent = count;
  const gp = $('geminiPill');
  if(gp){
    if(count > 1){
      gp.textContent = '● ' + count + ' KEYS LOADED (FAILOVER READY)';
      gp.style.background = '#123528'; gp.style.borderColor = '#245a45'; gp.style.color = '#71e5aa';
    } else if(count === 1){
      gp.textContent = '● 1 KEY ACTIVE';
      gp.style.background = '#123528'; gp.style.borderColor = '#245a45'; gp.style.color = '#71e5aa';
    } else {
      gp.textContent = '● NO KEYS FOUND';
      gp.style.background = '#382215'; gp.style.borderColor = '#784920'; gp.style.color = '#ffd699';
    }
  }
}

async function testSingleKey(idx){
  const inp = $('geminiKey_' + idx);
  const key = inp ? inp.value.trim() : '';
  const st = $('keyStatus_' + idx);
  if(!key){
    if(st){ st.textContent = 'Enter key first'; st.style.color = '#f87171'; }
    return;
  }
  if(st){ st.textContent = 'Pinging Gemini API...'; st.style.color = '#38bdf8'; }
  try{
    const res = await req('test_gemini_key', {
      method: 'POST',
      body: JSON.stringify({ key: key })
    });
    if(st){ st.textContent = '✓ ' + (res.message || 'Active (200 OK)'); st.style.color = '#4ade80'; }
  }catch(err){
    if(st){
      if(err.message.includes('429') || err.message.toLowerCase().includes('quota') || err.message.toLowerCase().includes('resource_exhausted')){
        st.textContent = '✕ Quota Exceeded (429) - Key is valid!'; st.style.color = '#facc15';
      } else {
        st.textContent = '✕ ' + err.message; st.style.color = '#f87171';
      }
    }
  }
}

async function loadSettings(){
  try{
    const j=await req('get_settings');
    const keys = (j.gemini_keys && j.gemini_keys.length) ? j.gemini_keys : (j.gemini_key ? [j.gemini_key] : []);

    for(let i = 0; i < 5; i++){
      const inp = $('geminiKey_' + i);
      if(inp){
        inp.value = (keys && keys[i]) ? keys[i] : '';
      }
      const st = $('keyStatus_' + i);
      if(st) st.textContent = '';
    }
    updateKeyPoolCount();

    if(j.buffer_access_token && $('bufferTokenInput')) $('bufferTokenInput').value=j.buffer_access_token;
    if(j.public_base_url && $('publicBaseUrlInput')) $('publicBaseUrlInput').value=j.public_base_url;

    // Buffer pill
    const bp=$('bufferPill');
    if(bp){
      if(j.has_buffer_token){
        bp.textContent='● BUFFER CONNECTED';
        bp.style.background='#123528';bp.style.borderColor='#245a45';bp.style.color='#71e5aa';
      }else{
        bp.textContent='● NOT CONNECTED';
        bp.style.background='#382215';bp.style.borderColor='#784920';bp.style.color='#ffd699';
      }
    }

    // Email notification settings
    if($('emailNotifyToggle')) $('emailNotifyToggle').checked = !!j.email_notifications_enabled;
    if(j.notification_email && $('notifyEmailInput')) $('notifyEmailInput').value = j.notification_email;
    if(j.smtp_host && $('smtpHostInput')) $('smtpHostInput').value = j.smtp_host;
    if(j.smtp_port && $('smtpPortInput')) $('smtpPortInput').value = j.smtp_port;
    if(j.smtp_user && $('smtpUserInput')) $('smtpUserInput').value = j.smtp_user;
    if(j.has_smtp_pass && $('smtpPassInput')) $('smtpPassInput').placeholder = '•••••••••••••••• (Saved)';

    // SMTP pill
    const sp = $('smtpPill');
    if(sp){
      if(j.email_notifications_enabled && j.smtp_user && j.has_smtp_pass){
        sp.textContent = '● ACTIVE & READY';
        sp.style.background = '#123528'; sp.style.borderColor = '#245a45'; sp.style.color = '#71e5aa';
      } else if(j.smtp_user) {
        sp.textContent = '● CONFIGURED (DISABLED)';
        sp.style.background = '#1e293b'; sp.style.borderColor = '#334155'; sp.style.color = '#94a3b8';
      } else {
        sp.textContent = '● NOT CONFIGURED';
        sp.style.background = '#382215'; sp.style.borderColor = '#784920'; sp.style.color = '#ffd699';
      }
    }
    // Graphic & Typography Design settings
    if (j.post_headline_font_size !== undefined) {
      postDesignConfig.headlineFontSize = parseInt(j.post_headline_font_size, 10) || 0;
      postDesignConfig.descriptionFontSize = parseInt(j.post_description_font_size, 10) || 0;
      postDesignConfig.logoAlignment = j.post_logo_alignment || 'top-right';
      postDesignConfig.footerBranding = j.post_footer_branding !== false;
      postDesignConfig.headlineCase = j.post_headline_case || 'uppercase';
      postDesignConfig.colorPalette = j.post_color_palette || 'gold-cyan';

      syncDesignUI();
      applyDesignPalette(postDesignConfig.colorPalette);
      render();
    }
  }catch(e){console.error('loadSettings error:', e)}
}

function syncDesignUI(){
  if($('settingHlFontSize')) $('settingHlFontSize').value = postDesignConfig.headlineFontSize;
  if($('settingHlFontSizePreset')) $('settingHlFontSizePreset').value = [0,56,64,72,80].includes(postDesignConfig.headlineFontSize) ? postDesignConfig.headlineFontSize : '0';
  if($('hlFontSizeBadge')) $('hlFontSizeBadge').textContent = postDesignConfig.headlineFontSize === 0 ? 'Auto (Dynamic)' : (postDesignConfig.headlineFontSize + 'px');

  if($('settingDescFontSize')) $('settingDescFontSize').value = postDesignConfig.descriptionFontSize;
  if($('settingDescFontSizePreset')) $('settingDescFontSizePreset').value = [0,20,24,28,32].includes(postDesignConfig.descriptionFontSize) ? postDesignConfig.descriptionFontSize : '0';
  if($('descFontSizeBadge')) $('descFontSizeBadge').textContent = postDesignConfig.descriptionFontSize === 0 ? 'Auto (24px)' : (postDesignConfig.descriptionFontSize + 'px');

  if($('settingLogoAlignment')) $('settingLogoAlignment').value = postDesignConfig.logoAlignment;
  if($('settingFooterBranding')) $('settingFooterBranding').value = postDesignConfig.footerBranding ? '1' : '0';
  if($('settingHeadlineCase')) $('settingHeadlineCase').value = postDesignConfig.headlineCase;
  if($('settingColorPalette')) $('settingColorPalette').value = postDesignConfig.colorPalette;

  if($('quickHlFontSize')) $('quickHlFontSize').value = [0,56,64,72,80].includes(postDesignConfig.headlineFontSize) ? postDesignConfig.headlineFontSize : '0';
  if($('quickDescFontSize')) $('quickDescFontSize').value = [0,20,24,28,32].includes(postDesignConfig.descriptionFontSize) ? postDesignConfig.descriptionFontSize : '0';
  if($('quickLogoAlign')) $('quickLogoAlign').value = postDesignConfig.logoAlignment;
  if($('quickFooterBrand')) $('quickFooterBrand').value = postDesignConfig.footerBranding ? '1' : '0';
}

function onSettingHlSlider(val){
  const num = parseInt(val, 10) || 0;
  postDesignConfig.headlineFontSize = num;
  if($('hlFontSizeBadge')) $('hlFontSizeBadge').textContent = num === 0 ? 'Auto (Dynamic)' : (num + 'px');
  if($('settingHlFontSizePreset')) $('settingHlFontSizePreset').value = [0,56,64,72,80].includes(num) ? num : '0';
  if($('quickHlFontSize')) $('quickHlFontSize').value = [0,56,64,72,80].includes(num) ? num : '0';
  render();
}

function applyHlPreset(val){
  const num = parseInt(val, 10) || 0;
  postDesignConfig.headlineFontSize = num;
  if($('settingHlFontSize')) $('settingHlFontSize').value = num;
  if($('hlFontSizeBadge')) $('hlFontSizeBadge').textContent = num === 0 ? 'Auto (Dynamic)' : (num + 'px');
  if($('quickHlFontSize')) $('quickHlFontSize').value = [0,56,64,72,80].includes(num) ? num : '0';
  render();
}

function setQuickHlFont(val){
  applyHlPreset(val);
}

function onSettingDescSlider(val){
  const num = parseInt(val, 10) || 0;
  postDesignConfig.descriptionFontSize = num;
  if($('descFontSizeBadge')) $('descFontSizeBadge').textContent = num === 0 ? 'Auto (24px)' : (num + 'px');
  if($('settingDescFontSizePreset')) $('settingDescFontSizePreset').value = [0,20,24,28,32].includes(num) ? num : '0';
  if($('quickDescFontSize')) $('quickDescFontSize').value = [0,20,24,28,32].includes(num) ? num : '0';
  render();
}

function applyDescPreset(val){
  const num = parseInt(val, 10) || 0;
  postDesignConfig.descriptionFontSize = num;
  if($('settingDescFontSize')) $('settingDescFontSize').value = num;
  if($('descFontSizeBadge')) $('descFontSizeBadge').textContent = num === 0 ? 'Auto (24px)' : (num + 'px');
  if($('quickDescFontSize')) $('quickDescFontSize').value = [0,20,24,28,32].includes(num) ? num : '0';
  render();
}

function setQuickDescFont(val){
  applyDescPreset(val);
}

function setQuickLogoAlign(val){
  postDesignConfig.logoAlignment = val;
  if($('settingLogoAlignment')) $('settingLogoAlignment').value = val;
  render();
}

function setQuickFooterBrand(val){
  postDesignConfig.footerBranding = (val === '1');
  if($('settingFooterBranding')) $('settingFooterBranding').value = val;
  render();
}

function onDesignInputChange(){
  if($('settingLogoAlignment')) postDesignConfig.logoAlignment = $('settingLogoAlignment').value;
  if($('settingFooterBranding')) postDesignConfig.footerBranding = ($('settingFooterBranding').value === '1');
  if($('settingHeadlineCase')) postDesignConfig.headlineCase = $('settingHeadlineCase').value;
  if($('settingColorPalette')){
    postDesignConfig.colorPalette = $('settingColorPalette').value;
    applyDesignPalette(postDesignConfig.colorPalette);
  }
  syncDesignUI();
  render();
}

async function saveDesignSettings(e){
  if(e && e.preventDefault) e.preventDefault();
  const b = $('saveDesignBtn');
  if(b) b.disabled = true;
  const msg = $('designMsg');
  if(msg){
    msg.textContent = 'Saving design settings...';
    msg.className = 'status';
  }
  try{
    onDesignInputChange();
    const res = await req('save_settings', {
      method: 'POST',
      body: JSON.stringify({
        post_headline_font_size: postDesignConfig.headlineFontSize,
        post_description_font_size: postDesignConfig.descriptionFontSize,
        post_logo_alignment: postDesignConfig.logoAlignment,
        post_footer_branding: postDesignConfig.footerBranding,
        post_headline_case: postDesignConfig.headlineCase,
        post_color_palette: postDesignConfig.colorPalette
      })
    });
    if(msg){
      msg.textContent = '✓ Graphic design settings saved! Applied to manual studio & 3x/day automated posts.';
      msg.className = 'status';
    }
  }catch(err){
    if(msg){
      msg.textContent = '✕ ' + err.message;
      msg.className = 'status error';
    }
  }finally{
    if(b) b.disabled = false;
  }
}

async function resetDesignSettingsDefaults(){
  postDesignConfig = {
    headlineFontSize: 0,
    descriptionFontSize: 0,
    logoAlignment: 'top-right',
    footerBranding: true,
    headlineCase: 'uppercase',
    colorPalette: 'gold-cyan'
  };
  syncDesignUI();
  applyDesignPalette('gold-cyan');
  render();
  await saveDesignSettings();
}

async function saveSettings(e){
  e.preventDefault();
  const b=$('saveSettingsBtn');
  b.disabled=true;
  $('settingsMsg').textContent='Saving keys & settings...';
  $('settingsMsg').classList.remove('error');

  const currentKeys = [];
  for(let i = 0; i < 5; i++){
    const inp = $('geminiKey_' + i);
    if(inp){
      const val = inp.value.trim();
      if(val && !currentKeys.includes(val)) currentKeys.push(val);
    }
  }

  try{
    const pass=$('newAdminPassword').value.trim();
    const j=await req('save_settings',{
      method:'POST',
      body:JSON.stringify({
        gemini_api_keys: currentKeys,
        gemini_api_key: currentKeys[0] || '',
        admin_password: pass,
        test: false
      })
    });
    $('settingsMsg').textContent='✓ '+(j.message||'Settings saved successfully! ('+currentKeys.length+' key(s) in failover pool)');
    $('newAdminPassword').value='';
    await loadSettings();
  }catch(e){
    $('settingsMsg').textContent=e.message;
    $('settingsMsg').classList.add('error');
  }finally{b.disabled=false}
}

async function syncBufferNow(){
  const tok = $('bufferTokenInput').value.trim();
  const msg = $('bufferMsg');
  const btn = $('syncBufferBtn');
  if(!tok){
    msg.textContent = 'Please enter a Buffer Access Token.';
    msg.className = 'status error';
    return;
  }
  btn.disabled = true;
  btn.textContent = '⚡ Connecting to Buffer...';
  msg.textContent = 'Connecting to Buffer API (https://api.bufferapp.com/1/profiles.json)...';
  msg.className = 'status';

  try{
    const res = await req('buffer_sync', {
      method: 'POST',
      body: JSON.stringify({ buffer_token: tok })
    });
    msg.textContent = '✓ ' + res.message;
    msg.className = 'status';
    await loadChannels();
    await loadSettings();
    status('✓ ' + res.message);
  }catch(err){
    msg.textContent = '✗ ' + err.message;
    msg.className = 'status error';
  }finally{
    btn.disabled = false;
    btn.textContent = '⚡ Connect & Sync Real Channels';
  }
}

async function saveBufferSettings(e){
  e.preventDefault();
  const tok = $('bufferTokenInput').value.trim();
  const pubUrl = $('publicBaseUrlInput') ? $('publicBaseUrlInput').value.trim() : '';
  const msg = $('bufferMsg');
  try{
    const res = await req('save_settings', {
      method: 'POST',
      body: JSON.stringify({
        buffer_access_token: tok,
        public_base_url: pubUrl
      })
    });
    msg.textContent = '✓ Publishing configuration saved!';
    msg.className = 'status';
    await loadSettings();
  }catch(err){
    msg.textContent = '✗ ' + err.message;
    msg.className = 'status error';
  }
}

async function saveEmailSettings(e){
  if(e && e.preventDefault) e.preventDefault();
  const enabled = $('emailNotifyToggle').checked;
  const notifyEmail = $('notifyEmailInput').value.trim();
  const smtpHost = $('smtpHostInput').value.trim();
  const smtpPort = parseInt($('smtpPortInput').value, 10) || 465;
  const smtpUser = $('smtpUserInput').value.trim();
  const smtpPass = $('smtpPassInput').value.trim();
  const msg = $('emailMsg');

  try{
    const body = {
      email_notifications_enabled: enabled,
      notification_email: notifyEmail,
      smtp_host: smtpHost,
      smtp_port: smtpPort,
      smtp_user: smtpUser
    };
    if(smtpPass) body.smtp_pass = smtpPass;

    const res = await req('save_settings', {
      method: 'POST',
      body: JSON.stringify(body)
    });
    msg.textContent = '✓ Email notification settings saved!';
    msg.className = 'status';
    await loadSettings();
  }catch(err){
    msg.textContent = '✗ ' + err.message;
    msg.className = 'status error';
  }
}

async function sendTestEmailNow(){
  const btn = $('testEmailBtn');
  const msg = $('emailMsg');
  btn.disabled = true;
  btn.textContent = '✉ Sending Test Email...';
  msg.textContent = 'Connecting to Gmail SMTP server (smtp.gmail.com:465)...';
  msg.className = 'status';

  const body = {
    notification_email: $('notifyEmailInput').value.trim(),
    smtp_host: $('smtpHostInput').value.trim(),
    smtp_port: parseInt($('smtpPortInput').value, 10) || 465,
    smtp_user: $('smtpUserInput').value.trim(),
    smtp_pass: $('smtpPassInput').value.trim()
  };

  try{
    const res = await req('test_email', {
      method: 'POST',
      body: JSON.stringify(body)
    });
    msg.textContent = '✓ ' + res.message;
    msg.className = 'status';
  }catch(err){
    msg.textContent = '✗ ' + err.message;
    msg.className = 'status error';
  }finally{
    btn.disabled = false;
    btn.textContent = '✉ Send Test Email';
  }
}

function toggleKeyVis(inputId, btnId){
  const inp = $(inputId || 'geminiKeyInput');
  const btn = $(btnId || 'toggleGeminiKey');
  if(!inp) return;
  inp.type = inp.type === 'password' ? 'text' : 'password';
  if(btn) btn.textContent = inp.type === 'password' ? 'SHOW' : 'HIDE';
}

function status(s,err=false){$('status').textContent=s;$('status').classList.toggle('error',err)}
async function fetchNews(){
  const b=$('fetchBtn');
  b.disabled=true;
  $('newsStatus').textContent="Fetching today's entertainment stories...";
  try{
    const j=await req('news');
    articles=j.items;
    $('stories').replaceChildren();
    if(!articles.length){
      $('newsStatus').textContent='No articles found for today yet. Check back soon!';
      return;
    }
    articles.forEach((a,i)=>{
      const el=document.createElement('button');
      el.type='button';
      el.className='story';

      // Use 300x300 compressed thumbnail for ultra-fast article list loading
      const thumbUrl = a.thumbnail || (a.image ? (a.image.split('?')[0] + '?width=300&height=300&fit=crop&format=auto&quality=60') : '');
      if(thumbUrl){
        const img=document.createElement('img');
        img.className='story-thumb';
        img.loading='lazy';
        img.alt='thumbnail';
        img.referrerPolicy='no-referrer';
        img.onerror=()=>{
          if(!img.dataset.proxied){
            img.dataset.proxied='1';
            img.src='api/index.php?action=image&url='+encodeURIComponent(thumbUrl);
          }else{
            img.style.display='none';
          }
        };
        img.src=thumbUrl;
        el.append(img);
      }

      const body=document.createElement('div');
      body.className='story-body';

      const meta=document.createElement('div');
      meta.className='story-meta';
      const ix=document.createElement('span');
      ix.className='idx';
      ix.textContent='ARTICLE '+String(i+1).padStart(2,'0');
      const dt=document.createElement('span');
      dt.className='story-date';
      dt.textContent=a.date_badge||'TODAY';
      meta.append(ix,dt);

      const title=document.createElement('div');
      title.className='story-title';
      title.textContent=a.title;

      body.append(meta,title);
      el.append(body);

      el.onclick=()=>selectStory(a,el);
      $('stories').append(el);
    });
    $('newsStatus').textContent=articles.length+" stories from today. Select any story to auto-apply!";
    // Auto-select the first story if nothing selected yet
    if(!current && articles.length > 0){
      selectStory(articles[0], $('stories').children[0]);
    }
  }catch(e){
    $('newsStatus').textContent=e.message;
  }finally{
    b.disabled=false;
  }
}
async function selectStory(a,el){
  document.querySelectorAll('.story').forEach(x=>x.classList.remove('selected'));
  if(el)el.classList.add('selected');
  current=a;
  sourceUrl=a.url;

  // Derive theme colors (random selection from Orange, Yellow, Blue)
  currentTheme = pickThemeForArticle(a.title || '');

  // 1. Auto-apply Title & Headline (sanitized from any source site names)
  const cleanTitle = cleanSourceText(a.title);
  $('sourceTitle').value=cleanTitle;
  $('headline').value=cleanTitle.toUpperCase();

  // 2. Auto-apply Description (sanitized)
  const cleanDesc = cleanSourceText(a.description||'');
  $('description').value=cleanDesc||'Loading source description...';
  $('caption').value=cleanTitle + (cleanDesc ? '\n\n' + cleanDesc : '') + '\n\n#voxbulletin #EntertainmentNews';
  if($('captionX')){
    $('captionX').value=formatTwitterPost(cleanTitle, cleanDesc);
    updateXCounter();
  }

  // 3. Auto-apply Hero Image (Main Full-Resolution for 1080x1350 canvas)
  const fullImageUrl = (a.image || '').split('?')[0];
  sourceImage = fullImageUrl;
  photo = null;
  status('Auto-applying story & topic image...');
  render();

  if(fullImageUrl && (fullImageUrl.startsWith('http://') || fullImageUrl.startsWith('https://'))){
    const proxyUrl = 'api/index.php?action=image&url=' + encodeURIComponent(fullImageUrl);
    const img = new Image();
    img.crossOrigin = 'anonymous'; // Direct draw into canvas without tainting
    img.onload = () => {
      photo = img;
      status('✓ Story, high-res image and description auto-applied! Ready to export or draft.');
      render();
    };
    img.onerror = () => {
      // Fallback to server proxy if direct CORS fails
      const fallbackImg = new Image();
      fallbackImg.onload = () => {
        photo = fallbackImg;
        status('✓ Story, image and description auto-applied via proxy! Ready to export or draft.');
        render();
      };
      fallbackImg.onerror = () => {
        status('Source image could not be loaded; you can upload an image manually.', true);
        render();
      };
      fallbackImg.src = proxyUrl;
    };
    img.src = fullImageUrl;
  } else {
    render();
  }
}
async function generate(evt){
  if(evt && evt.preventDefault) evt.preventDefault();
  if(evt && evt.stopPropagation) evt.stopPropagation();
  if(!current) return status('Select an article first.', true);
  const b=$('generateBtn');
  b.disabled=true;
  status('✦ Gemini is writing your draft (Draft only — will not post to social media)...');
  try{
    const j=await req('draft',{method:'POST',body:JSON.stringify({title:current.title,description:$('description').value,url:current.url})});
    $('headline').value=j.draft.headline;
    $('description').value=j.draft.description;
    $('caption').value=j.draft.caption;
    if($('captionX')){
      $('captionX').value=formatTwitterPost(j.draft.headline,j.draft.description);
      updateXCounter();
    }
    render();
    status('✓ Draft generated with Gemini! (Draft only — NOT published). Review and edit the copy, then click "🚀 Publish to Selected Channels" only when ready to post live.');
  }catch(e){
    status(e.message, true);
  }finally{
    b.disabled=false;
  }
}
function loadFile(file,callback){if(!file)return;const url=URL.createObjectURL(file);const img=new Image();img.onload=()=>{callback(img);URL.revokeObjectURL(url);render()};img.onerror=()=>status('Could not read image',true);img.src=url}
$('photoInput').onchange=e=>loadFile(e.target.files[0],i=>photo=i);$('logoInput').onchange=e=>loadFile(e.target.files[0],i=>logo=i);
async function loadSourceImage(){if(!sourceImage)return status('No source image was found.',true);try{const cleanUrl=sourceImage.split('?')[0];const r=await fetch('api/index.php?action=image&url='+encodeURIComponent(cleanUrl));if(!r.ok)throw Error('Source image unavailable. Upload an image you have permission to use.');const blob=await r.blob();loadFile(new File([blob],'source-image.jpg',{type:blob.type}),i=>photo=i);status('Source image loaded for preview. Check reuse rights before publishing.')}catch(e){status(e.message,true)}}
function wrapLines(ctx,text,maxWidth,maxLines=6){const words=text.trim().split(/\s+/).filter(Boolean);if(!words.length)return[];const lines=[];let cur=words[0];for(let i=1;i<words.length;i++){const test=cur+' '+words[i];if(ctx.measureText(test).width<=maxWidth){cur=test}else{lines.push(cur);cur=words[i];if(lines.length===maxLines-1){const rest=words.slice(i).join(' ');let last=rest;while(last&&ctx.measureText(last+'…').width>maxWidth){last=last.slice(0,-1)}lines.push(last===rest?last:(last+'…'));return lines}}}if(cur)lines.push(cur);return lines}
function render(){
  const c=$('poster'),ctx=c.getContext('2d'),W=1080,H=1350;
  ctx.fillStyle='#001226';
  ctx.fillRect(0,0,W,H);

  // 1. Photo rendering: draw deeper (down to 880px) so the bottom is completely buried
  if(photo){
    const imgH=880;
    const scale=Math.max(W/photo.width,imgH/photo.height);
    const sw=W/scale,sh=imgH/scale;
    ctx.drawImage(photo,(photo.width-sw)/2,0,sw,sh,0,0,W,imgH);
  }else{
    const g=ctx.createLinearGradient(0,0,W,780);
    g.addColorStop(0,'#18385b');
    g.addColorStop(1,'#001226');
    ctx.fillStyle=g;
    ctx.fillRect(0,0,W,780);
    ctx.font='900 62px "Barlow Condensed",Impact,sans-serif';
    ctx.fillStyle='#57728c';
    ctx.textAlign='center';
    ctx.fillText('ADD YOUR TOPIC IMAGE',540,370);
  }

  // 2. Seamless gradient fade: starts at y=360, reaches 100% solid navy at y=720
  const shade=ctx.createLinearGradient(0,360,0,720);
  shade.addColorStop(0.00,'rgba(0,18,38,0)');
  shade.addColorStop(0.25,'rgba(0,18,38,0.18)');
  shade.addColorStop(0.50,'rgba(0,18,38,0.52)');
  shade.addColorStop(0.75,'rgba(0,18,38,0.85)');
  shade.addColorStop(0.92,'rgba(0,18,38,0.98)');
  shade.addColorStop(1.00,'#001226');
  ctx.fillStyle=shade;
  ctx.fillRect(0,360,W,720-360+1);

  // Solid navy fill from 720 downwards (photo bottom edge at 880 is 100% hidden)
  ctx.fillStyle='#001226';
  ctx.fillRect(0,720,W,H-720);

  // 3. Brand Logo: 3D Globe Logo (image.png) + Vertically Centered VoxBulletin Text
  const logoAlign = postDesignConfig.logoAlignment || 'top-right';
  if(logoAlign !== 'hidden'){
    if(logo){
      const k=Math.min(180/logo.width,50/logo.height);
      const lw = logo.width*k;
      let lx = W - 55 - lw;
      if(logoAlign === 'top-left') lx = 55;
      else if(logoAlign === 'top-center') lx = Math.round((W - lw) / 2);
      ctx.drawImage(logo,lx,48,lw,logo.height*k);
    } else {
      ctx.save();
      const logoSize = 44;
      const gap = 12;
      const logoText = 'VoxBulletin';

      ctx.font = '800 28px "DM Sans",Arial,sans-serif';
      const tw = ctx.measureText(logoText).width;
      const totalLogoW = logoSize + gap + tw;
      const topY = 48;
      
      let startX = W - 55 - totalLogoW;
      if(logoAlign === 'top-left') startX = 55;
      else if(logoAlign === 'top-center') startX = Math.round((W - totalLogoW) / 2);

      // Sleek translucent dark backing pill for 100% legibility on any photo
      const padX = 16, padY = 9;
      const pillW = totalLogoW + padX * 2;
      const pillH = logoSize + padY * 2;
      ctx.fillStyle = 'rgba(0, 18, 38, 0.72)';
      drawRoundedRect(ctx, startX - padX, topY - padY, pillW, pillH, 16);
      ctx.fill();
      ctx.strokeStyle = 'rgba(255, 255, 255, 0.16)';
      ctx.lineWidth = 1;
      ctx.stroke();

      // Draw 3D Globe Logo (image.png / brand-globe.png)
      if(brandGlobeImg && brandGlobeImg.complete && brandGlobeImg.naturalWidth > 0){
        ctx.drawImage(brandGlobeImg, startX, topY, logoSize, logoSize);
      }

      // Draw VoxBulletin text perfectly centered vertically to the logo
      const centerY = topY + logoSize / 2;
      ctx.fillStyle = '#ffffff';
      ctx.font = '800 28px "DM Sans",Arial,sans-serif';
      ctx.textAlign = 'left';
      ctx.textBaseline = 'middle';
      ctx.fillText(logoText, startX + logoSize + gap, centerY);
      ctx.restore();
    }
  }

  // 4. Headline sizing & line-wrapping (max-width 960 to prevent orphan single words)
  const rawHl = $('headline').value || 'YOUR ENTERTAINMENT HEADLINE';
  const hlCase = postDesignConfig.headlineCase || 'uppercase';
  let headline = rawHl.trim();
  if (hlCase === 'uppercase') headline = headline.toUpperCase();
  else if (hlCase === 'titlecase') headline = toTitleCase(headline);

  const words=headline.replace(/\*/g,'').split(/\s+/).filter(Boolean);
  const wordCount=words.length;

  const customHl = parseInt(postDesignConfig.headlineFontSize, 10) || 0;
  let hlFont = customHl > 0 ? customHl : (wordCount<=6 ? 74 : (wordCount<=12 ? 68 : 62));

  let lines=[];
  do{
    ctx.font='900 '+hlFont+'px "Barlow Condensed",Impact,sans-serif';
    lines=wrapLines(ctx,headline.replace(/\*/g,''),960,4);
    if(customHl > 0) break;
    hlFont-=2;
  }while(hlFont>46&&(lines.some(l=>l.endsWith('…'))||(lines.length>1&&lines[lines.length-1].split(/\s+/).length===1&&lines[lines.length-1].length<=5&&hlFont>=58)));
  const finalHlFont = customHl > 0 ? customHl : (hlFont+2);
  const hlLineH=Math.round(finalHlFont*1.13);

  // 5. Description sizing & line-wrapping (strictly fits 4-5 complete lines without truncation)
  const desc=($('description').value||'Your concise story summary will appear here.').trim();
  const descLen=desc.length;
  const customDesc = parseInt(postDesignConfig.descriptionFontSize, 10) || 0;
  let descFont = customDesc > 0 ? customDesc : (descLen>280 ? 21 : (descLen>225 ? 23 : 25));

  ctx.font='500 '+descFont+'px "DM Sans",Arial,sans-serif';
  const dl=wrapLines(ctx,desc,940,5);
  const descLineH=Math.round(descFont*1.45);

  // 6. Balanced layout: position headline + description as ONE cohesive group
  const gapVisual=28; // tight, clean visual gap between headline and description
  const headlineBlockH=(lines.length-1)*hlLineH+Math.round(finalHlFont*0.85);
  const descBlockH=dl.length?((dl.length-1)*descLineH+Math.round(descFont*0.85)):0;
  const totalBlockH=headlineBlockH+(dl.length?(gapVisual+descBlockH):0);

  const darkTop=735;
  const footerY=1292;
  const availSpace=(footerY-45)-darkTop;
  const blockTop=darkTop+Math.max(15,(availSpace-totalBlockH)/2);
  let curY=Math.round(blockTop+finalHlFont*0.76);

  // 7. Render Headline
  const [colorA, colorB] = (Array.isArray(currentTheme) && currentTheme.length >= 2) ? currentTheme : ['#ffd12a', '#00d2ff'];
  ctx.textAlign='center';
  ctx.font='900 '+finalHlFont+'px "Barlow Condensed",Impact,sans-serif';
  let lastHlBaseline=curY;
  lines.forEach((line,i)=>{
    let color='#ffffff';
    if(lines.length===1)color=colorA;
    else if(lines.length===2)color=(i===0?colorA:colorB);
    else if(lines.length===3)color=(i===0?colorA:(i===2?colorB:'#ffffff'));
    else color=(i===0?colorA:(i===lines.length-1?colorB:'#ffffff'));

    ctx.fillStyle=color;
    ctx.fillText(line,540,curY);
    lastHlBaseline=curY;
    if(i<lines.length-1)curY+=hlLineH;
  });

  // 8. Render Description DIRECTLY below the title (no huge gap!)
  if(dl.length){
    // Baseline of first description line placed tightly below the last headline line
    let descY=lastHlBaseline+Math.round(finalHlFont*0.12)+gapVisual+Math.round(descFont*0.72);
    ctx.font='500 '+descFont+'px "DM Sans",Arial,sans-serif';
    ctx.fillStyle='#a6b6ce';
    dl.forEach(line=>{
      ctx.fillText(line,540,descY);
      descY+=descLineH;
    });
  }

  // 9. Centered Vox Bulletin brand with glowing dividers (Optional visibility)
  if (postDesignConfig.footerBranding !== false) {
    ctx.strokeStyle='rgba(255,255,255,0.7)';
    ctx.lineWidth=1.5;
    ctx.beginPath();
    ctx.moveTo(270,footerY);
    ctx.lineTo(430,footerY);
    ctx.moveTo(650,footerY);
    ctx.lineTo(810,footerY);
    ctx.stroke();

    ctx.fillStyle='#ffffff';
    ctx.font='700 20px "DM Sans",Arial,sans-serif';
    ctx.textAlign='center';
    ctx.fillText('VOX   BULLETIN',540,footerY+7);
  }
}
['headline','description'].forEach(id=>$(id).addEventListener('input',render));if($('captionX'))$('captionX').addEventListener('input',updateXCounter);document.fonts.ready.then(render);render();if(csrf){loadSettings();loadChannels();fetchNews();}
function download(){render();const a=document.createElement('a');a.href=$('poster').toDataURL('image/png');a.download='vox-bulletin-'+Date.now()+'.png';a.click()}
async function save(){if(!current)return status('Select a source article first.',true);if(!photo)return status('Add a topic image before saving.',true);if(!$('headline').value.trim()||!$('description').value.trim())return status('Headline and description are required.',true);const b=$('saveBtn');b.disabled=true;status('Saving PNG and post metadata to Hostinger...');try{render();const j=await req('save',{method:'POST',body:JSON.stringify({url:current.url,source_title:current.title,headline:$('headline').value,description:$('description').value,caption:$('caption').value,source_image_url:sourceImage,image:$('poster').toDataURL('image/png')})});status('Saved successfully! Post #'+j.id+' is available in your Post Library.')}catch(e){status(e.message,true)}finally{b.disabled=false}}
let savedPostsData = [];

async function loadPosts(){
  const grid=$('postGrid');
  grid.innerHTML='<div style="grid-column:1/-1;text-align:center;padding:40px;color:#94a3b8"><span style="animation:spin 1s linear infinite;display:inline-block">&#9696;</span> Loading post library...</div>';
  updateSelectedCount();
  try{
    const j=await req('posts');
    savedPostsData = j.items || [];
    grid.replaceChildren();
    if($('postCountBadge')) $('postCountBadge').textContent = savedPostsData.length + ' post' + (savedPostsData.length === 1 ? '' : 's');

    if(!savedPostsData.length){
      grid.innerHTML='<div style="grid-column:1/-1;text-align:center;padding:60px;color:#64748b;font-size:15px">No saved posts yet. Create a post or let the 3x/day automation generate them!</div>';
      return;
    }

    savedPostsData.forEach(p => {
      const card=document.createElement('div');
      card.className='post';
      card.id = 'postCard_' + p.id;

      // Selection Checkbox
      const checkWrap = document.createElement('label');
      checkWrap.className = 'post-check-wrap';
      checkWrap.title = 'Select post';
      const chk = document.createElement('input');
      chk.type = 'checkbox';
      chk.className = 'post-check';
      chk.value = p.id;
      chk.onchange = () => {
        card.classList.toggle('selected', chk.checked);
        updateSelectedCount();
      };
      checkWrap.append(chk);
      card.append(checkWrap);

      // Thumbnail Image
      const im=document.createElement('img');
      im.src='storage/posts/'+encodeURIComponent(p.image_file);
      im.loading='lazy';
      im.alt='post image';
      im.onerror = () => { im.style.background = '#0a101d'; im.style.display = 'none'; };
      card.append(im);

      const body=document.createElement('div');
      const h=document.createElement('h3');
      h.textContent=p.headline;

      const s=document.createElement('p');
      s.className='muted';
      s.style.fontSize='12px';
      s.style.margin='0 0 10px';
      s.textContent=p.created_at;

      const chRow=document.createElement('div');
      chRow.style.display='flex';
      chRow.style.gap='5px';
      chRow.style.flexWrap='wrap';
      chRow.style.marginBottom='10px';
      if(p.channels_published){
        p.channels_published.split(',').filter(Boolean).forEach(ch=>{
          const chSpan=document.createElement('span');
          chSpan.className='pill-ch';
          chSpan.textContent=(PLATFORMS_META[ch]||{}).name||ch;
          chRow.append(chSpan);
        });
      }

      // Footer with Download and Delete buttons
      const footer=document.createElement('div');
      footer.className='post-card-footer';

      const a=document.createElement('a');
      a.href=im.src;
      a.download='vox-bulletin-'+p.id+'.png';
      a.textContent='↓ Download';

      const delBtn=document.createElement('button');
      delBtn.type='button';
      delBtn.className='btn-del-single';
      delBtn.innerHTML='🗑 Delete';
      delBtn.title='Permanently delete this post and free storage';
      delBtn.onclick=()=>deleteSinglePost(p.id);

      footer.append(a, delBtn);
      body.append(h, s, chRow, footer);
      card.append(body);
      grid.append(card);
    });
  }catch(e){
    grid.innerHTML='<div style="grid-column:1/-1;color:#ef4444;padding:20px">'+e.message+'</div>';
  }
}

function updateSelectedCount(){
  const checked = document.querySelectorAll('.post-check:checked');
  const count = checked.length;
  if($('selectedCount')) $('selectedCount').textContent = count;
  if($('deleteSelectedBtn')) $('deleteSelectedBtn').disabled = (count === 0);
  if($('selectAllBtn')){
    const total = document.querySelectorAll('.post-check').length;
    $('selectAllBtn').textContent = (total > 0 && count === total) ? 'Deselect All' : 'Select All';
  }
}

function toggleSelectAllPosts(){
  const allChecks = Array.from(document.querySelectorAll('.post-check'));
  if(!allChecks.length) return;
  const anyUnchecked = allChecks.some(c => !c.checked);
  allChecks.forEach(c => {
    c.checked = anyUnchecked;
    const card = $('postCard_' + c.value);
    if(card) card.classList.toggle('selected', anyUnchecked);
  });
  updateSelectedCount();
}

async function deleteSinglePost(id){
  if(!confirm('Are you sure you want to delete Post #' + id + '?\n\nIts 1080x1350 image file will be permanently removed from disk storage to free up space.')){
    return;
  }
  try{
    const res = await req('delete_post', {
      method: 'POST',
      body: JSON.stringify({ id: id })
    });
    showLibraryNotice('✓ ' + res.message);
    await loadPosts();
  }catch(e){
    showLibraryNotice('✗ ' + e.message, true);
  }
}

async function deleteSelectedPosts(){
  const checked = Array.from(document.querySelectorAll('.post-check:checked'));
  const ids = checked.map(c => parseInt(c.value, 10)).filter(Boolean);
  if(!ids.length) return;

  const total = document.querySelectorAll('.post-check').length;
  const isAll = (ids.length === total);

  const confirmMsg = isAll
    ? '⚠️ DELETE ALL POSTS:\n\nAre you sure you want to permanently delete ALL ' + ids.length + ' saved posts?\n\nAll corresponding image files will be permanently deleted from disk storage to free up disk space.'
    : 'Are you sure you want to delete ' + ids.length + ' selected post(s)?\n\nTheir image files will be permanently removed from disk storage.';

  if(!confirm(confirmMsg)) return;

  const btn = $('deleteSelectedBtn');
  btn.disabled = true;
  btn.textContent = 'Deleting...';

  try{
    const res = await req('delete_posts', {
      method: 'POST',
      body: JSON.stringify({ ids: ids, all: isAll })
    });
    showLibraryNotice('✓ ' + res.message);
    await loadPosts();
  }catch(e){
    showLibraryNotice('✗ ' + e.message, true);
  }finally{
    if($('deleteSelectedBtn')) $('deleteSelectedBtn').textContent = '🗑 Delete Selected (' + document.querySelectorAll('.post-check:checked').length + ')';
  }
}

async function cleanupWeeklyPosts(){
  if(!confirm('🧹 RUN 7-DAY STORAGE CLEANUP:\n\nAre you sure you want to purge all posts and images older than 7 days?\n\nThis permanently frees disk storage so Hostinger never runs out of space.\n(Note: This also runs automatically in your 3x/day cron scheduler).')){
    return;
  }
  showLibraryNotice('Running 7-day storage cleanup...');
  try{
    const res = await req('cleanup_storage', { method: 'POST', body: '{}' });
    showLibraryNotice('✓ ' + res.message);
    await loadPosts();
  }catch(e){
    showLibraryNotice('✗ ' + e.message, true);
  }
}

function showLibraryNotice(msg, isError){
  const el = $('libraryStatus');
  if(!el) return;
  el.textContent = msg;
  el.className = 'notice' + (isError ? ' error' : '');
  el.classList.remove('hide');
  setTimeout(() => { el.classList.add('hide'); }, 6000);
}
</script>
<!-- Connect Channel & Consent Modal (Matching Images 1, 2, 3, 4, 5) -->
<div id="channelModal" class="modal-overlay hide">
  <div class="modal-card">
    <div class="modal-header">
      <h2 id="channelModalTitle">Connect a New Channel</h2>
      <button class="modal-close" onclick="closeChannelModal()" type="button">&times;</button>
    </div>
    <div class="modal-body">
      <!-- STEP 1: Channels Grid (Image 1) -->
      <div id="channelGridStep">
        <!-- Fast Buffer Sync Banner -->
        <div class="buffer-sync-banner">
          <div style="display:flex;align-items:center;gap:12px">
            <span style="font-size:24px">⚡</span>
            <div>
              <div style="font-weight:700;font-size:15px;color:#fff">1-Click Multi-Channel Sync via Buffer</div>
              <div class="muted" style="font-size:12px;margin-top:2px">Already connected Vox Bulletin on Buffer as shown in your screenshots? Sync Instagram, Facebook, X, and Pinterest all at once.</div>
            </div>
          </div>
          <div style="display:flex;gap:8px;margin-top:12px;flex-wrap:wrap">
            <input id="quickBufferToken" type="password" placeholder="Paste your Buffer Access Token" style="flex:1;min-width:220px;padding:9px 12px;background:#0d1829;border:1px solid #233554;border-radius:6px;color:#fff;font-size:13px">
            <button type="button" class="btn blue small" onclick="quickBufferSync()" id="quickSyncBtn">⚡ Sync Real Channels Now</button>
          </div>
          <div id="quickSyncStatus" class="status" style="margin-top:6px;padding:0;background:transparent"></div>
          <div class="muted" style="font-size:11px;margin-top:6px">
            Get your token in 1 click at <a href="https://buffer.com/developers/api/manage" target="_blank" rel="noopener" style="color:#ffd12a;text-decoration:underline">buffer.com/developers/api/manage ↗</a>
          </div>
        </div>

        <p class="muted" style="margin:16px 0 12px;font-size:13px;font-weight:600;color:#94a3b8">OR CONNECT INDIVIDUAL PLATFORMS:</p>
        <div id="channelsGridContainer" class="channels-grid"></div>
      </div>

      <!-- STEP 2: Consent & Authorization Screen (Images 2, 3, 4, 5) -->
      <div id="channelConsentStep" class="hide consent-wrap">
        <button type="button" class="btn alt small" onclick="backToChannelsGrid()" style="margin-bottom:18px">&larr; Back to Channels</button>
        <div class="consent-brand-row">
          <div id="consentIconWrap" class="channel-icon-wrap" style="width:48px;height:48px"></div>
          <div style="font-size:22px;font-weight:800" id="consentPlatformTitle">Instagram</div>
        </div>
        <p style="text-align:center;font-size:14px;color:#d8e5f7;margin-bottom:20px" id="consentHeading">
          <b style="color:#5784ff">VoxBulletin Studio</b> is requesting access to your account.
        </p>

        <form id="consentForm" onsubmit="submitChannelAuth(event)">
          <div class="field">
            <label>ACCOUNT HANDLE / PAGE NAME</label>
            <input id="consentHandle" type="text" required placeholder="@voxbulletin or Page Name">
          </div>

          <div class="consent-box">
            <div class="consent-item">
              <div>
                <div class="consent-item-label">View profile and access media (required)</div>
                <div class="consent-item-sub">Access your account identity, profile photo, and media uploads</div>
              </div>
              <label class="switch"><input type="checkbox" checked disabled><span class="slider"></span></label>
            </div>
            <div class="consent-item">
              <div>
                <div class="consent-item-label">Access and publish content</div>
                <div class="consent-item-sub">Create, upload, and publish graphics and captions directly</div>
              </div>
              <label class="switch"><input type="checkbox" id="permPublish" checked><span class="slider"></span></label>
            </div>
            <div class="consent-item">
              <div>
                <div class="consent-item-label">Access and manage comments</div>
                <div class="consent-item-sub">Read incoming responses to published posts</div>
              </div>
              <label class="switch"><input type="checkbox" id="permComments" checked><span class="slider"></span></label>
            </div>
            <div class="consent-item">
              <div>
                <div class="consent-item-label">Access and manage insights</div>
                <div class="consent-item-sub">Track reach, impressions, and engagement metrics</div>
              </div>
              <label class="switch"><input type="checkbox" id="permInsights" checked><span class="slider"></span></label>
            </div>
          </div>

          <div class="field">
            <label>API ACCESS TOKEN / PROFILE ID</label>
            <input id="consentToken" type="password" placeholder="Paste live API Access Token or Buffer Profile ID">
            <p class="muted" style="margin-top:6px;font-size:11px">Enter your live account Access Token or Buffer Token to enable real publishing.</p>
          </div>

          <div class="actions" style="margin-top:22px">
            <button type="submit" class="btn blue" style="flex:1;padding:14px" id="consentSubmitBtn">Allow & Connect Account</button>
            <button type="button" class="btn alt" onclick="backToChannelsGrid()">Cancel</button>
          </div>
          <div id="consentStatus" class="status"></div>
        </form>
      </div>
    </div>
  </div>
</div>

</body></html>
