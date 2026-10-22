<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AiChatService;
use Illuminate\Http\Request;

class AiChatController extends Controller
{
    public function chat(Request $request, AiChatService $aiService)
    {
        $data = $request->validate([
            'message' => 'required|string|max:1000',
            'history' => 'nullable|array',
        ]);

        $history = $data['history'] ?? [];
        // Ограничиваем историю последними 6 сообщениями, чтобы экономить токены
        $history = array_slice($history, -6);

        $result = $aiService->getRecommendation($data['message'], $history);

        return response()->json($result);
    }
}
