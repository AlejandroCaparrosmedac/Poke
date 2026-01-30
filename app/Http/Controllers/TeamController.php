<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TeamController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Get all teams for the authenticated user.
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();
        $teams = $user->teams()->with('battles.battle').paginate(20);

        return response()->json([
            'success' => true,
            'data' => $teams,
        ]);
    }

    /**
     * Create a new team.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'pokemon_data' => 'required|array|min:1|max:6',
            'pokemon_data.*' => 'array',
        ]);

        $user = auth()->user();

        $team = $user->teams()->create([
            'name' => $validated['name'],
            'pokemon_data' => $validated['pokemon_data'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Team created successfully',
            'data' => $team,
        ], 201);
    }

    /**
     * Get a specific team.
     */
    public function show(Team $team): JsonResponse
    {
        $this->authorize('view', $team);

        return response()->json([
            'success' => true,
            'data' => $team->load('user', 'battles'),
        ]);
    }

    /**
     * Update a team.
     */
    public function update(Request $request, Team $team): JsonResponse
    {
        $this->authorize('update', $team);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'pokemon_data' => 'sometimes|array|min:1|max:6',
        ]);

        $team->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Team updated successfully',
            'data' => $team,
        ]);
    }

    /**
     * Delete a team.
     */
    public function destroy(Team $team): JsonResponse
    {
        $this->authorize('delete', $team);

        $team->delete();

        return response()->json([
            'success' => true,
            'message' => 'Team deleted successfully',
        ]);
    }
}
