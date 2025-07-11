<?php

declare(strict_types = 1);

namespace QuantumTecnology\ControllerQraphQLExtension\Support;

final class PaginateSupport
{
    public function calculatePerPage(?string $perPage, string $path): int
    {
        if (blank($perPage)) {
            $perPage = config('quantum-controller-graphql.per_page');
        }

        if ($perPage > config('quantum-controller-graphql.max_page')) {
            LogSupport::add(
                sprintf(
                    'Per page value %s exceeds maximum allowed %s, setting to maximum on the %s.',
                    $perPage,
                    config('quantum-controller-graphql.max_page'),
                    'per_page_' . str_replace('.', '_', $path)
                )
            );

            $perPage = config('quantum-controller-graphql.max_page');
        }

        return (int) ($perPage ?: 1);
    }

    public function extractPagination(array $input): array
    {
        $pagination = [];

        foreach ($input as $key => $value) {
            if (preg_match('/^(per_page|page)_(.+)$/', $key, $matches)) {
                [$type, $rawPath] = [$matches[1], $matches[2]];

                $relationPath = str_replace('_', '.', $rawPath);

                if (!isset($pagination[$relationPath])) {
                    $pagination[$relationPath] = [];
                }

                $pagination[$relationPath][$type] = (int) $value;
            }
        }

        return $pagination;
    }
}
