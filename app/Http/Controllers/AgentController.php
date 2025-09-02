<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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

    public function run(Request $request, Agent $agent)
    {
        $data = $request->validate([
            'message' => 'required|string',
        ]);

        $base = rtrim(config('services.agent_service.base_url', ''), '/');
        if (!$base) {
            return response()->json(['error' => 'Agent service base URL not configured'], 500);
        }

        $apiKey = config('services.agent_service.openai_api_key');
        if (!$apiKey) {
            return response()->json(['error' => 'OPENAI API key not configured'], 500);
        }

        $url = $base . '/agents/' . urlencode($agent->id) . '/run';

        try {
            $resp = Http::asJson()->post($url, [
                'message' => $data['message'],
                'openai_api_key' => $apiKey,
            ]);

            if ($resp->failed()) {
                return response()->json([
                    'error' => 'Remote call failed',
                    'status' => $resp->status(),
                    'body' => $resp->json() ?? $resp->body(),
                ], 502);
            }

            return response()->json($resp->json());
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Exception during remote call',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
