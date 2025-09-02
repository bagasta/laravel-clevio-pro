function ensureBubbleRoot() {
  let root = document.getElementById('agent-bubble-chat');
  if (root) return root;
  root = document.createElement('div');
  root.id = 'agent-bubble-chat';
  // Ensure it's above any third-party widgets
  root.style.zIndex = '2147483647';
  root.innerHTML = `
    <div id="agent-bubble-backdrop" class="fixed inset-0 bg-black/30 hidden"></div>
    <aside id="agent-bubble-panel" class="fixed inset-y-0 left-0 bg-white shadow-2xl border-r flex flex-col -translate-x-full transition-transform" style="width: min(420px, 100vw);">
      <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200">
        <div class="font-semibold text-sm truncate" id="agent-bubble-title">Chat</div>
        <button type="button" class="text-gray-500 hover:text-gray-700" id="agent-bubble-close" aria-label="Close">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
      <div id="agent-bubble-messages" class="p-3 space-y-2 overflow-y-auto flex-1"></div>
      <form id="agent-bubble-form" class="border-t border-gray-200 p-3 flex gap-2">
        <input type="text" id="agent-bubble-input" class="flex-1 border rounded px-3 py-2 text-sm focus:outline-none focus:ring" placeholder="Type a message..." />
        <button type="submit" class="bg-indigo-600 text-white text-sm px-4 py-2 rounded hover:bg-indigo-700">Send</button>
      </form>
    </aside>`;
  document.body.appendChild(root);
  // Ensure it slides even if Tailwind utilities are purged
  const panel = root.querySelector('#agent-bubble-panel');
  if (panel) {
    panel.style.transform = 'translateX(-100%)';
    panel.style.transition = 'transform 180ms ease-in-out';
  }
  return root;
}

function getCsrfToken() {
  return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

function tidyText(text) {
  if (typeof text !== 'string') return String(text ?? '');
  return text
    .replace(/\r\n/g, '\n')    // normalize newlines
    .replace(/\n{3,}/g, '\n\n') // collapse too many blank lines
    .trim();
}

function appendMessage(container, text, who) {
  const msg = document.createElement('div');
  msg.className = who === 'user' ? 'text-right' : 'text-left';
  const bubble = document.createElement('div');
  bubble.className = (who === 'user')
    ? 'inline-block bg-indigo-600 text-white rounded-lg px-3 py-2 text-sm whitespace-pre-wrap break-words'
    : 'inline-block bg-gray-100 text-gray-800 rounded-lg px-3 py-2 text-sm whitespace-pre-wrap break-words';
  bubble.textContent = tidyText(text);
  msg.appendChild(bubble);
  container.appendChild(msg);
  container.scrollTop = container.scrollHeight;
}

function extractReply(data) {
  if (data == null) return '';
  if (typeof data === 'string') return data;
  if (typeof data === 'object') {
    const order = ['response', 'reply', 'message', 'output', 'text', 'content'];
    for (const k of order) {
      if (typeof data[k] === 'string' && data[k].trim()) return data[k];
    }
    // OpenAI-style responses
    if (Array.isArray(data.choices) && data.choices.length) {
      const c0 = data.choices[0];
      if (typeof c0?.text === 'string') return c0.text;
      if (typeof c0?.message?.content === 'string') return c0.message.content;
    }
    // If object has a single string value, use it
    const strValues = Object.values(data).filter(v => typeof v === 'string' && v.trim());
    if (strValues.length === 1) return strValues[0];
  }
  // Fallback: stringify, but UI asked for text only
  try { return JSON.stringify(data); } catch { return String(data); }
}

function showBubble(agentId, agentName) {
  const root = ensureBubbleRoot();
  root.dataset.agentId = agentId;
  document.getElementById('agent-bubble-title').textContent = `Chat — ${agentName || agentId}`;
  const backdrop = document.getElementById('agent-bubble-backdrop');
  const panel = document.getElementById('agent-bubble-panel');
  backdrop.classList.remove('hidden');
  requestAnimationFrame(() => {
    panel.classList.remove('-translate-x-full');
    panel.style.transform = 'translateX(0)';
    document.getElementById('agent-bubble-input').focus();
  });
}

function hideBubble() {
  const root = document.getElementById('agent-bubble-chat');
  if (!root) return;
  const backdrop = document.getElementById('agent-bubble-backdrop');
  const panel = document.getElementById('agent-bubble-panel');
  panel.classList.add('-translate-x-full');
  panel.style.transform = 'translateX(-100%)';
  backdrop.classList.add('hidden');
}

function initAgentBubble() {
  const root = ensureBubbleRoot();
  const messages = document.getElementById('agent-bubble-messages');
  const form = document.getElementById('agent-bubble-form');
  const input = document.getElementById('agent-bubble-input');

  document.getElementById('agent-bubble-close').addEventListener('click', hideBubble);
  document.getElementById('agent-bubble-backdrop').addEventListener('click', hideBubble);
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') hideBubble(); });

  document.body.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-bubble-chat');
    if (!btn) return;
    // Reset messages when opening for a new agent
    messages.innerHTML = '';
    const agentId = btn.getAttribute('data-agent-id');
    const agentName = btn.getAttribute('data-agent-name') || '';
    const runUrl = btn.getAttribute('data-run-url') || '';
    // Prefer explicit URL from button; fallback to backend proxy
    root.dataset.runUrl = runUrl || `/agents/${encodeURIComponent(agentId)}/run`;
    showBubble(agentId, agentName);
  });

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const text = input.value.trim();
    if (!text) return;
    const agentId = root.dataset.agentId;
    appendMessage(messages, text, 'user');
    input.value = '';

    // Loading indicator
    const loadingId = `loading-${Date.now()}`;
    appendMessage(messages, 'Thinking…', 'bot');
    const loaderEl = messages.lastElementChild?.querySelector('div');
    if (loaderEl) loaderEl.dataset.loadingId = loadingId;

    const tryFetch = async (url) => {
      return fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': getCsrfToken(),
        },
        body: JSON.stringify({ message: text }),
      });
    };

    try {
      let resp = await tryFetch(root.dataset.runUrl || `/agents/${encodeURIComponent(agentId)}/run`);
      // If CORS/network error occurred above, it will throw and go to catch,
      // where we will attempt the proxy fallback.
      let data;
      try {
        data = await resp.json();
      } catch (_) {
        data = null;
      }

      // Remove loading line
      if (loaderEl && loaderEl.parentElement) loaderEl.parentElement.remove();

      if (!resp.ok) {
        // Fallback to backend proxy when direct URL fails (e.g., CORS)
        try {
          resp = await tryFetch(`/agents/${encodeURIComponent(agentId)}/run`);
          data = await resp.json();
        } catch (_) {}
        if (!resp || !resp.ok) {
          appendMessage(messages, (data && (data.error || data.message)) || `Error ${resp?.status || ''}`.trim(), 'bot');
          return;
        }
      }

      const reply = extractReply(data);
      appendMessage(messages, reply, 'bot');
    } catch (err) {
      // Attempt proxy as final fallback
      try {
        const resp = await tryFetch(`/agents/${encodeURIComponent(agentId)}/run`);
        const data = await resp.json();
        if (loaderEl && loaderEl.parentElement) loaderEl.parentElement.remove();
        if (!resp.ok) {
          appendMessage(messages, (data && (data.error || data.message)) || `Error ${resp.status}`, 'bot');
          return;
        }
        const reply = extractReply(data);
        appendMessage(messages, reply, 'bot');
      } catch (err2) {
        if (loaderEl && loaderEl.parentElement) loaderEl.parentElement.remove();
        appendMessage(messages, `Request failed: ${err?.message || err}`, 'bot');
      }
    }
  });
}

if (document.readyState === 'loading') {
  window.addEventListener('DOMContentLoaded', initAgentBubble);
} else {
  initAgentBubble();
}
