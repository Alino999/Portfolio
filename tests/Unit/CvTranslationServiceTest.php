<?php

namespace Tests\Unit;

use App\Services\CvTranslationService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class CvTranslationServiceTest extends TestCase
{
    public function test_translates_supported_cv_fields_without_changing_the_french_source(): void
    {
        config([
            'services.deepl.api_key' => 'test-key',
            'services.deepl.url' => 'https://api-free.deepl.com/v2/translate',
        ]);
        Http::fake(fn (Request $request) => Http::response([
            'translations' => array_map(fn (string $text) => ['text' => 'EN: '.$text], $request->data()['text']),
        ]));

        $parsed = app(CvTranslationService::class)->translate([
            'title' => 'Consultant informatique',
            'summary' => 'Passionné de cybersécurité.',
            'experiences' => [[
                'company' => 'Société X',
                'position' => 'Consultant',
                'description' => 'Formation des utilisateurs.',
            ]],
            'educations' => [['school' => 'Université Aube Nouvelle', 'degree' => 'Master informatique']],
            'skills' => ['Maintenance informatique'],
            'languages' => [['name' => 'Français', 'level' => 'Courant']],
            'references' => [['name' => 'Louis Dupont', 'role' => 'Développeur', 'company' => 'Société Y']],
        ]);

        $this->assertSame('Consultant informatique', $parsed['title']);
        $this->assertSame('EN: Consultant informatique', $parsed['title_en']);
        $this->assertSame('EN: Passionné de cybersécurité.', $parsed['summary_en']);
        $this->assertSame('Société X', $parsed['experiences'][0]['company']);
        $this->assertSame('EN: Consultant', $parsed['experiences'][0]['position_en']);
        $this->assertSame('EN: Master informatique', $parsed['educations'][0]['degree_en']);
        $this->assertSame('EN: Maintenance informatique', $parsed['skills_en'][0]);
        $this->assertSame('EN: Français', $parsed['languages'][0]['name_en']);
        $this->assertSame('EN: Développeur', $parsed['references'][0]['role_en']);

        Http::assertSent(fn (Request $request) => $request->hasHeader('Authorization', 'DeepL-Auth-Key test-key')
            && $request['target_lang'] === 'EN-US');
    }

    public function test_refuses_to_translate_when_the_api_key_is_missing(): void
    {
        config(['services.deepl.api_key' => null]);

        $this->expectException(RuntimeException::class);
        app(CvTranslationService::class)->translate(['title' => 'Consultant']);
    }

    public function test_rejects_a_failed_deepl_response(): void
    {
        config([
            'services.deepl.api_key' => 'test-key',
            'services.deepl.url' => 'https://api-free.deepl.com/v2/translate',
        ]);
        Http::fake(fn () => Http::response([], 403));

        $this->expectException(RuntimeException::class);
        app(CvTranslationService::class)->translate(['title' => 'Consultant']);
    }
}