<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TenantCreationController extends Controller
{
    /**
     * Принимает данные от фронтенда и обращается к внутреннему API
     */
    public function store(Request $request)
    {
        // 1. Валидация данных от фронтенда
        $validated = $request->validate([
            'url_or_slug' => ['required', 'string', 'max:255'],
            'name'        => ['nullable', 'string', 'max:255'],
        ]);

        // 2. Формируем URL внутреннего API
        // (используем url() для получения абсолютного пути, например http://localhost/api/tenants)
        $apiUrl = url('/api/tenants');

        try {
            // 3. Делаем HTTP-запрос к нашему API
            // Если API защищен (например, Sanctum), добавьте ->withToken('your-api-token')
            $response = Http::post($apiUrl, $validated);

            // 4. Если запрос успешен (статус 2xx)
            if ($response->successful()) {
                return response()->json($response->json());
            }

            // 5. Если API вернул ошибку (4xx или 5xx), логируем её и возвращаем фронтенду
            Log::warning('Ошибка при создании тенанта через API', [
                'status' => $response->status(),
                'body'   => $response->body(),
                'input'  => $validated,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Не удалось создать тенант. Проверьте корректность данных.',
                'errors'  => $response->json('errors', []), // Пробрасываем ошибки валидации из API
                'logs'    => $response->json('logs', []),   // Пробрасываем логи для отладки
            ], $response->status());

        } catch (\Exception $e) {
            // 6. Обработка сетевых ошибок (например, API вообще недоступен)
            Log::error('Сетевая ошибка при обращении к API тенантов', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Внутренняя ошибка сервера при создании тенанта.',
            ], 500);
        }
    }
}
