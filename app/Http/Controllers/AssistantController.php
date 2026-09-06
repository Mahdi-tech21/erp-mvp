<?php

namespace App\Http\Controllers;

use App\Models\AssistantConversation;
use App\Services\Assistant\AssistantManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssistantController extends Controller
{
    public function history(Request $request): JsonResponse
    {
        $conversation = $this->conversation($request);

        return response()->json([
            'messages' => $conversation
                ->messages()
                ->orderBy('id')
                ->get(['role', 'content']),
        ]);
    }

    public function message(Request $request, AssistantManager $assistant): StreamedResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'context' => ['nullable', 'string', 'max:120'],
        ]);

        $conversation = $this->conversation($request);

        $todayCount = $conversation->messages()
            ->where('role', 'user')
            ->where('created_at', '>=', now()->startOfDay())
            ->count();

        if ($todayCount >= config('assistant.daily_message_limit')) {
            abort(429, 'Daily question limit reached. Try again tomorrow.');
        }

        $conversation->messages()->create(['role' => 'user', 'content' => $data['message']]);

        $history = $conversation->messages()
            ->orderByDesc('id')
            ->take(config('assistant.history_turns') * 2)
            ->get(['role', 'content'])
            ->reverse()
            ->map(fn ($m) => ['role' => $m->role, 'content' => $m->content])
            ->values()
            ->all();

        $driver = $assistant->driver();

        return response()->stream(function () use ($driver, $history, $data, $conversation) {
            $full = '';

            try {
                foreach ($driver->reply($history, $data['context'] ?? null) as $chunk) {
                    $full .= $chunk;
                    echo $chunk;
                    $this->flush();
                }
            } catch (\Throwable $e) {
                $full = 'Sorry, the assistant is unavailable right now.';
                echo $full;
                report($e);
            }

            $conversation->messages()->create(['role' => 'assistant', 'content' => $full]);
        }, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    private function conversation(Request $request): AssistantConversation
    {
        return $request->user()->assistantConversations()->latest('id')->first()
            ?? $request->user()->assistantConversations()->create();
    }

    private function flush(): void
    {
        if (ob_get_level() > 0) {
            @ob_flush();
        }

        flush();
    }
}
