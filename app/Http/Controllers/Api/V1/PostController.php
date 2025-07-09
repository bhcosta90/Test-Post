<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostRequest;
use App\Http\Resources\GenericResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

final class PostController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $fields  = $request->input('fields', '');
        $include = $request->input('include', '');

        $fieldsArray  = array_filter(array_map('trim', explode(',', $fields)));
        $includeArray = array_filter(array_map('trim', explode(',', $include)));

        // Extrair relacionamentos aninhados de fields (ex: posts.comments.body -> posts, posts.comments)
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

        $query = Post::query();

        if (!empty($allIncludes)) {
            $query = $query->with($allIncludes);
        }

        // Paginação
        $page    = $request->input('page', 1);
        $perPage = $request->input('perPage', 1);

        $posts = $query->paginate($perPage, ['*'], 'page', $page);

        // Atualiza include na request para o GenericResource (caso precise)
        $request->merge([
            'include' => implode(',', $allIncludes),
        ]);

        return GenericResource::collection($posts);
    }

    public function store(PostRequest $request)
    {
        return Post::create($request->validated());
    }

    public function show(Post $post)
    {
        return $post;
    }

    public function update(PostRequest $request, Post $post)
    {
        $post->update($request->validated());

        return $post;
    }

    public function destroy(Post $post)
    {
        $post->delete();

        return response()->json();
    }
}
