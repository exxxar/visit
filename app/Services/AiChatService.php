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
            // Убрали ->where('is_active', true), так как этой колонки нет.
            // Если у тебя есть поле статуса (например, 'approved'), раскомментируй строку ниже:
            // ->where('status', 'approved')

            $contextPlaces = Place::where(function ($q) use ($keywords) {
                foreach ($keywords as $word) {
                    $q->orWhere('name', 'like', "%{$word}%")
                        ->orWhere('short_description', 'like', "%{$word}%")
                        ->orWhereHas('category', fn($c) => $c->where('name', 'like', "%{$word}%"));
                }
            })
                ->with('category:id,name')
                ->limit(5) // Ограничиваем контекст, чтобы не переполнить токены
                ->get(['id', 'name', 'short_description', 'category_id'])
                ->map(fn($p) => [
                    'name' => $p->name,
                    'category' => $p->category?->name ?? 'Заведение',
                    'info' => $p->short_description ?: 'Описание отсутствует',
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
3. Отвечай кратко, структурированно и с эмодзи.
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

        // 5. Запрос к DeepSeek
        try {
            $response = Http::withToken(config('services.deepseek.api_key'))
                ->timeout(15)
                ->post(config('services.deepseek.api_url'), [
                    'model' => config('services.deepseek.model'),
                    'messages' => $messages,
                    'temperature' => 0.7,
                    'max_tokens' => 500,
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
