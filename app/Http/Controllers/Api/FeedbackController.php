<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\FeedbackAdminNotificationMail;
use App\Mail\FeedbackThankYouMail;
use App\Models\Feedback;
use App\Services\TelegramNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class FeedbackController extends Controller
{
    public function store(Request $request, TelegramNotifier $telegram)
    {
        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:100'],
            'contact' => ['required', 'string', 'max:150'],
            'subject' => ['required', 'string', 'in:' . implode(',', array_keys(Feedback::SUBJECTS))],
            'message' => ['required', 'string', 'max:2000'],
        ], [
            'name.required'    => 'Укажите имя',
            'contact.required' => 'Укажите телефон или email',
            'subject.required' => 'Выберите тему обращения',
            'subject.in'       => 'Некорректная тема',
            'message.required' => 'Напишите сообщение',
            'message.max'      => 'Сообщение слишком длинное (до 2000 символов)',
        ]);

        // 1. Сохраняем обращение
        $feedback = Feedback::create([
            'name'       => $validated['name'],
            'contact'    => trim($validated['contact']),
            'subject'    => $validated['subject'],
            'message'    => $validated['message'],
            'status'     => Feedback::STATUS_NEW,
            'ip'         => $request->ip(),
            'user_agent' => substr($request->userAgent() ?? '', 0, 500),
        ]);

        // 2. Письмо пользователю — только если контакт это валидный email
        if (filter_var($feedback->contact, FILTER_VALIDATE_EMAIL)) {
            try {
                Mail::to($feedback->contact)->send(new FeedbackThankYouMail($feedback));
                Log::info('[FEEDBACK] ✓ Письмо благодарности отправлено', ['email' => $feedback->contact]);
            } catch (\Throwable $e) {
                Log::error('[FEEDBACK] Ошибка письма пользователю', ['error' => $e->getMessage()]);
            }
        }

        // 3. Письмо админу
        $adminEmail = config('services.mail.admin');
        if ($adminEmail) {
            try {
                Mail::to($adminEmail)->send(new FeedbackAdminNotificationMail($feedback));
                Log::info('[FEEDBACK] ✓ Уведомление админу отправлено', ['email' => $adminEmail]);
            } catch (\Throwable $e) {
                Log::error('[FEEDBACK] Ошибка письма админу', ['error' => $e->getMessage()]);
            }
        } else {
            Log::warning('[FEEDBACK] MAIL_ADMIN не настроен — письмо админу не отправлено');
        }

        // 4. Телеграм (если настроен)
        $telegram->sendFeedback($feedback);

        return response()->json([
            'message' => 'Сообщение отправлено! Мы свяжемся с вами в ближайшее время.',
            'id'      => $feedback->id,
        ], 201);
    }
}
