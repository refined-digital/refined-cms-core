<?php

namespace RefinedDigital\CMS\Modules\Core\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\ViewErrorBag;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function(Throwable $e) {
            //
        });
    }

    /**
     * Render the given HttpException.
     *
     * @param  \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface  $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function renderHttpException(HttpExceptionInterface $e)
    {
        $this->registerErrorViewPaths();

        if (!request()->segment(1) === 'api') {
            $dir = __DIR__.'/../Resources/views/errors/front-end';
            if(request()->segment(1) == 'refined') {
                $dir = __DIR__.'/../Resources/views/errors/admin';
            }

            // replace the errors with the custom cms ones
            view()->replaceNamespace('errors', [
                base_path('resources/views/errors'),
                $dir,
                app_path('views/errors'),
                base_path('vendor/laravel/framework/src/Illuminate/Foundation/Exceptions/views')
            ]);
        }

        if(view()->exists($view = "errors::{$e->getStatusCode()}")) {
            return response()->view($view, [
                'errors'    => new ViewErrorBag,
                'exception' => $e,
            ], $e->getStatusCode(), $e->getHeaders());
        }

        return $this->convertExceptionToResponse($e);
    }
}
