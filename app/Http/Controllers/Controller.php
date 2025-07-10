<?php

declare(strict_types = 1);

namespace App\Http\Controllers;

use App\Http\Presenters\GenericPresenter;
use App\Http\Requests\CommentRequest;
use App\Http\Resources\GenericResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

abstract class Controller
{
    abstract protected function model(): Model;

    final public function index(Request $request, GenericPresenter $genericPresenter): JsonResponse
    {
        $query = $this->queryModel($request);

        $page    = $request->input('page', 1);
        $perPage = $request->input('perPage', 1);

        $models = $query->paginate($perPage, ['*'], 'page', $page);

        // return GenericResource::collection($models);
        return response()->json([
            'data' => $models->getCollection()->map(fn ($post) => $genericPresenter->transform($post, $request->all())),
            'meta' => [
                'current_page' => $models->currentPage(),
                'last_page'    => $models->lastPage(),
                'total'        => $models->total(),
                'per_page'     => $models->perPage(),
            ],
            'links' => [
                'first' => $models->url(1),
                'last'  => $models->url($models->lastPage()),
                'prev'  => $models->previousPageUrl(),
                'next'  => $models->nextPageUrl(),
            ],
        ]);
    }

    final public function store(CommentRequest $request): GenericResource
    {
        return new GenericResource($this->model()->create($request->validated()));
    }

    final public function show(Request $request): GenericResource
    {
        return new GenericResource($this->findByOne($request));
    }

    final public function update(CommentRequest $request): GenericResource
    {
        $model = $this->findByOne($request);

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
        $fields  = $request->input('fields', '');
        $include = $request->input('include', '');

        $fieldsArray  = array_filter(array_map('trim', explode(',', $fields)));
        $includeArray = array_filter(array_map('trim', explode(',', $include)));

        // Extract Nesty Relationships from Fields (ex: posts.comments.body -> posts, posts.comments)
        $relationsFromFields = [];

        foreach ($fieldsArray as $field) {
            if (str_contains($field, 'actions.')) {
                continue;
            }

            if (Str::contains($field, '.')) {
                $parts = explode('.', $field);
                $path  = '';

                foreach ($parts as $index => $part) {
                    $path = '' === $path ? $part : $path . '.' . $part;

                    // ignora o último que é o campo, pega só as relações
                    if ($index < count($parts) - 1) {
                        $relationsFromFields[] = $path;
                    }
                }
            }
        }

        $relationsFromFields = array_unique($relationsFromFields);

        // Une include original com os relacionamentos extraídos de fields, único
        $allIncludes = array_unique(array_merge($includeArray, $relationsFromFields));

        // Monta o with() para Eloquent
        // Aqui a ideia é passar os includes simples, como 'comments', 'posts.comments', etc.
        // O with() do Laravel aceita nested relationships com pontos.

        $query = $this->model()->query();

        if (!empty($allIncludes)) {
            $query = $query->with($allIncludes);
        }

        $request->merge([
            'include' => implode(',', $allIncludes),
        ]);

        return $query;
    }
}
