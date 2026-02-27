# BoA - Vercel Deployment

## Project structure

```
boa-deploy/
├── api/
│   └── telegram.js    ← Serverless function (replaces boa-api.php)
├── vercel.json        ← Rewrites api.php → /api/telegram
├── index.html         ← Redirects / to oatest.html
├── oatest.html        ← Main page
├── boa-api.php        ← For Render/cPanel (not used on Vercel)
└── Dockerfile         ← For Render (not used on Vercel)
```

## Vercel setup

1. Connect your GitHub repo to Vercel
2. If the repo root is `boa-deploy`, deploy as-is
3. If the repo root contains `boa-deploy` as a subfolder:
   - Project Settings → General → Root Directory → set to `boa-deploy`
4. Deploy

## How it works

- Page calls `fetch('api.php', ...)` 
- `vercel.json` rewrites `/api.php` → `/api/telegram`
- `api/telegram.js` handles sendMessage, getUpdates, answerCallbackQuery

## Test

After deploy, open `https://your-app.vercel.app/oatest.html` and submit a test. Check Telegram for notifications.
