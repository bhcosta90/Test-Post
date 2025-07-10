<?php

declare(strict_types = 1);

namespace QuantumTecnology\ControllerQraphQLExtension\Support;

final class PaginateSupport
{
    public function calculatePerPage(?string $perPage): int
    {
        if (blank($perPage)) {
            $perPage = config('quantum-controller-graphql.per_page');
        }

        if ($perPage > config('quantum-controller-graphql.max_page')) {
            $perPage = config('quantum-controller-graphql.max_page');
            LogSupport::add(
                sprintf(
                    'Per page value %s exceeds maximum allowed %s, setting to maximum.',
                    $perPage,
                    config('quantum-controller-graphql.max_page')
                )
            );
        }

        return (int) ($perPage ?: 1);
    }
}
