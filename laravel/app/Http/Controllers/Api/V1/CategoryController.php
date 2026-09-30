<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::query()
            ->active()
            ->topLevel()
            ->with(['children' => fn ($q) => $q->active()])
            ->get();

        return CategoryResource::collection($categories);
    }
}
