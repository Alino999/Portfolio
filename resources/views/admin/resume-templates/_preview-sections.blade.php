@foreach($sections as $section)
    <section class="preview-section preview-section-{{ $section['key'] }}">
        <h2>{{ $section['title'] }}</h2>
        @foreach($section['items'] as $item)
            <div class="preview-entry">
                <h3>{{ $item['title'] }}</h3>
                @if($item['subtitle'] || $item['meta'])
                    <p class="preview-meta">{{ implode(' · ', array_filter([$item['subtitle'], $item['meta']])) }}</p>
                @endif
                @if($item['description'])<p>{{ $item['description'] }}</p>@endif
                @if($item['items'])
                    <ul>
                        @foreach($item['items'] as $line)<li>{{ $line }}</li>@endforeach
                    </ul>
                @endif
            </div>
        @endforeach
    </section>
@endforeach
