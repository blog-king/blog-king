<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
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
        return response()->json([
            'data' => $data,
            'code' => $code,
            'message' => $message,
        ], $httpCode);
    }

    /**
     * * 500 的错误返回结果格式.
     *
     * @param $code
     * @param $message
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function buildReturn500($code, $message)
    {
        return response()->json([
            'data' => null,
            'code' => $code,
            'message' => $message,
        ], 500);
    }
}
