<?php

namespace Darpersodigital\Cms\Services;

use Artisan;
use Schema;
use Auth;
use DB;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

use Darpersodigital\Cms\Models\Language;
use Darpersodigital\Cms\Models\PostType;
use Darpersodigital\Cms\Models\Sitemap;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class SitemapServices
{
    private $base_url;

    private function hasMultipleLocales(array $locales): bool
    {
        return count($locales) > 1;
    }

    public function __construct()
    {
        $this->base_url = rtrim(config('app.url'), '/');
    }

    public function generate(): void
    {

        $locales = Language::get()->pluck('slug')->toArray();
        $postTypes = PostType::query()->get()->keyBy('id');
        $this->deleteSitemaps();
        $this->generateStaticSitemaps($locales);
        $this->generatePostTypesSitemap($locales, $postTypes);
        $this->generateMultipleRecordPostTimeSitemap($locales, $postTypes);
        $this->generateIndexSitemap();
    }
    private function deleteSitemaps(): void
    {
        $files = File::files(public_path());

        foreach ($files as $file) {
            if (str_starts_with($file->getFilename(), 'sitemap') && $file->getFilename()!=="sitemap-manual.xml") {
                File::delete($file->getPathname());
            }
        }
    }

    private function generateStaticSitemaps(array $locales): void
    {
        $staticItems = Sitemap::query()->where('published', 1)->whereNull('post_type_id')->whereNull('post_type_children_id')->get();

        $urls = $staticItems
            ->map(function ($item) use ($locales) {
                $path = '/' . ltrim($item->url ?? '', '/');

                return $this->makeSitemapUrlItem(path: $path, locale: $item->locale, locales: $locales, lastmod: $item->updated_at, changefreq: $item->change_frequency , priority: $item->priority , alternates: $this->buildAlternates($path, $locales));
            })
            ->toArray();

        File::put(public_path('sitemap-static.xml'), $this->buildUrlSetXml($urls));
    }

    private function generatePostTypesSitemap(array $locales, Collection $postTypes): void
    {
        $sitemap_records = Sitemap::where('published', 1)->whereNotNull('post_type_id')->whereNull('post_type_children_id')->get();
        $urls = [];
        foreach ($sitemap_records as $item) {
            $postType = $postTypes->get($item->post_type_id);
            if (!$postType) {
                continue;
            }
            $model = 'App\\Models\\' . $postType['model_name'];
            $row = $model::first();

            if (isset($row)) {
                $pathsByLocale = [];
                foreach ($locales as $locale) {
                    $slug = $this->resolveSlug($row, $locale, $item->url);
                    $pathsByLocale[$locale] = '/' . ltrim($slug ?? '', '/');
                }

                foreach ($locales as $locale) {
                    $updatedAt = $this->resolveUpdatedAt($row, $locale);
                    $urls[] = $this->makeSitemapUrlItem(path: $pathsByLocale[$locale], locale: $locale, locales: $locales, lastmod: $updatedAt, changefreq: $item->change_frequency , priority: $item->priority, alternates: $this->buildAlternatesFromMap($pathsByLocale, $locales));
                }
            }
        }

        File::put(public_path('sitemap-pages.xml'), $this->buildUrlSetXml($urls));
    }

    private function generateMultipleRecordPostTimeSitemap(array $locales, Collection $postTypes): void
    {
        $sitemap_records = Sitemap::where('published', 1)->whereNotNull('post_type_id')->whereNotNull('post_type_children_id')->get();

        foreach ($sitemap_records as $item) {
            $urls = [];
            $parentPostType = $postTypes->get($item->post_type_id);
            $childPostType = $postTypes->get($item->post_type_children_id);
            if (!$parentPostType || !$childPostType) {
                continue;
            }

            $parentModel = 'App\\Models\\' . $parentPostType['model_name'];
            $childModel = 'App\\Models\\' . $childPostType['model_name'];
            $parent = $parentModel::first();
            $rows = $childModel::where('published', 1)->get();

            if (isset($parent) && isset($rows) && count($rows) > 0) {
                foreach ($rows as $row) {
                    $pathsByLocale = [];

                    foreach ($locales as $locale) {
                        $slug = $this->resolveSlug($row, $locale, $item->url);

                        if ($slug !== '') {
                            $pathsByLocale[$locale] = '/' . ltrim($slug, '/');
                        }
                    }

                    if (empty($pathsByLocale)) {
                        continue;
                    }

                    foreach ($pathsByLocale as $locale => $path) {
                        $updatedAt = $this->resolveUpdatedAt($row, $locale);
                        $urls[] = $this->makeSitemapUrlItem(path: $path, locale: $locale, locales: $locales, lastmod: $updatedAt, changefreq: $item->change_frequency , priority: $item->priority , alternates: $this->buildAlternatesFromMap($pathsByLocale, $locales));
                    }
                }
            }
            File::put(public_path('sitemap-' . $childPostType['route'] . '.xml'), $this->buildUrlSetXml($urls));
        }
    }

    private function generateIndexSitemap(): void
    {
        $sitemapFiles = collect(File::files(public_path()))
            ->filter(function ($file) {
                $filename = $file->getFilename();
                return str_starts_with($filename, 'sitemap-') && str_ends_with($filename, '.xml');
            })
            ->sortBy(fn($file) => $file->getFilename())
            ->values()
            ->all();

        File::put(public_path('sitemap.xml'), $this->buildSitemapIndexXml($sitemapFiles));
    }

    private function makeSitemapUrlItem(string $path, ?string $locale, array $locales, mixed $lastmod, $changefreq ='monthly', $priority='0.7', array $alternates = []): array
    {
        return [
            'loc' => $this->generateSitemapItemUrl($path, $this->hasMultipleLocales($locales) ? $locale : null),
            'lastmod' => $lastmod,
            'changefreq' => $changefreq,
            'priority' => $priority ,
            'alternates' => $alternates,
        ];
    }

    private function buildAlternates(string $path, array $locales): array
    {
        if (!$this->hasMultipleLocales($locales)) {
            return [];
        }

        $alternates = [];

        foreach ($locales as $locale) {
            $alternates[] = [
                'hreflang' => $locale,
                'href' => $this->generateSitemapItemUrl($path, $locale),
            ];
        }

        if (!empty($locales)) {
            $alternates[] = [
                'hreflang' => 'x-default',
                'href' => $this->generateSitemapItemUrl($path, $locales[0]),
            ];
        }

        return $alternates;
    }

    private function buildAlternatesFromMap(array $pathsByLocale, array $locales): array
    {
        if (!$this->hasMultipleLocales($locales)) {
            return [];
        }

        $alternates = [];

        foreach ($pathsByLocale as $locale => $path) {
            $alternates[] = [
                'hreflang' => $locale,
                'href' => $this->generateSitemapItemUrl($path, $locale),
            ];
        }

        $defaultLocale = array_key_first($pathsByLocale);

        if ($defaultLocale) {
            $alternates[] = [
                'hreflang' => 'x-default',
                'href' => $this->generateSitemapItemUrl($pathsByLocale[$defaultLocale], $defaultLocale),
            ];
        }

        return $alternates;
    }

    private function generateSitemapItemUrl(string $path, $locale = null): string
    {
        $path = '/' . ltrim($path, '/');
        $base = rtrim($this->base_url, '/');

        if (isset($locale)) {
            return $path === '/' ? "{$base}/{$locale}" : "{$base}/{$locale}{$path}";
        } else {
            return $path === '/' ? "{$base}/" : "{$base}{$path}";
        }
    }

    private function resolveSlug(object $record, string $locale, ?string $fallback = null): string
    {
        $translatedRecord = method_exists($record, 'translate') ? $record->translate($locale) : null;

        if (!empty($translatedRecord?->slug)) {
            return $translatedRecord->slug;
        }
        if (!empty($translatedRecord?->translate_slug)) {
            return $translatedRecord->translate_slug;
        }
        if (!empty($record->slug)) {
            return $record->slug;
        }

        return $fallback ?? '';
    }

    private function resolveUpdatedAt(object $record, string $locale): mixed
    {
        $translatedRecord = method_exists($record, 'translate') ? $record->translate($locale) : null;
        $recordUpdatedAt = $record->updated_at ?? null;
        $translatedUpdatedAt = $translatedRecord->updated_at ?? null;

        if ($recordUpdatedAt && $translatedUpdatedAt) {
            return strtotime($recordUpdatedAt) > strtotime($translatedUpdatedAt) ? $recordUpdatedAt : $translatedUpdatedAt;
        }
        return $translatedUpdatedAt ?? $recordUpdatedAt;
    }

    private function buildUrlSetXml(array $urls): string
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $urlset = $dom->createElement('urlset');
        $urlset->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        $hasAlternates = collect($urls)->contains(function ($url) {
            return !empty($url['alternates']);
        });

        if ($hasAlternates) {
            $urlset->setAttribute('xmlns:xhtml', 'http://www.w3.org/1999/xhtml');
        }

        $dom->appendChild($urlset);

        foreach ($urls as $url) {
            $urlNode = $dom->createElement('url');

            $loc = $dom->createElement('loc', $url['loc']);
            $urlNode->appendChild($loc);

            foreach ($url['alternates'] ?? [] as $alternate) {
                $alt = $dom->createElement('xhtml:link');
                $alt->setAttribute('rel', 'alternate');
                $alt->setAttribute('hreflang', $alternate['hreflang']);
                $alt->setAttribute('href', $alternate['href']);
                $urlNode->appendChild($alt);
            }

            if (!empty($url['lastmod'])) {
                $lastmod = $dom->createElement('lastmod', Carbon::parse($url['lastmod'])->toAtomString());
                $urlNode->appendChild($lastmod);
            }

            if (!empty($url['changefreq'])) {
                $changefreq = $dom->createElement('changefreq', $url['changefreq']);
                $urlNode->appendChild($changefreq);
            }

            if (isset($url['priority']) && $url['priority'] !== '') {
                $priority = $dom->createElement('priority', (string) $url['priority']);
                $urlNode->appendChild($priority);
            }

            $urlset->appendChild($urlNode);
        }

        return $dom->saveXML();
    }

    private function buildSitemapIndexXml(array $sitemapFiles): string
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $index = $dom->createElement('sitemapindex');
        $index->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $dom->appendChild($index);

        foreach ($sitemapFiles as $file) {
            $sitemapNode = $dom->createElement('sitemap');

            $loc = $dom->createElement('loc', $this->generateSitemapItemUrl('/' . ltrim($file->getFilename(), '/')));
            $sitemapNode->appendChild($loc);

            $lastmod = $dom->createElement('lastmod', Carbon::createFromTimestamp($file->getMTime())->toAtomString());
            $sitemapNode->appendChild($lastmod);

            $index->appendChild($sitemapNode);
        }

        return $dom->saveXML();
    }
}
