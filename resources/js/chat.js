import '@n8n/chat/style.css';
import { createChat } from '@n8n/chat';

const WEBHOOK = import.meta.env.VITE_N8N_WEBHOOK_URL || '';

window.addEventListener('DOMContentLoaded', () => {
  if (!WEBHOOK) return;

  createChat({
    webhookUrl: WEBHOOK,
    mode: 'float',            // tampil sebagai bubble (pojok kanan bawah)
    showWelcomeScreen: false,
    loadPreviousSession: true,
  });
});
