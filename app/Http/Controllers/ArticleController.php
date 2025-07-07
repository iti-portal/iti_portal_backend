<?php

namespace App\Http\Controllers;

use App\Http\Requests\Articles\StoreArticleRequest;
use App\Http\Requests\Articles\UpdateArticleRequest;
use App\Http\Requests\Articles\ChangeArticleImageRequest;
use App\Http\Requests\Articles\ArticleStatusRequest;
use App\Models\Article;
use App\Models\ArticleLike;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ArticleController extends Controller
{
    /**
     * Get published articles, with an indicator if liked by the user (if student/alumni).
     */
    public function getPublishedArticles(Request $request): JsonResponse
    {
        try {
            $articles = Article::with('author')->where('status', 'published')->get();
            $user = $request->user();

            if ($user && ($user->isStudentOrAlumni())) {
                $articles->map(function ($article) use ($user) {
                    $article->is_liked_by_user = $article->likes()->where('user_id', $user->id)->exists();
                    return $article;
                });
            }

            return response()->json([
                'success' => true,
                'message' => 'Articles retrieved successfully.',
                'data' => $articles
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving articles: ' . $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Get all articles, without an indicator if liked by the user because it will be used in Admin & Staff Dashboard.
     */
    public function getAllArticles(Request $request): JsonResponse
    {
        try {
            $articles = Article::with('author')->get();

            return response()->json([
                'success' => true,
                'message' => 'Articles retrieved successfully.',
                'data' => $articles
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving articles: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * View a specific article, with an indicator if liked by the user (if student/alumni).
     */
    public function viewArticle(Request $request, Article $article): JsonResponse
    {
        try {
            $user = $request->user();

            if ($user && ($user->isStudentOrAlumni())) {
                $article->is_liked_by_user = $article->likes()->where('user_id', $user->id)->exists();
            }

            return response()->json([
                'success' => true,
                'message' => 'Article retrieved successfully.',
                'data' => $article
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving article: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Like an article.
     */
    public function likeArticle(Request $request, Article $article): JsonResponse
    {
        $user = $request->user();

        if ($article->status !== 'published') {
            return response()->json([
                'success' => false,
                'message' => 'Only published articles can be liked.',
            ], 403);
        }

        try {
            if (ArticleLike::where('user_id', $user->id)->where('article_id', $article->id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Article already liked by this user.',
                ], 409); // Conflict
            }

            DB::beginTransaction();

            ArticleLike::create([
                'user_id' => $user->id,
                'article_id' => $article->id,
            ]);

            $article->increment('like_count');

            Db::commit();

            return response()->json([
                'success' => true,
                'message' => 'Article liked successfully.',
                'data' => [
                    'article_id' => $article->id,
                    'like_count' => $article->like_count,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error liking article: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Unlike an article.
     */
    public function unlikeArticle(Request $request, Article $article): JsonResponse
    {
        $user = $request->user();

        if ($article->status !== 'published') {
            return response()->json([
                'success' => false,
                'message' => 'Only published articles can be unliked.',
            ], 403);
        }

        try {
            $like = ArticleLike::where('user_id', $user->id)->where('article_id', $article->id)->first();

            if (!$like) {
                return response()->json([
                    'success' => false,
                    'message' => 'Article not liked by this user.',
                ], 404); // Not Found
            }

            DB::beginTransaction();

            $like->delete();
            $article->decrement('like_count');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Article unliked successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error unliking article: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get popular articles (ordered by like count).
     */
    public function popularArticles(Request $request): JsonResponse
    {
        try {
            $articles = Article::with('author')
                ->where('status', 'published')
                ->orderBy('like_count', 'desc')
                ->get();

            $user = $request->user();

            if ($user && ($user->isStudentOrAlumni())) {
                $articles->map(function ($article) use ($user) {
                    $article->is_liked_by_user = $article->likes()->where('user_id', $user->id)->exists();
                    return $article;
                });
            }

            return response()->json([
                'success' => true,
                'message' => 'Popular articles retrieved successfully.',
                'data' => $articles
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving popular articles: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Edit an article (excluding image).
     */
    public function editArticle(UpdateArticleRequest $request, Article $article): JsonResponse
    {
        try {
            $article->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Article updated successfully.',
                'data' => $article
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating article: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete an article.
     */
    public function deleteArticle(Article $article): JsonResponse
    {
        try {
            if ($article->featured_image) {
                Storage::disk('public')->delete($article->featured_image);
            }
            $article->delete();

            return response()->json([
                'success' => true,
                'message' => 'Article deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting article: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Publish a draft article.
     */
    public function publishArticle(Article $article): JsonResponse
    {
        try {
            if ($article->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only draft articles can be published.',
                ], 409); // Conflict
            }

            $article->update([
                'status' => 'published',
                'published_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Article published successfully.',
                'data' => $article
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error publishing article: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Archive a published article.
     */
    public function archiveArticle(Article $article): JsonResponse
    {
        try {
            if ($article->status !== 'published') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only published articles can be archived.',
                ], 409); // Conflict
            }

            $article->update(['status' => 'archived']);

            return response()->json([
                'success' => true,
                'message' => 'Article archived successfully.',
                'data' => $article
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error archiving article: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Unarchive an archived article.
     */
    public function unarchiveArticle(Article $article): JsonResponse
    {
        try {
            if ($article->status !== 'archived') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only archived articles can be unarchived.',
                ], 409); // Conflict
            }

            $article->update(['status' => 'published']);

            return response()->json([
                'success' => true,
                'message' => 'Article unarchived successfully.',
                'data' => $article
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error unarchiving article: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new article.
     */
    public function createArticle(StoreArticleRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        try {
            $featuredImagePath = null;
            if ($request->hasFile('featured_image')) {
                $featuredImagePath = $request->file('featured_image')->store('articles/images', 'public');
            }

            $article = Article::create([
                'author_id' => $request->user()->id,
                'title' => $validatedData['title'],
                'content' => $validatedData['content'],
                'featured_image' => $featuredImagePath,
                'external_link' => $validatedData['external_link'] ?? null,
                'status' => $validatedData['status'],
                'published_at' => $validatedData['status'] === 'published' ? now() : null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Article created successfully.',
                'data' => $article
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating article: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Change the featured image for an article.
     */
    public function changeArticleImage(ChangeArticleImageRequest $request, Article $article): JsonResponse
    {
        $validatedData = $request->validated();

        try {
            if ($article->featured_image) {
                Storage::disk('public')->delete($article->featured_image);
            }

            $featuredImagePath = $request->file('featured_image')->store('articles/images', 'public');
            $article->update(['featured_image' => $featuredImagePath]);

            return response()->json([
                'success' => true,
                'message' => 'Article featured image updated successfully.',
                'data' => $article
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error changing article image: ' . $e->getMessage(),
            ], 500);
        }
    }
}
