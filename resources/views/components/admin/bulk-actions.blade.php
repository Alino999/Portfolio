@props(['type', 'actions', 'contentType' => null, 'contextName' => null, 'context' => null])

@php($formId = 'bulk-action-'.\Illuminate\Support\Str::slug($type.'-'.$contentType))

<form id="{{ $formId }}" method="POST" action="{{ route('admin.bulk-action', $type) }}" class="d-flex flex-wrap align-items-center gap-2 mb-3" data-bulk-action-form>
    @csrf
    @if($contentType)<input type="hidden" name="content_type" value="{{ $contentType }}">@endif
    @if($contextName && $context !== null)<input type="hidden" name="{{ $contextName }}" value="{{ $context }}">@endif
    <input type="checkbox" id="{{ $formId }}-all" class="form-check-input" form="{{ $formId }}" data-bulk-select-all aria-label="Tout sélectionner">
    <label class="form-check-label small" for="{{ $formId }}-all">Tout sélectionner</label>
    <label class="visually-hidden" for="{{ $formId }}-action">Action groupée</label>
    <select id="{{ $formId }}-action" name="action" class="form-select form-select-sm" style="max-width: 240px" required>
        <option value="">Action sur la sélection</option>
        @foreach($actions as $value => $label)
            <option value="{{ $value }}">{{ $label }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-sm btn-primary" data-bulk-submit disabled>
        <i class="bi bi-check2-square me-1" aria-hidden="true"></i>Appliquer (<span data-bulk-count>0</span>)
    </button>
</form>