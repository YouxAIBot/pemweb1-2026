<?php

namespace App\Services\Integrations;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAIQuestionGeneratorService
{
    public function generate(array $payload): array
    {
        $apiKey = config('services.openai.api_key');

        if (blank($apiKey)) {
            throw new RuntimeException('OPENAI_API_KEY belum diisi di .env.');
        }

        $schema = $this->schema();

        $prompt = $this->buildPrompt($payload);

        $response = Http::timeout(60)
            ->retry(2, 700)
            ->withToken($apiKey)
            ->acceptJson()
            ->asJson()
            ->post(config('services.openai.responses_endpoint'), [
                'model' => config('services.openai.model', 'gpt-4.1-mini'),
                'input' => [
                    [
                        'role' => 'system',
                        'content' => 'You generate safe, original, classroom-ready language learning questions. Always follow the supplied JSON schema exactly. Do not include copyrighted passages or private/personal data.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'text' => [
                    'format' => [
                        'type' => 'json_schema',
                        'name' => 'yolearning_question_batch',
                        'schema' => $schema,
                        'strict' => true,
                    ],
                ],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('OpenAI Question Generator gagal: ' . $response->body());
        }

        $jsonText = $this->extractText($response->json());

        if (blank($jsonText)) {
            throw new RuntimeException('OpenAI tidak mengembalikan JSON yang bisa dibaca.');
        }

        $decoded = json_decode($jsonText, true);

        if (! is_array($decoded)) {
            throw new RuntimeException('Output OpenAI bukan JSON valid: ' . json_last_error_msg());
        }

        return $decoded;
    }

    private function buildPrompt(array $payload): string
    {
        $type = $payload['question_type'] ?? 'multiple_choice';
        $count = (int) ($payload['question_count'] ?? 3);
        $topic = $payload['topic'] ?? 'daily conversation';
        $language = $payload['target_language'] ?? 'English';
        $difficulty = $payload['difficulty'] ?? 'beginner';
        $notes = $payload['notes'] ?? '';

        return <<<PROMPT
Generate {$count} {$type} questions for a language learning web app.

Target language: {$language}
Difficulty: {$difficulty}
Topic: {$topic}
Additional admin notes: {$notes}

Rules:
- Use short, original, beginner-friendly material unless difficulty says otherwise.
- For multiple_choice, real_case, and mixed: include 2-4 answer options and mark exactly one correct option where possible.
- For listening: create a listening_flow with story blocks and question blocks. Do not include audio paths.
- For word_match: create word_pairs in settings.
- For sentence_order: create sentence_tokens in settings in the correct order, and set correct_answer to the full sentence.
- Keep explanations short and useful.
- Output must match the JSON schema.
PROMPT;
    }

    private function extractText(array $payload): ?string
    {
        if (isset($payload['output_text']) && is_string($payload['output_text'])) {
            return $payload['output_text'];
        }

        foreach (($payload['output'] ?? []) as $output) {
            foreach (($output['content'] ?? []) as $content) {
                if (($content['type'] ?? null) === 'output_text' && isset($content['text'])) {
                    return $content['text'];
                }

                if (isset($content['text']) && is_string($content['text'])) {
                    return $content['text'];
                }
            }
        }

        return null;
    }

    private function schema(): array
    {
        $option = [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => [
                'text' => ['type' => 'string'],
                'is_correct' => ['type' => 'boolean'],
            ],
            'required' => ['text', 'is_correct'],
        ];

        $wordPair = [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => [
                'left' => ['type' => 'string'],
                'right' => ['type' => 'string'],
            ],
            'required' => ['left', 'right'],
        ];

        $sentenceToken = [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => [
                'text' => ['type' => 'string'],
            ],
            'required' => ['text'],
        ];

        $storySegment = [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => [
                'text' => ['type' => 'string'],
                'audio_path' => ['type' => 'string'],
            ],
            'required' => ['text', 'audio_path'],
        ];

        $listeningFlowItem = [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => [
                'type' => ['type' => 'string', 'enum' => ['story', 'question']],
                'story_text' => ['type' => 'string'],
                'question_text' => ['type' => 'string'],
                'question_audio_path' => ['type' => 'string'],
                'options' => [
                    'type' => 'array',
                    'items' => $option,
                ],
                'explanation' => ['type' => 'string'],
            ],
            'required' => ['type', 'story_text', 'question_text', 'question_audio_path', 'options', 'explanation'],
        ];

        return [
            'type' => 'object',
            'additionalProperties' => false,
            'properties' => [
                'batch_title' => ['type' => 'string'],
                'questions' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'properties' => [
                            'type' => [
                                'type' => 'string',
                                'enum' => ['multiple_choice', 'word_match', 'sentence_order', 'listening', 'real_case', 'mixed'],
                            ],
                            'instruction' => ['type' => 'string'],
                            'question_text' => ['type' => 'string'],
                            'correct_answer' => ['type' => 'string'],
                            'explanation' => ['type' => 'string'],
                            'points' => ['type' => 'integer'],
                            'time_limit' => ['type' => 'integer'],
                            'options' => [
                                'type' => 'array',
                                'items' => $option,
                            ],
                            'settings' => [
                                'type' => 'object',
                                'additionalProperties' => false,
                                'properties' => [
                                    'listening_flow' => [
                                        'type' => 'array',
                                        'items' => $listeningFlowItem,
                                    ],
                                    'word_pairs' => [
                                        'type' => 'array',
                                        'items' => $wordPair,
                                    ],
                                    'sentence_tokens' => [
                                        'type' => 'array',
                                        'items' => $sentenceToken,
                                    ],
                                    'scenario_context' => ['type' => 'string'],
                                    'ideal_response' => ['type' => 'string'],
                                    'story_segments' => [
                                        'type' => 'array',
                                        'items' => $storySegment,
                                    ],
                                ],
                                'required' => ['listening_flow', 'word_pairs', 'sentence_tokens', 'scenario_context', 'ideal_response', 'story_segments'],
                            ],
                        ],
                        'required' => ['type', 'instruction', 'question_text', 'correct_answer', 'explanation', 'points', 'time_limit', 'options', 'settings'],
                    ],
                ],
            ],
            'required' => ['batch_title', 'questions'],
        ];
    }
}
