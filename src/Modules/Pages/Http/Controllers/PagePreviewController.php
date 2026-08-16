<?php

namespace RefinedDigital\CMS\Modules\Pages\Http\Controllers;

use Illuminate\Routing\Controller;
use RefinedDigital\CMS\Modules\Pages\Models\Page;
use RefinedDigital\CMS\Modules\Pages\Traits\RendersBlockPreview;

class PagePreviewController extends Controller
{
    use RendersBlockPreview;

    /**
     * Full-page preview for the builder iframe.
     */
    public function show($id)
    {
        return $this->renderPreviewPage(Page::findOrFail($id));
    }

    /**
     * Renders draft content posted by the builder without persisting anything.
     * Returns just the blocks region HTML for the shim to patch in.
     */
    public function render($id)
    {
        try {
            $page = Page::findOrFail($id);
            $html = $this->renderDraftBlocks($page, request()->input('content', []));

            return response()->json(['success' => 1, 'html' => $html]);
        } catch (\Throwable $e) {
            // a half-typed value blowing up a blade must not break the builder;
            // the admin keeps its last good preview and shows a quiet warning
            return response()->json(['success' => 0, 'msg' => $e->getMessage()]);
        }
    }
}
