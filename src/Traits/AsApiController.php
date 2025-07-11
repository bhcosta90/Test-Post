<?php

declare(strict_types = 1);

namespace QuantumTecnology\ControllerQraphQLExtension\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use QuantumTecnology\ControllerQraphQLExtension\QueryBuilder\GenerateQuery;
use QuantumTecnology\ControllerQraphQLExtension\Resources\GenericResource;
use QuantumTecnology\ControllerQraphQLExtension\Support\PaginateSupport;

trait AsApiController
{
    abstract protected function model(): Model;

    final public function index(Request $request, PaginateSupport $paginateSupport): AnonymousResourceCollection
    {
        $query = $this->queryModel($request, __FUNCTION__);

        $page    = $request->input('page', 1);
        $perPage = $paginateSupport->calculatePerPage($request->input('per_page'), 'father');

        $models = $query->paginate($perPage, ['*'], 'page', $page);

        return GenericResource::collection($models);
    }

    final public function store(): GenericResource
    {
        $request = app($this->getNamespaceRequest('store'));

        abort_unless($request->authorize(), 403, 'This action is unauthorized.');

        return new GenericResource($this->model()->create($request->validated()));
    }

    final public function show(Request $request): GenericResource
    {
        return new GenericResource($this->findByOne($request));
    }

    final public function update(): GenericResource
    {
        $request = app($this->getNamespaceRequest('update'));
        $model   = $this->findByOne($request);

        abort_unless($request->authorize($model), 403, 'This action is unauthorized.');

        return new GenericResource(tap($model)->update($request->validated()));
    }

    final public function destroy(Request $request): JsonResponse
    {
        $this->findByOne($request)->delete();

        return response()->json();
    }

    protected function findByOne(Request $request): Model
    {
        $routeParams = $request->route()?->parameters() ?: [];

        $id = $this->model()->getKeyName();

        return $this->queryModel($request, __FUNCTION__)->where($id, end($routeParams))->sole();
    }

    protected function queryModel(Request $request, string $action): Builder
    {
        $fields = $request->input('fields', '');

        dd($this->extractFilter($request->all()));
        $query = app(GenerateQuery::class, [
            'model'         => $this->model(),
            'classCallable' => $this,
            'action'        => $action,
        ])->execute(
            fields: $fields,
            pagination: app(PaginateSupport::class)->extractPagination($request->all()),
            filters: $this->extractFilter($request->all()),
        );

        if (config('app.debug')) {
            match (true) {
                request()->has('dd')       => $query->dd(),
                request()->has('dump')     => $query->dump(),
                request()->has('dd_raw')   => $query->ddRawSql(),
                request()->has('dump_raw') => $query->dumpRawSql(),
                default                    => false,
            };
        }

        return $query;
    }

    protected function getNamespaceRequest(?string $action = null): string
    {
        $value = str_replace(['Controller', 'App\\Http\\Controllers\\'],
            ['Request', 'App\\Http\\Requests\\'],
            static::class);

        if (blank($action)) {
            return $value;
        }

        $value = mb_substr($value, 0, -7) . '\\' . ucfirst($action) . 'Request';

        if (class_exists($value)) {
            return $value;
        }

        return self::getNamespaceRequest();
    }

    protected function extractFilter(array $input): array
    {
        $filters = [];

        foreach ($input as $key => $value) {
            if (preg_match('/^scope_(.+?)(?:\[(.+)\])?$/', $key, $matches)) {
                $rawPath  = $matches[1];
                $operator = $matches[2] ?? 'in';

                $segments     = explode('_', $rawPath);
                $field        = array_pop($segments);
                $relationPath = implode('_', $segments);

                // Caso o valor já seja array com operador como chave (ex: ['>=' => '2,2,5']),
                // vamos detectar e tratar corretamente
                if (is_array($value) && 1 === count($value)) {
                    // extrai a chave e o valor do array
                    $firstKey = array_key_first($value);
                    $firstVal = $value[$firstKey];

                    // se a chave do array for operador, usamos ela como operador real,
                    // e o valor como valor para processar
                    if (preg_match('/^[=!<>]+$/', $firstKey) || 'in' === $firstKey || 'like' === $firstKey) {
                        $operator = $firstKey;
                        $value    = $firstVal;
                    }
                }

                // Normaliza valor para array, separando por ',' ou '|'
                if (is_array($value)) {
                    $parsedValues = $value;
                } elseif (is_string($value)) {
                    $parsedValues = preg_split('/[,\|]/', $value);
                } else {
                    $parsedValues = [$value];
                }

                // Limpa e converte valores
                $parsedValues = array_map(function ($v) {
                    $v = mb_trim($v);

                    return is_numeric($v) ? (int) $v : $v;
                }, $parsedValues);

                $filters[$relationPath][$field] = [
                    $operator => $parsedValues,
                ];
            }
        }

        return $filters;

    }
}
