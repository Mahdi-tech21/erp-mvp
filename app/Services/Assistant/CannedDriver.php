<?php

namespace App\Services\Assistant;

/**
 * Answers from a fixed knowledge base - no API, no cost. Matches the question
 * against each topic's keywords and canonical question, then streams the
 * written answer word by word so it reads like a live assistant.
 */
class CannedDriver implements AssistantDriver
{
    /**
     * @param  list<array{question: string, keywords: list<string>, answer: string}>  $topics
     */
    public function __construct(private array $topics) {}

    public function reply(array $history, ?string $context = null): iterable
    {
        $question = strtolower(trim((string) (end($history)['content'] ?? '')));

        $answer = $this->match($question)['answer'] ?? $this->fallback();

        $typing = ! app()->runningUnitTests();

        foreach (preg_split('/(\s+)/', $answer, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [$answer] as $chunk) {
            yield $chunk;

            if ($typing) {
                usleep(12_000);
            }
        }
    }

    /**
     * @return array{question: string, keywords: list<string>, answer: string}|null
     */
    private function match(string $question): ?array
    {
        $best = null;
        $bestScore = 0.0;

        foreach ($this->topics as $topic) {
            $score = 0.0;

            foreach ($topic['keywords'] as $keyword) {
                if (str_contains($question, $keyword)) {
                    $score += 2 + substr_count($keyword, ' ');
                }
            }

            similar_text($question, strtolower($topic['question']), $percent);
            $score += $percent / 25;

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $topic;
            }
        }

        return $bestScore >= 3.0 ? $best : null;
    }

    private function fallback(): string
    {
        $lines = array_map(fn ($t) => '- '.$t['question'], $this->topics);

        return "I'm not sure about that one. I can help with:\n\n".implode("\n", $lines)
            ."\n\nFor anything about your own figures, account or a bug, please contact support.";
    }
}
