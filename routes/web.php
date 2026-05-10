<?php

use App\Http\Controllers\TelegramAuthController;
use App\Http\Controllers\FeedController;
use App\Services\UpworkProvider;
use App\Services\UpworkService;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::resource('feeds', FeedController::class);
    Route::patch('/feeds/{id}/toggle', [FeedController::class, 'toggle'])->name('feeds.toggle');

    Route::get('/oauth/upwork', function () {
        $provider = new UpworkProvider;

        // Redirect the user to the authorization URL
        $authorizationUrl = $provider->getAuthorizationUrl();
        session(['oauth2state' => $provider->getState()]);

        return redirect($authorizationUrl);
    });

    Route::get('setup', function () {
        $url = url(config('telegram.bots.mybot.token').'/webhook');
        Telegram::setWebhook(['url' => $url]);

        return 'ok';
    });
});

Route::post(config('telegram.bots.mybot.token').'/webhook', function () {
    Telegram::commandsHandler(true);

    return 'ok';
});

Route::get('/oauth/upwork/callback', function (UpworkService $upwork) {
    // Check state parameter
    $state = session('oauth2state');
    if (empty($state) || request('state') !== $state) {
        request()->session()->forget('oauth2state');
        abort(403, 'Invalid state');
    }

    try {
        $token = $upwork->getAccessToken(request('code'));

        return response()->json(['access_token' => $token->getToken()]);
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

// telegram web app auth
Route::post('tg-login', [TelegramAuthController::class, 'telegramAuth'])
    ->name('login.telegram');

require __DIR__.'/settings.php';
