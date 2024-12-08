<?php

class AIScraper
{
    private $openai_key;
    private $valid_api_keys = [];

    public function __construct()
    {
        // $this->openai_key = getenv('OPENAI_API_KEY');
        $this->openai_key = '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';
        if (empty($this->openai_key)) {
            throw new Exception("OpenAI API key not configured");
        }
        $this->valid_api_keys[] = 'test-key-123';
    }

    public function retrieve($params)
    {
        try {
            // Validate API key first
            if (!isset($params['api_key'])) {
                throw new Exception("Missing API key");
            }

            if (!in_array($params['api_key'], $this->valid_api_keys)) {
                throw new Exception("Invalid API key");
            }

            // Validate required parameters
            $required = ['webpage_url', 'api_method_name', 'api_response_structure'];
            foreach ($required as $field) {
                if (!isset($params[$field])) {
                    throw new Exception("Missing required parameter: {$field}");
                }
            }

            $html = $this->fetchPage($params['webpage_url']);
            $text = $this->cleanHTML($html);

            $prompt = [
                "messages" => [
                    [
                        "role" => "system",
                        "content" => "You are a web scraping AI. Your task is to extract information according to the method '{$params['api_method_name']}'. Return data exactly matching the provided structure."
                    ],
                    [
                        "role" => "user",
                        "content" => "Extract the following from this webpage:\n" .
                            json_encode($params['api_response_structure']) . "\n\nContent:\n" . $text
                    ]
                ]
            ];

            $result = $this->extractWithAI($prompt);

            $response = ['response' => $result];

            if (isset($params['verbose']) && $params['verbose']) {
                $response['verbose_full_html'] = $html;
                $response['verbose_markdown'] = "Markdown conversion not implemented yet";
            }

            return $response;
        } catch (Exception $e) {
            return [
                'error' => true,
                'reason' => $e->getMessage()
            ];
        }
    }

    private function extractWithAI($prompt)
    {
        $ch = curl_init('https://api.openai.com/v1/chat/completions');

        $data = [
            'model' => 'gpt-4o-mini',
            'messages' => $prompt['messages'],
            'temperature' => 0.3,
            // 'max_tokens' => 1000
        ];

        error_log("OpenAI Request: " . json_encode($data));

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . trim($this->openai_key),
                'Content-Type: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode($data)
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Add detailed error logging
        if ($response === false) {
            error_log("CURL Error: " . curl_error($ch));
        }

        error_log("OpenAI Response HTTP Code: " . $httpCode);
        error_log("OpenAI Raw Response: " . $response);

        curl_close($ch);

        if ($httpCode !== 200) {
            error_log("OpenAI API Error Response: " . $response);
            throw new Exception("OpenAI API error: " . $httpCode);
        }

        $result = json_decode($response, true);
        error_log("Decoded Response: " . json_encode($result));

        if (!isset($result['choices']) || !isset($result['choices'][0]['message']['content'])) {
            error_log("Unexpected OpenAI response structure: " . json_encode($result));
            throw new Exception("Invalid response from OpenAI API");
        }

        try {
            $content = $result['choices'][0]['message']['content'];
            error_log("AI Response Content: " . $content);

            // Remove markdown code block formatting if present
            $content = preg_replace('/^```json\s*|\s*```$/m', '', $content);

            return json_decode($content, true);
        } catch (Exception $e) {
            error_log("Failed to parse AI response content: " . $content);
            throw new Exception("Failed to parse AI response");
        }
    }

    private function fetchPage($url)
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.5',
                'Connection: keep-alive',
                'Upgrade-Insecure-Requests: 1'
            ]
        ]);

        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode !== 200) {
            throw new Exception("Failed to fetch page. HTTP Code: " . $httpCode);
        }

        curl_close($ch);
        return $html;
    }

    private function cleanHTML($html)
    {
        // First remove all the unnecessary elements
        $patterns = [
            '/<script\b[^>]*>(.*?)<\/script>/is',
            '/<style\b[^>]*>(.*?)<\/style>/is',
            '/<header\b[^>]*>(.*?)<\/header>/is',
            '/<footer\b[^>]*>(.*?)<\/footer>/is',
            '/<nav\b[^>]*>(.*?)<\/nav>/is',
            '/<aside\b[^>]*>(.*?)<\/aside>/is',
            '/<!--(.*?)-->/s',
            '/<link\b[^>]*>/is',
            '/<meta\b[^>]*>/is',
            '/<noscript\b[^>]*>(.*?)<\/noscript>/is'
        ];

        $html = preg_replace($patterns, '', $html);

        // Strip tags but keep essential ones
        $text = strip_tags($html, '<h1><h2><p><img><span>');

        // Normalize whitespace more aggressively
        $text = preg_replace('/\s+/', ' ', trim($text));

        // Much shorter limit to ensure we don't exceed API limits
        return substr($text, 0, 4000);
    }
}
