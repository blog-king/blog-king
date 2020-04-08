<div class="row no-gutters border rounded overflow-hidden flex-md-row mb-4 shadow-sm h-md-250 position-relative">
    <div class="col p-4 d-flex flex-column position-static">
        <strong class="d-inline-block mb-2 text-{{ $color ?? 'primary' }}">{{ $post->tags_text }}</strong>
        <h3 class="mb-0">{{ $post->title }}</h3>
        <div class="mb-1 text-muted">{{ $post->created_at->diffForHumans() }}</div>
        <p class="card-text mb-auto post-description overflow-hidden">{{ $post->description }}</p>
        <a href="#" class="stretched-link">继续阅读</a>
    </div>
    <div class="col-auto d-none d-lg-block align-self-center pr-3">
        @if ($post->thumbnail)
            <img class="post-thumbnail img-thumbnail" src="{{ $post->thumbnail }}"
                 alt="{{ $post->title }}">
        @else
            <svg class="bd-placeholder-img" width="200" height="250"
                 xmlns="http://www.w3.org/2000/svg"
                 preserveAspectRatio="xMidYMid slice" focusable="false" role="img"
                 aria-label="Placeholder: Thumbnail"><title>{{ $post->title }}</title>
                <rect width="100%" height="100%" fill="#55595c"/>
                <text x="50%" y="50%" fill="#eceeef" dy=".3em">封面</text>
            </svg>
        @endif
    </div>
</div>

