<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostImageUploadRequest;
use App\Services\Contracts\PostContentImageServiceContract;
use Illuminate\Http\JsonResponse;

/**
 * HTTP-контроллер загрузки изображений контента поста (TinyMCE).
 *
 * @property-read PostContentImageServiceContract $images
 */
class PostImageUploadController extends Controller
{
    public function __construct(protected PostContentImageServiceContract $images) {}

    /**
     * store.

     *
     * @return JsonResponse
     */
    public function store(PostImageUploadRequest $request): JsonResponse
    {
        $location = $this->images->storeAndGetUrl($request->file('file'));

        return response()->json(['location' => $location]);
    }
}
