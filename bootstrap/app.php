<?php
use Illuminate\Foundation\Application;
use Illuminate\Validation\ValidationException;
return Application::configure(basePath: dirname(__DIR__))
 ->withRouting(web: __DIR__.'/../routes/web.php')
 ->withMiddleware(function ($middleware) {})
 ->withExceptions(function ($exceptions) {
    $exceptions->render(function (DomainException $e) {
        return back()->withErrors($e->getMessage())->withInput();
    });
 })->create();
