import '@n8n/chat/style.css';
import { createChat } from '@n8n/chat';

const WEBHOOK = import.meta.env.VITE_N8N_WEBHOOK_URL || '';

function isAgentChatPage() {
  return !!document.getElementById('agent-chat-page');
}

function hideExistingN8nBubble() {
  const selectors = [
    '#n8n-chat',
    '.n8n-chat',
    'n8n-chat',
    '[data-n8n-chat]',
    '.n8n-floating-button',
    '.n8n-chat-position',
    'iframe[src*="n8n"]',
  ];
  selectors.forEach((sel) => {
    document.querySelectorAll(sel).forEach((el) => {
      el.style.display = 'none';
      el.style.opacity = '0';
      el.style.pointerEvents = 'none';
      el.setAttribute('aria-hidden', 'true');
    });
  });
}

function initN8nChat() {
  if (!WEBHOOK) return;

  // Do not render the floating widget on the full chat page
  if (isAgentChatPage()) {
    hideExistingN8nBubble();
    // Keep hiding if the element is injected later
    try {
      const mo = new MutationObserver(() => hideExistingN8nBubble());
      mo.observe(document.body, { childList: true, subtree: true });
      // Fallback timer for environments without MutationObserver quirks
      let tries = 0; const id = setInterval(() => {
        hideExistingN8nBubble();
        if (++tries > 50) clearInterval(id); // ~5s if 100ms interval
      }, 100);
      window.addEventListener('beforeunload', () => { try { mo.disconnect(); } catch(_) {} });
    } catch(_) {}
    return;
  }

  const userId = document.querySelector('meta[name="user-id"]')?.content;

  const options = {
    webhookUrl: WEBHOOK,
    mode: 'float',
    showWelcomeScreen: false,
    loadPreviousSession: true,
  };

  if (userId) options.metadata = { userId };

  createChat(options);
}

if (document.readyState === 'loading') {
  window.addEventListener('DOMContentLoaded', initN8nChat);
} else {
  initN8nChat();
}
