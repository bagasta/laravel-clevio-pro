<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    public function edit(Agent $agent)
    {
        return view('agents.edit', compact('agent'));
    }

    public function update(Request $request, Agent $agent)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'systemMessage' => 'required|string',
        ]);

        $agent->update($data);

        return redirect()->route('dashboard')->with('status', 'Agent updated.');
    }

    public function destroy(Agent $agent)
    {
        $agent->delete();

        return redirect()->route('dashboard')->with('status', 'Agent deleted.');
    }
}

