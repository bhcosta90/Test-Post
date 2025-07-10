<?php

declare(strict_types = 1);

namespace App\Http\Controllers;

use App\Http\Requests\PostRequest;
use App\Http\Resources\GenericResource;
use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

abstract class Controller
{
    abstract protected function model(): Model;

    final public function index(Request $request): AnonymousResourceCollection
    {
        $query = $this->queryModel($request);

        $page    = $request->input('page', 1);
        $perPage = $request->input('perPage', 1);

        $posts = $query->paginate($perPage, ['*'], 'page', $page);

        return GenericResource::collection($posts);
    }

    final public function store(PostRequest $request): GenericResource
    {
        return new GenericResource(Post::create($request->validated()));
    }

    final public function show(Request $request): GenericResource
    {
        return new GenericResource($this->findByOne($request));
    }

    final public function update(PostRequest $request, Post $post): GenericResource
    {
        return new GenericResource(tap($post)->update($request->validated()));
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
