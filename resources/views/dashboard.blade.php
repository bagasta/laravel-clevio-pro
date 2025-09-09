@php
    use Illuminate\Support\Str;
    $agents = $agents ?? collect(); // safety net

    $totalAgents = $agents->count();
    $memoryOn = $agents->where('memoryEnabled', true)->count();
    $uniqueModels = $agents->pluck('modelName')->filter()->unique()->count();
    $uniqueTools = $agents->flatMap(function ($a) {
        return is_array($a->tools ?? null) ? $a->tools : [];
    })->filter()->unique()->values()->count();
    $types = $agents->pluck('agentType')->filter()->unique()->values();
@endphp

<x-app-layout>
  

  <div class="max-w-screen-2xl mx-auto p-6 lg:p-8 space-y-6 pb-24 md:pb-8">
    <!-- Summary -->
    <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-blue-100">
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="bg-blue-50 rounded-lg p-3 border border-blue-100">
          <div class="text-xs text-blue-700/70">Agents</div>
          <div class="text-xl font-semibold text-blue-900">{{ $totalAgents }}</div>
        </div>
        <div class="bg-blue-50 rounded-lg p-3 border border-blue-100">
          <div class="text-xs text-blue-700/70">Memory On</div>
          <div class="text-xl font-semibold text-blue-900">{{ $memoryOn }}</div>
        </div>
        <div class="bg-blue-50 rounded-lg p-3 border border-blue-100">
          <div class="text-xs text-blue-700/70">Models</div>
          <div class="text-xl font-semibold text-blue-900">{{ $uniqueModels }}</div>
        </div>
        <div class="bg-blue-50 rounded-lg p-3 border border-blue-100">
          <div class="text-xs text-blue-700/70">Tools</div>
          <div class="text-xl font-semibold text-blue-900">{{ $uniqueTools }}</div>
        </div>
      </div>
    </div>

    <!-- Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center gap-3 justify-between">
      <div class="flex-1 relative">
        <input id="agent-search" type="text" placeholder="Search agents, models, tools…" class="w-full pl-9 pr-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" />
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      </div>
      <div class="flex items-center gap-2">
        <select id="agent-type-filter" class="border rounded-lg px-2.5 py-2 text-sm focus:ring-2 focus:ring-blue-500">
          <option value="">All types</option>
          @foreach($types as $t)
            <option value="{{ $t }}">{{ $t }}</option>
          @endforeach
        </select>
        <select id="agent-memory-filter" class="border rounded-lg px-2.5 py-2 text-sm focus:ring-2 focus:ring-blue-500">
          <option value="">All memory</option>
          <option value="on">Memory: On</option>
          <option value="off">Memory: Off</option>
        </select>
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto border rounded-xl bg-white shadow-sm">
      <table class="min-w-full text-sm">
        <thead class="bg-blue-50 text-left md:sticky md:top-0 z-10">
          <tr class="text-blue-700">
            <th class="p-3 font-medium">Agent</th>
            <th class="p-3 font-medium hidden lg:table-cell">Instruction</th>
            <th class="p-3 font-medium">Model</th>
            <th class="p-3 font-medium hidden sm:table-cell">Tools</th>
            <th class="p-3 font-medium">Memory</th>
            <th class="p-3 font-medium">Type</th>
            <th class="p-3 font-medium hidden sm:table-cell">Created</th>
            <th class="p-3 text-right font-medium hidden md:table-cell"><span class="sr-only">Actions</span></th>
          </tr>
        </thead>
        <tbody id="agent-table-body">
          @forelse ($agents as $a)
            <tr class="border-t hover:bg-gray-50 transition" data-agent-row>
              <!-- Agent -->
              <td class="p-3 align-top">
                <div class="flex items-start gap-3">
                  <div class="h-9 w-9 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-semibold">
                    {{ Str::upper(Str::substr($a->name, 0, 2)) }}
                  </div>
                  <div class="min-w-0">
                    <div class="font-medium text-gray-900 truncate" title="{{ $a->name }}">{{ $a->name }}</div>
                    <div class="text-xs text-gray-500 font-mono truncate" title="{{ $a->id }}">{{ $a->id }}</div>
                    <!-- Mobile actions -->
                    <div class="flex items-center gap-1.5 mt-2 md:hidden">
                      <button type="button" class="btn-bubble-chat inline-flex items-center justify-center p-2 rounded-md text-indigo-600 hover:bg-indigo-50" title="Open chat"
                        data-agent-id="{{ $a->id }}"
                        data-agent-name="{{ $a->name }}"
                        data-chat-url="{{ route('agents.chat', $a->id) }}"
                      >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3h6.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                      </button>
                      <button type="button" class="btn-run-agent inline-flex items-center justify-center p-2 rounded-md text-green-600 hover:bg-green-50" title="Run"
                        data-agent-id="{{ $a->id }}"
                        data-agent-name="{{ $a->name }}"
                      >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.25v13.5L18 12 5.25 5.25z" />
                        </svg>
                      </button>
                      <a href="{{ route('agents.edit', $a->id) }}" class="inline-flex items-center justify-center p-2 rounded-md text-blue-600 hover:bg-blue-50" title="Edit">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.651-1.651a1.875 1.875 0 112.652 2.652L7.5 19.154l-4.243.707.707-4.243L16.862 4.487z" />
                          <path stroke-linecap="round" stroke-linejoin="round" d="M15 5l3 3" />
                        </svg>
                      </a>
                      <form action="{{ route('agents.destroy', $a->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this agent?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center justify-center p-2 rounded-md text-red-600 hover:bg-red-50" title="Delete">
                          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.166L18.16 19.673A2.25 2.25 0 0115.916 21.75H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.11 48.11 0 013.478-.397m7.5 0V4.5a1.5 1.5 0 00-1.5-1.5h-3a1.5 1.5 0 00-1.5 1.5v.893m7.5 0a48.667 48.667 0 00-7.5 0" />
                          </svg>
                        </button>
                      </form>
                    </div>
                  </div>
                </div>
              </td>

              <!-- Instruction -->
              <td class="p-3 align-top max-w-[26rem] hidden lg:table-cell">
                <div class="text-gray-700">
                  {{ Str::limit($a->systemMessage, 120) }}
                </div>
              </td>

              <!-- Model -->
              <td class="p-3 align-top whitespace-nowrap">
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100" title="Model name">{{ $a->modelName }}</span>
              </td>

              <!-- Tools -->
              <td class="p-3 align-top hidden sm:table-cell">
                @if(is_array($a->tools) && count($a->tools))
                  <div class="flex flex-wrap gap-1">
                    @foreach($a->tools as $tool)
                      <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-700 border border-gray-200" title="Tool">{{ $tool }}</span>
                    @endforeach
                  </div>
                @else
                  <span class="text-gray-400">—</span>
                @endif
              </td>

              <!-- Memory -->
              <td class="p-3 align-top whitespace-nowrap">
                @if($a->memoryEnabled)
                  <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-100" title="Memory backend: {{ $a->memoryBackend }}">
                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                    On
                  </span>
                @else
                  <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-gray-50 text-gray-600 border border-gray-200">
                    <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span>
                    Off
                  </span>
                @endif
              </td>

              <!-- Type -->
              <td class="p-3 align-top whitespace-nowrap">
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">{{ $a->agentType }}</span>
              </td>

              <!-- Created -->
              <td class="p-3 align-top whitespace-nowrap text-gray-600 hidden sm:table-cell" title="{{ optional($a->createdAt)->toDayDateTimeString() }}">
                {{ optional($a->createdAt)->diffForHumans() ?? '—' }}
              </td>

              <!-- Actions -->
              <td class="p-3 align-top hidden md:table-cell">
                <div class="flex items-center justify-end gap-1.5">
                  <button type="button" class="btn-bubble-chat inline-flex items-center justify-center p-2 rounded-md text-blue-600 hover:bg-blue-50" title="Open chat"
                    data-agent-id="{{ $a->id }}"
                    data-agent-name="{{ $a->name }}"
                    data-chat-url="{{ route('agents.chat', $a->id) }}"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3h6.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </button>
                  <button type="button" class="btn-run-agent inline-flex items-center justify-center p-2 rounded-md text-green-600 hover:bg-green-50" title="Run"
                    data-agent-id="{{ $a->id }}"
                    data-agent-name="{{ $a->name }}"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.25v13.5L18 12 5.25 5.25z" />
                    </svg>
                  </button>
                  <a href="{{ route('agents.edit', $a->id) }}" class="inline-flex items-center justify-center p-2 rounded-md text-blue-600 hover:bg-blue-50" title="Edit">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.651-1.651a1.875 1.875 0 112.652 2.652L7.5 19.154l-4.243.707.707-4.243L16.862 4.487z" />
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15 5l3 3" />
                    </svg>
                  </a>
                  <form action="{{ route('agents.destroy', $a->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this agent?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center justify-center p-2 rounded-md text-red-600 hover:bg-red-50" title="Delete">
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.166L18.16 19.673A2.25 2.25 0 0115.916 21.75H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .563c.34-.059.68-.114 1.022-.165m0 0L5.26 19.673A2.25 2.25 0 007.504 21.75h7.912a2.25 2.25 0 002.244-2.077L19.228 5.79m-14.456 0a48.11 48.11 0 013.478-.397m7.5 0V4.5a1.5 1.5 0 00-1.5-1.5h-3a1.5 1.5 0 00-1.5 1.5v.893m7.5 0a48.667 48.667 0 00-7.5 0" />
                      </svg>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td class="p-10 text-center text-gray-500" colspan="8">
                No agents yet.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="hidden lg:flex justify-end">
      <div class="text-xs text-gray-400">Copyright 2025 | PT. Clevio</div>
    </div>
  </div>
</x-app-layout>
