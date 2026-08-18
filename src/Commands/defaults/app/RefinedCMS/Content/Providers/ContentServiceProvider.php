<?php

namespace App\RefinedCMS\Content\Providers;

use App\RefinedCMS\Content\Blocks\Banner\Banner;
use App\RefinedCMS\Content\Blocks\Banners\Banners;
use App\RefinedCMS\Content\Blocks\Content\Content;
use App\RefinedCMS\Content\Blocks\Form\Form;
use App\RefinedCMS\Content\Blocks\FullWidthImage\FullWidthImage;
use App\RefinedCMS\Content\Blocks\PlainContent\PlainContent;
use App\RefinedCMS\Content\Blocks\VideoBanner\VideoBanner;
use Illuminate\Support\ServiceProvider;
use RefinedDigital\CMS\Modules\Core\Aggregates\ContentAggregate;
use RefinedDigital\CMS\Modules\Core\Enums\PageContentType;

class ContentServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        view()->addNamespace('content', [
            __DIR__.'/../Blocks',
            base_path().'/resources/views/blocks',
        ]);

        view()->addNamespace('content-templates', [
            __DIR__.'/../resources/templates',
            base_path().'/resources/views/blocks',
        ]);
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $agg = app(ContentAggregate::class);

        // register any custom global fields
        $agg
            ->registerNewField('heading', [
                'name' => 'Heading',
                'type' => PageContentType::STATIC,
            ])
            ->registerNewField('title', [
                'name' => 'Title',
                'type' => PageContentType::STATIC,
            ])
            ->registerNewField('content', [
                'name' => 'Content',
                'type' => PageContentType::RICH,
            ])
            ->registerNewField('link', [
                'name' => 'Link',
                'type' => PageContentType::LINK,
            ])
            ->registerNewField(
                'background',
                [
                    'name' => 'Background Colour',
                    'type' => PageContentType::COLOUR_SET,
                ]
            );

        // register the content fields
        // $agg->register(Accordion::class);
        $agg->register(Banner::class);
        $agg->register(Banners::class);
        $agg->register(Content::class);
        $agg->register(Form::class);
        $agg->register(FullWidthImage::class);
        $agg->register(PlainContent::class);
        $agg->register(VideoBanner::class);
    }
}
