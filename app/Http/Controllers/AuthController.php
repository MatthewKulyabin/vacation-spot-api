<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register a new user",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="User registration data",
     *         @OA\JsonContent(
     *             required={"login", "password", "password_confirmation"},
     *             @OA\Property(property="login", type="string", example="johndoe"),
     *             @OA\Property(property="password", type="string", format="password", example="secret123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="secret123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User registered successfully"),
     *             @OA\Property(property="user", type="object",
     *              @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="login", type="string", example="john_doe"),
     *                 @OA\Property(property="email", type="string", example="john@example.com"),
     *                 @OA\Property(property="role_id", type="integer", example=2),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-15T12:34:56Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-15T12:34:56Z")),
     *             @OA\Property(property="token", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Database or unexpected error"
     *     )
     * )
     */
    public function register(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'login' => ['required', Rule::unique('users', 'login')],
                'password' => ['required', 'confirmed']
            ]);

            $user = User::create([
                'login' => $validatedData['login'],
                'password' => $validatedData['password'],
                'role_id' => getUserRoleId()
            ]);

            $token = JWTAuth::fromUser($user);

            return response()->json([
                'message' => 'User registered successfully',
                'user' => $user,
                'token' => $token
            ], 201);
        } catch (QueryException $e) {
            Log::error('Registration failed: ' . $e->getMessage());
            return response()->json(['error' => 'Database error, please try egain'], 500);
        } catch (Exception $e) {
            Log::error('Unexpected registration error: ' . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occured'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="User login to retrieve JWT token",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Login credentials",
     *         @OA\JsonContent(
     *             required={"login", "password"},
     *             @OA\Property(property="login", type="string", example="johndoe"),
     *             @OA\Property(property="password", type="string", format="password", example="secret123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="JWT token generated",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="JWT creation error or unexpected error"
     *     )
     * )
     */

    public function login(Request $request)
    {
        $credentials = $request->only('login', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }

            return response()->json(compact('token'));
        } catch (JWTException $e) {
            Log::error('JWT error during login: ' . $e->getMessage());
            return response()->json(['error' => 'Could not create token'], 500);
        } catch (Exception $e) {
            Log::error('Unexpected login error: ' . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Logout the authenticated user",
     *     tags={"Authentication"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="User logged out successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User logged out successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Logout error"
     *     )
     * )
     */

    public function logout()
    {
        try {
            $token = JWTAuth::getToken();
            JWTAuth::invalidate($token);

            return response()->json(['message' => 'User logged out successfully']);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Failed to log out, please try again.'], 500);
        }

    }

    /**
     * @OA\Post(
     *     path="/api/refresh",
     *     summary="Refresh the JWT token",
     *     tags={"Authentication"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="JWT token refreshed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="new_token_here")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Token refresh error"
     *     )
     * )
     */

    public function refresh()
    {
        try {
            $newToken = JWTAuth::parseToken()->refresh();
        } catch (JWTException $e) {
            Log::error('Token refresh failed: ' . $e->getMessage());
            return response()->json(['error' => 'Could not refresh token'], 500);
        }

        return response()->json(['token' => $newToken]);
    }

    /**
     * @OA\Get(
     *     path="/api/me",
     *     summary="Get details of the authenticated user",
     *     tags={"Authentication"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Current user details",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */

    public function me()
    {
        $user = JWTAuth::parseToken()->authenticate();
        return response()->json(compact('user'));
    }
}
