@php
  $agent = $agent ?? null;
  $title = $agent ? ($agent->name ?? $agent->id) : 'Chat';
@endphp

<x-app-layout>
  <x-slot name="header">
    <div class="max-w-screen-2xl mx-auto">
      <div class="relative overflow-hidden rounded-2xl bg-white border border-blue-100 shadow-sm">
        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-blue-500 via-sky-500 to-indigo-600"></div>
        <div class="px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between gap-3">
          <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-xl bg-blue-600/10 text-blue-700 flex items-center justify-center text-xs font-semibold">
              {{ Str::upper(Str::substr($title, 0, 2)) }}
            </div>
            <div>
              <h2 class="font-semibold text-xl text-gray-900 leading-tight">Chat — {{ $title }}</h2>
              <div class="text-xs text-gray-500 flex items-center gap-2 mt-0.5">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-100">Model: {{ $agent->modelName }}</span>
                @if($agent->memoryEnabled)
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100">Memory: On</span>
                @else
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-50 text-gray-600 border border-gray-200">Memory: Off</span>
                @endif
              </div>
            </div>
          </div>
          <div class="hidden sm:flex items-center gap-2">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 bg-white text-blue-700 hover:bg-blue-50 px-3 py-2 rounded-lg text-sm border border-blue-200 shadow-sm">Back to dashboard</a>
          </div>
        </div>
      </div>
    </div>
  </x-slot>

  <div class="max-w-screen-2xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div id="agent-chat-page"
         data-agent-id="{{ $agent->id }}"
         data-agent-name="{{ $agent->name }}"
         data-run-url="{{ route('agents.run', $agent->id) }}"
         data-warm-url="{{ route('agents.warm', $agent->id) }}"
         data-fast-model="gpt-4o-mini"
         class="relative bg-white rounded-2xl shadow border flex flex-col overflow-hidden"
         style="height: 78vh; max-height: 86vh;">
      <!-- Top bar -->
      <div class="border-b border-blue-100 px-4 py-2.5 text-sm text-gray-600 flex items-center justify-between bg-blue-50/50">
        <div class="flex items-center gap-2 truncate">
          <span class="text-gray-500">You are chatting with</span>
          <span class="font-medium text-gray-900 truncate">{{ $agent->name }}</span>
        </div>
        <div class="text-xs flex items-center gap-2">
          <button type="button" id="agent-fast-toggle" class="px-2 py-1 rounded-md border text-xs">Fast: On</button>
          <button type="button" id="agent-memory-toggle" class="px-2 py-1 rounded-md border text-xs" title="Use agent memory while in Fast mode">Memory: Off</button>
        </div>
      </div>
      <!-- Messages -->
      <div id="agent-chat-messages" class="flex-1 p-4 sm:p-5 space-y-4 overflow-y-auto overscroll-contain bg-gray-50"></div>
      <!-- Scroll to bottom button -->
      <button id="agent-chat-scroll-bottom" type="button" class="hidden absolute right-4 bottom-24 sm:bottom-28 bg-white/90 backdrop-blur border shadow px-3 py-1.5 rounded-full text-xs text-gray-700 hover:bg-white">New messages ↓</button>
      <!-- Composer -->
      <form id="agent-chat-form" class="border-t p-3 sm:p-4 flex gap-2 bg-white">
        <textarea id="agent-chat-input" rows="1" class="flex-1 border rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring resize-none" placeholder="Type a message. Press Enter to send, Shift+Enter for a new line."></textarea>
        <button type="submit" class="bg-blue-600 text-white text-sm px-4 py-2 rounded-xl hover:bg-blue-700">Send</button>
      </form>
    </div>
  </div>
  @php
    $__agentChatCfg = [
      'model_name' => $agent->modelName,
      'system_message' => $agent->systemMessage,
      'tools' => is_array($agent->tools) ? array_values($agent->tools) : [],
      'memory_enabled' => (bool) $agent->memoryEnabled
    ];
  @endphp
  <script id="agent-chat-config" type="application/json">{!! json_encode($__agentChatCfg) !!}</script>
  <script>
    // Add a body class to force-hide any floating n8n widget via CSS
    (function(){
      try {
        document.documentElement.classList.add('chat-page');
        document.body.classList.add('chat-page');
        window.addEventListener('beforeunload', function(){
          document.body.classList.remove('chat-page');
          document.documentElement.classList.remove('chat-page');
        });
      } catch(_) {}
    })();
  </script>
</x-app-layout>
