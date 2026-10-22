<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\TenantController;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PlaceTenantController extends Controller
{
    public function store(Place $place, Request $request)
    {
        $validated = $request->validate([
            'slug' => ['required', 'string', 'max:255', 'alpha_dash'],
            'name' => ['required', 'string', 'max:255'],
            // Новые поля для миграции данных
            'description'       => ['nullable', 'string'],
            'short_description' => ['nullable', 'string'],
            'address'           => ['nullable', 'string'],
            'phone'             => ['nullable', 'string'],
            'site'              => ['nullable', 'url'],
            'lat'               => ['nullable', 'numeric'],
            'lng'               => ['nullable', 'numeric'],
            'working_hours'     => ['nullable', 'array'],
        ]);

        if ($place->external_id) {
            return response()->json([
                'success' => false,
                'message' => 'Тенант для этого заведения уже создан.',
            ], 409);
        }

        try {
            $apiController = app(TenantController::class);

            // Передаем все данные места в API-контроллер
            $apiRequest = Request::create('/api/tenants', 'POST', array_merge(
                [
                    'url_or_slug' => $validated['slug'],
                    'name'        => $validated['name'],
                ],
                // Передаем только заполненные поля, чтобы не засорять массив null-ами
                array_filter([
                    'description'       => $validated['description'] ?? null,
                    'short_description' => $validated['short_description'] ?? null,
                    'address'           => $validated['address'] ?? null,
                    'phone'             => $validated['phone'] ?? null,
                    'site'              => $validated['site'] ?? null,
                    'lat'               => $validated['lat'] ?? null,
                    'lng'               => $validated['lng'] ?? null,
                    'working_hours'     => $validated['working_hours'] ?? null,
                ], fn($value) => $value !== null)
            ));

            $response = $apiController->create($apiRequest);
            $responseData = $response->getData(true);

            if ($responseData['success']) {
                $place->update([
                    'external_source' => 'mypwa.ru',
                    'external_id'     => $responseData['tenant']['id'],
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Тенант создан, данные заведения перенесены.',
                    'admin'   => $responseData['admin'],
                    'logs'    => $responseData['logs'],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $responseData['message'] ?? 'Ошибка при создании тенанта.',
                'errors'  => $responseData['errors'] ?? [],
            ], $response->status());

        } catch (\Exception $e) {
            Log::error('Ошибка создания тенанта из админки места', [
                'place_id' => $place->id,
                'error'    => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Внутренняя ошибка сервера.',
            ], 500);
        }
    }
}
