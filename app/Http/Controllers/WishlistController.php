<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use Illuminate\Http\Request;
use App\Rules\UniqueWishlist;
use App\Rules\MaxWishlistsPerUser;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

/**
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */

class WishlistController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/wishlists",
     *     summary="Retrieve all wishlists for the authenticated user or all wishlists if the user is an admin",
     *     tags={"Wishlists"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of wishlists",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="vacation_spot_id", type="integer", example=1),
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
            $isAdmin = $currentUser->id === getAdminRoleId();

            $wishlists =
                $isAdmin ?
                Wishlist::all() :
                Wishlist::where('user_id', $currentUser->id)->get();

            return response()->json($wishlists);
        } catch (QueryException $e) {
            Log::error('Getting wishlists failed: ' . $e->getMessage());
            return response()->json(['error' => 'Database error, please try again'], 500);
        }

    }

    /**
     * @OA\Post(
     *     path="/api/wishlists",
     *     summary="Add a new vacation spot to the user's wishlist",
     *     tags={"Wishlists"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"vacation_spot_id"},
     *             @OA\Property(property="vacation_spot_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Wishlist item created",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="vacation_spot_id", type="integer", example=1),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-15T12:34:56Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-15T12:34:56Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Database error"
     *     )
     * )
     */

    public function store(Request $request)
    {
        try {
            $currentUser = JWTAuth::parseToken()->authenticate();

            $request->validate([
                'vacation_spot_id' => [
                    'required',
                    'exists:vacation_spots,id',
                    new UniqueWishlist($currentUser->id),
                    new MaxWishlistsPerUser($currentUser->id)
                ]
            ]);

            $wishlist = Wishlist::create([
                'user_id' => $currentUser->id,
                'vacation_spot_id' => $request->vacation_spot_id
            ]);

            return response()->json($wishlist, 201);
        } catch (QueryException $e) {
            Log::error('Creating wishlist failed: ' . $e->getMessage());
            return response()->json(['error' => 'Database error, please try again'], 500);
        }

    }

    /**
     * @OA\Get(
     *     path="/api/wishlists/{id}",
     *     summary="Retrieve a specific wishlist item",
     *     tags={"Wishlists"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the wishlist item",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Wishlist item details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="vacation_spot_id", type="integer", example=1),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-15T12:34:56Z"),
     *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-15T12:34:56Z")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Wishlist item not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Database error"
     *     )
     * )
     */

    public function show(Wishlist $wishlist)
    {
        return response()->json($wishlist);
    }

    /**
     * @OA\Delete(
     *     path="/api/wishlists/{id}",
     *     summary="Remove a vacation spot from the user's wishlist",
     *     tags={"Wishlists"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the wishlist item to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Wishlist item deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Vacation Spot has been deleted from your wishlists successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Wishlist item not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Database error"
     *     )
     * )
     */

    public function destroy(Wishlist $wishlist)
    {
        try {
            $wishlist->delete();

            return response()->json(['message' => 'Vacation Spot has been deleted from you\'re wishlists successfully']);
        } catch (QueryException $e) {
            Log::error('Deleting wishlist failed: ' . $e->getMessage());
            return response()->json(['error' => 'Database error, please try again'], 500);
        }

    }
}
