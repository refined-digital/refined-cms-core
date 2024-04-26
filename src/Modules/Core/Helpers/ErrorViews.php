<?php

namespace RefinedDigital\CMS\Modules\Core\Helpers;

use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;
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
}
