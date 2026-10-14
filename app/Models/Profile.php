<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Profile extends Model
{
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'title',
        'title_en',
        'subtitle',
        'subtitle_en',
        'summary',
        'summary_en',
        'bio',
        'bio_en',
        'email',
        'phone',
        'location',
        'country',
        'availability',
        'professional_status',
        'years_experience',
        'website',
        'linkedin',
        'github',
        'avatar_path',
        'cover_path',
        'cv_path',
        'custom_titles',
        'primary_skills',
        'is_public',
    ];

    protected $casts = [
        'custom_titles' => 'array',
        'primary_skills' => 'array',
        'is_public' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(Experience::class)->orderBy('sort_order');
    }

    public function educations(): HasMany
    {
        return $this->hasMany(Education::class)->orderBy('sort_order');
    }

    public function socialLinks(): HasMany
    {
        return $this->hasMany(SocialLink::class)->orderBy('sort_order');
    }

    public function resumes(): HasMany
    {
        return $this->hasMany(Resume::class);
    }

    public function professionalReferences(): HasMany
    {
        return $this->hasMany(ProfessionalReference::class)->orderBy('sort_order');
    }

    public function achievements(): HasMany
    {
        return $this->hasMany(Achievement::class)->orderBy('sort_order');
    }
}
