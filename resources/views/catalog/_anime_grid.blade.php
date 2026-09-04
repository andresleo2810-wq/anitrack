@foreach($animeList as $anime)
    <div data-mal-id="{{ $anime['mal_id'] }}">
        <x-anime-card
            :mal-id="$anime['mal_id']"
            :title="$anime['title'] ?? 'Sin título'"
            :image="$anime['images']['jpg']['image_url'] ?? null"
            :score="$anime['score'] ?? null"
            :type="$anime['type'] ?? null"
        />
    </div>
@endforeach