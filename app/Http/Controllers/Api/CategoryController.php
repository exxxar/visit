<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        return Category::query()
            ->root()
            ->with(['children' => fn ($q) => $q->orderBy('sort')])
            ->orderBy('sort')
            ->get();
    }
}
