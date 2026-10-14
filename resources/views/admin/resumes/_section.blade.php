<section class="resume-section resume-section-{{ $resumeSection['key'] }}">
    <h2 style="border-color:{{ $secondaryColor }}; color:{{ $primaryColor }};">{{ $resumeSection['title'] }}</h2>
    @foreach($resumeSection['items'] as $item)
        <article class="entry">
            <h3>{{ $item['title'] }}</h3>
            @if($item['subtitle'] || $item['meta'])
                <p class="meta">{{ implode(' · ', array_filter([$item['subtitle'], $item['meta']])) }}</p>
            @endif
            @if($item['description'])<p>{{ $item['description'] }}</p>@endif
            @if($item['items'])
                <ul>
                    @foreach($item['items'] as $line)<li>{{ $line }}</li>@endforeach
                </ul>
            @endif
        </article>
    @endforeach
</section>
