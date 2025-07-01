<?php

namespace App\Services;

use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserProfileService
{
    public function searchAndFilter(Request $request)
    {
        $query = UserProfile::select([
            'user_profiles.id',
            'user_profiles.first_name',
            'user_profiles.last_name',
            'user_profiles.username',
            'user_profiles.phone',
            'user_profiles.track',
            'user_profiles.intake',
            'user_profiles.job_profile',
            'user_profiles.user_id'
        ]);

        if ($request->has('track') && $request->filled('track')) {
            $query->where('track', '=', $request->input('track'));
        }

        if ($request->has('intake') && $request->filled('intake')) {
            $query->where('intake', '=', $request->input('intake'));
        }

        if ($request->has('program') && $request->filled('program')) {
            $query->where('program', '=', $request->input('program'));
        }

        // Store job_profile filter for later fuzzy matching
        $jobProfileFilter = null;
        if ($request->has('job_profile') && $request->filled('job_profile')) {
            $jobProfileFilter = strtolower($request->input('job_profile'));
        }

        if ($request->has('skill') && $request->filled('skill')) {
            $skillTerm = $request->input('skill');
            $query->whereExists(function ($subQuery) use ($skillTerm) {
                $subQuery->select(DB::raw(1))
                         ->from('user_skills')
                         ->join('skills', 'user_skills.skill_id', '=', 'skills.id')
                         ->whereColumn('user_skills.user_id', 'user_profiles.user_id')
                         ->where('skills.name', 'like', $skillTerm . '%');
            });
        }

        $users = $query->get();

        // Apply fuzzy job_profile filtering
        if ($jobProfileFilter) {
            $jobProfileWords = explode(' ', trim($jobProfileFilter));
            $users = $users->filter(function ($user) use ($jobProfileFilter, $jobProfileWords) {
                if (empty($user->job_profile)) {
                    return false;
                }
                
                $userJobProfile = strtolower($user->job_profile);
                
                // Direct substring match
                if (strpos($userJobProfile, $jobProfileFilter) !== false) {
                    return true;
                }
                
                // Word-based fuzzy matching
                $userJobWords = explode(' ', $userJobProfile);
                $matchedWords = 0;
                
                foreach ($jobProfileWords as $filterWord) {
                    if (strlen($filterWord) > 2) {
                        foreach ($userJobWords as $userWord) {
                            if (strlen($userWord) > 2 && levenshtein($userWord, $filterWord) <= 2) {
                                $matchedWords++;
                                break; // Found match for this filter word, move to next
                            }
                        }
                    }
                }
                
                // Consider it a match if at least half of the filter words matched
                return $matchedWords >= ceil(count($jobProfileWords) / 2);
            });
        }

        if ($request->has('search') && $request->filled('search')) {
            $searchTerm = strtolower($request->input('search'));
            $searchWords = explode(' ', trim($searchTerm));
            
            $users = $users->filter(function ($user) use ($searchTerm, $searchWords) {
                $userFields = [
                    strtolower($user->first_name),
                    strtolower($user->last_name),
                    strtolower($user->username)
                ];
                
                // Check if search term matches any name field (single word search)
                $nameMatch = false;
                if (count($searchWords) == 1) {
                    foreach ($userFields as $field) {
                        if (levenshtein($field, $searchTerm) <= 2) {
                            $nameMatch = true;
                            break;
                        }
                    }
                } else {
                    // Multi-word search: check if each search word matches any user field
                    $matchedWords = 0;
                    foreach ($searchWords as $searchWord) {
                        if (strlen($searchWord) > 1) { // Skip very short words
                            foreach ($userFields as $field) {
                                if (levenshtein($field, $searchWord) <= 2) {
                                    $matchedWords++;
                                    break; // Found match for this search word, move to next
                                }
                            }
                        }
                    }
                    // Consider it a match if at least half of the search words matched
                    $nameMatch = $matchedWords >= ceil(count($searchWords) / 2);
                }
                
                // For job_profile, use both substring matching and word-based fuzzy matching
                $jobProfileMatch = false;
                if (!empty($user->job_profile)) {
                    $jobProfile = strtolower($user->job_profile);
                    
                    // Direct substring match
                    if (strpos($jobProfile, $searchTerm) !== false) {
                        $jobProfileMatch = true;
                    } else {
                        // Word-based fuzzy matching - split job profile into words
                        $jobWords = explode(' ', $jobProfile);
                        foreach ($searchWords as $searchWord) {
                            if (strlen($searchWord) > 2) {
                                foreach ($jobWords as $jobWord) {
                                    if (strlen($jobWord) > 2 && levenshtein($jobWord, $searchWord) <= 2) {
                                        $jobProfileMatch = true;
                                        break 2; // Break out of both loops
                                    }
                                }
                            }
                        }
                    }
                }
                
                return $nameMatch || $jobProfileMatch;
            });
        }
        
        $perPage = min((int)$request->input('per_page', 15), 100);
        $page = $request->input('page', 1);
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $users->forPage($page, $perPage),
            $users->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return $paginated;
    }
}
