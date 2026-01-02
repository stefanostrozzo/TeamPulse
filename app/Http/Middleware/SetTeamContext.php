<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetTeamContext
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->current_team_id) {
            $teamId = Auth::user()->current_team_id;

            setPermissionsTeamId($teamId);
            $request->merge(['current_team' => Auth::user()->currentTeam]);
        }

        return $next($request);
    }
}
