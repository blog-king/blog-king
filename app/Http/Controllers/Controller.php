<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    /**
     * @param $data
     * @param int $httpCode
     * @param int $code
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function buildReturnData($data, $httpCode = 200, $code = 0, string $message = null)
    {
        return new JsonResponse([
            'data' => $data,
            'code' => $code,
            'message' => $message,
        ], $httpCode);
    }

    /**
     * 格式化分页.
     *
     * @param \Illuminate\Contracts\Pagination\Paginator $paginator
     *
     * @return array
     */
    public function formatPaginate(Paginator $paginator)
    {
        return [
            'list' => $paginator->items(),
            'per_page' => $paginator->perPage(),
            'page' => $paginator->currentPage(),
            'has_next' => $paginator->hasMorePages(),
            'total' => $paginator instanceof LengthAwarePaginator ? $paginator->total() : null,
        ];
    }
}
