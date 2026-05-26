@php
    $schNavItems = [
        ['id' => 'personal', 'label' => 'Personal Information'],
        ['id' => 'educational', 'label' => 'Educational Background'],
        ['id' => 'background', 'label' => 'Background Information'],
        ['id' => 'additional', 'label' => 'Additional Information'],
        ['id' => 'requirements', 'label' => 'Requirements'],
    ];
@endphp

<aside class="sk-side" id="skSideNav">
    <div class="sk-side__panel">
        <nav class="sk-side__nav" aria-label="Application sections">
            @foreach ($schNavItems as $index => $item)
            <button type="button" class="sk-side__link {{ $index === 0 ? 'is-active' : '' }}" data-section="{{ $item['id'] }}">
                <span class="sk-side__link-body">
                    <span class="sk-side__step">Step {{ $index + 1 }}</span>
                    <span class="sk-side__name">{{ $item['label'] }}</span>
                </span>
            </button>
            @endforeach
        </nav>
    </div>
</aside>

