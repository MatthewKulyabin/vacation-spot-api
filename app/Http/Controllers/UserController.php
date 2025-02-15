<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Tymon\JWTAuth\Exceptions\JWTException;


class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/users",
     *     summary="Get a list of users",
     *     tags={"Users"},
     *     @OA\Response(
     *         response=200,
     *         description="List of users",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="login", type="string", example="john_doe"),
     *                 @OA\Property(property="email", type="string", example="john@example.com"),
     *                 @OA\Property(property="role_id", type="integer", example=2),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-15T12:34:56Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-15T12:34:56Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Database error"
     *     )
     * )
     */

    public function index()
    {
        try {
            $currentUser = JWTAuth::parseToken()->authenticate();
        } catch (JWTException $e) {
            $currentUser = null;
        }

        try {
            $isAdmin = $currentUser?->role_id === getAdminRoleId();

            $users = $isAdmin
                ? User::all()
                : User::where('role_id', '!=', getAdminRoleId())->get();

            return response()->json($users);
        } catch (QueryException $e) {
            Log::error('Getting users failed: ' . $e->getMessage());
            return response()->json(['error' => 'Database error, please try again'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/users/{id}",
     *     summary="Get user details",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User details",
     *         @OA\JsonContent(type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="login", type="string", example="john_doe"),
     *                 @OA\Property(property="email", type="string", example="john@example.com"),
     *                 @OA\Property(property="role_id", type="integer", example=2),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-15T12:34:56Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-15T12:34:56Z"))
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     */

    public function show(User $user)
    {
        if ($user->role_id === getAdminRoleId()) {
            return response()->json(['error' => 'Forbidden'], 403);
        }
        return response()->json($user);
    }

    /**
     * @OA\Put(
     *     path="/api/users/{id}",
     *     summary="Update user details",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="login", type="string", example="john_doe"),
     *             @OA\Property(property="password", type="string", example="new_password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully",
     *         @OA\JsonContent(type="object",
     *          @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="login", type="string", example="john_doe"),
     *                 @OA\Property(property="email", type="string", example="john@example.com"),
     *                 @OA\Property(property="role_id", type="integer", example=2),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-15T12:34:56Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-15T12:34:56Z"))
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid data"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Database error"
     *     )
     * )
     */

    public function update(Request $request, User $user)
    {
        try {
            $request->validate([
                'login' => ['sometimes', Rule::unique('users', 'login')],
                'password' => ['sometimes', 'confirmed']
            ]);

            $user->update($request->only(['login', 'password']));

            return response()->json($user);
        } catch (QueryException $e) {
            Log::error('Updating user failed: ' . $e->getMessage());
            return response()->json(['error' => 'Database error, please try again'], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     summary="Delete a user",
     *     security={{"bearerAuth": {}}},
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User has been deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Database error"
     *     )
     * )
     */
    public function destroy(User $user)
    {
        try {
            $user->delete();

            return response()->json(['message' => 'User has been deleted successfully'], 200);
        } catch (QueryException $e) {
            Log::error('Deleting user failed: ' . $e->getMessage());
            return response()->json(['error' => 'Database error, please try again'], 500);
        }
    }
}
