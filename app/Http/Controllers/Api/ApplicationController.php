<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreApplicationRequest;
use App\Models\Application;

class ApplicationController extends Controller
{
    public function store(StoreApplicationRequest $request)
    {
        $application = Application::create(
            $request->validated() + ['user_id' => $request->user()?->id]
        );

        return response()->json([
            'message' => 'Заявка отправлена. Мы свяжемся с вами после проверки.',
            'id'      => $application->id,
        ], 201);
    }
}
