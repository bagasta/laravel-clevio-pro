<x-app-layout>
  <div class="max-w-2xl mx-auto p-6 space-y-6">
    <h1 class="text-xl font-semibold">Edit Agent</h1>
    <form method="POST" action="{{ route('agents.update', $agent->id) }}" class="space-y-4">
      @csrf
      @method('PUT')
      <div>
        <label class="block text-sm font-medium text-gray-700" for="name">Name</label>
        <input id="name" name="name" type="text" value="{{ old('name', $agent->name) }}" class="mt-1 block w-full border-gray-300 rounded" required>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700" for="systemMessage">Instruction</label>
        <textarea id="systemMessage" name="systemMessage" rows="4" class="mt-1 block w-full border-gray-300 rounded" required>{{ old('systemMessage', $agent->systemMessage) }}</textarea>
      </div>
      <div class="flex justify-end space-x-2">
        <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-gray-200 rounded">Cancel</a>
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded">Save</button>
      </div>
    </form>
  </div>
</x-app-layout>
