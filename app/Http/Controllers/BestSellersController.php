<?php

namespace App\Http\Controllers;

use App\Http\Requests\FetchBestSellers;
use App\Service\NYTService;
use Illuminate\Http\JsonResponse;

class BestSellersController extends Controller
{

    public function fetchBestSellers(FetchBestSellers $request, NYTService $nytService): JsonResponse
    {
        $params = $this->params($request);
        $data = $nytService->fetchBestSellers($params);
        return response()->json($data);
    }

    private function params(FetchBestSellers $request): array
    {
        $params = $request->safe()->only(['author', 'isbn', 'title', 'offset']);
        return $this->implodeISBN($params);
    }

    /**
     * @param array $params
     * @return array
     */
    private function implodeISBN(array $params): array
    {
        $isbn = $params['isbn'] ?? [];
        $params['isbn'] = implode(';', $isbn);
        if (empty($params['isbn'])) {
            unset($params['isbn']);
        }
        return $params;
    }

}
