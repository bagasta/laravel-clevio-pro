function getCsrfToken() {
  return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

function tidyText(text) {
  if (typeof text !== 'string') return String(text ?? '');
  return text.replace(/\r\n/g, '\n').replace(/\n{3,}/g, '\n\n').trim();
}

function renderMessageContent(text) {
  // Very light support for code blocks via triple backticks
  const root = document.createElement('div');
  root.className = 'space-y-2';
  const parts = tidyText(text).split(/```/g);
  for (let i = 0; i < parts.length; i++) {
    const seg = parts[i];
    const isCode = i % 2 === 1; // odd indices are code segments
    if (isCode) {
      const pre = document.createElement('pre');
      pre.className = 'bg-gray-900 text-gray-100 rounded-lg text-xs p-3 overflow-auto';
      const code = document.createElement('code');
      code.textContent = seg.trim();
      pre.appendChild(code);
      root.appendChild(pre);
    } else if (seg.trim()) {
      const p = document.createElement('p');
      p.className = 'whitespace-pre-wrap break-words text-sm text-gray-800';
      p.textContent = seg.trim();
      root.appendChild(p);
    }
  }
  return root;
}

function appendMessage(container, text, who, avatarText) {
  const line = document.createElement('div');
  line.className = `flex items-end gap-2 ${who === 'user' ? 'justify-end' : ''}`;

  const avatar = document.createElement('div');
  avatar.className = `shrink-0 h-7 w-7 rounded-full ${who === 'user' ? 'bg-blue-600 text-white' : 'bg-gray-300 text-gray-700'} flex items-center justify-center text-[10px] font-semibold`;
  avatar.textContent = (avatarText && avatarText.trim()) ? avatarText.trim().slice(0,2).toUpperCase() : (who === 'user' ? 'ME' : 'AI');

  const bubbleWrap = document.createElement('div');
  bubbleWrap.className = 'max-w-[78%] sm:max-w-[72%]';

  const bubble = document.createElement('div');
  bubble.className = (who === 'user')
    ? 'inline-block bg-blue-600 text-white rounded-2xl px-3 py-2 text-sm shadow-sm'
    : 'inline-block bg-white text-gray-900 rounded-2xl px-3 py-2 text-sm shadow-sm ring-1 ring-gray-200';

  // Fill content
  if (who === 'user') {
    bubble.textContent = tidyText(text);
  } else {
    bubble.appendChild(renderMessageContent(text));
  }

  if (who === 'user') {
    // For user, order bubble then avatar (right aligned)
    bubbleWrap.appendChild(bubble);
    line.appendChild(bubbleWrap);
    line.appendChild(avatar);
  } else {
    // For bot, order avatar then bubble
    line.appendChild(avatar);
    bubbleWrap.appendChild(bubble);
    line.appendChild(bubbleWrap);
  }

  container.appendChild(line);
  container.scrollTop = container.scrollHeight;
  bubble.__line = line; // attach line reference for future removal
  return bubble; // return bubble node for updates
}

function extractReply(data) {
  if (data == null) return '';
  if (typeof data === 'string') return data;
  if (typeof data === 'object') {
    const order = ['response', 'reply', 'message', 'output', 'text', 'content'];
    for (const k of order) {
      if (typeof data[k] === 'string' && data[k].trim()) return data[k];
    }
    if (Array.isArray(data.choices) && data.choices.length) {
      const c0 = data.choices[0];
      if (typeof c0?.text === 'string') return c0.text;
      if (typeof c0?.message?.content === 'string') return c0.message.content;
    }
    const strValues = Object.values(data).filter(v => typeof v === 'string' && v.trim());
    if (strValues.length === 1) return strValues[0];
  }
  try { return JSON.stringify(data); } catch { return String(data); }
}

function initAgentChatPage() {
  const root = document.getElementById('agent-chat-page');
  if (!root) return; // not on this page

  const agentId = root.getAttribute('data-agent-id');
  const runUrl = root.getAttribute('data-run-url') || `/agents/${encodeURIComponent(agentId)}/run`;
  const warmUrl = root.getAttribute('data-warm-url') || `/agents/${encodeURIComponent(agentId)}/warm`;
  const fastModel = root.getAttribute('data-fast-model') || 'gpt-4o-mini';
  const userId = document.querySelector('meta[name="user-id"]')?.content || null;
  // Stable session id per agent (persists in localStorage)
  const SESSION_KEY = `agentChat.session.${agentId}`;
  let sessionId = null;
  try {
    sessionId = localStorage.getItem(SESSION_KEY);
    if (!sessionId) {
      sessionId = `${agentId}:${Math.random().toString(36).slice(2)}`;
      localStorage.setItem(SESSION_KEY, sessionId);
    }
  } catch(_) {}
  // Prefer direct agent service on localhost:8000 with proxy fallback
  const directBase = 'http://localhost:8000';
  const directRunUrl = `${directBase}/agents/${encodeURIComponent(agentId)}/run`;
  const directWarmUrl = `${directBase}/agents/${encodeURIComponent(agentId)}/warm`;

  const messages = document.getElementById('agent-chat-messages');
  const form = document.getElementById('agent-chat-form');
  const input = document.getElementById('agent-chat-input');
  const scrollBtn = document.getElementById('agent-chat-scroll-bottom');
  const fastToggle = document.getElementById('agent-fast-toggle');
  const memoryToggle = document.getElementById('agent-memory-toggle');

  // Fast mode state in localStorage
  const FAST_KEY = 'agentChat.fastMode';
  let fastMode = true;
  try {
    const saved = localStorage.getItem(FAST_KEY);
    if (saved != null) fastMode = saved === '1';
  } catch(_) {}

  const refreshFastToggle = () => {
    if (!fastToggle) return;
    fastToggle.textContent = `Fast: ${fastMode ? 'On' : 'Off'}`;
    fastToggle.className = `px-2 py-0.5 rounded border text-xs ${fastMode ? 'bg-green-100 text-green-700 border-green-200' : 'bg-gray-100 text-gray-700 border-gray-200'}`;
  };
  refreshFastToggle();
  if (fastToggle) {
    fastToggle.addEventListener('click', () => {
      fastMode = !fastMode;
      refreshFastToggle();
      try { localStorage.setItem(FAST_KEY, fastMode ? '1' : '0'); } catch(_) {}
      refreshMemoryToggle();
    });
  }

  // Memory toggle state (only used in Fast mode overrides)
  const MEMORY_KEY = 'agentChat.memoryMode';
  let memoryMode = false; // default Off for best speed
  try {
    const saved = localStorage.getItem(MEMORY_KEY);
    if (saved != null) memoryMode = saved === '1';
  } catch(_) {}

  const refreshMemoryToggle = () => {
    if (!memoryToggle) return;
    const enabled = fastMode; // disable when not in fast mode
    memoryToggle.disabled = !enabled;
    memoryToggle.textContent = `Memory: ${memoryMode ? 'On' : 'Off'}`;
    memoryToggle.className = `px-2 py-0.5 rounded border text-xs ${enabled ? '' : 'opacity-50 cursor-not-allowed'} ${memoryMode ? 'bg-blue-100 text-blue-700 border-blue-200' : 'bg-gray-100 text-gray-700 border-gray-200'}`;
  };
  refreshMemoryToggle();
  if (memoryToggle) {
    memoryToggle.addEventListener('click', () => {
      if (!fastMode) return; // ignore when fast is off
      memoryMode = !memoryMode;
      refreshMemoryToggle();
      try { localStorage.setItem(MEMORY_KEY, memoryMode ? '1' : '0'); } catch(_) {}
    });
  }

  // Auto-grow textarea and send on Enter (allow Shift+Enter)
  const autogrow = () => {
    if (!input) return;
    input.style.height = 'auto';
    const maxH = 160; // px
    input.style.height = Math.min(input.scrollHeight, maxH) + 'px';
  };
  input?.addEventListener('input', autogrow);
  input?.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      form?.dispatchEvent(new Event('submit', { cancelable: true }));
    }
  });
  autogrow();

  // Scroll-to-bottom visibility
  const nearBottom = () => {
    const thresh = 72; // px
    return (messages.scrollHeight - messages.clientHeight - messages.scrollTop) <= thresh;
  };
  const updateScrollButton = () => {
    if (!scrollBtn) return;
    if (nearBottom()) scrollBtn.classList.add('hidden');
    else scrollBtn.classList.remove('hidden');
  };
  messages?.addEventListener('scroll', updateScrollButton);
  scrollBtn?.addEventListener('click', () => {
    messages.scrollTop = messages.scrollHeight;
    updateScrollButton();
  });

  // Warm caches non-blocking
  try {
    // Warm preferred mode (fast by default)
    let warmConfig;
    try {
      const cfgEl = document.getElementById('agent-chat-config');
      if (cfgEl?.textContent) warmConfig = JSON.parse(cfgEl.textContent);
    } catch(_) {}
    if (fastMode) {
      warmConfig = Object.assign({}, warmConfig || {}, {
        model_name: fastModel,
        memory_enabled: memoryMode,
        tools: [],
      });
    }
    const warmBody = {};
    if (fastMode && warmConfig) warmBody.config = warmConfig;
    if (sessionId) warmBody.session_id = sessionId;
    if (userId) warmBody.metadata = { userId };
    const warmPayload = JSON.stringify(warmBody);
    // Try direct first, then proxy (non-blocking best-effort)
    fetch(directWarmUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: warmPayload,
      keepalive: true,
    }).catch(() => {
      fetch(warmUrl, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': getCsrfToken(), 'Content-Type': 'application/json' },
        body: warmPayload,
        keepalive: true,
      }).catch(() => {});
    });
  } catch (_) {}

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const text = input.value.trim();
    if (!text) return;
    appendMessage(messages, text, 'user');
    input.value = '';
    input.disabled = true;
    autogrow();

    const loaderId = `loading-${Date.now()}`;
    const thinkingBubble = appendMessage(messages, 'Thinking…', 'bot', (root.getAttribute('data-agent-name') || '').slice(0,2));
    if (thinkingBubble) thinkingBubble.dataset.loadingId = loaderId;
    updateScrollButton();

    // Prepare inline config for fast path
    const buildInlineConfig = (useFast) => {
      let cfg = undefined;
      try {
        const cfgEl = document.getElementById('agent-chat-config');
        if (cfgEl?.textContent) cfg = JSON.parse(cfgEl.textContent);
      } catch(_) {}
      if (useFast) {
        cfg = Object.assign({}, cfg || {}, { model_name: fastModel, memory_enabled: memoryMode, tools: [] });
      }
      return cfg;
    };
    let inlineConfig = fastMode ? buildInlineConfig(true) : undefined;

    // Abort timeout: longer when memory is enabled
    const controller = new AbortController();
    // Determine whether memory is effectively on for this request
    let memoryOnThisRequest = false;
    try {
      if (fastMode) {
        memoryOnThisRequest = !!memoryMode;
      } else {
        const baseCfg = JSON.parse(document.getElementById('agent-chat-config')?.textContent || 'null');
        memoryOnThisRequest = !!(baseCfg && baseCfg.memory_enabled);
      }
    } catch(_) {}
    const t = setTimeout(() => controller.abort(), memoryOnThisRequest ? 120000 : 30000);

    const buildBody = () => {
      const body = { message: text };
      if (inlineConfig) body.config = inlineConfig;
      if (sessionId) body.session_id = sessionId;
      if (userId) body.metadata = { userId };
      if (memoryOnThisRequest) body.long_timeout = true;
      return body;
    };

    const makeReq = async (url, useFastHeader) => fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': getCsrfToken(),
        ...(useFastHeader ? { 'X-Fast-Mode': '1' } : {}),
      },
      body: JSON.stringify(buildBody()),
      signal: controller.signal,
    });

    try {
      // Choose primary endpoint based on fast vs memory mode
      const primaryRunUrl = fastMode ? directRunUrl : runUrl;
      const secondaryRunUrl = fastMode ? runUrl : directRunUrl;
      let resp = await makeReq(primaryRunUrl, fastMode);
      let data;
      try { data = await resp.json(); } catch(_) { data = null; }
      if (!resp.ok && fastMode && (resp.status >= 400 && resp.status < 500)) {
        // Auto fallback by disabling fast mode once on client error (e.g., unsupported model)
        fastMode = false; refreshFastToggle();
        try { localStorage.setItem(FAST_KEY, '0'); } catch(_) {}
        // Rebuild request without fast overrides
        inlineConfig = undefined; // allow backend to load full config & memory
        resp = await makeReq(primaryRunUrl, false);
        try { data = await resp.json(); } catch(_) { data = null; }
      } else if (!resp.ok && resp.status >= 500) {
        // Retry once after small delay on server errors
        await new Promise(r => setTimeout(r, 1200));
        try {
          resp = await makeReq(primaryRunUrl, fastMode);
          try { data = await resp.json(); } catch(_) { data = null; }
        } catch (e2) {}
      }
      // If direct failed, try proxy route as fallback
      if (!resp.ok) {
        try {
          resp = await makeReq(secondaryRunUrl, fastMode);
          try { data = await resp.json(); } catch(_) { data = null; }
          if (!resp.ok && fastMode && (resp.status >= 400 && resp.status < 500)) {
            // Fast not supported even via proxy; disable and retry proxy once
            fastMode = false; refreshFastToggle();
            try { localStorage.setItem(FAST_KEY, '0'); } catch(_) {}
            inlineConfig = undefined;
            resp = await makeReq(secondaryRunUrl, false);
            try { data = await resp.json(); } catch(_) { data = null; }
          } else if (!resp.ok && resp.status >= 500) {
            await new Promise(r => setTimeout(r, 1200));
            resp = await makeReq(secondaryRunUrl, fastMode);
            try { data = await resp.json(); } catch(_) { data = null; }
          }
        } catch (e3) {
          // fall through to final error display
        }
      }
      if (thinkingBubble && thinkingBubble.__line) thinkingBubble.__line.remove();
      if (!resp || !resp.ok) {
        appendMessage(messages, (data && (data.error || data.message)) || `Error ${resp?.status || ''}`, 'bot');
      } else {
        const reply = extractReply(data);
        appendMessage(messages, reply, 'bot', (root.getAttribute('data-agent-name') || '').slice(0,2));
      }
    } catch (err) {
      if (thinkingBubble && thinkingBubble.__line) thinkingBubble.__line.remove();
      const msg = (err && (err.name === 'AbortError')) ? 'Request timed out. Please try again.' : `Request failed: ${err?.message || err}`;
      appendMessage(messages, msg, 'bot', (root.getAttribute('data-agent-name') || '').slice(0,2));
    } finally {
      clearTimeout(t);
      input.disabled = false;
      input.focus();
      updateScrollButton();
    }
  });
}

if (document.readyState === 'loading') {
  window.addEventListener('DOMContentLoaded', initAgentChatPage);
} else {
  initAgentChatPage();
}
