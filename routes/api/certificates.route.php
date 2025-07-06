<?php

use App\Http\Controllers\CertificateController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// certificates routes
Route::prefix('certificates')->middleware('auth:sanctum')->group(function () {
    Route::get('/my-certificates', [CertificateController::class, 'getMyCertificates']);         // GET /api/certificates/my-certificates
    Route::get('/user/{user}', [CertificateController::class, 'getUserCertificates']);     // GET /api/certificates/user/{user}
    Route::post('/add', [CertificateController::class, 'createCertificate']);              // POST /api/certificates/add
    Route::get('/{certificate}', [CertificateController::class, 'viewCertificate']);             // GET /api/certificates/{certificate}
    Route::put('/{certificate}', [CertificateController::class, 'editCertificate']);             // PUT /api/certificates/{certificate}
    Route::delete('/{certificate}', [CertificateController::class, 'deleteCertificate']);        // DELETE /api/certificates/{certificate}
    Route::post('/{certificate}/image', [CertificateController::class, 'changeCertificateImage']); // POST /api/certificates/{certificate}/image
});
