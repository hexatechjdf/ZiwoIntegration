<?php

namespace App\Http\Middleware;

use App\Helper\Dropshipzone;
use App\Models\DropshipzoneToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Auth;
use Carbon\Carbon;
class LocationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $user = loginUser();
        if (Auth::check() && loginUser()->role == 2)
        {

            Dropshipzone::ensureValidDropshipzoneToken($user,false);
            return $next($request);
        } else {
            return redirect()->route("login");
        }
    }

}
