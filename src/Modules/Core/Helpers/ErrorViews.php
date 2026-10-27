<?php

namespace RefinedDigital\CMS\Modules\Core\Helpers;

use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ErrorViews
{
    protected string $publicViewPath = 'core::errors.front-end';
    protected string $adminViewPath = 'core::errors.admin';

    public function register(Exceptions $exceptions) {
        $exceptions
            ->render(function(HttpException $exception, $request) {
                return $this->render($exception, $request);
            });
        ;
    }

    public function render($exception, Request $request)
    {
        $statusCode = $exception->getStatusCode();
        $view = $this->findView($request, $statusCode);
        if (!$request->is('api/*') && view()->exists($view)) {
            return response()->view($view, ['exception' => $exception], $statusCode);
        }
    }

    public function findView(Request $request, int $statusCode): string
    {
        $path = $request->is('refined/*') ? $this->adminViewPath : $this->publicViewPath;

        return $path.'.'.$statusCode;
    }

    /**
     * Renders the error through the site's Content block so it is styled like
     * body copy on a normal page, falling back to a plain heading and paragraph.
     */
    public function pageContent(Page404 $page, string $message): string
    {
        $body = '<p>'.e($message).'</p>';
        $class = 'App\\RefinedCMS\\Content\\Blocks\\Content\\Content';
        $template = 'content::Content.Views.content';

        if (!class_exists($class) || !view()->exists($template)) {
            return '<h1>'.e($page->title).'</h1>'.$body;
        }

        // the block view may read any of its fields, so start them all empty
        $content = new \stdClass();
        foreach ((new $class())->getFields() as $field) {
            $content->{Str::snake($field['field'])} = null;
        }
        $content->heading = e($page->title);
        $content->content = $body;

        // the error heading claims the h1
        format()->resetHeadingTag();

        return view($template, [
            'page' => $page,
            'content' => $content,
            'index' => 0,
            'classes' => ['page__block', 'page__block--content'],
            'colours' => [],
            'colourStyles' => '',
        ])->render();
    }
}
