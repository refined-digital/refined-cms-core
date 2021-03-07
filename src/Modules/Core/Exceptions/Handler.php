<?php

namespace RefinedDigital\CMS\Modules\Core\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\ViewErrorBag;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];



    protected function renderHttpException(HttpExceptionInterface $e)
    {
        $this->registerErrorViewPaths();

        $dir = __DIR__.'/../Resources/views/errors/front-end';
        if (request()->segment(1) == 'refined') {
            $dir = __DIR__.'/../Resources/views/errors/admin';
        }

        // replace the errors with the custom cms ones
        view()->replaceNamespace('errors', [
            base_path('resources/views/errors'),
            $dir,
            app_path('views/errors'),
            base_path('vendor/laravel/framework/src/Illuminate/Foundation/Exceptions/views')
        ]);

        if (view()->exists($view = "errors::{$e->getStatusCode()}")) {
            return response()->view($view, [
                'errors' => new ViewErrorBag,
                'exception' => $e,
            ], $e->getStatusCode(), $e->getHeaders());
        }

        return $this->convertExceptionToResponse($e);
    }
}
