<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\Certification;
use App\Models\Education;
use App\Models\Experience;
use App\Models\Language;
use App\Models\ProfessionalReference;
use App\Models\Skill;
use App\Models\SocialLink;
use App\Models\Technology;
use App\Services\CvImporter;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use Tests\TestCase;

class CvImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.deepl.api_key' => 'test-key',
            'services.deepl.url' => 'https://api-free.deepl.com/v2/translate',
        ]);
        Http::fake(fn (HttpRequest $request) => Http::response([
            'translations' => array_map(fn (string $text) => ['text' => 'EN: '.$text], $request->data()['text']),
        ]));
    }

    public function test_admin_can_import_cv_text_into_profile_and_related_models(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $profile = Profile::query()->create([
            'user_id' => $user->id,
            'title' => 'Ancien titre',
            'summary' => 'Ancien résumé',
            'email' => 'old@example.com',
        ]);

        $this->actingAs($user);

        $response = $this->post(route('admin.profile.import'), [
            'cv_text' => "Jean Dupont\nConsultant IT & Cybersécurité\njean.dupont@example.com\n+226 70 12 34 56\n\nEXPERIENCE\nSociété X | Consultant IT\n2022 - Aujourd'hui\n- Support utilisateur\n- Gestion des incidents\n\nFORMATION\nMaster Informatique\n2020 - 2022\n\nCOMPÉTENCES\nLaravel, Docker, Cybersécurité",
        ]);

        $response->assertRedirect(route('admin.profile.edit'))
            ->assertSessionHas('success');

        $profile->refresh();

        $this->assertSame('Jean', $profile->first_name);
        $this->assertSame('Dupont', $profile->last_name);
        $this->assertSame('Consultant IT & Cybersécurité', $profile->title);
        $this->assertSame('EN: Consultant IT & Cybersécurité', $profile->title_en);
        $this->assertSame('EN: Consultant IT', Experience::query()->where('profile_id', $profile->id)->firstOrFail()->position_en);
        $this->assertSame('jean.dupont@example.com', $profile->email);
        $this->assertSame('+226 70 12 34 56', $profile->phone);

        $this->assertDatabaseHas('experiences', [
            'profile_id' => $profile->id,
            'company' => 'Société X',
            'position' => 'Consultant IT',
        ]);

        $this->assertDatabaseHas('educations', [
            'profile_id' => $profile->id,
            'degree' => 'Master Informatique',
        ]);
        $this->assertSame('EN: Master Informatique', Education::query()->where('profile_id', $profile->id)->firstOrFail()->degree_en);

        $this->assertTrue(
            Skill::query()
                ->where('name', 'like', '%Laravel%')
                ->orWhere('name', 'like', '%Cybersécurité%')
                ->exists()
        );
    }

    public function test_admin_can_import_all_supported_cv_sections_into_portfolio(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($user);

        $this->post(route('admin.profile.import'), [
            'cv_text' => implode("\n", [
                'Alain GUIGMA',
                'Consultant IT',
                'Ouagadougou, Burkina Faso',
                'alain@example.test',
                '+226 70 12 34 56',
                'https://github.com/alino999',
                'linkedin.com/in/alain-guigma',
                '',
                'À PROPOS',
                'Consultant en informatique et cybersécurité.',
                '',
                'EXPÉRIENCES PROFESSIONNELLES',
                'Groupe Logiciel | Consultant Sage X3',
                'Février 2025 - Aujourd’hui',
                'Déploiement et support ERP.',
                '',
                'FORMATIONS',
                'Université Aube Nouvelle',
                'Master en systèmes informatiques',
                '2024 - 2026',
                '',
                'COMPÉTENCES',
                'Laravel, SQL, Cybersécurité',
                '',
                'CERTIFICATIONS',
                'Analyste junior en cybersécurité | Cisco Networking Academy | 2024',
                '',
                'LANGUES',
                'Français : Courant',
                'Anglais : Intermédiaire',
                '',
                'TECHNOLOGIES',
                'Sage X3, MySQL',
                '',
                'RÉFÉRENCES PROFESSIONNELLES',
                'Louis NABALOUM          Ïmael OUIYA          Fatah Oumar OUEDRAOGO',
                'Développeur à Total Avengers          Ingénieur Cloud à TI          IT à Burkina Mining Company',
                'Tél : +226 56 92 06 71          Tél : +226 60 18 54 25          Tél : +226 70 19 50 32',
                '',
                'RÉSEAUX SOCIAUX',
                'GitHub: https://github.com/alino999',
                'LinkedIn: https://linkedin.com/in/alain-guigma',
                '',
                'PROJETS',
                'Portfolio web | Développement du portfolio personnel',
                '',
                'RÉALISATIONS',
                'Certification Analyste cybersécurité | Réussite du parcours en 2025',
            ]),
        ])->assertRedirect(route('admin.profile.edit'))->assertSessionHas('success');

        $profile = Profile::query()->where('user_id', $user->id)->firstOrFail();
        $this->assertSame('Consultant IT', $profile->title);
        $this->assertSame('EN: Consultant IT', $profile->title_en);
        $this->assertSame('Ouagadougou, Burkina Faso', $profile->location);
        $this->assertSame('https://github.com/alino999', $profile->github);
        $this->assertDatabaseHas('experiences', ['profile_id' => $profile->id, 'company' => 'Groupe Logiciel', 'position' => 'Consultant Sage X3']);
        $this->assertDatabaseHas('educations', ['profile_id' => $profile->id, 'school' => 'Université Aube Nouvelle', 'degree' => 'Master en systèmes informatiques']);
        $education = Education::query()->where('profile_id', $profile->id)->firstOrFail();
        $this->assertSame('EN: Master en systèmes informatiques', $education->degree_en);
        $certification = Certification::query()->where('issuer', 'Cisco Networking Academy')->firstOrFail();
        $this->assertSame('EN: Analyste junior en cybersécurité', $certification->title['en']);
        $this->assertSame('Cisco Networking Academy', $certification->issuer_en);
        $frenchLanguage = Language::query()->where('locale', 'fr')->firstOrFail();
        $this->assertSame('EN: Français', $frenchLanguage->name['en']);
        $this->assertSame('EN: Courant', $frenchLanguage->level_en);
        $laravelSkill = Skill::query()->where('name->fr', 'Laravel')->firstOrFail();
        $this->assertSame('EN: Laravel', $laravelSkill->name['en']);
        $this->assertTrue(Technology::query()->where('name->fr', 'Sage X3')->exists());
        $this->assertTrue(Technology::query()->where('name->fr', 'MySQL')->exists());
        $this->assertDatabaseHas('professional_references', ['profile_id' => $profile->id, 'name' => 'Louis NABALOUM']);
        $reference = ProfessionalReference::query()->where('profile_id', $profile->id)->where('name', 'Louis NABALOUM')->firstOrFail();
        $this->assertSame('EN: Développeur', $reference->role_en);
        $this->assertDatabaseHas('professional_references', ['profile_id' => $profile->id, 'name' => 'Ïmael OUIYA']);
        $this->assertDatabaseHas('professional_references', ['profile_id' => $profile->id, 'name' => 'Fatah Oumar OUEDRAOGO']);
        $this->assertDatabaseHas('social_links', ['profile_id' => $profile->id, 'platform' => 'GitHub', 'url' => 'https://github.com/alino999']);
        $this->assertDatabaseHas('projects', ['slug' => 'portfolio-web']);
        $this->assertDatabaseHas('achievements', ['profile_id' => $profile->id]);
        $this->assertGreaterThanOrEqual(1, Skill::query()->count());
        $this->assertGreaterThanOrEqual(2, Technology::query()->count());
        $this->assertGreaterThanOrEqual(1, Education::query()->where('profile_id', $profile->id)->count());
        $this->assertGreaterThanOrEqual(1, Experience::query()->where('profile_id', $profile->id)->count());
        $this->assertGreaterThanOrEqual(1, Language::query()->count());
        $this->assertGreaterThanOrEqual(1, Certification::query()->count());
        $this->assertGreaterThanOrEqual(1, ProfessionalReference::query()->where('profile_id', $profile->id)->count());
        $this->assertGreaterThanOrEqual(2, SocialLink::query()->where('profile_id', $profile->id)->count());
    }

    public function test_reimport_updates_an_existing_experience_without_creating_a_duplicate(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $profile = Profile::query()->create(['user_id' => $user->id]);
        Experience::query()->create([
            'profile_id' => $profile->id,
            'company' => 'Société X',
            'position' => 'Ancien poste mal importé',
            'start_date' => '2019-01-01',
            'published' => true,
        ]);

        $this->actingAs($user)->post(route('admin.profile.import'), [
            'cv_text' => "Jean Dupont\nConsultant IT\n\nEXPERIENCE\nSociété X | Consultant IT\n2022 - Aujourd'hui\nSupport utilisateur",
        ])->assertRedirect(route('admin.profile.edit'))->assertSessionHas('success');

        $this->assertDatabaseCount('experiences', 1);
        $this->assertDatabaseHas('experiences', [
            'profile_id' => $profile->id,
            'company' => 'Société X',
            'position' => 'Consultant IT',
            'is_current' => 1,
        ]);
        $this->assertSame('2022-01-01', Experience::query()->where('profile_id', $profile->id)->firstOrFail()->start_date->format('Y-m-d'));
    }

    public function test_cv_import_does_not_save_french_as_english_when_deepl_is_not_configured(): void
    {
        config(['services.deepl.api_key' => null]);
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)->post(route('admin.profile.import'), [
            'cv_text' => "Jean Dupont\nConsultant informatique\nRésumé en français.",
        ])->assertRedirect()->assertSessionHas('error');

        $profile = Profile::query()->where('user_id', $user->id)->firstOrFail();
        $this->assertNull($profile->title);
        $this->assertNull($profile->title_en);
    }

    public function test_pdf_is_not_stored_when_deepl_is_not_configured(): void
    {
        Storage::fake('public');
        config(['services.deepl.api_key' => null]);
        $user = User::factory()->create(['email_verified_at' => now()]);
        $pdfFile = UploadedFile::fake()->createWithContent(
            'cv.pdf',
            Pdf::loadHTML('<h1>Jean Dupont</h1><p>Consultant informatique</p>')->output(),
        );

        $this->actingAs($user)->post(route('admin.profile.import'), [
            'cv_file' => $pdfFile,
        ])->assertRedirect()->assertSessionHas('error');

        $profile = Profile::query()->where('user_id', $user->id)->firstOrFail();
        $this->assertNull($profile->cv_path);
        $this->assertSame([], Storage::disk('public')->allFiles('cv-imports'));
    }

    public function test_cv_importer_parses_a_two_column_french_cv(): void
    {
        $row = fn (string $left, string $right = '') => str_pad($left, 52).' '.$right;
        $text = implode("\n", [
            $row('', 'Alain'),
            $row('', 'GUIGMA'),
            $row('', 'IT Maintenance & Cybersecurity'),
            $row('A propos', 'Education'),
            $row('Passionné de la Sécurité Informatique, de', 'Université Aube Nouvelle'),
            $row('la programmation web et Mobile, je suis', 'Master en Système Informatique'),
            $row('créatif, curieux, rigoureux et déterminé.', '2024 - 2026'),
            $row('+226 66 41 07 68 / 71 40 62 88', 'FORCE-N'),
            $row('guigmaa507@gmail.com', 'Cybersécurité'),
            $row('Songnaaba Secteur 29', '2024'),
            $row('Compétences', 'Université Aube Nouvelle'),
            $row('HTML/CSS / Bootstrap', 'Licence en Génie logiciel'),
            $row('JS/ JQuery / TotalJs', '2023 - 2024'),
            $row('PHP/ Laravel', 'Expériences'),
            $row('SQL/ MySQL / PostgreSQL /Oracle', 'GROUPE LOGICIEL & SERVICES          Fev 2025 à nos jours'),
            $row('Java', 'Consultant (Sage, MedSys, GMAO), formateur, requêteur sql'),
            $row('C#', 'DJAGO INTERNATIONAL                 Juil 2024 à Fev 2025'),
            $row('Windows', 'Consultant (Sage, MedSys, GMAO), formateur'),
            $row('Windows server', 'Total Innovation                    2023 - à Juin 2024'),
            $row('Linux', 'Technicien, Développeur web et mobile, Web Design'),
            $row('Maintenance', 'DAAN (ASECNA BF)                    2023'),
            $row('Devops', 'Stage de 04 mois au service informatique et maintenance'),
            $row('Photoshop / XD / Canva', 'Afric Consultin Group               2022'),
            $row('Sage X3', 'Développeur web et technicien durant l’année 2022'),
            $row('Cybersécurité', 'Afric Consultin Group               2021'),
            $row('Langue', 'Stage de 6 mois en développement web'),
            $row('Morée', 'Références'),
            $row('Français', 'Louis NABALOUM          Ïmael OUIYA          Fatah Oumar OUEDRAOGO'),
            $row('Anglais', 'Dév à Total Avengers  Ingénieur Cloud à TI  IT à Burkina Mining Company'),
            $row('', 'Tél : +226 56 92 06 71  Tél : +226 60 18 54 25  Tél : +226 70 19 50 32'),
        ]);

        $parsed = app(CvImporter::class)->parse($text);

        $this->assertSame('Alain', $parsed['first_name']);
        $this->assertSame('GUIGMA', $parsed['last_name']);
        $this->assertSame('IT Maintenance & Cybersecurity', $parsed['title']);
        $this->assertSame('guigmaa507@gmail.com', $parsed['email']);
        $this->assertSame('+226 66 41 07 68 / 71 40 62 88', $parsed['phone']);
        $this->assertSame('Songnaaba Secteur 29', $parsed['location']);
        $this->assertStringContainsString('Passionné de la Sécurité Informatique', $parsed['summary']);
        $this->assertCount(3, $parsed['educations']);
        $this->assertCount(6, $parsed['experiences']);
        $this->assertContains('PHP/ Laravel', $parsed['skills']);
        $this->assertCount(3, $parsed['languages']);
        $this->assertCount(3, $parsed['references']);
    }

    public function test_cv_importer_parses_the_actual_text_order_from_the_uploaded_pdf(): void
    {
                $html = '<table style="width:100%;border-collapse:collapse"><tr>'
                        .'<td style="width:40%;vertical-align:top">Alain<br>GUIGMA<br>IT Maintenance &amp; Cybersecurity<br>'
                        .'À propos<br>Passionné de la sécurité informatique.<br>+226 66 41 07 68 / 71 40 62 88<br>'
                        .'alain@example.test<br>Songnaaba Secteur 29<br>Compétences<br>HTML/CSS / Bootstrap<br>PHP / Laravel<br>'
                        .'Langue<br>Morée<br>Français<br>Anglais</td>'
                        .'<td style="width:60%;vertical-align:top">Education<br>Université Aube Nouvelle<br>Master Informatique<br>2024 - 2026<br>'
                        .'Expériences<br>GROUPE LOGICIEL &amp; SERVICES<br>Fev 2025 à nos jours<br>Consultant Sage X3<br>'
                        .'Références<br>Louis NABALOUM | Dev | Total Avengers | +226 56 92 06 71</td>'
                        .'</tr></table>';
                $pdfFile = UploadedFile::fake()->createWithContent('cv.pdf', Pdf::loadHTML($html)->output());
                $importer = app(CvImporter::class);
                $parsed = $importer->parse($importer->extractFile($pdfFile));

                $this->assertSame('Alain', $parsed['first_name']);
                $this->assertSame('GUIGMA', $parsed['last_name']);
                $this->assertSame('IT Maintenance & Cybersecurity', $parsed['title']);
                $this->assertSame('alain@example.test', $parsed['email']);
                $this->assertSame('+226 66 41 07 68 / 71 40 62 88', $parsed['phone']);
                $this->assertSame('Songnaaba Secteur 29', $parsed['location']);
                $this->assertCount(1, $parsed['educations']);
                $this->assertCount(1, $parsed['experiences']);
                $this->assertContains('PHP / Laravel', $parsed['skills']);
                $this->assertSame(['Morée', 'Français', 'Anglais'], array_column($parsed['languages'], 'name'));
                $this->assertSame('Louis NABALOUM', $parsed['references'][0]['name']);
    }

    public function test_admin_can_import_cv_from_txt_file(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $this->actingAs($user);

        $file = UploadedFile::fake()->createWithContent('cv.txt', "Alice Martin\nDéveloppeuse Full Stack\nhttps://example.com\n\nCOMPÉTENCES\nPHP, Laravel, SQL", 'text/plain');

        $response = $this->post(route('admin.profile.import'), [
            'cv_file' => $file,
        ]);

        $response->assertRedirect(route('admin.profile.edit'))
            ->assertSessionHas('success');

        $profile = Profile::query()->where('user_id', $user->id)->first();
        $this->assertSame('Alice', $profile->first_name);
        $this->assertSame('Martin', $profile->last_name);
        $this->assertSame('Développeuse Full Stack', $profile->title);
        $this->assertNotNull($profile->website);
    }

    public function test_cv_importer_extracts_pdf_and_docx_text(): void
    {
        $importer = app(CvImporter::class);
        $pdfFile = UploadedFile::fake()->createWithContent(
            'cv.pdf',
            Pdf::loadHTML('<h1>Alain GUIGMA</h1><p>Développeur Laravel</p><p>alain@example.test</p><h2>COMPÉTENCES</h2><p>Laravel, PHP</p>')->output(),
        );
        $this->assertStringContainsString('Alain GUIGMA', $importer->extractFile($pdfFile));

        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($user)
            ->post(route('admin.profile.import'), ['cv_file' => $pdfFile])
            ->assertRedirect(route('admin.profile.edit'))
            ->assertSessionHas('success');
        $profile = Profile::query()->where('user_id', $user->id)->firstOrFail();
        $this->assertSame('Alain', $profile->first_name);
        $this->assertSame('alain@example.test', $profile->email);
        $this->assertNotEmpty($profile->cv_path);

        $docxPath = tempnam(sys_get_temp_dir(), 'cv-import-').'.docx';
        $document = new PhpWord();
        $document->addSection()->addText('CV Alain DOCX');
        IOFactory::createWriter($document, 'Word2007')->save($docxPath);

        try {
            $docxFile = new UploadedFile(
                $docxPath,
                'cv.docx',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                UPLOAD_ERR_OK,
                true,
            );
            $this->assertStringContainsString('CV Alain DOCX', $importer->extractFile($docxFile));
        } finally {
            @unlink($docxPath);
        }
    }
}
