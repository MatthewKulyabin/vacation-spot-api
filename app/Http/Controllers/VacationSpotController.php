<?php

namespace App\Http\Controllers;

use App\Models\VacationSpot;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

class VacationSpotController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/vacation-spots",
     *     summary="Retrieve a list of vacation spots",
     *     tags={"Vacation Spots"},
     *     @OA\Response(
     *         response=200,
     *         description="A list of vacation spots",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Beach Paradise"),
     *                 @OA\Property(property="latitude", type="number", format="float", example=-21.45339700),
     *                 @OA\Property(property="longitude", type="number", format="float", example=-173.69610300)
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
            return response()->json(VacationSpot::all());
        } catch (QueryException $e) {
            Log::error('Getting vacation spot failed: ' . $e->getMessage());
            return response()->json(['error' => 'Database error, please try again'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/vacation-spots",
     *     summary="Create a new vacation spot",
     *     tags={"Vacation Spots"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "latitude", "longitude"},
     *             @OA\Property(property="name", type="string", example="Mountain Retreat"),
     *             @OA\Property(property="latitude", type="number", format="float", example=-21.45339700),
     *             @OA\Property(property="longitude", type="number", format="float", example=-173.69610300)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Vacation spot created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=2),
     *             @OA\Property(property="name", type="string", example="Mountain Retreat"),
     *             @OA\Property(property="latitude", type="number", format="float", example=-21.45339700),
     *             @OA\Property(property="longitude", type="number", format="float", example=-173.69610300)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
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
            $validatedData = $request->validate([
                'name' => ['required', 'max:255', Rule::unique('vacation_spots', 'name')],
                'latitude' => ['required', 'numeric', 'between:-90,90'],
                'longitude' => ['required', 'numeric', 'between:-180,180']
            ]);

            $vacationSpot = VacationSpot::create([
                'name' => $validatedData['name'],
                'latitude' => $validatedData['latitude'],
                'longitude' => $validatedData['longitude']
            ]);

            return response()->json($vacationSpot, 201);
        } catch (QueryException $e) {
            Log::error('Creating vacation spot failed: ' . $e->getMessage());
            return response()->json(['error' => 'Database error, please try again'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/vacation-spots/{id}",
     *     summary="Retrieve a specific vacation spot",
     *     tags={"Vacation Spots"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the vacation spot",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Vacation spot details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Beach Paradise"),
     *             @OA\Property(property="latitude", type="number", format="float", example=-21.45339700),
     *             @OA\Property(property="longitude", type="number", format="float", example=-173.69610300)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Vacation spot not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Database error"
     *     )
     * )
     */

    public function show(VacationSpot $vacationSpot)
    {
        return response()->json($vacationSpot);
    }

    /**
     * @OA\Put(
     *     path="/api/vacation-spots/{id}",
     *     summary="Update an existing vacation spot",
     *     tags={"Vacation Spots"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the vacation spot",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Mountain Retreat"),
     *             @OA\Property(property="latitude", type="number", format="float", example=-21.45339700),
     *             @OA\Property(property="longitude", type="number", format="float", example=-173.69610300)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Vacation spot updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Mountain Retreat"),
     *             @OA\Property(property="latitude", type="number", format="float", example=-21.45339700),
     *             @OA\Property(property="longitude", type="number", format="float", example=-173.69610300)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Vacation spot not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Database error"
     *     )
     * )
     */

    public function update(Request $request, VacationSpot $vacationSpot)
    {
        try {
            $request->validate([
                'name' => ['sometimes', 'max:255', Rule::unique('vacation_spots', 'name')],
                'latitude' => ['sometimes', 'numeric', 'between:-90,90'],
                'longitude' => ['sometims', 'numeric', 'between:-180,180']
            ]);

            $vacationSpot->update($request->only(['name', 'latitude', 'longitude']));

            return response()->json($vacationSpot);
        } catch (QueryException $e) {
            Log::error('Updating vacation spot failed: ' . $e->getMessage());
            return response()->json(['error' => 'Database error, please try again'], 500);
        }

    }

    /**
     * @OA\Delete(
     *     path="/api/vacation-spots/{id}",
     *     summary="Delete a specific vacation spot",
     *     tags={"Vacation Spots"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the vacation spot to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Vacation spot deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Vacation Spot has been deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Vacation spot not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Database error"
     *     )
     * )
     */

    public function destroy(VacationSpot $vacationSpot)
    {
        try {
            $vacationSpot->delete();
            return response()->json(['message' => 'Vacation Spot has been deleted successfully']);
        } catch (QueryException $e) {
            Log::error('Deleting vacation spot failed: ' . $e->getMessage());
            return response()->json(['error' => 'Database error, please try again'], 500);
        }
    }
}
