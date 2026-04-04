<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Http\Kernel::class);
// Bypass CSRF by removing the VerifyCsrfToken middleware
$app->instance(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class, new class {
    public function handle($request, $next) { return $next($request); }
});
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/admin/department/edit/14', 'POST', [], [], [], [
    'HTTP_ACCEPT' => 'application/json',
    'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
    'CONTENT_TYPE' => 'application/json'
], json_encode([
    'id' => 14,
    'organization_id' => '1',
    'sbu_id' => '1',
    'name' => 'Team Leader',
    'code' => 'Test-Code'
]));

try {
    $response = $kernel->handle($request);
    echo "Status: " . $response->getStatusCode() . "\n";
    echo "Body: " . $response->getContent() . "\n";
} catch (\Exception $e) {
    if ($e instanceof \Illuminate\Validation\ValidationException) {
        echo "Validation failed: " . json_encode($e->errors());
    } else {
        echo "Error: " . $e->getMessage();
    }
}
