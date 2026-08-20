<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\District;

class DistrictController extends Controller
{
    public function index()
    {
        return District::query()
            ->orderBy('sort')
            ->get(['id', 'name', 'slug', 'description', 'image', 'stats']);
    }
}
