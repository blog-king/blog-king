<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @throws \Exception
     *
     * @return void
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @throws \Throwable
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @param \Illuminate\Http\Request $request
     */
    public function render($request, Throwable $exception)
    {
        if ($request->expectsJson()) {
            if ($exception instanceof ValidationException) {
                return new JsonResponse([
                    'code' => $exception->getCode(),
                    'data' => ['errors' => $exception->errors()],
                    'msg' => $exception->getMessage(),
                ], 422);
            } elseif ($exception instanceof HttpException){
                return new JsonResponse([
                    'code' => $exception->getCode(),
                    'data' => [],
                    'msg' => $exception->getMessage(),
                ], $exception->getStatusCode());
            }
        }

        return parent::render($request, $exception);
    }
}
