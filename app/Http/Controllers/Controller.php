<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="BNB Management API",
 *     version="1.0.0",
 *     description="Professional BNB Management System API with JWT Authentication"
 * )
 * @OA\Server(
 *     url="/api/v1",
 *     description="API v1"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
abstract class Controller
{
    //
}
