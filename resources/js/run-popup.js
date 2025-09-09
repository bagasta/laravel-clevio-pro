function ensureRunPopupRoot() {
  let root = document.getElementById('run-popup-root');
  if (root) return root;
  root = document.createElement('div');
  root.id = 'run-popup-root';
  root.style.zIndex = '2147483647';
  root.innerHTML = `
    <div id="run-popup-backdrop" class="fixed inset-0 bg-black/30 hidden"></div>
    <div id="run-popup-dialog" class="fixed inset-0 hidden items-center justify-center p-4">
      <div class="bg-white w-full max-w-sm rounded-lg shadow-xl border overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 border-b">
          <div class="text-sm font-semibold truncate" id="run-popup-title">Run</div>
          <button type="button" id="run-popup-close" class="text-gray-500 hover:text-gray-700" aria-label="Close">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>
        <div class="px-4 py-5">
          <div class="text-sm text-gray-600 mb-3">Choose a channel:</div>
          <div class="flex items-center justify-center gap-5">
            <button type="button" class="run-channel" data-channel="whatsapp" title="WhatsApp">
              <div class="w-14 h-14 rounded-full flex items-center justify-center" style="background:#25D366">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-8 h-8" fill="white">
                  <path d="M20.52 3.48A11.94 11.94 0 0012 0C5.372 0 0 5.373 0 12c0 2.112.55 4.11 1.512 5.84L0 24l6.333-1.66A11.934 11.934 0 0012 24c6.627 0 12-5.373 12-12 0-3.208-1.289-6.122-3.48-8.52zM12 21.6c-1.86 0-3.59-.555-5.03-1.506l-.36-.23-3.76.987 1.004-3.66-.235-.372A9.594 9.594 0 012.4 12C2.4 6.705 6.705 2.4 12 2.4S21.6 6.705 21.6 12 17.295 21.6 12 21.6zm4.328-6.39c-.239-.12-1.408-.695-1.626-.773-.218-.08-.378-.119-.538.119-.159.239-.618.773-.758.932-.139.16-.278.179-.517.06-.239-.12-1.01-.372-1.926-1.187-.71-.63-1.191-1.406-1.33-1.645-.139-.239-.015-.369.104-.487.106-.105.239-.278.359-.417.12-.14.159-.239.239-.398.08-.16.04-.299-.02-.418-.06-.12-.538-1.298-.738-1.776-.195-.468-.396-.404-.538-.412l-.46-.008c-.159 0-.418.06-.638.299-.219.239-.839.817-.839 1.992s.859 2.31.978 2.47c.119.159 1.689 2.582 4.095 3.619.572.247 1.021.394 1.369.504.574.182 1.098.157 1.513.095.461-.069 1.408-.576 1.606-1.132.198-.556.198-1.033.139-1.132-.06-.1-.219-.159-.458-.278z"/>
                </svg>
              </div>
              <div class="text-xs text-center mt-1">WhatsApp</div>
            </button>
            <button type="button" class="run-channel" data-channel="instagram" title="Instagram">
              <div class="w-14 h-14 rounded-lg flex items-center justify-center bg-gradient-to-tr from-yellow-400 via-pink-500 to-purple-600">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-8 h-8" fill="white">
                  <path d="M7 2h10a5 5 0 015 5v10a5 5 0 01-5 5H7a5 5 0 01-5-5V7a5 5 0 015-5zm0 2a3 3 0 00-3 3v10a3 3 0 003 3h10a3 3 0 003-3V7a3 3 0 00-3-3H7z"/>
                  <path d="M12 7a5 5 0 110 10 5 5 0 010-10zm0 2.2a2.8 2.8 0 100 5.6 2.8 2.8 0 000-5.6z"/>
                  <circle cx="17.5" cy="6.5" r="1.2"/>
                </svg>
              </div>
              <div class="text-xs text-center mt-1">Instagram</div>
            </button>
            <button type="button" class="run-channel" data-channel="facebook" title="Facebook">
              <div class="w-14 h-14 rounded-full flex items-center justify-center" style="background:#1877F2">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-8 h-8" fill="white">
                  <path d="M22.675 0H1.325C.593 0 0 .593 0 1.326v21.348C0 23.407.593 24 1.325 24h11.494v-9.294H9.847V11.06h2.972V8.414c0-2.943 1.796-4.548 4.42-4.548 1.257 0 2.337.093 2.653.135v3.074h-1.821c-1.429 0-1.704.68-1.704 1.675v2.31h3.406l-.444 3.646h-2.962V24h5.807C23.407 24 24 23.407 24 22.674V1.326C24 .593 23.407 0 22.675 0z"/>
                </svg>
              </div>
              <div class="text-xs text-center mt-1">Facebook</div>
            </button>
          </div>
        </div>
      </div>
    </div>`;
  document.body.appendChild(root);
  return root;
}

function openRunPopup(agentName) {
  const root = ensureRunPopupRoot();
  const backdrop = document.getElementById('run-popup-backdrop');
  const dialog = document.getElementById('run-popup-dialog');
  const titleEl = document.getElementById('run-popup-title');
  if (agentName) titleEl.textContent = `Run — ${agentName}`;
  backdrop.classList.remove('hidden');
  dialog.classList.remove('hidden');
  dialog.classList.add('flex');
}

function closeRunPopup() {
  const backdrop = document.getElementById('run-popup-backdrop');
  const dialog = document.getElementById('run-popup-dialog');
  backdrop.classList.add('hidden');
  dialog.classList.add('hidden');
  dialog.classList.remove('flex');
}

function initRunPopup() {
  ensureRunPopupRoot();
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-run-agent');
    if (btn) {
      const agentName = btn.getAttribute('data-agent-name') || '';
      openRunPopup(agentName);
      return;
    }
    // Close on backdrop
    if (e.target.id === 'run-popup-backdrop') {
      closeRunPopup();
      return;
    }
    // Close button
    const closeBtn = e.target.closest('#run-popup-close');
    if (closeBtn) {
      closeRunPopup();
      return;
    }
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeRunPopup();
  });
  // Optional: handle click on channel icons
  document.addEventListener('click', (e) => {
    const ch = e.target.closest('.run-channel');
    if (!ch) return;
    e.preventDefault();
    const channel = ch.getAttribute('data-channel');
    // Placeholder: just close for now; hook here if needed
    closeRunPopup();
  });
}

if (document.readyState === 'loading') {
  window.addEventListener('DOMContentLoaded', initRunPopup);
} else {
  initRunPopup();
}

