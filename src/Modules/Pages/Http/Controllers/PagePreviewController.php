<?php

namespace RefinedDigital\CMS\Modules\Pages\Http\Controllers;

use Illuminate\Routing\Controller;
use RefinedDigital\CMS\Modules\Content\Models\Content;
use RefinedDigital\CMS\Modules\Pages\Http\Repositories\PageRepository;
use RefinedDigital\CMS\Modules\Pages\Models\Page;

class PagePreviewController extends Controller
{
    protected $pageRepository;

    public function __construct(PageRepository $pageRepository)
    {
        $this->pageRepository = $pageRepository;
        $this->pageRepository->setModel(Page::class);
    }

    /**
     * Full-page preview for the builder iframe. Renders the page's real
     * template with its saved content, each block wrapped in comment markers
     * the preview shim uses to patch and select blocks.
     */
    public function show($id)
    {
        $target = Page::findOrFail($id);

        // resolve through findByUri so the preview gets the identical render
        // context (head, assets, classes, listing) the public page gets.
        // ponytail: placeholder / single-page-mode sites preview the page those
        // modes force instead of the requested one - bypass findByUri's top
        // section if that ever matters
        $page = $this->pageRepository->findByUri($target->meta->uri ?? '/');

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

        return str_replace('</body>', $shim.'</body>', $html);
    }

    /**
     * Renders draft content posted by the builder without persisting anything.
     * Returns just the blocks region HTML for the shim to patch in.
     */
    public function render($id)
    {
        try {
            $page = Page::findOrFail($id);

            format()->resetHeadingTag();

            $rows = array_map(
                fn ($row) => new Content($row),
                $page->transformAdminContentToRows(request()->input('content', []))
            );

            $html = $this->renderWithMarkers($page, $page->groupContentRows($rows));

            return response()->json(['success' => 1, 'html' => $html]);
        } catch (\Throwable $e) {
            // a half-typed value blowing up a blade must not break the builder;
            // the admin keeps its last good preview and shows a quiet warning
            return response()->json(['success' => 0, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * Renders grouped rows with per-block comment markers and the outer region
     * markers, emitted even when the page has no blocks yet so the shim always
     * has an insertion point.
     */
    private function renderWithMarkers(Page $page, array $grouped): string
    {
        $html = $page->renderBlockRows(
            $grouped,
            fn ($blockHtml, $index) => '<!--rb:'.$index.'-->'.$blockHtml.'<!--/rb:'.$index.'-->'
        );

        return '<!--rb-region-->'.$html.'<!--/rb-region-->';
    }
}
