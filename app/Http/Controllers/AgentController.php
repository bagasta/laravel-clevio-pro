<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AgentController extends Controller
{
    public function chat(Agent $agent)
    {
        return view('agents.chat', compact('agent'));
    }

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
            'message'      => 'required|string',
            'config'       => 'sometimes|array',
            'session_id'   => 'sometimes|string',
            'metadata'     => 'sometimes|array',
            'long_timeout' => 'sometimes|boolean',
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
            $payload = [
                'message' => $data['message'],
                'openai_api_key' => $apiKey,
            ];
            if (isset($data['config'])) {
                $payload['config'] = $data['config'];
            }
            if (isset($data['session_id'])) {
                $payload['session_id'] = $data['session_id'];
            }
            if (isset($data['metadata'])) {
                $payload['metadata'] = $data['metadata'];
            }
            $timeout = !empty($data['long_timeout']) ? 120 : 30;
            $resp = Http::timeout($timeout)
                ->connectTimeout(5)
                ->retry(1, 200)
                ->asJson()
                ->post($url, $payload);

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

    public function warm(Request $request, Agent $agent)
    {
        $base = rtrim(config('services.agent_service.base_url', ''), '/');
        if (!$base) {
            return response()->json(['ok' => false, 'error' => 'Agent service base URL not configured']);
        }

        $apiKey = config('services.agent_service.openai_api_key');
        $url = $base . '/agents/' . urlencode($agent->id) . '/warm';

        try {
            $payload = array_filter([
                'openai_api_key' => $apiKey,
                'config' => $request->input('config'),
                'session_id' => $request->input('session_id'),
                'metadata' => $request->input('metadata'),
            ], function ($v) { return !is_null($v); });
            $resp = Http::timeout(15)->connectTimeout(5)->asJson()->post($url, $payload);
            // Do not fail hard; return best-effort info
            return response()->json([
                'ok' => $resp->ok(),
                'status' => $resp->status(),
                'body' => $resp->json() ?? $resp->body(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'error' => 'Exception during warm',
                'message' => $e->getMessage(),
            ]);
        }
    }
}
