<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create(
    '/api/plans/18/belongings',
    'GET',
    [],
    [],
    [],
    ['HTTP_AUTHORIZATION' => 'Bearer test-token']
);

// Mock authentication
$user = App\Models\User::find(3);
$request->setUserResolver(function () use ($user) {
    return $user;
});

$response = $kernel->handle($request);
$content = json_decode($response->getContent(), true);

echo "API Response for user_id=3:\n";
if (isset($content['data'])) {
    foreach ($content['data'] as $item) {
        echo sprintf("  ID: %d, Name: %s, user_is_checked: %s\n", 
            $item['id'], 
            $item['name'], 
            isset($item['user_is_checked']) && $item['user_is_checked'] ? 'true' : 'false'
        );
    }
} else {
    echo "Error: " . json_encode($content) . "\n";
}

$kernel->terminate($request, $response);
