<?php

namespace Darpersodigital\Cms\Controllers\seo;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Routing\Controller as BaseController;
class SeoAIController extends BaseController
{
    /**
     * Analyze SEO fields using OpenAI.
     */
    public function analyze(Request $request)
    {
        $fields = $request->input('fields', []);

        // Only send text fields to OpenAI
        $textFields = collect($fields)
            ->only(['seo_title','seo_page_title', 'seo_description', 'seo_keywords', 'seo_author'])
            ->filter(fn($v) => !empty($v))
            ->all();

        if (empty($textFields)) {
            return response()->json([
                'success' => false,
                'message' => 'No fields to analyze.',
                'results' => [],
            ]);
        }

        $contentPayload = '';
        foreach ($textFields as $field => $value) {
            $contentPayload .= "$field:\n$value\n\n";
        }

        try {
            $apiKey = env('OPENAI_API_KEY');
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-3.5-turbo', // or gpt-4 if you have access
                'messages' => [['role' => 'system', 'content' => 'You are a helpful assistant.'], ['role' => 'user', 'content' => "Evaluate these SEO fields and give a score from 1 to 10 for each, plus a short message.\n$contentPayload"]],
                'max_tokens' => 300,
            ]);

            $data = $response->json();

            if (!isset($data['choices'][0]['message']['content'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid AI response',
                    'results' => [],
                    'data'=>$data
                ]);
            }

            $lines = explode("\n", $data['choices'][0]['message']['content']);

            // Parse AI response lines
            $lines = explode("\n", $data['choices'][0]['message']['content']);
            $results = [];
            foreach ($lines as $line) {
                if (preg_match('/^(\w+):\s*Score\s*(\d+)/i', $line, $matches)) {
                    $results[$matches[1]] = [
                        'score' => (int) $matches[2],
                        'message' => $line,
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'results' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'AI request failed: ' . $e->getMessage(),
                'results' => [],
            ]);
        }
    }
}
