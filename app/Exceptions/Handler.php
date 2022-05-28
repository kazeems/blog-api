<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof AuthorizationException && $request->expectsJson()) {
            return response()->json([
                "success" => false,
                "message" => $exception->getMessage()
            ], 403);
            // return ApiResponder::failureResponse("You are not authorized to access this resource", 403);
        }

        if ($exception instanceof ModelNotFoundException && $request->expectsJson()) {

            return response()->json([
                "success" => false,
                "message" => "The resource was not found in the database"
            ], 404);
            // return ApiResponder::failureResponse("The resource was not found in the database", 404);
        }

        if ($exception instanceof AuthenticationException && $request->expectsJson()) {

            return response()->json([
                "success" => false,
                "message" => $exception->getMessage()
            ], 401);
            // return ApiResponder::failureResponse("You are not logged in", 401);
        }

        if ($exception instanceof ValidationException && $request->expectsJson()) {
            return response()->json([
                "success" => false,
                "message" => $exception->getMessage(),
                "validationErrors" => $exception->errors()
            ], 422);
            // return ApiResponder::failureResponse($exception->getMessage(),  $exception->status, $exception->errors());
            // return ApiResponder::failureResponse($exception->getMessage(),  $exception->status, $this->transformErrors($exception));
        }

        if ($exception instanceof NotFoundHttpException && $request->expectsJson()) {
            return response()->json([
                "success" => false,
                "message" => "Link does not exist"
            ], 404);
            // return ApiResponder::failureResponse($exception->getMessage(),  $exception->status, $exception->errors());
            // return ApiResponder::failureResponse($exception->getMessage(),  $exception->status, $this->transformErrors($exception));
        }
        return parent::render($request, $exception);
    }
}
