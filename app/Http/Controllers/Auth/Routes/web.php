use App\Http\Controllers\LoadController;
use App\Http\Controllers\PodController;

Route::middleware(['auth','verified'])->group(function(){
    Route::get('/loads',[LoadController::class,'index'])->name('loads.index');
    Route::get('/loads/{load}',[LoadController::class,'show'])->name('loads.show');
    Route::post('/loads/{load}/checkout-link',[LoadController::class,'createCheckoutLink'])->name('loads.checkout-link');

    Route::get('/loads/{load}/pod/sign',[PodController::class,'signPage'])->name('pod.sign.page');
    Route::post('/loads/{load}/pod',[PodController::class,'submit'])->name('pod.submit');
    Route::post('/pods/{pod}/share',[PodController::class,'share'])->name('pod.share');
});
