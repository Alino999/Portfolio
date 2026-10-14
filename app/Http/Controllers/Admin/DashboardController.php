<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Certification;
use App\Models\ContactMessage;
use App\Models\Experience;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Resume;
use App\Models\Skill;
use App\Models\SiteSetting;
use App\Support\LocaleHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DashboardController extends Controller
{
    public function index()
    {
        $profile = Profile::query()->where('user_id', auth()->id())->first();
        $stats = [
            'projects' => Project::count(),
            'experiences' => Experience::count(),
            'educations' => $profile?->educations()->count() ?? 0,
            'skills' => Skill::count(),
            'certifications' => Certification::count(),
            'resumes' => $profile ? Resume::query()->where('profile_id', $profile->id)->count() : 0,
            'messages' => ContactMessage::count(),
            'articles' => Article::count(),
        ];

        $latestMessage = ContactMessage::latest()->first();
        $latestProject = Project::latest()->first();
        $latestExperience = $profile?->experiences()->reorder()->orderByDesc('updated_at')->first();
        $latestArticle = Article::latest()->first();
        $latestResume = $profile ? Resume::query()->where('profile_id', $profile->id)->latest()->first() : null;
        $recentActivity = collect([
            $latestMessage ? [
                'type' => 'Message reçu',
                'title' => trim($latestMessage->name.' — '.$latestMessage->subject, ' —'),
                'date' => $latestMessage->created_at,
                'url' => route('admin.messages.show', $latestMessage),
                'icon' => 'bi-envelope',
            ] : null,
            $latestProject ? [
                'type' => 'Projet',
                'title' => LocaleHelper::text($latestProject->title),
                'date' => $latestProject->updated_at,
                'url' => route('admin.projects.index'),
                'icon' => 'bi-briefcase',
            ] : null,
            $latestExperience ? [
                'type' => 'Expérience',
                'title' => $latestExperience->position.' — '.$latestExperience->company,
                'date' => $latestExperience->updated_at,
                'url' => route('admin.experiences.index'),
                'icon' => 'bi-clock-history',
            ] : null,
            $latestArticle ? [
                'type' => 'Article',
                'title' => LocaleHelper::text($latestArticle->title),
                'date' => $latestArticle->updated_at,
                'url' => route('admin.articles.show', $latestArticle),
                'icon' => 'bi-journal-text',
            ] : null,
            $latestResume ? [
                'type' => 'CV',
                'title' => $latestResume->title,
                'date' => $latestResume->created_at,
                'url' => route('admin.resumes.index'),
                'icon' => 'bi-file-earmark-person',
            ] : null,
        ])->filter()->sortByDesc('date')->values();

        return view('admin.dashboard', compact('stats', 'recentActivity'));
    }

    public function settings()
    {
        $settings = SiteSetting::query()->get()->mapWithKeys(fn ($setting) => [$setting->key => $setting->value]);
        $backupGroups = $this->portfolioBackupGroups();

        return view('admin.settings', compact('settings', 'backupGroups'));
    }

    public function saveSettings(Request $request)
    {
        $data = $request->validate([
            'site_name' => ['nullable', 'string', 'max:255'],
            'site_email' => ['nullable', 'email', 'max:255'],
            'site_phone' => ['nullable', 'string', 'max:100'],
            'site_location' => ['nullable', 'string', 'max:255'],
            'default_locale' => ['nullable', 'in:fr,en'],
            'primary_color' => ['nullable', 'regex:/\A#[0-9A-Fa-f]{6}\z/'],
            'accent_color' => ['nullable', 'regex:/\A#[0-9A-Fa-f]{6}\z/'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_logo' => ['nullable', 'boolean'],
        ]);

        $oldLogoPath = SiteSetting::query()->where('key', 'logo_path')->value('value');
        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('branding', 'public');
            if ($oldLogoPath) {
                Storage::disk('public')->delete($oldLogoPath);
            }
        } elseif ($request->boolean('remove_logo')) {
            if ($oldLogoPath) {
                Storage::disk('public')->delete($oldLogoPath);
            }
            $data['logo_path'] = null;
        }

        unset($data['logo'], $data['remove_logo']);

        foreach ($data as $key => $value) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return back()->with('success', 'Paramètres enregistrés avec succès.');
    }

    public function backup(Request $request)
    {
        $groups = $this->portfolioBackupGroups();
        if (! $request->query->has('groups')) {
            $request->query->set('groups', array_keys($groups));
        }
        $validated = $request->validate([
            'groups' => ['required', 'array', 'min:1'],
            'groups.*' => ['required', 'string', Rule::in(array_keys($groups))],
        ]);
        $selectedGroups = array_values(array_unique($validated['groups']));
        $tables = collect($selectedGroups)
            ->flatMap(fn (string $group) => $groups[$group]['tables'])
            ->unique()
            ->all();
        $payload = [
            'generated_at' => now()->toISOString(),
            'app' => 'portfolio',
            'selected_groups' => $selectedGroups,
            'tables' => [],
        ];

        foreach ($tables as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $payload['tables'][$table] = DB::table($table)->get()->map(fn ($row) => (array) $row)->all();
        }

        $content = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, 'portfolio-backup-'.now()->format('Ymd-His').'.json', [
            'Content-Type' => 'application/json; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="portfolio-backup-'.now()->format('Ymd-His').'.json"',
        ]);
    }

    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => ['required', 'file', 'mimetypes:application/json,text/plain,application/octet-stream', 'max:20480'],
        ]);

        $content = file_get_contents($request->file('backup_file')->getRealPath());
        $payload = json_decode($content, true);

        if (! is_array($payload) || empty($payload['tables']) || ! is_array($payload['tables'])) {
            throw ValidationException::withMessages([
                'backup_file' => 'Le fichier de sauvegarde est invalide ou incomplet.',
            ]);
        }

        $database = DB::connection();
        $database->getSchemaBuilder()->disableForeignKeyConstraints();

        try {
            foreach ($this->portfolioBackupTables() as $table) {
                if (! Schema::hasTable($table) || ! array_key_exists($table, $payload['tables'])) {
                    continue;
                }

                $database->table($table)->delete();

                foreach ($payload['tables'][$table] as $row) {
                    $normalizedRow = [];
                    foreach ((array) $row as $column => $value) {
                        $normalizedRow[$column] = is_array($value) || is_object($value)
                            ? json_encode($value, JSON_UNESCAPED_UNICODE)
                            : $value;
                    }

                    $database->table($table)->insert($normalizedRow);
                }
            }
        } finally {
            $database->getSchemaBuilder()->enableForeignKeyConstraints();
        }

        return redirect()->route('admin.settings')->with('success', 'La sauvegarde du portfolio a été restaurée avec succès.');
    }

    public function resetData()
    {
        $preservedTables = [
            'users', 'migrations', 'password_reset_tokens', 'sessions', 'cache', 'cache_locks',
            'jobs', 'job_batches', 'failed_jobs', 'permissions', 'roles', 'model_has_permissions',
            'model_has_roles', 'role_has_permissions',
        ];
        $connection = DB::connection();
        $schema = $connection->getSchemaBuilder();
        $schema->disableForeignKeyConstraints();

        try {
            foreach (array_diff($schema->getTableListing(schemaQualified: false), $preservedTables) as $table) {
                if (str_starts_with($table, 'pma__') || ! $schema->hasTable($table)) {
                    continue;
                }
                $connection->table($table)->truncate();
            }
        } finally {
            $schema->enableForeignKeyConstraints();
        }

        return redirect()->route('admin.dashboard')->with('success', 'Les données du portfolio ont été supprimées. Votre compte administrateur est conservé.');
    }

    protected function portfolioBackupTables(): array
    {
        return collect($this->portfolioBackupGroups())
            ->flatMap(fn (array $group) => $group['tables'])
            ->unique()
            ->values()
            ->all();
    }

    protected function portfolioBackupGroups(): array
    {
        return [
            'site' => ['label' => 'Profil et paramètres du site', 'tables' => ['site_settings', 'profiles', 'sections', 'menu_items', 'seo_metadata', 'translations']],
            'portfolio' => ['label' => 'Contenu du portfolio', 'tables' => ['projects', 'project_categories', 'skills', 'skill_categories', 'experiences', 'educations', 'services', 'testimonials', 'certifications', 'social_links', 'languages', 'technologies', 'professional_references', 'achievements', 'media']],
            'articles' => ['label' => 'Articles et commentaires', 'tables' => ['articles', 'article_categories', 'article_comments', 'article_likes', 'article_tag', 'tags']],
            'messages' => ['label' => 'Messages de contact', 'tables' => ['contact_messages']],
            'resumes' => ['label' => 'CV et modèles de CV', 'tables' => ['resume_template_categories', 'resume_templates', 'resumes']],
            'activity' => ['label' => 'Journal d’activité', 'tables' => ['activity_logs']],
        ];
    }
}