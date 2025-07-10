<?php

declare(strict_types = 1);

namespace App\Http\Resources;

use Illuminate\Database\Eloquent\Relations;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

final class GenericResource extends JsonResource
{
    protected array $onlyFields = [];

    public function toArray($request): array
    {
        $model    = $this->resource;
        $parsed   = $this->parseFields($request->input('fields', ''));
        $includes = explode(',', $request->input('include', ''));

        $requestedFields = $this->onlyFields['__self'] ?? $parsed['__self'] ?? [];

        $metaActions = [];

        foreach (get_class_methods($model) as $method) {
            if (str_starts_with($method, 'can_')) {
                $fieldName     = Str::snake($method);
                $metaActions[] = $fieldName;
            }
        }

        $selfFields = array_unique(array_merge($requestedFields, $metaActions));

        $groupedFields = $this->groupNestedFields($selfFields);

        $output = [];

        foreach ($groupedFields as $field => $nestedFields) {
            if (str_contains($field, '.')) {
                [$rootKey, $subKey] = explode('.', $field);

                $value = data_get($model, $field);

                if (!isset($output[$rootKey])) {
                    $output[$rootKey] = [];
                }

                $output[$rootKey][$subKey] = $value;

                continue;
            }

            if ($model->offsetExists($field) || $model->hasGetMutator($field) || method_exists($model, 'get' . Str::studly($field) . 'Attribute')) {
                $value = $model->$field;
            } else {
                $value = null;
            }

            if (is_array($value) && true !== $nestedFields) {
                $output[$field] = collect($value)->only($nestedFields)->toArray();
            } else {
                $output[$field] = $value;
            }
        }

        foreach ($includes as $includePath) {
            $segments = explode('.', $includePath);
            $this->handleIncludePath($model, $output, $parsed, $segments, []);
        }

        return collect($output)
            ->partition(fn ($value, $key) => str_starts_with($key, 'can_'))
            ->pipe(function ($partitions) {
                [$can, $rest] = $partitions;
                $metaActions  = $can->all();

                if (empty($metaActions)) {
                    return $rest->toArray();
                }

                return $rest
                    ->put('meta_actions', $metaActions)
                    ->toArray();
            });
    }

    protected function withOnlyFields(array $fields): self
    {
        $this->onlyFields = ['__self' => $fields];

        return $this;
    }

    protected function handleIncludePath($model, &$output, $parsed, $segments, $pathSoFar): ?array
    {
        $relation = array_shift($segments);
        $fullPath = implode('.', [...$pathSoFar, $relation]);
        $camelRel = Str::camel($relation);

        if (!method_exists($model, $camelRel)) {
            return null;
        }

        $relationObject = $model->$camelRel();
        $relationKey    = implode('', array_map('ucfirst', [...$pathSoFar, $relation]));
        $page           = request()->input("page$relationKey", 1);
        $perPage        = request()->input("perPage$relationKey", 5);

        match (true) {
            $relationObject instanceof Relations\HasMany => (
                function () use ($model, $camelRel, $perPage, $page, $parsed, $fullPath, &$output, $relation) {
                    $paginator = $model->$camelRel()->paginate($perPage, ['*'], 'page', $page);

                    $data = $paginator->getCollection()->map(function ($item) use ($parsed, $fullPath) {
                        $fieldsForChild = $parsed[$fullPath] ?? [];

                        $resource = new self($item);
                        $resource->withOnlyFields($fieldsForChild);

                        return $resource->toArray(request());
                    });

                    $output[$relation] = [
                        'data' => $data,
                        'meta' => [
                            'current_page' => $paginator->currentPage(),
                            'last_page'    => $paginator->lastPage(),
                            'total'        => $paginator->total(),
                            'per_page'     => $paginator->perPage(),
                        ],
                    ];
                }
            )(),
            $relationObject instanceof Relations\BelongsTo => (
                function () use ($model, $camelRel, $parsed, $fullPath, &$output, $relation) {
                    $related = $model->$camelRel;

                    if (!$related) {
                        return;
                    }

                    $fieldsForChild = $parsed[$fullPath] ?? [];

                    $resource = new self($related);
                    $resource->withOnlyFields($fieldsForChild);

                    $output[$relation] = $resource->toArray(request());
                }
            )(),
            default => (
                function () use ($model, $camelRel, $parsed, $fullPath, &$output, $relation) {
                    $related = $model->$camelRel;

                    if (!$related) {
                        return;
                    }

                    $resource = new self($related);
                    $fields   = $parsed[$fullPath] ?? [];
                    $resource->withOnlyFields(['__self' => $fields]);

                    $output[$relation] = $resource->toArray(request());
                }
            )(),
        };

        if (!empty($segments)) {
            if (isset($output[$relation]['data'])) {
                $output[$relation]['data'] = collect($output[$relation]['data'])->map(function ($item) use ($segments, $parsed, $pathSoFar, $relation) {
                    $resourceArray = $item;
                    $this->handleIncludePath((object) $item, $resourceArray, $parsed, $segments, [...$pathSoFar, $relation]);

                    return $resourceArray;
                });
            } else {
                $this->handleIncludePath((object) $output[$relation], $output[$relation], $parsed, $segments, [...$pathSoFar, $relation]);
            }
        }

        return null;
    }

    protected function parseFields(string $raw): array
    {
        $result = [];

        foreach (explode(',', $raw) as $field) {
            if (!$field = mb_trim($field)) {
                continue;
            }

            if (str_contains($field, '.')) {
                [$relationPath, $nested] = explode('.', $field, 2);
                $result[$relationPath][] = $nested;
            } else {
                $result['__self'][] = $field;
            }
        }

        return $result;
    }

    protected function groupNestedFields(array $fields): array
    {
        $result = [];

        foreach ($fields as $field) {

            if (!is_string($field)) {
                // Se for array, podemos querer mesclar recursivamente, ou ignorar.
                // Aqui vamos ignorar para evitar erro.
                continue;
            }

            if (str_contains($field, '.')) {
                [$parent, $child]  = explode('.', $field, 2);
                $result[$parent][] = $child;
            } else {
                $result[$field] = true;
            }
        }

        return $result;
    }
}
