<?php

use App\Router;

$router = Router::getInstance();

$router->get('/', 'HomeController@index')->name('home');
$router->get('/how-it-works', 'HomeController@howItWorks')->name('how-it-works');

$router->group('/auth', function (Router $router) {
    $router->get('/login', 'AuthController@showLogin')->name('login')->middleware(['guest']);
    $router->post('/login', 'AuthController@login')->name('login.post')->middleware(['guest', 'ratelimit:login']);
    $router->get('/register', 'AuthController@showRegister')->name('register')->middleware(['guest']);
    $router->post('/register', 'AuthController@register')->name('register.post')->middleware(['guest', 'ratelimit:registration']);
    $router->post('/logout', 'AuthController@logout')->name('logout')->middleware(['auth']);
    $router->get('/verify-email', 'AuthController@showVerifyEmail')->name('verification.notice')->middleware(['auth']);
    $router->post('/verify-email', 'AuthController@verifyEmail')->name('verification.verify')->middleware(['auth']);
    $router->get('/password/reset', 'AuthController@showForgotPassword')->name('password.request')->middleware(['guest']);
    $router->post('/password/reset', 'AuthController@sendResetLink')->name('password.email')->middleware(['guest', 'ratelimit:login']);
    $router->get('/password/reset/{token}', 'AuthController@showResetPassword')->name('password.reset')->middleware(['guest']);
    $router->post('/password/reset/{token}', 'AuthController@resetPassword')->name('password.update')->middleware(['guest', 'ratelimit:login']);
});

$router->group('/dashboard', function (Router $router) {
    $router->get('/', 'DashboardController@index')->name('dashboard')->middleware(['auth']);
    $router->get('/continue/{caseId}', 'DashboardController@continueCase')->name('dashboard.continue')->middleware(['auth']);
    $router->get('/xp-history', 'DashboardController@xpHistory')->name('dashboard.xp-history')->middleware(['auth']);
    $router->get('/recent-queries', 'DashboardController@recentQueries')->name('dashboard.recent-queries')->middleware(['auth']);
    $router->get('/stats', 'DashboardController@stats')->name('dashboard.stats')->middleware(['auth']);
});

$router->group('/cases', function (Router $router) {
    $router->get('/', 'CaseController@index')->name('cases')->middleware(['auth']);
    $router->get('/{case}', 'CaseController@show')->name('cases.show')->middleware(['auth']);
    $router->get('/{case}/evidence', 'CaseController@evidence')->name('cases.evidence')->middleware(['auth']);
    $router->get('/{case}/suspects', 'CaseController@suspects')->name('cases.suspects')->middleware(['auth']);
    $router->get('/{case}/briefing', 'CaseController@briefing')->name('cases.briefing')->middleware(['auth']);
    $router->get('/{case}/progress', 'CaseController@progress')->name('cases.progress')->middleware(['auth']);
});

$router->group('/detective', function (Router $router) {
    $router->get('/{case}', 'DetectiveController@workspace')->name('detective.workspace')->middleware(['auth']);
    $router->get('/{case}/schema', 'DetectiveController@schema')->name('detective.schema')->middleware(['auth']);
    $router->post('/query/execute', 'DetectiveController@executeQuery')->name('detective.query.execute')->middleware(['auth', 'ratelimit:query_execution']);
    $router->get('/{case}/history', 'DetectiveController@queryHistory')->name('detective.query.history')->middleware(['auth']);
    $router->post('/{case}/challenge/{challenge}', 'DetectiveController@submitChallenge')->name('detective.challenge.submit')->middleware(['auth', 'ratelimit:challenge_submission']);
    $router->post('/{case}/hint/{hint}', 'DetectiveController@useHint')->name('detective.hint.use')->middleware(['auth']);
});

$router->group('/profile', function (Router $router) {
    $router->get('/', 'ProfileController@index')->name('profile')->middleware(['auth']);
    $router->patch('/', 'ProfileController@update')->name('profile.update')->middleware(['auth']);
    $router->patch('/password', 'ProfileController@updatePassword')->name('profile.password')->middleware(['auth']);
    $router->get('/achievements', 'ProfileController@achievements')->name('profile.achievements')->middleware(['auth']);
    $router->get('/settings', 'ProfileController@settings')->name('profile.settings')->middleware(['auth']);
});

$router->group('/leaderboard', function (Router $router) {
    $router->get('/', 'LeaderboardController@index')->name('leaderboard');
    $router->get('/api', 'LeaderboardController@api')->name('leaderboard.api');
});

$router->group('/achievements', function (Router $router) {
    $router->get('/', 'AchievementController@index')->name('achievements');
});

$router->group('/admin', function (Router $router) {
    $router->get('/', 'AdminController@dashboard')->name('admin.dashboard')->middleware(['auth', 'admin']);
    $router->get('/users', 'AdminController@users')->name('admin.users')->middleware(['auth', 'admin']);
    $router->patch('/users/{user}/toggle', 'AdminController@toggleUser')->name('admin.users.toggle')->middleware(['auth', 'admin']);
    $router->get('/cases', 'AdminController@cases')->name('admin.cases')->middleware(['auth', 'admin']);
    $router->get('/cases/create', 'AdminController@createCase')->name('admin.cases.create')->middleware(['auth', 'admin']);
    $router->post('/cases', 'AdminController@storeCase')->name('admin.cases.store')->middleware(['auth', 'admin']);
    $router->get('/cases/{case}/edit', 'AdminController@editCase')->name('admin.cases.edit')->middleware(['auth', 'admin']);
    $router->patch('/cases/{case}', 'AdminController@updateCase')->name('admin.cases.update')->middleware(['auth', 'admin']);
    $router->delete('/cases/{case}', 'AdminController@deleteCase')->name('admin.cases.delete')->middleware(['auth', 'admin']);
    $router->get('/challenges', 'AdminController@challenges')->name('admin.challenges')->middleware(['auth', 'admin']);
    $router->get('/challenges/create', 'AdminController@createChallenge')->name('admin.challenges.create')->middleware(['auth', 'admin']);
    $router->post('/challenges', 'AdminController@storeChallenge')->name('admin.challenges.store')->middleware(['auth', 'admin']);
    $router->get('/evidence', 'AdminController@evidence')->name('admin.evidence')->middleware(['auth', 'admin']);
    $router->get('/suspects', 'AdminController@suspects')->name('admin.suspects')->middleware(['auth', 'admin']);
    $router->get('/hints', 'AdminController@hints')->name('admin.hints')->middleware(['auth', 'admin']);
    $router->get('/achievements', 'AdminController@achievements')->name('admin.achievements')->middleware(['auth', 'admin']);
    $router->get('/submissions', 'AdminController@submissions')->name('admin.submissions')->middleware(['auth', 'admin']);
    $router->get('/logs', 'AdminController@logs')->name('admin.logs')->middleware(['auth', 'admin']);
    $router->get('/stats', 'AdminController@stats')->name('admin.stats')->middleware(['auth', 'admin']);
});

$router->group('/api', function (Router $router) {
    $router->post('/query/execute', 'ApiController@executeQuery')->name('api.query.execute')->middleware(['auth', 'ratelimit:query_execution']);
    $router->get('/cases/{case}/schema', 'ApiController@caseSchema')->name('api.case.schema')->middleware(['auth']);
    $router->get('/cases/{case}/evidence', 'ApiController@caseEvidence')->name('api.case.evidence')->middleware(['auth']);
    $router->get('/cases/{case}/challenges', 'ApiController@caseChallenges')->name('api.case.challenges')->middleware(['auth']);
    $router->post('/challenges/{challenge}/submit', 'ApiController@submitChallenge')->name('api.challenge.submit')->middleware(['auth', 'ratelimit:challenge_submission']);
    $router->get('/leaderboard', 'ApiController@leaderboard')->name('api.leaderboard');
    $router->get('/profile', 'ApiController@profile')->name('api.profile')->middleware(['auth']);
    $router->get('/achievements', 'ApiController@achievements')->name('api.achievements')->middleware(['auth']);
});

$router->get('/health', function () {
    return new \Laminas\Diactoros\Response\JsonResponse(['status' => 'ok']);
})->name('health');