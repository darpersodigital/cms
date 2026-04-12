@php
    $currentLocale = App::getLocale();
    $translated_page = $page ? $page->translate($currentLocale) ?? null : null;
    $translated_seo_settings = $seo_settings ? $seo_settings->translate($currentLocale) ?? null : null;

    if (isset($translated_page['seo_title']) && !empty($translated_page['seo_title'])) {
        $seo_title = $translated_page['seo_title'];
    } elseif (isset($translated_seo_settings['seo_title']) && !empty($translated_seo_settings['seo_title'])) {
        $seo_title = $translated_seo_settings['seo_title'];
    }
    if (isset($translated_page['seo_description']) && !empty($translated_page['seo_description'])) {
        $seo_description = $translated_page['seo_description'];
    } elseif (
        isset($translated_seo_settings['seo_description']) &&
        !empty($translated_seo_settings['seo_description'])
    ) {
        $seo_description = $translated_seo_settings['seo_description'];
    }
    if (isset($translated_page['seo_keywords']) && !empty($translated_page['seo_keywords'])) {
        $seo_keywords = $translated_page['seo_keywords'];
    } elseif (isset($translated_seo_settings['seo_keywords']) && !empty($translated_seo_settings['seo_keywords'])) {
        $seo_keywords = $translated_seo_settings['seo_keywords'];
    }
    if (isset($translated_page['seo_robots']) && !empty($translated_page['seo_robots'])) {
        $seo_robots = $translated_page['seo_robots'];
    } elseif (isset($translated_seo_settings['seo_robots']) && !empty($translated_seo_settings['seo_robots'])) {
        $seo_robots = $translated_seo_settings['seo_robots'];
    }
    if (isset($translated_page['seo_image']) && !empty($translated_page['seo_image'])) {
        $seo_image = Storage::url($translated_page['seo_image']);
    } elseif (isset($translated_seo_settings['seo_image']) && !empty($translated_seo_settings['seo_image'])) {
        $seo_image = Storage::url($translated_seo_settings['seo_image']);
    }
    if (isset($translated_page['seo_author']) && !empty($translated_page['seo_author'])) {
        $seo_author = $translated_page['seo_author'];
    } elseif (isset($translated_seo_settings['seo_author']) && !empty($translated_seo_settings['seo_author'])) {
        $seo_author = $translated_seo_settings['seo_author'];
    }
@endphp
@if (isset($seo_title))
    <title>{{ $seo_title }}</title>
@endif
<!-- SEO Meta Tags -->
@if (isset($seo_description))
    <meta name="description" content="{{ $seo_description }}">
@endif
@if (isset($seo_keywords))
    <meta name="keywords" content="{{ $seo_keywords }}">
@endif
@if (isset($seo_robots))
    <meta name="robots" content="{{ $seo_robots }}">
@endif
@if (isset($seo_author))
    <meta name="author" content="{{ $seo_author }}">
@endif
<!-- Open Graph Meta Tags for social media -->
@if (isset($seo_title))
    <meta property="og:title" content="{{ $seo_title }}">
@endif
@if (isset($seo_description))
    <meta property="og:description" content="{{ $seo_description }}">
@endif
@if (isset($seo_image))
    <meta property="og:image" content="{{ $seo_image }}">
@endif
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:type" content="website">
<!-- Twitter Card Meta Tags -->
@if (isset($seo_title))
    <meta name="twitter:title" content="{{ $seo_title }}">
    <meta name="twitter:url" content="{{ url()->current() }}">
@endif
@if (isset($seo_description))
    <meta name="twitter:description" content="{{ $seo_description }}">
@endif
@if (isset($seo_image))
    <meta name="twitter:image" content="{{ $seo_image }}">
    <meta name="twitter:card" content="summary_large_image">
@endif
<!-- Canonical Link (helps with SEO by specifying the preferred URL) -->
<link rel="canonical" href="{{ url()->current() }}">
