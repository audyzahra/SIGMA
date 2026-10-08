<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class AIRecommendationService
{
    public const STATUS_DISABLED = 'disabled';
    public const STATUS_ENDPOINT_MISSING = 'endpoint_missing';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_QUOTA_EXCEEDED = 'quota_exceeded';
    public const STATUS_REQUEST_FAILED = 'request_failed';

    protected const CACHE_PREFIX = 'sigma.ai.recommendation';
    protected const CACHE_TTL_MINUTES = 1440;

    protected string $url;
    protected int $timeout;

    public function __construct()
    {
        $this->url = rtrim(
            (string) config(
                'services.ai.url',
                'http://127.0.0.1:8000'
            ),
            '/'
        );

        $this->timeout = (int) config(
            'services.ai.timeout',
            30
        );
    }

    public function enabled(): bool
    {
        return (bool) config(
            'sigma_priority.ai_recommendation.enabled',
            false
        );
    }

    public function provider(): ?string
    {
        return config(
            'sigma_priority.ai_recommendation.provider',
            'gemini'
        );
    }

    public function endpoint(): ?string
    {
        return config(
            'sigma_priority.ai_recommendation.endpoint'
        );
    }

    public function payload(array $detail): array
    {
        $region = (array) ($detail['region'] ?? []);
        $risk = (array) ($detail['risk'] ?? []);
        $impact = (array) ($detail['impact'] ?? []);
        $priority = (array) ($detail['priority'] ?? []);
        $sources = (array) ($detail['sources'] ?? []);

        return [
            'region' => [
                'id' => $region['id'] ?? null,
                'name' => $region['name'] ?? null,
                'code' => $region['code'] ?? null,
                'level' => $region['level'] ?? null,
                'parent_name' => $region['parent_name']
                    ?? $region['parent']
                    ?? null,
            ],

            'risk' => [
                'score' => $risk['score'] ?? null,
                'level' => $risk['level'] ?? null,
                'weather' => $risk['weather'] ?? null,
            ],

            'impact' => [
                'score' => $impact['score'] ?? null,
                'population' => $impact['population'] ?? null,
                'land_cover' => $impact['land_cover'] ?? null,
            ],

            'priority' => [
                'score' => $priority['score'] ?? null,
                'level' => $priority['level'] ?? null,
                'ranking_position' => $priority['ranking_position']
                    ?? null,
            ],

            'sources' => $this->normalizeSources($sources),
        ];
    }

    protected function normalizeSources(
        array $sources
    ): array {
        $normalized = [];

        foreach ($sources as $group => $items) {
            if (! is_array($items)) {
                $normalized[] = [
                    'group' => $group,
                    'value' => $items,
                ];

                continue;
            }

            foreach ($items as $key => $item) {
                if (is_array($item)) {
                    $normalized[] = array_merge(
                        [
                            'group' => $group,
                            'key' => $key,
                        ],
                        $item
                    );
                } else {
                    $normalized[] = [
                        'group' => $group,
                        'key' => $key,
                        'value' => $item,
                    ];
                }
            }
        }

        return $normalized;
    }

    public function recommend(array $payload): array
    {
        if (! $this->enabled()) {
            return $this->result(
                self::STATUS_DISABLED
            );
        }

        $endpoint = $this->endpoint();

        if (! $endpoint) {
            return $this->result(
                self::STATUS_ENDPOINT_MISSING
            );
        }

        $cacheKey = $this->cacheKey($payload);

        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            $cached['cached'] = true;

            Log::info(
                'AI Recommendation menggunakan cache.',
                [
                    'provider' => $this->provider(),
                    'region' => $payload['region']['name']
                        ?? null,
                    'priority' => $payload['priority']['level']
                        ?? null,
                    'priority_score' => $payload['priority']['score']
                        ?? null,
                ]
            );

            return $cached;
        }

        $url = rtrim($this->url, '/')
            . '/'
            . ltrim($endpoint, '/');

        try {
            Log::info(
                'AI Recommendation request dimulai.',
                [
                    'provider' => $this->provider(),
                    'url' => $url,
                    'region' => $payload['region']['name']
                        ?? null,
                    'priority' => $payload['priority']['level']
                        ?? null,
                    'priority_score' => $payload['priority']['score']
                        ?? null,
                    'sources_count' => count(
                        $payload['sources'] ?? []
                    ),
                ]
            );

            $response = Http::timeout(
                $this->timeout
            )
                ->acceptJson()
                ->asJson()
                ->post(
                    $url,
                    $payload
                );

            if ($response->status() === 429) {
                $detail = $response->json('detail');

                if (is_array($detail)) {
                    $message = $detail['message']
                        ?? 'Quota Gemini telah tercapai.';

                    $code = $detail['code']
                        ?? 'quota_exceeded';

                    $model = $detail['model']
                        ?? null;
                } else {
                    $message = is_string($detail)
                        ? $detail
                        : 'Quota Gemini telah tercapai.';

                    $code = 'quota_exceeded';
                    $model = null;
                }

                Log::warning(
                    'AI Recommendation Gemini quota exceeded.',
                    [
                        'provider' => $this->provider(),
                        'url' => $url,
                        'status' => 429,
                        'code' => $code,
                        'model' => $model,
                        'message' => $message,
                    ]
                );

                return [
                    'available' => false,
                    'status' => self::STATUS_QUOTA_EXCEEDED,
                    'provider' => $this->provider(),
                    'recommendation' => null,
                    'priority_action' => null,
                    'actions' => [],
                    'reasoning' => null,
                    'message' => $message,
                    'code' => $code,
                    'model' => $model,
                    'cached' => false,
                ];
            }

            if ($response->failed()) {
                Log::error(
                    'AI Recommendation request gagal.',
                    [
                        'provider' => $this->provider(),
                        'url' => $url,
                        'status' => $response->status(),
                        'body' => $response->body(),
                        'payload' => $payload,
                    ]
                );

                return [
                    'available' => false,
                    'status' => self::STATUS_REQUEST_FAILED,
                    'provider' => $this->provider(),
                    'recommendation' => null,
                    'priority_action' => null,
                    'actions' => [],
                    'reasoning' => null,
                    'message' => null,
                    'cached' => false,
                ];
            }

            $data = $response->json();

            if (! is_array($data)) {
                Log::error(
                    'AI Recommendation response bukan JSON object.',
                    [
                        'url' => $url,
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]
                );

                return [
                    'available' => false,
                    'status' => self::STATUS_REQUEST_FAILED,
                    'provider' => $this->provider(),
                    'recommendation' => null,
                    'priority_action' => null,
                    'actions' => [],
                    'reasoning' => null,
                    'message' => null,
                    'cached' => false,
                ];
            }

            $result = [
                'available' => (bool) (
                    $data['available'] ?? true
                ),

                'status' => $data['status']
                    ?? self::STATUS_SUCCESS,

                'provider' => $data['provider']
                    ?? $this->provider(),

                'recommendation' => $data['recommendation']
                    ?? null,

                'priority_action' => $data['priority_action']
                    ?? null,

                'actions' => is_array(
                    $data['actions'] ?? null
                )
                    ? $data['actions']
                    : [],

                'reasoning' => $data['reasoning']
                    ?? null,

                'message' => $data['message']
                    ?? null,

                'code' => $data['code']
                    ?? null,

                'model' => $data['model']
                    ?? null,

                'cached' => false,
            ];

            Log::info(
                'AI Recommendation berhasil.',
                [
                    'provider' => $result['provider'],
                    'status' => $result['status'],
                    'region' => $payload['region']['name']
                        ?? null,
                ]
            );

            if (
                $result['available'] === true
                && $result['status'] === self::STATUS_SUCCESS
            ) {
                Cache::put(
                    $cacheKey,
                    $result,
                    now()->addMinutes(
                        self::CACHE_TTL_MINUTES
                    )
                );
            }

            return $result;
        } catch (Throwable $e) {
            Log::error(
                'AI Recommendation exception.',
                [
                    'provider' => $this->provider(),
                    'url' => $url,
                    'message' => $e->getMessage(),
                    'payload' => $payload,
                ]
            );

            return [
                'available' => false,
                'status' => self::STATUS_REQUEST_FAILED,
                'provider' => $this->provider(),
                'recommendation' => null,
                'priority_action' => null,
                'actions' => [],
                'reasoning' => null,
                'message' => null,
                'cached' => false,
            ];
        }
    }

    protected function cacheKey(array $payload): string
    {
        $normalized = $this->normalizeForCache(
            $payload
        );

        return self::CACHE_PREFIX
            . '.'
            . hash(
                'sha256',
                json_encode(
                    $normalized,
                    JSON_UNESCAPED_UNICODE
                    | JSON_UNESCAPED_SLASHES
                )
            );
    }

    protected function normalizeForCache(
        array $data
    ): array {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->normalizeForCache(
                    $value
                );
            }
        }

        if (! array_is_list($data)) {
            ksort($data);
        }

        return $data;
    }

    protected function result(
        string $status
    ): array {
        return [
            'available' => false,
            'status' => $status,
            'provider' => $this->provider(),
            'recommendation' => null,
            'priority_action' => null,
            'actions' => [],
            'reasoning' => null,
            'message' => null,
            'code' => null,
            'model' => null,
            'cached' => false,
        ];
    }
}