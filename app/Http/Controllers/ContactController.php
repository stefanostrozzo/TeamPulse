<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * Search users for the messaging feature.
     * Returns cross-team results with team names inline.
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q');
        
        if (strlen($query) < 2 && $query !== ' ') {
            return response()->json(['users' => []]);
        }

        $usersQuery = User::with(['teams:id,name'])
            ->where('id', '!=', $request->user()->id);

        if (trim($query) !== '') {
            $usersQuery->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            });
        }

        $users = $usersQuery->limit(20)
            ->get()
            ->map(function ($user) {
                // Formatting for display: "John Doe (Developers)"
                $teamNames = $user->teams->pluck('name')->join(', ');
                $displayName = $user->name;
                if ($teamNames) {
                    $displayName .= " ({$teamNames})";
                }

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'display_name' => $displayName,
                ];
            });

        return response()->json(['users' => $users]);
    }
}
