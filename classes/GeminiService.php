<?php
require_once __DIR__ . '/../config/env.php';

class GeminiService {
    private string $apiKey;
    private string $apiUrl;

    public function __construct() {
        $this->apiKey = GEMINI_API_KEY;
        $this->apiUrl = GEMINI_API_ENDPOINT . '?key=' . urlencode($this->apiKey);
    }

    public function analyzeDecision(array $params): array {
        $prompt = $this->buildPrompt($params);
        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature'     => 0.7,
                'topK'            => 40,
                'topP'            => 0.95,
                'maxOutputTokens' => 8192,   // Increased: 2.5-flash needs more room for full JSON
            ]
        ];

        $response = $this->makeRequest($payload);

        $candidate = $response['candidates'][0] ?? null;
        if (!isset($candidate['content']['parts'][0]['text'])) {
            throw new RuntimeException('Gemini API returned an unexpected response format.');
        }

        // Detect truncated responses before attempting to parse
        $finishReason = $candidate['finishReason'] ?? 'STOP';
        if ($finishReason === 'MAX_TOKENS') {
            throw new RuntimeException(
                'The AI response was cut off (MAX_TOKENS). Try shortening your option descriptions, or the analysis will be retried automatically.'
            );
        }

        $rawText = $candidate['content']['parts'][0]['text'];
        return $this->parseJsonResponse($rawText);
    }

    private function buildPrompt(array $p): string {
        return <<<PROMPT
You are RealityOS AI — an elite decision intelligence engine. Your purpose is to provide rigorous, balanced, actionable decision analysis.

DECISION SCENARIO:
Title: {$p['decision_title']}
Category: {$p['category']}
Situation: {$p['situation_description']}

OPTION A: {$p['option_a_title']}
{$p['option_a_description']}

OPTION B: {$p['option_b_title']}
{$p['option_b_description']}

USER CONTEXT:
Goal: {$p['user_goal']}
Time Horizon: {$p['time_horizon']}
Risk Tolerance: {$p['risk_tolerance']}

YOUR TASK:
Analyze both options thoroughly. Be practical, realistic, and data-driven. Consider short-term and long-term implications, risks, opportunities, and alignment with the user's stated goal and risk tolerance.

CRITICAL: Respond ONLY with a valid JSON object — no markdown, no code blocks, no additional text. The JSON must exactly match this structure:

{
  "decision_summary": "Brief summary of the decision scenario",
  "option_a": {
    "title": "Option A title",
    "analysis": "Detailed analysis paragraph",
    "pros": ["pro 1", "pro 2", "pro 3"],
    "cons": ["con 1", "con 2", "con 3"],
    "risk_level": "Low|Medium|High",
    "opportunity_score": 75,
    "short_term_outlook": "What happens in the short term",
    "long_term_outlook": "What happens long term"
  },
  "option_b": {
    "title": "Option B title",
    "analysis": "Detailed analysis paragraph",
    "pros": ["pro 1", "pro 2", "pro 3"],
    "cons": ["con 1", "con 2", "con 3"],
    "risk_level": "Low|Medium|High",
    "opportunity_score": 82,
    "short_term_outlook": "What happens in the short term",
    "long_term_outlook": "What happens long term"
  },
  "comparison": {
    "risk_level":       { "label": "Risk Level",       "option_a_score": 60, "option_b_score": 75, "winner": "Option A or Option B", "note": "Higher score = lower risk" },
    "growth_potential": { "label": "Growth Potential",  "option_a_score": 80, "option_b_score": 65, "winner": "Option A or Option B", "note": "Higher score = more growth" },
    "cost_efficiency":  { "label": "Cost Efficiency",   "option_a_score": 70, "option_b_score": 85, "winner": "Option A or Option B", "note": "Higher score = better value" },
    "time_to_results":  { "label": "Time to Results",   "option_a_score": 90, "option_b_score": 55, "winner": "Option A or Option B", "note": "Higher score = faster results" },
    "stability":        { "label": "Stability",          "option_a_score": 75, "option_b_score": 50, "winner": "Option A or Option B", "note": "Higher score = more stable" }
  },
  "recommended_option": "Option A or Option B",
  "confidence_score": 85,
  "reasoning": "High-level summary of the decision logic",
  "recommendation_details": {
    "why_better": "Clear explanation of why the winner was chosen",
    "situational_advantages": ["advantage 1", "advantage 2", "advantage 3"],
    "when_not_to_choose": "A specific warning or context where this choice might fail"
  },
  "execution_roadmap": {
    "action_steps": [
      "Step 1: specific actionable step",
      "Step 2: specific actionable step",
      "Step 3: specific actionable step",
      "Step 4: specific actionable step"
    ],
    "preparation_list": ["Item to prepare 1", "Item to prepare 2"],
    "common_mistakes": ["Mistake to avoid 1", "Mistake to avoid 2"],
    "timeline_expectations": "Expected time to see results"
  },
  "final_verdict": "Compelling one-paragraph final verdict"
}

opportunity_score and confidence_score must be integers between 0 and 100.
PROMPT;
    }

    private function makeRequest(array $payload): array {
        $ch = curl_init($this->apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_SSL_VERIFYPEER => false, // set true in production
        ]);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new RuntimeException('CURL error: ' . $curlError);
        }

        $decoded = json_decode($result, true);
        if ($httpCode !== 200 || !is_array($decoded)) {
            $errMsg = isset($decoded['error']['message']) ? $decoded['error']['message'] : 'Unknown API error.';
            throw new RuntimeException('Gemini API error (' . $httpCode . '): ' . $errMsg);
        }

        return $decoded;
    }

    private function parseJsonResponse(string $text): array {
        // Strip markdown code fences if the model added them
        $text = preg_replace('/^```(?:json)?\s*/i', '', trim($text));
        $text = preg_replace('/\s*```$/i', '', $text);
        $text = trim($text);

        // First attempt: decode the whole text as-is
        $parsed = json_decode($text, true);

        // Second attempt: extract the outermost JSON object using DOTALL so
        // newlines inside values are matched correctly
        if (!is_array($parsed)) {
            if (preg_match('/\{.+\}/s', $text, $matches)) {
                $parsed = json_decode($matches[0], true);
            }
        }

        if (!is_array($parsed)) {
            $errCode = json_last_error();
            $errMsg  = json_last_error_msg();
            throw new RuntimeException(
                "Could not parse AI response as JSON (error {$errCode}: {$errMsg}). " .
                "Raw snippet: " . substr($text, 0, 400)
            );
        }

        // Clamp numeric scores to 0-100
        if (isset($parsed['confidence_score'])) {
            $parsed['confidence_score'] = min(100, max(0, (int)$parsed['confidence_score']));
        }
        foreach (['option_a', 'option_b'] as $opt) {
            if (isset($parsed[$opt]['opportunity_score'])) {
                $parsed[$opt]['opportunity_score'] = min(100, max(0, (int)$parsed[$opt]['opportunity_score']));
            }
        }

        return $parsed;
    }
}
