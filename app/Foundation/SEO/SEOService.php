<?php

declare(strict_types=1);

namespace App\Foundation\SEO;

use App\Foundation\Contracts\ServiceInterface;
use Illuminate\Support\Facades\Request;

class SEOService implements ServiceInterface
{
    /**
     * Get the canonical URL for the current request.
     */
    public function getCanonicalUrl(): string
    {
        return Request::url();
    }

    /**
     * Resolve the SEO meta title.
     */
    public function resolveTitle(?string $title = null): string
    {
        $suffix = 'fstio | Fast Technologies';
        if (empty($title)) {
            return $suffix;
        }

        return "{$title} | {$suffix}";
    }

    /**
     * Resolve the SEO meta description.
     */
    public function resolveDescription(?string $description = null): string
    {
        if (empty($description)) {
            return 'Buy computer hardware, laptops, security cameras, and IT products at best price in Bangladesh from Fast Technologies.';
        }

        return $description;
    }

    /**
     * Generate OpenGraph tags array.
     *
     * @return array<string, string>
     */
    public function getOpenGraphTags(string $title, string $description, ?string $image = null): array
    {
        return [
            'og:title' => $title,
            'og:description' => $description,
            'og:image' => $image ?? asset('vendor/fstio/images/logo.svg'),
            'og:type' => 'website',
            'og:url' => $this->getCanonicalUrl(),
        ];
    }

    /**
     * Generate Twitter Card tags array.
     *
     * @return array<string, string>
     */
    public function getTwitterCardTags(string $title, string $description, ?string $image = null): array
    {
        return [
            'twitter:card' => 'summary_large_image',
            'twitter:title' => $title,
            'twitter:description' => $description,
            'twitter:image' => $image ?? asset('vendor/fstio/images/logo.svg'),
        ];
    }

    /**
     * Generate LocalBusiness JSON-LD structure.
     *
     * @return array<string, mixed>
     */
    public function getLocalBusinessSchema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => 'Fast Technologies',
            'image' => asset('vendor/fstio/images/logo.svg'),
            '@id' => route('shop.home.index'),
            'url' => route('shop.home.index'),
            'telephone' => '+8801759190782',
            'priceRange' => '৳৳',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => 'Multiplan Center, Elephant Road',
                'addressLocality' => 'Dhaka',
                'postalCode' => '1205',
                'addressCountry' => 'BD',
            ],
        ];
    }
}
