@props(['number', 'title', 'description'])

<section class="recipe-form-section">
    <header class="recipe-form-section-heading">
        <span class="recipe-form-section-number">{{ $number }}</span>
        <div><h2>{{ $title }}</h2><p>{{ $description }}</p></div>
    </header>
    <div class="recipe-form-section-content">{{ $slot }}</div>
</section>
