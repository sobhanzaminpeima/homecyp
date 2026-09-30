<?php

namespace App\Filament\Pages;

use App\Models\Post;
use App\Models\Project;
use App\Models\Property;
use Filament\Pages\Page;

class SeoDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass-circle';
    protected static ?string $navigationGroup = 'Tools';
    protected static ?string $navigationLabel = 'SEO Status';
    protected static ?string $title = 'SEO Status & Health';
    protected static ?int $navigationSort = 0;
    protected static string $view = 'filament.pages.seo-dashboard';

    public function getSeoData(): array
    {
        return [
            'projects' => $this->auditProjects(),
            'properties' => $this->auditProperties(),
            'posts' => $this->auditPosts(),
            'global' => $this->auditGlobal(),
        ];
    }

    protected function auditProjects(): array
    {
        $items = [];
        foreach (Project::with('translations')->get() as $p) {
            $t = $p->translation('en');
            $items[] = $this->scoreRow(
                type: 'Project',
                title: $t?->title ?? $p->slug,
                editUrl: route('filament.admin.resources.projects.edit', $p),
                metaTitle: $t?->meta_title,
                metaDescription: $t?->meta_description,
                hasImage: $p->getMedia('cover')->count() > 0 || $p->getMedia('gallery')->count() > 0,
                bodyLen: strlen(strip_tags($t?->description ?? '')),
            );
        }
        return $items;
    }

    protected function auditProperties(): array
    {
        $items = [];
        foreach (Property::with('translations')->where('status', 'active')->limit(50)->get() as $p) {
            $t = $p->translation('en');
            $items[] = $this->scoreRow(
                type: 'Property',
                title: $t?->title ?? $p->slug,
                editUrl: route('filament.admin.resources.properties.edit', $p),
                metaTitle: $t?->meta_title,
                metaDescription: $t?->meta_description,
                hasImage: $p->getMedia('cover')->count() > 0 || $p->getMedia('gallery')->count() > 0,
                bodyLen: strlen(strip_tags($t?->description ?? '')),
            );
        }
        return $items;
    }

    protected function auditPosts(): array
    {
        $items = [];
        foreach (Post::with('translations')->get() as $p) {
            $t = $p->translation('en');
            $items[] = $this->scoreRow(
                type: 'Post',
                title: $t?->title ?? $p->slug,
                editUrl: route('filament.admin.resources.posts.edit', $p),
                metaTitle: $t?->meta_title,
                metaDescription: $t?->meta_description,
                hasImage: $p->getMedia('cover')->count() > 0,
                bodyLen: strlen(strip_tags($t?->body ?? '')),
            );
        }
        return $items;
    }

    protected function scoreRow(string $type, string $title, string $editUrl, ?string $metaTitle, ?string $metaDescription, bool $hasImage, int $bodyLen): array
    {
        $checks = [
            'meta_title' => $metaTitle && mb_strlen($metaTitle) >= 30 && mb_strlen($metaTitle) <= 60,
            'meta_description' => $metaDescription && mb_strlen($metaDescription) >= 70 && mb_strlen($metaDescription) <= 160,
            'image' => $hasImage,
            'content' => $bodyLen >= 300,
        ];
        $passed = count(array_filter($checks));
        $score = (int) round($passed / count($checks) * 100);

        return [
            'type' => $type,
            'title' => $title,
            'editUrl' => $editUrl,
            'checks' => $checks,
            'score' => $score,
            'metaTitleLen' => $metaTitle ? mb_strlen($metaTitle) : 0,
            'metaDescLen' => $metaDescription ? mb_strlen($metaDescription) : 0,
        ];
    }

    protected function auditGlobal(): array
    {
        return [
            'sitemap' => route('sitemap'),
            'robots' => url('/robots.txt'),
            'analytics' => (bool) \App\Models\SiteSetting::get('google_analytics_id'),
            'pixel' => (bool) \App\Models\SiteSetting::get('meta_pixel_id'),
            'recaptcha' => \App\Services\RecaptchaService::enabled(),
        ];
    }
}
