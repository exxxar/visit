<?php

namespace App\Services;

use App\Models\Place;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiChatService
{
    /**
     * Получает ответ от ИИ с контекстом из базы данных
     */
    public function getRecommendation(string $userMessage, array $chatHistory = []): array
    {
        // 1. Извлекаем ключевые слова (убираем знаки препинания, берем слова длиннее 3 букв)
        $keywords = collect(preg_split('/\s+/', $userMessage))
            ->map(fn($w) => mb_strtolower(preg_replace('/[^\p{L}\p{N}]/u', '', $w))) // Оставляем только буквы и цифры
            ->filter(fn($w) => mb_strlen($w) > 3)
            ->unique()
            ->take(3)
            ->toArray();

        // 2. Ищем релевантные заведения в БД (контекст)
        $contextPlaces = [];
        if (!empty($keywords)) {
            $contextPlaces = Place::where(function ($q) use ($keywords) {
                foreach ($keywords as $word) {
                    $q->orWhere('name', 'like', "%{$word}%")
                        ->orWhere('short_description', 'like', "%{$word}%")
                        ->orWhere('description', 'like', "%{$word}%")      // ✅ ДОБАВЛЕНО: полное описание
                        ->orWhere('address', 'like', "%{$word}%")         // ✅ ДОБАВЛЕНО: адрес
                        ->orWhereHas('category', fn($c) => $c->where('name', 'like', "%{$word}%"))
                        ->orWhereHas('district', fn($d) => $d->where('name', 'like', "%{$word}%")); // ✅ ДОБАВЛЕНО: район
                }
            })
                ->with(['category:id,name', 'district:id,name']) // ✅ ДОБАВЛЕНО: подгружаем район
                ->limit(5) // Ограничиваем контекст, чтобы не переполнить токены
                ->get(['id', 'name', 'short_description', 'description', 'address', 'category_id', 'district_id'])
                ->map(fn($p) => [
                    'name' => $p->name,
                    'category' => $p->category?->name ?? 'Заведение',
                    'district' => $p->district?->name ?? 'Не указан',
                    'address' => $p->address ?? 'Адрес не указан',
                    'description' => $p->short_description ?: ($p->description ?: 'Описание отсутствует'),
                ])
                ->toArray();
        }

        // 3. Формируем системный промпт
        $contextJson = json_encode($contextPlaces, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $systemPrompt = <<<EOT
Ты — дружелюбный и полезный ИИ-помощник городского путеводителя.
Твоя задача — рекомендовать пользователю заведения ТОЛЬКО на основе предоставленного ниже контекста.

ПРАВИЛА:
1. Используй ТОЛЬКО информацию из блока [КОНТЕКСТ ЗАВЕДЕНИЙ]. Не выдумывай места.
2. Если в контексте нет подходящих мест, честно скажи: "К сожалению, в моей базе пока нет точных совпадений по вашему запросу. Попробуйте уточнить категорию или название".
3. Отвечай кратко, структурированно и с эмодзи. Обязательно упоминай район и адрес заведения, если эта информация есть в контексте.
4. Если пользователь спрашивает что-то не по теме города/заведений или использует нецензурную лексику, вежливо верни его к теме путеводителя.

[КОНТЕКСТ ЗАВЕДЕНИЙ]
{$contextJson}
[КОНЕЦ КОНТЕКСТА]
EOT;

        // 4. Формируем сообщения для API (OpenAI/DeepSeek формат)
        $messages = array_merge(
            [['role' => 'system', 'content' => $systemPrompt]],
            $chatHistory,
            [['role' => 'user', 'content' => $userMessage]]
        );

        // 5. Запрос к DeepSeek (или другому провайдеру)
        try {
            $response = Http::withToken(config('services.deepseek.api_key'))
                ->timeout(15)
                ->post(config('services.deepseek.api_url'), [
                    'model' => config('services.deepseek.model'),
                    'messages' => $messages,
                    'temperature' => 0.7,
                    'max_tokens' => 600, // Немного увеличил лимит для более полных ответов с адресами
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'message' => $data['choices'][0]['message']['content'],
                    'used_context' => count($contextPlaces) > 0,
                ];
            }

            Log::error('DeepSeek API Error', ['response' => $response->body()]);
            return ['success' => false, 'message' => 'Извините, сейчас я немного перегружен. Попробуйте позже или напишите нам в форму обратной связи.'];

        } catch (\Exception $e) {
            Log::error('DeepSeek Request Failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Ошибка соединения с ИИ. Пожалуйста, используйте форму обратной связи.'];
        }
    }
}
