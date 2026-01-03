<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetTeamContext
{
    /**
     * Handle an incoming request.
     *
     * This middleware sets the global team context for Spatie permissions.
     * It prioritizes the team ID from the route parameters (RESTful context)
     * and falls back to the user's current_team_id for general requests.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $teamId = null;

        /**
         * 1. Route Parameter Check
         * If the URL contains a {team} parameter (e.g., /teams/5/members),
         * we extract the ID to ensure the authorization check happens against the correct team.
         */
        $team = $request->route('team');

        if ($team instanceof \App\Models\Team) {
            $teamId = $team->id;
        } elseif (is_numeric($team)) {
            $teamId = $team;
        }

        /**
         * 2. User Preference Fallback
         * If no team is explicitly defined in the route, we fall back to
         * the user's active team session/preference.
         */
        if (!$teamId && Auth::check()) {
            $teamId = Auth::user()->current_team_id;
        }

        /**
         * 3. Apply Context
         * If a team context is identified, we set the global ID for Spatie's multi-team
         * feature and merge the team object into the request for easy access.
         */
        if ($teamId) {
            setPermissionsTeamId($teamId);

            if (Auth::check() && !$request->has('current_team')) {
                // Ensure the current_team object is available for Inertia/Blade views
                $request->merge(['current_team' => Auth::user()->currentTeam]);
            }
        }

        return $next($request);
    }
}
