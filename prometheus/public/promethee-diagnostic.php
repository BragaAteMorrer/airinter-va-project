<?php

header('Content-Type: text/plain; charset=utf-8');

echo "===== REQUETE /login VIA KERNEL =====\n\n";

try {
    require __DIR__ . '/../vendor/autoload.php';

    $app = require_once __DIR__ . '/../bootstrap/app.php';

    $kernel = $app->make(
        Illuminate\Contracts\Http\Kernel::class
    );

    $request = Illuminate\Http\Request::create(
        'https://promethee.airinter-va.org/login',
        'GET'
    );

    echo "Avant handle()\n";
    echo "URI = " . $request->getRequestUri() . "\n\n";

    $response = $kernel->handle($request);

    echo "===== RESPONSE =====\n";
    echo "STATUS = " . $response->getStatusCode() . "\n";
    echo "CLASS = " . get_class($response) . "\n\n";

    echo "===== APRES HANDLE =====\n";

    echo "finder = ";
    try {
        echo get_class(app('view.finder')) . "\n";
    } catch (Throwable $e) {
        echo "ERREUR: " . $e->getMessage() . "\n";
    }

    echo "theme = ";
    try {
        var_dump(
            Igaster\LaravelTheme\Facades\Theme::get()
        );
    } catch (Throwable $e) {
        echo "ERREUR: " . $e->getMessage() . "\n";
    }

    echo "auth.login exists = ";
    try {
        var_dump(view()->exists('auth.login'));
    } catch (Throwable $e) {
        echo "ERREUR: " . $e->getMessage() . "\n";
    }

    echo "\n===== BODY =====\n";

    $body = $response->getContent();

    echo substr($body, 0, 10000);

    $kernel->terminate($request, $response);

} catch (Throwable $e) {

    echo "===== EXCEPTION =====\n";
    echo "CLASS: " . get_class($e) . "\n";
    echo "MESSAGE: " . $e->getMessage() . "\n";
    echo "FILE: " . $e->getFile() . "\n";
    echo "LINE: " . $e->getLine() . "\n";

    echo "\n===== ETAT AU MOMENT DU CRASH =====\n";

    echo "finder = ";
    try {
        echo get_class(app('view.finder')) . "\n";
    } catch (Throwable $x) {
        echo "ERREUR: " . $x->getMessage() . "\n";
    }

    echo "theme = ";
    try {
        var_dump(
            Igaster\LaravelTheme\Facades\Theme::get()
        );
    } catch (Throwable $x) {
        echo "ERREUR: " . $x->getMessage() . "\n";
    }

    echo "auth.login exists = ";
    try {
        var_dump(view()->exists('auth.login'));
    } catch (Throwable $x) {
        echo "ERREUR: " . $x->getMessage() . "\n";
    }

    echo "\n===== TRACE =====\n";
    echo $e->getTraceAsString();
}

echo "\n\n===== FIN =====\n";
