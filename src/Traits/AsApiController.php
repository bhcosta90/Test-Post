<?php

declare(strict_types = 1);

namespace QuantumTecnology\ControllerQraphQLExtension\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use QuantumTecnology\ControllerQraphQLExtension\Presenters\GenericPresenter;
use QuantumTecnology\ControllerQraphQLExtension\Resources\GenericResource;
use QuantumTecnology\ControllerQraphQLExtension\Support\PaginateSupport;

trait AsApiController
{
    abstract protected function model(): Model;

    final public function index(Request $request, PaginateSupport $paginateSupport): AnonymousResourceCollection
    {
        $query = $this->queryModel($request);

        $page    = $request->input('page', 1);
        $perPage = $paginateSupport->calculatePerPage($request->input('perPage'));

        $models = $query->paginate($perPage, ['*'], 'page', $page);

        return GenericResource::collection($models);
    }

    final public function store(): GenericResource
    {
        $request = app($this->getNamespaceRequest('update'));

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

        return $this->queryModel($request)->where($id, end($routeParams))->sole();
    }

    protected function queryModel(Request $request): Builder
    {
        $query = $this->model()->query();

        $genericPresenter = app(GenericPresenter::class);

        if (!empty($allIncludes = $genericPresenter->getIncludes($request->input('fields', '')))) {
            $query = $query->with($allIncludes);
        }

        $request->merge([
            'include' => implode(',', $allIncludes),
        ]);

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
}
