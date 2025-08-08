@php
    $agents = $agents ?? collect();  // safety net
@endphp

<x-app-layout>
  <div class="max-w-7xl mx-auto p-6 lg:p-8 space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-semibold">Clevio PRO — Manage Agent</h1>
      <a href="#" class="px-3 py-2 border rounded">Add New Agent</a>
    </div>

    <div class="overflow-x-auto border rounded">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-100 text-left">
          <tr>
            <th class="p-3">Agent Name</th>
            <th class="p-3">Instruction</th>
            <th class="p-3">Model</th>
            <th class="p-3">Tools</th>
            <th class="p-3">Memory</th>
            <th class="p-3">Type</th>
            <th class="p-3">Created</th>
            <th class="p-3 text-right">
              <span class="sr-only">Actions</span>
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 inline">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm7.5 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm7.5 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
              </svg>
            </th>
          </tr>
        </thead>
        <tbody>
          @forelse ($agents as $a)
            <tr class="border-t align-top">
              <td class="p-3 font-medium break-words">{{ $a->name }}</td>
              <td class="p-3 break-words">{{ $a->systemMessage }}</td>
              <td class="p-3 break-words">{{ $a->modelName }}</td>
              <td class="p-3 break-words">
                @if(is_array($a->tools)) {{ implode(', ', $a->tools) }} @endif
              </td>
              <td class="p-3 break-words">{{ $a->memoryEnabled ? 'On' : 'Off' }} ({{ $a->memoryBackend }})</td>
              <td class="p-3 break-words">{{ $a->agentType }}</td>
              <td class="p-3 break-words">{{ optional($a->createdAt)->format('Y-m-d') }}</td>
              <td class="p-3 text-right space-x-2">
                <button class="inline-block text-green-600 hover:text-green-800" title="Run">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.25v13.5L18 12 5.25 5.25z" />
                  </svg>
                  <span class="sr-only">Run</span>
                </button>
                <a href="{{ route('agents.edit', $a->id) }}" class="inline-block text-blue-600 hover:text-blue-800" title="Edit">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.651-1.651a1.875 1.875 0 112.652 2.652L7.5 19.154l-4.243.707.707-4.243L16.862 4.487z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 5l3 3" />
                  </svg>
                  <span class="sr-only">Edit</span>
                </a>
                <form action="{{ route('agents.destroy', $a->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this agent?');">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="text-red-600 hover:text-red-800" title="Delete">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.166L18.16 19.673A2.25 2.25 0 0115.916 21.75H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .563c.34-.059.68-.114 1.022-.165m0 0L5.26 19.673A2.25 2.25 0 007.504 21.75h7.912a2.25 2.25 0 002.244-2.077L19.228 5.79m-14.456 0a48.11 48.11 0 013.478-.397m7.5 0V4.5a1.5 1.5 0 00-1.5-1.5h-3a1.5 1.5 0 00-1.5 1.5v.893m7.5 0a48.667 48.667 0 00-7.5 0" />
                    </svg>
                    <span class="sr-only">Delete</span>
                  </button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td class="p-6" colspan="8">Belum ada agent.</td>
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
