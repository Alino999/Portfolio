<?php

use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\BulkActionController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExperienceController;
use App\Http\Controllers\Admin\HomeSlideController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\PortfolioEntryController;
use App\Http\Controllers\Admin\ResumeController;
use App\Http\Controllers\Admin\ResumeTemplateCategoryController;
use App\Http\Controllers\Admin\ResumeTemplateController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SkillController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicArticleController;
use App\Http\Controllers\PublicPortfolioController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicPortfolioController::class, 'index'])->name('home');
Route::get('/blog/{article:slug}', [PublicArticleController::class, 'show'])->name('public.articles.show');
Route::post('/blog/{article:slug}/like', [PublicArticleController::class, 'toggleLike'])->name('public.articles.like');
Route::post('/blog/{article:slug}/comments', [PublicArticleController::class, 'comment'])->middleware('throttle:10,1')->name('public.articles.comments.store');
Route::post('/contact', [PublicPortfolioController::class, 'contact'])->name('public.contact');
Route::post('/testimonials', [PublicPortfolioController::class, 'submitTestimonial'])->middleware('throttle:5,10')->name('public.testimonials.store');
Route::get('/locale/{locale}', function ($locale) {
    if (in_array($locale, ['fr', 'en'], true)) {
        session()->put('locale', $locale);
        app()->setLocale($locale);
    }

    return redirect()->back();
})->name('locale.switch');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/admin/bulk/{type}', [BulkActionController::class, 'handle'])->name('admin.bulk-action');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/settings', [DashboardController::class, 'settings'])->name('admin.settings');
    Route::get('/admin/settings/backup', [DashboardController::class, 'backup'])->name('admin.settings.backup');
    Route::post('/admin/settings/restore', [DashboardController::class, 'restore'])->name('admin.settings.restore');
    Route::post('/admin/settings', [DashboardController::class, 'saveSettings'])->name('admin.settings.save');
    Route::post('/admin/settings/reset', [DashboardController::class, 'resetData'])->name('admin.settings.reset');
    Route::get('/admin/portfolio-content/{type}', [PortfolioEntryController::class, 'index'])->name('admin.portfolio-content.index');
    Route::post('/admin/portfolio-content/{type}', [PortfolioEntryController::class, 'store'])->name('admin.portfolio-content.store');
    Route::get('/admin/portfolio-content/{type}/export', [PortfolioEntryController::class, 'export'])->name('admin.portfolio-content.export');
    Route::post('/admin/portfolio-content/{type}/import', [PortfolioEntryController::class, 'import'])->name('admin.portfolio-content.import');
    Route::get('/admin/portfolio-content/{type}/{entry}/edit', [PortfolioEntryController::class, 'edit'])->name('admin.portfolio-content.edit');
    Route::put('/admin/portfolio-content/{type}/{entry}', [PortfolioEntryController::class, 'update'])->name('admin.portfolio-content.update');
    Route::delete('/admin/portfolio-content/{type}/{entry}', [PortfolioEntryController::class, 'destroy'])->name('admin.portfolio-content.destroy');
    Route::get('/admin/profile', [AdminProfileController::class, 'edit'])->name('admin.profile.edit');
    Route::post('/admin/profile', [AdminProfileController::class, 'update'])->name('admin.profile.update');
    Route::post('/admin/profile/import', [AdminProfileController::class, 'import'])->name('admin.profile.import');
    Route::get('/admin/messages', [ContactMessageController::class, 'index'])->name('admin.messages.index');
    Route::get('/admin/messages/{contactMessage}', [ContactMessageController::class, 'show'])->name('admin.messages.show');
    Route::delete('/admin/messages/{contactMessage}', [ContactMessageController::class, 'destroy'])->name('admin.messages.destroy');
    Route::get('/admin/articles', [ArticleController::class, 'index'])->name('admin.articles.index');
    Route::post('/admin/articles/categories', [ArticleController::class, 'storeCategory'])->name('admin.articles.categories.store');
    Route::post('/admin/articles/search', [ArticleController::class, 'search'])->name('admin.articles.search');
    Route::post('/admin/articles/import-url', [ArticleController::class, 'importUrl'])->name('admin.articles.import-url');
    Route::get('/admin/articles/{article}/edit', [ArticleController::class, 'edit'])->name('admin.articles.edit');
    Route::put('/admin/articles/{article}', [ArticleController::class, 'update'])->name('admin.articles.update');
    Route::patch('/admin/articles/{article}/toggle-publication', [ArticleController::class, 'togglePublication'])->name('admin.articles.toggle-publication');
    Route::patch('/admin/articles/{article}/comments/{comment}/approve', [ArticleController::class, 'approveComment'])->name('admin.articles.comments.approve');
    Route::delete('/admin/articles/{article}/comments/{comment}', [ArticleController::class, 'destroyComment'])->name('admin.articles.comments.destroy');
    Route::get('/admin/articles/{article}', [ArticleController::class, 'show'])->name('admin.articles.show');
    Route::delete('/admin/articles/{article}', [ArticleController::class, 'destroy'])->name('admin.articles.destroy');
    Route::get('/admin/home-slides', [HomeSlideController::class, 'index'])->name('admin.home-slides.index');
    Route::post('/admin/home-slides', [HomeSlideController::class, 'store'])->name('admin.home-slides.store');
    Route::delete('/admin/home-slides/{media}', [HomeSlideController::class, 'destroy'])->name('admin.home-slides.destroy');
    Route::get('/admin/resumes', [ResumeController::class, 'index'])->name('admin.resumes.index');
    Route::post('/admin/resumes', [ResumeController::class, 'store'])->name('admin.resumes.store');
    Route::get('/admin/resumes/{resume}', [ResumeController::class, 'show'])->name('admin.resumes.show');
    Route::get('/admin/resumes/{resume}/edit', [ResumeController::class, 'edit'])->name('admin.resumes.edit');
    Route::get('/admin/resumes/{resume}/edit-modal', [ResumeController::class, 'editModal'])->name('admin.resumes.edit-modal');
    Route::put('/admin/resumes/{resume}', [ResumeController::class, 'update'])->name('admin.resumes.update');
    Route::delete('/admin/resumes/{resume}', [ResumeController::class, 'destroy'])->name('admin.resumes.destroy');
    Route::get('/admin/resumes/{resume}/download', [ResumeController::class, 'download'])->name('admin.resumes.download');
    Route::get('/admin/skills', [SkillController::class, 'index'])->name('admin.skills.index');
    Route::post('/admin/skills', [SkillController::class, 'store'])->name('admin.skills.store');
    Route::get('/admin/skills/{skill}/edit', [SkillController::class, 'edit'])->name('admin.skills.edit');
    Route::put('/admin/skills/{skill}', [SkillController::class, 'update'])->name('admin.skills.update');
    Route::delete('/admin/skills/{skill}', [SkillController::class, 'destroy'])->name('admin.skills.destroy');
    Route::get('/admin/projects', [ProjectController::class, 'index'])->name('admin.projects.index');
    Route::post('/admin/projects', [ProjectController::class, 'store'])->name('admin.projects.store');
    Route::get('/admin/projects/{project}/edit', [ProjectController::class, 'edit'])->name('admin.projects.edit');
    Route::put('/admin/projects/{project}', [ProjectController::class, 'update'])->name('admin.projects.update');
    Route::delete('/admin/projects/{project}', [ProjectController::class, 'destroy'])->name('admin.projects.destroy');
    Route::get('/admin/experiences', [ExperienceController::class, 'index'])->name('admin.experiences.index');
    Route::post('/admin/experiences', [ExperienceController::class, 'store'])->name('admin.experiences.store');
    Route::get('/admin/experiences/{experience}/edit', [ExperienceController::class, 'edit'])->name('admin.experiences.edit');
    Route::put('/admin/experiences/{experience}', [ExperienceController::class, 'update'])->name('admin.experiences.update');
    Route::delete('/admin/experiences/{experience}', [ExperienceController::class, 'destroy'])->name('admin.experiences.destroy');
    Route::get('/admin/services', [ServiceController::class, 'index'])->name('admin.services.index');
    Route::post('/admin/services', [ServiceController::class, 'store'])->name('admin.services.store');
    Route::get('/admin/services/{service}/edit', [ServiceController::class, 'edit'])->name('admin.services.edit');
    Route::put('/admin/services/{service}', [ServiceController::class, 'update'])->name('admin.services.update');
    Route::delete('/admin/services/{service}', [ServiceController::class, 'destroy'])->name('admin.services.destroy');
    Route::get('/admin/testimonials', [TestimonialController::class, 'index'])->name('admin.testimonials.index');
    Route::post('/admin/testimonials', [TestimonialController::class, 'store'])->name('admin.testimonials.store');
    Route::get('/admin/testimonials/{testimonial}/edit', [TestimonialController::class, 'edit'])->name('admin.testimonials.edit');
    Route::put('/admin/testimonials/{testimonial}', [TestimonialController::class, 'update'])->name('admin.testimonials.update');
    Route::patch('/admin/testimonials/{testimonial}/publication', [TestimonialController::class, 'togglePublication'])->name('admin.testimonials.toggle-publication');
    Route::delete('/admin/testimonials/{testimonial}', [TestimonialController::class, 'destroy'])->name('admin.testimonials.destroy');

    Route::middleware('role:super-admin')->group(function () {
        Route::get('/admin/resume-templates', [ResumeTemplateController::class, 'index'])->name('admin.resume-templates.index');
        Route::post('/admin/resume-templates', [ResumeTemplateController::class, 'store'])->name('admin.resume-templates.store');
        Route::get('/admin/resume-templates/{resumeTemplate}/edit', [ResumeTemplateController::class, 'edit'])->name('admin.resume-templates.edit');
        Route::put('/admin/resume-templates/{resumeTemplate}', [ResumeTemplateController::class, 'update'])->name('admin.resume-templates.update');
        Route::post('/admin/resume-templates/{resumeTemplate}/duplicate', [ResumeTemplateController::class, 'duplicate'])->name('admin.resume-templates.duplicate');
        Route::patch('/admin/resume-templates/{resumeTemplate}/toggle', [ResumeTemplateController::class, 'toggle'])->name('admin.resume-templates.toggle');
        Route::delete('/admin/resume-templates/{resumeTemplate}', [ResumeTemplateController::class, 'destroy'])->name('admin.resume-templates.destroy');
        Route::get('/admin/resume-templates/{resumeTemplate}/preview', [ResumeTemplateController::class, 'preview'])->name('admin.resume-templates.preview');
        Route::get('/admin/resume-template-categories', [ResumeTemplateCategoryController::class, 'index'])->name('admin.resume-template-categories.index');
        Route::post('/admin/resume-template-categories', [ResumeTemplateCategoryController::class, 'store'])->name('admin.resume-template-categories.store');
        Route::put('/admin/resume-template-categories/{category}', [ResumeTemplateCategoryController::class, 'update'])->name('admin.resume-template-categories.update');
        Route::delete('/admin/resume-template-categories/{category}', [ResumeTemplateCategoryController::class, 'destroy'])->name('admin.resume-template-categories.destroy');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
