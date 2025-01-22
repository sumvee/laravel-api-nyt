<?php

namespace App\Service;

use App\Exception\NYTApiException;
use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NYTService
{

    /**
     * @param array $params
     * @return string[]
     */
    public function fetchBestSellers(array $params): array
    {
        try {
            $cacheKey = 'nyt_best_sellers_' . md5(json_encode($params));
            return Cache::remember($cacheKey, now()->addMinutes(config('services.nyt.cache_minutes')), function () use ($params) {
                return $this->fetchAPIData($params)->json();
            });
        } catch (Exception $e) {
            Log::error($e->getMessage(), ['exception' => $e]);
            return ['error' => 'Unable to fetch best sellers at this time. Please try again later.'];
        }
    }

    /**
     * @param array $params
     * @return Response
     * @throws NYTApiException
     */
    public function fetchAPIData(array $params): Response
    {
        $params['api-key'] = config('services.nyt.api_key');
        $response = Http::get(config('services.nyt.base_uri'), $params);
//dd($response);
        if (!$this->responseHasOk($response)) {
            throw new NYTApiException('NYT API Error', $response->status());
        }

        return $response;
    }

    /**
     * @param Response $response
     * @return bool
     */
    private function responseHasOk(Response $response): bool
    {
//        dd($response);
        if (!$response->successful() || !$response->json()) {
            return false;
        }

        return data_get($response->json(), 'status') === 'OK';
    }
}

