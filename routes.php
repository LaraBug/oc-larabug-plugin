<?php
$enableRoute = Config::get('larabug.larabug::config.enableTestRoute');
if (is_null($enableRoute)) {
    $enableRoute = Config::get('app.debug');
}
if ($enableRoute) {
    Route::get('/debug-larabug', function () {
        throw new Exception('LaraBug test exception, check www.larabug.com to confirm successful report');
    })->middleware('web');
}