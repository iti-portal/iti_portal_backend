<?php
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| This file is the central point for loading all API routes.
| Each feature or logical group of routes is defined in its own file
| within the '/routes/api/' directory for better organization.
|
*/

// Core Application Routes
require __DIR__ . '/api/auth.route.php';
require __DIR__ . '/api/admin.route.php';

// Feature & Resource Routes
require __DIR__ . '/api/users.route.php';
require __DIR__ . '/api/companies.route.php';
require __DIR__ . '/api/education.route.php';
require __DIR__ . '/api/projects.route.php';
require __DIR__ . '/api/oldAchievments.route.php';
require __DIR__ . '/api/jobs.route.php';
require __DIR__ . '/api/achievments.route.php';
require __DIR__ . '/api/articles.route.php';
require __DIR__ . '/api/services.route.php';
require __DIR__ . '/api/connections.route.php';
require __DIR__ . '/api/skills.route.php';
require __DIR__ . '/api/workExperience.route.php';
require __DIR__. '/api/userprofiles.route.php';
require __DIR__. '/api/jobApplication.route.php';
require __DIR__. '/api/awards.route.php';
require __DIR__. '/api/certificates.route.php';
require __DIR__ . '/api/chat.route.php';

require __DIR__. '/api/statistics.route.php';
require __DIR__ . '/api/contactus.route.php';









//     }); 
//         Route::post('mark-student-as-graduate/{user}', [UserManagementController::class, 'markStudentAsGraduate']);
// });

