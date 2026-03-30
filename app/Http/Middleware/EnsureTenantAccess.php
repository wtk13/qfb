<?php

namespace App\Http\Middleware;

use Closure;
use Domain\Shared\ValueObject\TenantId;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->tenant_id) {
            abort(403, 'No tenant access.');
        }

        $request->attributes->set('tenant_id', new TenantId($user->tenant_id));

        return $next($request);
    }
}
