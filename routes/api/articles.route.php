<?php

use App\Http\Controllers\ArticleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Articles routes
Route::prefix('articles')->middleware('auth:sanctum')->group(function () {
    // Publicly accessible articles
    Route::get('/', [ArticleController::class, 'getPublishedArticles']); // GET /api/articles (only published articles for public view)
    Route::get('/popular', [ArticleController::class, 'popularArticles']); // GET /api/articles/popular
    
    // Actions that require authentication and specific roles (staff for create/edit/delete, student/alumni for like/unlike)
    Route::middleware('role:staff|admin')->group(function () {
        Route::get('/all', [ArticleController::class, 'getAllArticles']); // GET /api/articles/all (all articles for staff/admin)
        Route::post('/add', [ArticleController::class, 'createArticle']); // POST /api/articles/add
        Route::put('/{article}', [ArticleController::class, 'editArticle']); // PUT /api/articles/{article}
        Route::delete('/{article}', [ArticleController::class, 'deleteArticle']); // DELETE /api/articles/{article}
        Route::post('/{article}/publish', [ArticleController::class, 'publishArticle']); // POST /api/articles/{article}/publish
        Route::post('/{article}/archive', [ArticleController::class, 'archiveArticle']); // POST /api/articles/{article}/archive
        Route::post('/{article}/unarchive', [ArticleController::class, 'unarchiveArticle']); // POST /api/articles/{article}/unarchive
        Route::post('/{article}/image', [ArticleController::class, 'changeArticleImage']); // POST /api/articles/{article}/image
    });

    Route::middleware('role:student|alumni')->group(function () {
        Route::post('/{article}/like', [ArticleController::class, 'likeArticle']); // POST /api/articles/{article}/like
        Route::post('/{article}/unlike', [ArticleController::class, 'unlikeArticle']); // POST /api/articles/{article}/unlike
    });

    // This route should be last among the GET routes with wildcards
    Route::get('/{article}', [ArticleController::class, 'viewArticle']); // GET /api/articles/{article}
});
