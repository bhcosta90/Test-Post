<?php

declare(strict_types = 1);

namespace QuantumTecnology\ControllerQraphQLExtension\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use QuantumTecnology\ControllerQraphQLExtension\Support\LogSupport;

final class LogMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        match (true) {
            $response instanceof JsonResponse => (function () use (&$response) {
                $data = $response->getData(true);

                if ($messages = LogSupport::getMessages()) {
                    $data['quantum_log'] = $messages;
                }
                $response->setData($data);
            })(),
            default => Log::debug(json_encode(LogSupport::getMessages())),
        };

        return $response;
    }
}
