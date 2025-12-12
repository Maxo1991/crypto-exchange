    <?php

    use Illuminate\Foundation\Application;
    use Illuminate\Support\Facades\Route;
    use Inertia\Inertia;
    use App\Http\Controllers\AssetController;
    use App\Http\Controllers\OrdersController;
    use App\Http\Controllers\TradesController;

    Route::get('/', function () {
        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
        ]);
    });

    Route::middleware(['auth'])->group(function () {
        // Dashboard
        Route::get('/dashboard', [AssetController::class, 'dashboard'])->name('dashboard');

        // Orders controller
        Route::get('/orders', [OrdersController::class, 'index'])->name('orders');
        Route::get('/api/orders', [OrdersController::class, 'getAll']);
        Route::post('/api/orders', [OrdersController::class, 'store']);
        Route::post('/api/orders/{id}/cancel', [OrdersController::class, 'cancel']);

        // Assets controller
        Route::get('/api/profile', [AssetController::class, 'profile']);
        Route::post('/api/assets', [AssetController::class, 'store']);
        Route::delete('/api/assets/{id}', [AssetController::class, 'destroy']);

        // Trades controller
        Route::get('/api/trades', [TradesController::class, 'index']);
    });

    require __DIR__.'/auth.php';

