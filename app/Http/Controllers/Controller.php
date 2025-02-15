<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="Vacation Spots API",
 *     version="1.0.0",
 *     description="API for managing vacation spots and user wishlists."
 * ),
 *
 * @OA\SecurityScheme(
 *     type="http",
 *     description="Login with email and password to get the authentication token",
 *     name="Token based Based",
 *     in="header",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     securityScheme="apiAuth",
 * )
 */
abstract class Controller
{
    //
}
