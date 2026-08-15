<div class="ba-card-expand" hidden>
    <div class="ba-expand-stats">
        <div><span>Target</span><strong>{{ number_format($card['target_beneficiaries']) }}</strong></div>
        <div><span>Actual</span><strong>{{ number_format($card['beneficiaries']) }}</strong></div>
        <div><span>Budget</span><strong>₱{{ number_format($card['approved_budget'], 2) }}</strong></div>
        <div><span>Spent</span><strong>₱{{ number_format($card['expenditure'], 2) }}</strong></div>
    </div>

    @if ($card['objectives'] || $card['summary'] || $card['actual_result'])
        <div class="ba-expand-copy">
            @if ($card['objectives'])
                <h4>Program Objective</h4>
                <p>{{ $card['objectives'] }}</p>
            @endif
            @if ($card['summary'])
                <h4>Implementation Summary</h4>
                <p>{{ $card['summary'] }}</p>
            @endif
            @if ($card['actual_result'])
                <h4>Actual Result</h4>
                <p>{{ $card['actual_result'] }}</p>
            @endif
        </div>
    @endif

    <p class="ba-expand-facts">
        {{ $card['implementation_label'] ?: $card['date_label'] }}
        · {{ $card['location'] }}
        · {{ $card['committee'] }}
        · Remaining ₱{{ number_format($card['remaining'], 2) }}
    </p>

    @if (count($card['photos']) > 0)
        @php
            $photos = collect($card['photos']);
            $photoCount = $photos->count();
            $visiblePhotos = $photos->take(5);
            $extraCount = max(0, $photoCount - 5);
            $gridCount = min(5, $photoCount);
        @endphp
        <div class="ba-gallery" data-gallery data-photos='@json($photos->values())'>
            <h4>Photo Gallery</h4>
            <div class="ba-fb-grid" data-count="{{ $gridCount }}">
                @foreach ($visiblePhotos as $index => $photo)
                    <button
                        type="button"
                        class="ba-fb-item"
                        data-gallery-index="{{ $index }}"
                        aria-label="Preview photo {{ $index + 1 }}"
                    >
                        <img src="{{ $photo['src'] }}" alt="" loading="lazy">
                        @if ($index === 4 && $extraCount > 0)
                            <span class="ba-fb-more">+{{ $extraCount }}</span>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>
    @endif
</div>
