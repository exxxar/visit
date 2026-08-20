<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreLeadRequest;
use App\Models\Lead;

class LeadController extends Controller
{
    public function store(StoreLeadRequest $request)
    {
        Lead::create($request->validated());

        return response()->json([
            'message'  => 'Презентация готова',
            'download' => route('presentation.download'), // маршрут отдачи PDF
        ], 201);
    }
}
