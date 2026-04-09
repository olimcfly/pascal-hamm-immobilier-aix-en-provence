<?php

declare(strict_types=1);

final class BlueprintService
{
    public const VERSION = '1.0';

    public function compile(array $answersByStep): array
    {
        $identity = $answersByStep['identity'] ?? [];
        $target = $answersByStep['target'] ?? [];
        $offer = $answersByStep['offer'] ?? [];
        $territory = $answersByStep['territory'] ?? [];
        $goal = $answersByStep['goal'] ?? [];

        return [
            'version' => self::VERSION,
            'identity' => [
                'name' => $this->cleanText($identity['name'] ?? ''),
                'brand' => $this->cleanText($identity['brand'] ?? ''),
                'role' => $this->cleanText($identity['role'] ?? ''),
            ],
            'persona' => [
                'primary' => $this->cleanText($target['persona'] ?? ''),
                'pain' => $this->cleanText($target['pain'] ?? ''),
                'desire' => $this->cleanText($target['desire'] ?? ''),
            ],
            'offer' => [
                'type' => $this->cleanText($offer['type'] ?? ''),
                'promise' => $this->cleanText($offer['promise'] ?? ''),
                'differentiator' => $this->cleanText($offer['differentiator'] ?? ''),
                'timeline' => $this->cleanText($offer['timeline'] ?? ''),
            ],
            'territory' => [
                'city' => $this->cleanText($territory['city'] ?? ''),
                'districts' => $this->toList($territory['districts'] ?? ''),
                'radius_km' => $this->toPositiveInt($territory['radius_km'] ?? 0),
            ],
            'acquisition' => [
                'primary_goal' => $this->cleanText($goal['primary_goal'] ?? ''),
                'primary_channel' => $this->cleanText($goal['primary_channel'] ?? ''),
                'budget_monthly' => $this->toNullableFloat($goal['budget_monthly'] ?? null),
            ],
            'outputs' => $this->outputsFromSelection($goal['outputs'] ?? []),
        ];
    }

    private function cleanText(mixed $value): string
    {
        if (!is_scalar($value)) {
            return '';
        }

        return trim((string) $value);
    }

    private function toList(mixed $value): array
    {
        if (is_array($value)) {
            $items = $value;
        } else {
            $items = preg_split('/[,;\n]+/', (string) $value) ?: [];
        }

        return array_values(array_filter(array_map(static function ($item): string {
            return trim((string) $item);
        }, $items), static function ($item): bool {
            return $item !== '';
        }));
    }

    private function toPositiveInt(mixed $value): int
    {
        $number = (int) $value;
        return $number > 0 ? $number : 0;
    }

    private function toNullableFloat(mixed $value): ?float
    {
        if ($value === null) {
            return null;
        }

        $normalized = str_replace(',', '.', trim((string) $value));
        if ($normalized === '' || !is_numeric($normalized)) {
            return null;
        }

        $amount = (float) $normalized;
        return $amount >= 0 ? $amount : null;
    }

    private function outputsFromSelection(mixed $selected): array
    {
        $values = is_array($selected) ? $selected : [];
        $lookup = array_fill_keys(array_map(static fn ($item): string => (string) $item, $values), true);

        return [
            'site' => isset($lookup['site']),
            'funnel' => isset($lookup['funnel']),
            'seo' => isset($lookup['seo']),
            'content' => isset($lookup['content']),
            'crm' => isset($lookup['crm']),
        ];
    }
}
