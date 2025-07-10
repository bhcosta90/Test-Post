<?php

declare(strict_types = 1);

namespace QuantumTecnology\ControllerQraphQLExtension\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use QuantumTecnology\ControllerQraphQLExtension\Support\LogSupport;

final class LogMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($response instanceof JsonResponse) {
            $data = $response->getData(true);

            if ($messages = LogSupport::getMessages()) {
                $data['quantum_log'] = $messages;
            }

            $response->setData($data);
        }

        return $response;
    }
}
