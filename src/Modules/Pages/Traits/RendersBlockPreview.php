<?php

namespace RefinedDigital\CMS\Modules\Pages\Traits;

use RefinedDigital\CMS\Modules\Content\Models\Content;
use RefinedDigital\CMS\Modules\Pages\Http\Repositories\PageRepository;

// shared by PagePreviewController and the generic WorkspaceController: renders
// a HasContentBlocks record's blocks wrapped in the comment markers the
// preview shim patches against
trait RendersBlockPreview
{
    /**
     * Full-page preview html for the builder iframe: the record's real
     * template rendered with its saved content, markers and the shim script.
     */
    protected function renderPreviewPage($record): string
    {
        // resolve through findByUri so the preview gets the identical render
        // context (head, assets, classes, listing) the public page gets.
        // ponytail: placeholder / single-page-mode sites preview the page those
        // modes force instead of the requested one - bypass findByUri's top
        // section if that ever matters
        $page = app(PageRepository::class)->findByUri($record->meta->uri ?? '/');

        if (class_basename($page) === 'RedirectResponse') {
            abort(404);
        }

        // the first block heading of this render claims the h1
        format()->resetHeadingTag();

        $grouped = $page->groupContentRows(
            Content::select(['content_class', 'field', 'data', 'position'])
                ->whereContentableId($page->id)
                ->whereContentableType($page::class)
                ->orderBy('position')
                ->get()
        );

        $page->setRenderedContent($this->renderWithMarkers($page, $grouped));

        $view = view('templates::'.$page->meta->template->source)
            ->with(compact('page'))->render();

        $html = $page->assetAggregate->resolvePlaceholders($view);

        // inject the preview shim so the parent admin window can talk to us
        $shim = '<script src="'.refined_asset('vendor/refined/core/js/PreviewShim.js').'"></script>';
        $html = str_replace('</body>', $shim.'</body>', $html);

        // mark the document so site css can keep entrance animations out of the
        // preview (morphdom patches strip js-added reveal classes, leaving
        // animated blocks stuck hidden). sites guard their animation rules with
        // html:not([data-rcms-preview]) so they simply never engage here
        return preg_replace('/<html\b/', '<html data-rcms-preview', $html, 1);
    }

    /**
     * Renders draft content posted by the builder without persisting anything.
     */
    protected function renderDraftBlocks($record, array $content): string
    {
        format()->resetHeadingTag();

        $rows = array_map(
            fn ($row) => new Content($row),
            $record->transformAdminContentToRows($content)
        );

        return $this->renderWithMarkers($record, $record->groupContentRows($rows));
    }

    /**
     * Renders grouped rows with per-block comment markers and the outer region
     * markers, emitted even when there are no blocks yet so the shim always
     * has an insertion point.
     */
    protected function renderWithMarkers($record, array $grouped): string
    {
        $html = $record->renderBlockRows(
            $grouped,
            fn ($blockHtml, $index) => '<!--rb:'.$index.'-->'.$blockHtml.'<!--/rb:'.$index.'-->'
        );

        return '<!--rb-region-->'.$html.'<!--/rb-region-->';
    }
}
