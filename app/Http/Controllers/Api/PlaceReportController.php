<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlaceReport; // Создай эту модель
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PlaceReportController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'place_id' => 'required|exists:places,id',
            'issue_type' => 'required|string',
            'description' => 'nullable|string|max:1000',
            'screenshots.*' => 'nullable|image|max:5120', // до 5MB
            'audio_recording' => 'nullable|file|mimes:webm,mp3,wav|max:10240', // до 10MB
        ]);

        $report = PlaceReport::create([
            'place_id' => $data['place_id'],
            'user_id' => auth()->id(),
            'issue_type' => $data['issue_type'],
            'description' => $data['description'],
        ]);

        // Сохранение скриншотов
        if ($request->hasFile('screenshots')) {
            foreach ($request->file('screenshots') as $file) {
                $path = $file->store('reports/screenshots', 'public');
                $report->attachments()->create(['path' => $path, 'type' => 'image']);
            }
        }

        // Сохранение голосового
        if ($request->hasFile('audio_recording')) {
            $path = $request->file('audio_recording')->store('reports/audio', 'public');
            $report->attachments()->create(['path' => $path, 'type' => 'audio']);
        }

        return response()->json(['message' => 'Сообщение успешно отправлено']);
    }
}
