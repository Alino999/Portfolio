<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use App\Models\Article;
use App\Models\ArticleComment;
use App\Models\Certification;
use App\Models\ContactMessage;
use App\Models\Education;
use App\Models\Experience;
use App\Models\Language;
use App\Models\Media;
use App\Models\ProfessionalReference;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Resume;
use App\Models\ResumeTemplate;
use App\Models\ResumeTemplateCategory;
use App\Models\Service;
use App\Models\Skill;
use App\Models\SocialLink;
use App\Models\Technology;
use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BulkActionController extends Controller
{
    private const INDEX_ROUTES = [
        'articles' => 'admin.articles.index',
        'article-comments' => 'admin.articles.index',
        'contact-messages' => 'admin.messages.index',
        'experiences' => 'admin.experiences.index',
        'home-slides' => 'admin.home-slides.index',
        'projects' => 'admin.projects.index',
        'resumes' => 'admin.resumes.index',
        'resume-templates' => 'admin.resume-templates.index',
        'resume-template-categories' => 'admin.resume-template-categories.index',
        'services' => 'admin.services.index',
        'skills' => 'admin.skills.index',
        'testimonials' => 'admin.testimonials.index',
    ];

    public function handle(Request $request, string $type)
    {
        abort_unless($type === 'portfolio-content' || isset(self::INDEX_ROUTES[$type]), 404);
        abort_unless(! in_array($type, ['resume-templates', 'resume-template-categories'], true) || $request->user()->hasRole('super-admin'), 403);

        $allowedActions = $this->allowedActions($type, $request);
        $validated = $request->validate([
            'action' => ['required', 'string', Rule::in($allowedActions)],
            'ids' => ['required', 'array', 'min:1', 'max:10'],
            'ids.*' => ['required', 'integer', 'distinct'],
        ]);
        $ids = array_map('intval', $validated['ids']);
        $records = $this->queryFor($type, $request)->whereKey($ids)->get();

        abort_unless($records->count() === count($ids), 404);

        if ($type === 'articles' && $validated['action'] === 'publish'
            && $records->contains(fn (Article $article) => ! collect($article->content ?? [])->contains(fn ($value) => is_string($value) && trim($value) !== ''))) {
            throw ValidationException::withMessages(['ids' => 'Un article sélectionné est vide. Ajoutez son contenu avant de le publier.']);
        }

        if ($validated['action'] === 'delete') {
            $this->deleteRecords($type, $records);
            $message = count($ids).' élément(s) supprimé(s).';
        } elseif ($validated['action'] === 'mark-read') {
            ContactMessage::query()->whereKey($ids)->update(['status' => 'read']);
            $message = count($ids).' message(s) marqué(s) comme lu(s).';
        } else {
            $published = $validated['action'] === 'publish';
            foreach ($records as $record) {
                $this->setPublication($type, $record, $published, $request);
            }
            $message = count($ids).($published ? ' élément(s) publié(s) ou activé(s).' : ' élément(s) masqué(s) ou désactivé(s).');
        }

        $route = match ($type) {
            'portfolio-content' => route('admin.portfolio-content.index', ['type' => $request->input('content_type')]),
            'article-comments' => route('admin.articles.show', ['article' => $request->input('article_id')]),
            default => route(self::INDEX_ROUTES[$type]),
        };

        return redirect()->to($route)->with('success', $message);
    }

    private function allowedActions(string $type, Request $request): array
    {
        if ($type === 'contact-messages') {
            return ['mark-read', 'delete'];
        }

        if ($type === 'article-comments') {
            return ['publish', 'unpublish', 'delete'];
        }

        if ($type === 'portfolio-content') {
            abort_unless(in_array($request->input('content_type'), [
                'educations', 'certifications', 'languages', 'technologies',
                'professional-references', 'social-links', 'achievements',
            ], true), 404);
        }

        return ['publish', 'unpublish', 'delete'];
    }

    private function queryFor(string $type, Request $request): Builder
    {
        if ($type === 'portfolio-content') {
            $contentType = $request->input('content_type');
            $profileScoped = in_array($contentType, ['educations', 'professional-references', 'social-links', 'achievements'], true);
            $model = match ($contentType) {
                'educations' => Education::class,
                'certifications' => Certification::class,
                'languages' => Language::class,
                'technologies' => Technology::class,
                'professional-references' => ProfessionalReference::class,
                'social-links' => SocialLink::class,
                'achievements' => Achievement::class,
            };
            $query = $model::query();

            if ($profileScoped) {
                $profileId = Profile::query()->where('user_id', $request->user()->id)->value('id') ?? 0;
                $query->where('profile_id', $profileId);
            }

            return $query;
        }

        return match ($type) {
            'articles' => Article::query(),
            'article-comments' => ArticleComment::query()->where('article_id', Article::query()->findOrFail($request->input('article_id'))->id),
            'contact-messages' => ContactMessage::query(),
            'experiences' => Experience::query()->where('profile_id', Profile::query()->where('user_id', $request->user()->id)->value('id') ?? 0),
            'home-slides' => Media::query()->where('category', 'homepage_slider'),
            'projects' => Project::query(),
            'resumes' => Resume::query()->where('profile_id', Profile::query()->where('user_id', $request->user()->id)->value('id') ?? 0),
            'resume-templates' => ResumeTemplate::query(),
            'resume-template-categories' => ResumeTemplateCategory::query(),
            'services' => Service::query(),
            'skills' => Skill::query(),
            'testimonials' => Testimonial::query(),
        };
    }

    private function setPublication(string $type, $record, bool $published, Request $request): void
    {
        if ($type === 'articles') {
            $record->update([
                'status' => $published ? 'published' : 'draft',
                'published_at' => $published ? ($record->published_at ?? now()) : $record->published_at,
            ]);

            return;
        }

        if ($type === 'article-comments') {
            $record->update(['status' => $published ? 'approved' : 'pending']);

            return;
        }

        $field = match (true) {
            $type === 'contact-messages' => null,
            $type === 'home-slides' => 'is_public',
            $type === 'resume-templates', $type === 'resume-template-categories' => 'is_active',
            $type === 'portfolio-content' && $request->input('content_type') === 'social-links' => 'is_active',
            default => 'published',
        };

        $values = [$field => $published];
        if ($type === 'testimonials' && $published) {
            $values['submitted_by_client'] = false;
        }
        $record->update($values);
    }

    private function deleteRecords(string $type, $records): void
    {
        if ($type === 'resume-templates') {
            foreach ($records as $record) {
                if ($record->is_system || Resume::query()->where('resume_template_id', $record->id)->exists()) {
                    throw ValidationException::withMessages(['ids' => 'Un modèle sélectionné est protégé ou utilisé par un CV. Désactivez-le plutôt que de le supprimer.']);
                }
            }
        }

        foreach ($records as $record) {
            if ($type === 'home-slides') {
                $this->deleteStoredFile($record->file_path);
            } else {
                $this->deleteStoredFile($record->image_path ?? null);
            }
            $record->delete();
        }
    }

    private function deleteStoredFile(?string $path): void
    {
        if ($path && ! \Illuminate\Support\Str::startsWith($path, ['http://', 'https://'])) {
            Storage::disk('public')->delete($path);
        }
    }
}