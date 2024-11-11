<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EmailVerifiedMiddleware
{
  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure  $next
   * @return mixed
   */
  public function handle(Request $request, Closure $next)
  {
    if (!$request->user() || !$request->user()->hasVerifiedEmail()) {
      return redirect()->route('verification.notice');
    }

    return $next($request);
  }
}
