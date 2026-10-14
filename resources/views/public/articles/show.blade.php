<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ App\Support\LocaleHelper::text($article->excerpt) }}">
    <title>{{ App\Support\LocaleHelper::text($article->title) }} · {{ $settings['site_name'] ?? 'Portfolio' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <x-brand-theme :settings="$settings" />
    @if(!empty($settings['logo_path']))<link rel="icon" href="{{ asset('storage/'.$settings['logo_path']) }}">@endif
    <style>
        :root { --ink: #172b2b; --muted: #637474; --paper: #f4f7f5; --line: #dce5e1; --teal: var(--brand-primary); }
        body { color: var(--ink); background: #172b2bcc; font-family: "Trebuchet MS", "Segoe UI", sans-serif; }
        .article-modal-backdrop { position: fixed; inset: 0; z-index: 1050; overflow-y: auto; }
        .article-modal-backdrop .modal-dialog { margin: 1.5rem auto; }
        .article-modal-backdrop .modal-content { color: var(--ink); background: #fff; }
        .article-modal-backdrop .modal-header, .article-modal-backdrop .modal-footer { border-color: var(--line); }
        .article-wrap { max-width: 850px; }
        .article-kicker { color: var(--teal); font-size: .85rem; font-weight: 700; text-transform: uppercase; }
        h1, .article-content { font-family: Georgia, "Times New Roman", serif; }
        h1 { font-size: clamp(2rem, 5vw, 3.5rem); line-height: 1.12; }
        .article-cover { width: 100%; max-height: 520px; object-fit: cover; border-radius: 8px; }
        .article-excerpt { color: var(--muted); font-size: 1.15rem; line-height: 1.7; }
        .article-content { font-size: 1.15rem; line-height: 1.9; white-space: pre-line; }
        .interaction-bar { border-block: 1px solid var(--line); }
        .comment-item { border-bottom: 1px solid var(--line); }
        .btn-primary { --bs-btn-bg: var(--teal); --bs-btn-border-color: var(--teal); --bs-btn-hover-bg: #105d57; --bs-btn-hover-border-color: #105d57; }
    </style>
</head>
<body>
    <div class="modal-backdrop fade show" aria-hidden="true"></div>
    <div class="modal fade show d-block article-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="article-title">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <span class="small text-secondary">{{ $settings['site_name'] ?? 'Portfolio' }}</span>
                        @if($article->category)<div class="article-kicker">{{ App\Support\LocaleHelper::text($article->category->name) }}</div>@endif
                        <h1 class="modal-title h3" id="article-title">{{ App\Support\LocaleHelper::text($article->title) }}</h1>
                    </div>
                    <a class="btn-close" href="{{ route('home') }}#blog" aria-label="{{ __('messages.close') }}"></a>
                </div>
                <div class="modal-body">
        <header class="mt-4 mb-4">
            <div class="text-secondary small">{{ $article->published_at?->translatedFormat('d F Y') }}@if($article->author_name) · {{ $article->author_name }}@endif</div>
        </header>

        @if($article->image_path)
            @php
                $articleImageUrl = \Illuminate\Support\Str::startsWith($article->image_path, ['http://', 'https://'])
                    ? $article->image_path
                    : url('/storage/'.ltrim($article->image_path, '/'));
            @endphp
            <img src="{{ $articleImageUrl }}" class="article-cover mb-4" alt="{{ App\Support\LocaleHelper::text($article->title) }}">
        @endif

        @if($article->excerpt)
            <p class="article-excerpt">{{ App\Support\LocaleHelper::text($article->excerpt) }}</p>
        @endif
        <div class="article-content mt-4">{{ App\Support\LocaleHelper::text($article->content) }}</div>

        @if($article->source_url)
            <p class="mt-4"><a href="{{ $article->source_url }}" target="_blank" rel="noopener noreferrer">{{ __('messages.original_source') }} ↗</a></p>
        @endif

        <section id="article-actions" class="interaction-bar py-3 mt-5 d-flex flex-wrap align-items-center gap-2">
            <form method="POST" action="{{ route('public.articles.like', $article->slug) }}">
                @csrf
                <button class="btn {{ $hasLiked ? 'btn-primary' : 'btn-outline-primary' }}" type="submit">{{ $hasLiked ? __('messages.liked') : __('messages.like') }} · {{ $likesCount }}</button>
            </form>
            <button id="share-article" class="btn btn-outline-secondary" type="button">{{ __('messages.share') }}</button>
        </section>

        <section id="comments" class="mt-5">
            <h2 class="h4 mb-4">{{ __('messages.comments') }} ({{ $article->comments->count() }})</h2>
            @if(session('comment_pending'))<div class="alert alert-success">{{ __('messages.comment_pending') }}</div>@endif
            @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

            @forelse($article->comments as $comment)
                <article class="comment-item py-3">
                    <div class="fw-semibold">{{ $comment->author_name }}</div>
                    <p class="mb-0 mt-1" style="white-space: pre-line">{{ $comment->content }}</p>
                </article>
            @empty
                <p class="text-secondary">{{ __('messages.no_comments') }}</p>
            @endforelse

            <form method="POST" action="{{ route('public.articles.comments.store', $article->slug) }}" class="mt-4">
                @csrf
                <div class="mb-3"><label class="form-label" for="comment-name">{{ __('messages.comment_name') }}</label><input id="comment-name" name="author_name" class="form-control" maxlength="100" required></div>
                <div class="mb-3"><label class="form-label" for="comment-content">{{ __('messages.comment_placeholder') }}</label><textarea id="comment-content" name="content" class="form-control" rows="4" maxlength="2000" required></textarea></div>
                <button class="btn btn-primary" type="submit">{{ __('messages.send_comment') }}</button>
            </form>
        </section>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('share-article').addEventListener('click', async () => {
            const shareData = { title: document.title, url: window.location.href };
            if (navigator.share) {
                await navigator.share(shareData);
                return;
            }

            window.open(`https://wa.me/?text=${encodeURIComponent(`${shareData.title} ${shareData.url}`)}`, '_blank', 'noopener');
        });
    </script>
</body>
</html>