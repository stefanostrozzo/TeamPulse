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
     * */
    public function handle(Request $request, Closure $next): Response
    {
        \Log::info('Middleware SetTeamContext eseguito per il team: ' . $request->route('team'));
        $teamId = null;

        //Retrieves the team from the route
        $teamParam = $request->route('team');

        //Manage all cases for Team
        if ($teamParam instanceof \App\Models\Team) {
            $teamId = $teamParam->id;
        } elseif (is_numeric($teamParam) || is_string($teamParam)) {
            $teamId = $teamParam;
        }

        //Fallback
        if (!$teamId && Auth::check()) {
            $teamId = Auth::user()->current_team_id;
        }

        if ($teamId) {
            //Set global context for Spatie
            setPermissionsTeamId($teamId);

            if (Auth::check() && !$request->has('current_team')) {
                $request->merge(['current_team' => Auth::user()->currentTeam]);
            }
        }

        return $next($request);
    }
}
