<?php

declare(strict_types = 1);

namespace QuantumTecnology\ControllerQraphQLExtension\Presenters;

use BackedEnum;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;
use Illuminate\Support\Str;
use QuantumTecnology\ControllerQraphQLExtension\Support\PaginateSupport;

final class GenericPresenter
{
    public function __construct(protected PaginateSupport $paginateSupport)
    {
    }

    public function transform(Model $model, array $options = []): array
    {
        $fields     = $this->parseFields($options['fields'] ?? '');
        $includes   = $this->getIncludes($model, $options['fields'] ?? '');
        $pagination = $this->extractPagination($options);

        $output = [];

        $selfFields = $fields['__self'] ?? array_keys($model->getAttributes());

        // Adiciona automaticamente os campos que começam com "can"
        foreach (get_object_vars($model) as $key => $value) {
            if (Str::startsWith($key, 'can_') && !in_array($key, $selfFields)) {
                $selfFields[] = $key;
            }
        }

        $groupedFields = $this->groupNestedFields($selfFields);

        foreach ($groupedFields as $field => $nested) {
            $value = data_get($model, $field);

            if (is_array($value) && true !== $nested) {
                $output[$field] = collect($value)->only($nested)->toArray();
            } else {
                $output[$field] = match (true) {
                    $value instanceof CarbonImmutable => $value->toDateTimeString(),
                    $value instanceof BackedEnum      => [
                        'type'  => 'enum',
                        'value' => $value->value,
                        'key'   => $value->name,
                    ],
                    default => $value
                };
            }
        }

        foreach ($includes as $key => $includeValue) {
            $segments = explode('.', is_int($key) ? $includeValue : $key);
            $this->handleIncludePath($model, $output, $fields, $pagination, $segments, []);
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
                    ->put('actions', $metaActions)
                    ->toArray();
            });
    }

    public function extractPagination(array $input): array
    {
        $pagination = [];

        foreach ($input as $key => $value) {
            if (preg_match('/^(page|perPage)([A-Z]\w*)$/', $key, $matches)) {
                $type     = lcfirst($matches[1]);
                $relation = lcfirst($matches[2]);

                if (!isset($pagination[$relation])) {
                    $pagination[$relation] = [];
                }

                $pagination[$relation][$type] = (int) $value;
            }
        }

        return $pagination;
    }

    public function getIncludes(Model $model, string $fields): array
    {
        $relationsFromFields = [];
        $includes            = [];

        $fieldsArray = array_filter(array_map('trim', explode(',', $fields)));

        foreach ($fieldsArray as $field) {
            if (str_contains($field, 'actions.')) {
                continue;
            }

            if (Str::contains($field, '.')) {
                $parts = explode('.', $field);
                $path  = '';

                foreach ($parts as $index => $part) {
                    $path = '' === $path ? $part : $path . '.' . $part;

                    if ($index < count($parts) - 1) {
                        $relationsFromFields[] = $path;
                    }
                }
            }
        }

        foreach (array_unique($relationsFromFields) as $relationPath) {
            $segments     = explode('.', $relationPath);
            $currentModel = $model;
            $isHasMany    = true;

            foreach ($segments as $segment) {
                $method = Str::camel($segment);

                if (!method_exists($currentModel, $method)) {
                    $isHasMany = false;

                    break;
                }

                $relation = $currentModel->$method();

                if (!($relation instanceof Relations\Relation)) {
                    $isHasMany = false;

                    break;
                }

                if (!$relation instanceof Relations\HasMany) {
                    $isHasMany = false;
                }

                $currentModel = $relation->getRelated();
            }

            if ($isHasMany) {
                $root = explode('.', $relationPath)[0];

                $includes[$root] = fn ($query) => $query->limit(5);
            } else {
                $includes[] = $relationPath;
            }
        }

        return $includes;
    }

    public function getWithCount(Model $model, $allIncludes): array
    {
        $withCount = [];

        foreach ($allIncludes as $key => $value) {
            $relation = is_int($key) ? $value : $key;

            if (method_exists($model, Str::camel($relation))) {
                $relationObject = $model->{Str::camel($relation)}();

                if ($relationObject instanceof Relations\HasMany) {
                    $withCount[] = $relation;
                }

            }
        }

        return $withCount;
    }

    private function handleIncludePath($model, &$output, $fields, $pagination, $segments, $pathSoFar)
    {
        $relation = array_shift($segments);
        $camelRel = Str::camel($relation);
        $fullPath = implode('.', [...$pathSoFar, $relation]);

        if (!method_exists($model, $camelRel)) {
            return null;
        }

        $relationObject = $model->$camelRel();

        if ($relationObject instanceof Relations\HasMany) {
            $related = $model->$camelRel;

            if (!$related) {
                return null;
            }

            $countAttribute = $relation . '_count';

            $output[$relation] = [
                'data' => $related->map(function ($item) use ($fields, $fullPath, $pagination) {
                    return $this->transform($item, [
                        'fields'     => $this->transformArrayToString($fields[$fullPath] ?? []),
                        'include'    => '',
                        'pagination' => $pagination,
                    ]);
                }),
                'meta' => [
                    'total' => $model->$countAttribute,
                    'limit' => $related->count(),
                ],
            ];

        } elseif ($relationObject instanceof Relations\BelongsTo) {
            $related = $model->$camelRel;

            if (!$related) {
                return null;
            }

            $output[$relation] = $this->transform($related, [
                'fields'     => $this->transformArrayToString($fields[$fullPath] ?? []),
                'include'    => '',
                'pagination' => $pagination,
            ]);
        }

        if (!empty($segments)) {
            if (isset($output[$relation]['data'])) {
                $output[$relation]['data'] = collect($output[$relation]['data'])->map(function ($item) use ($segments, $fields, $pagination, $pathSoFar, $relation) {
                    $this->handleIncludePath((object) $item, $item, $fields, $pagination, $segments, [...$pathSoFar, $relation]);

                    return $item;
                });
            } else {
                $this->handleIncludePath((object) $output[$relation], $output[$relation], $fields, $pagination, $segments, [...$pathSoFar, $relation]);
            }
        }

        return null;
    }

    private function parseFields(string $raw): array
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

    private function groupNestedFields(array $fields): array
    {
        $result = [];

        foreach ($fields as $field) {
            if (str_contains($field, '.')) {
                [$parent, $child]  = explode('.', $field, 2);
                $result[$parent][] = $child;
            } else {
                $result[$field] = true;
            }
        }

        return $result;
    }

    private function transformArrayToString(array | string | null $fields): string
    {
        if (blank($fields)) {
            return '';
        }

        if (is_array($fields)) {
            return implode(',', array_map('trim', $fields));
        }

        return $fields;
    }
}
