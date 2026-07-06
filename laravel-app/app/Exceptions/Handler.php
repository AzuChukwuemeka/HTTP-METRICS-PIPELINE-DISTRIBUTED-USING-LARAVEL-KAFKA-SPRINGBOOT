<?php

namespace App\Exceptions;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Throwable;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

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
    public function register(): void
    {
        $this->renderable(function (ValidationException $e, $request) {
            return response()->json($e->errors(), 422);
        });
        $this->renderable(function (ApiExpiredException $e, $request) {
            return response()->json($e->getMessage(), 400);
        });
        $this->renderable(function (UserLoginException $e, $request) {
            echo $e->getMessage() . PHP_EOL;
            return response()->json($e->getMessage(), 400);
        });
        $this->renderable(function (QueryException $e, $request) {
            return response()->json(["Error" => " Duplicate Key Exception " . $request->input("email")], 400);
        });
        $this->renderable(function (\Exception $exception){
            return response()->json([
                "Error" => "Internal Server Error"
            ])->status(500);
        });
    }
    public function render($request, Throwable $e)
    {
        if($e instanceof QueryException){
            echo $e::class . PHP_EOL;
            echo $e->getMessage();
            return response()->json([
                "Error" => "Internal Server Error",
                "Message" => "Duplicate Key Found, Key Already Exists in Database"
            ],500);
        }
        if($e instanceof TokenExpiredException){
            return response()->json([
                "Error" => "Token Expired"
            ]);
        }
        if($e instanceof TokenInvalidException){
            return response()->json([
                "Error" => "Invalid Token"
            ]);
        }
        if($e instanceof JwtException){
            return response()->json([
                "Error" => "JwtException"
            ]);
        }
        echo $e::class . PHP_EOL;
//        echo $e->getTraceAsString() . PHP_EOL;
        return response()->json([
            "Error" => "Internal Server Error",
            "Message" => $e->getMessage()
        ]);
    }
}
