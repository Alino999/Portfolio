<form method="POST" action="{{ $action }}">
    @csrf
    @if($method === 'PUT')
        @method('PUT')
    @endif
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Auteur</label>
            <input type="text" name="author" class="form-control" value="{{ old('author', $testimonial?->author) }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Fonction</label>
            <input type="text" name="role" class="form-control" value="{{ old('role', $testimonial?->role) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Entreprise</label>
            <input type="text" name="company" class="form-control" value="{{ old('company', $testimonial?->company) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label">Témoignage FR</label>
            <textarea name="content_fr" class="form-control" rows="4" required>{{ old('content_fr', App\Support\LocaleHelper::text($testimonial?->content ?? [], 'fr')) }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label">Témoignage EN</label>
            <textarea name="content_en" class="form-control" rows="4">{{ old('content_en', App\Support\LocaleHelper::text($testimonial?->content ?? [], 'en')) }}</textarea>
        </div>
        <div class="col-md-4">
            <label class="form-label">Note</label>
            <select name="rating" class="form-select" required>
                @foreach(range(1, 5) as $rating)
                    <option value="{{ $rating }}" {{ (int) old('rating', $testimonial?->rating ?? 5) === $rating ? 'selected' : '' }}>{{ $rating }} / 5</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Ordre</label>
            <input type="number" min="0" name="sort_order" class="form-control" value="{{ old('sort_order', $testimonial?->sort_order ?? 0) }}">
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" name="published" value="1" {{ old('published', $testimonial?->published ?? true) ? 'checked' : '' }}>
                <label class="form-check-label">Publier</label>
            </div>
        </div>
    </div>
    <x-admin.icon-action type="submit" variant="primary" :icon="$method === 'PUT' ? 'bi-floppy' : 'bi-plus-lg'" :label="$method === 'PUT' ? 'Enregistrer les modifications' : 'Ajouter le témoignage'" class="mt-3" />
</form>
