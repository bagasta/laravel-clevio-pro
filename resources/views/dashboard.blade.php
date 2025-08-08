@php
    use Illuminate\Support\Str;
    $agents = $agents ?? collect();  // safety net
@endphp

<x-app-layout>
  <div class="max-w-7xl mx-auto p-6 lg:p-8 space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-semibold">Manage Agent</h1>
      <!-- <a href="#" class="px-3 py-2 border rounded">Add New Agent</a> -->
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
            <th class="p-3">Options</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($agents as $a)
            <tr class="border-t">
              <td class="p-3 font-medium">{{ $a->name }}</td>
              <td class="p-3">{{ Str::limit($a->systemMessage, 50) }}</td>
              <td class="p-3">{{ $a->modelName }}</td>
              <td class="p-3">
                @if(is_array($a->tools)) {{ implode(', ', $a->tools) }} @endif
              </td>
              <td class="p-3">{{ $a->memoryEnabled ? 'On' : 'Off' }} ({{ $a->memoryBackend }})</td>
              <td class="p-3">{{ $a->agentType }}</td>
              <td class="p-3">{{ optional($a->createdAt)->format('Y-m-d') }}</td>
              <td class="p-3 space-x-2">
                <button class="px-2 py-1 border rounded">Run</button>
                <button class="px-2 py-1 border rounded">Edit</button>
                <button class="px-2 py-1 border rounded">Delete</button>
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
