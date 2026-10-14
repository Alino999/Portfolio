<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class CvTranslationService
{
    public function translate(array $parsed): array
    {
        $texts = [];
        $targets = [];
        $queue = function (string $path, mixed $value) use (&$texts, &$targets): void {
            if (! is_string($value) || trim($value) === '') {
                return;
            }

            $targets[] = $path;
            $texts[] = $value;
        };

        $queue('title_en', $parsed['title'] ?? null);
        $queue('summary_en', $parsed['summary'] ?? null);

        foreach (($parsed['experiences'] ?? []) as $index => $experience) {
            $queue("experiences.{$index}.position_en", $experience['position'] ?? null);
            $queue("experiences.{$index}.description_en", $experience['description'] ?? null);
            foreach (($experience['responsibilities'] ?? []) as $itemIndex => $responsibility) {
                $queue("experiences.{$index}.responsibilities_en.{$itemIndex}", $responsibility);
            }
            foreach (($experience['achievements'] ?? []) as $itemIndex => $achievement) {
                $queue("experiences.{$index}.achievements_en.{$itemIndex}", $achievement);
            }
        }

        foreach (($parsed['educations'] ?? []) as $index => $education) {
            $queue("educations.{$index}.degree_en", $education['degree'] ?? null);
            $queue("educations.{$index}.field_en", $education['field'] ?? null);
            $queue("educations.{$index}.description_en", $education['description'] ?? null);
        }

        foreach (($parsed['skills'] ?? []) as $index => $skill) {
            $queue("skills_en.{$index}", $skill);
        }

        foreach (($parsed['certifications'] ?? []) as $index => $certification) {
            $queue("certifications.{$index}.title_en", $certification['title'] ?? null);
            $queue("certifications.{$index}.description_en", $certification['description'] ?? null);
        }

        foreach (($parsed['languages'] ?? []) as $index => $language) {
            $queue("languages.{$index}.name_en", $language['name'] ?? null);
            $queue("languages.{$index}.level_en", $language['level'] ?? null);
        }

        foreach (($parsed['references'] ?? []) as $index => $reference) {
            $queue("references.{$index}.role_en", $reference['role'] ?? null);
        }

        foreach (($parsed['projects'] ?? []) as $index => $project) {
            $queue("projects.{$index}.title_en", $project['title'] ?? null);
            $queue("projects.{$index}.description_en", $project['description'] ?? null);
        }

        foreach (($parsed['achievements'] ?? []) as $index => $achievement) {
            $queue("achievements.{$index}.title_en", $achievement['title'] ?? null);
            $queue("achievements.{$index}.description_en", $achievement['description'] ?? null);
        }

        if ($texts === []) {
            return $parsed;
        }

        $apiKey = (string) config('services.deepl.api_key', '');
        if (trim($apiKey) === '') {
            throw new RuntimeException('La traduction anglaise nécessite DEEPL_API_KEY dans le fichier .env. Aucune donnée du CV n’a été enregistrée.');
        }

        $translated = [];
        foreach (array_chunk($texts, 50) as $chunk) {
            try {
                $response = Http::withHeaders(['Authorization' => 'DeepL-Auth-Key '.$apiKey])
                    ->timeout(20)
                    ->post((string) config('services.deepl.url'), [
                        'text' => $chunk,
                        'source_lang' => 'FR',
                        'target_lang' => 'EN-US',
                        'preserve_formatting' => true,
                    ]);
            } catch (Throwable) {
                throw new RuntimeException('Le service DeepL est inaccessible. Vérifiez votre connexion et réessayez. Aucune donnée du CV n’a été enregistrée.');
            }

            $translations = $response->json('translations');
            if (! $response->successful() || ! is_array($translations) || count($translations) !== count($chunk)) {
                throw new RuntimeException('DeepL n’a pas pu traduire le CV. Vérifiez la clé API et réessayez. Aucune donnée du CV n’a été enregistrée.');
            }

            foreach ($translations as $translation) {
                if (! is_string($translation['text'] ?? null) || trim($translation['text']) === '') {
                    throw new RuntimeException('DeepL a renvoyé une traduction incomplète. Aucune donnée du CV n’a été enregistrée.');
                }
                $translated[] = $translation['text'];
            }
        }

        foreach ($targets as $index => $target) {
            Arr::set($parsed, $target, $translated[$index]);
        }

        return $parsed;
    }
}