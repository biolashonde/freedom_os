<?php
declare(strict_types=1);

$router->get('/', [HomeController::class, 'index']);
$router->get('/donate', [DonationController::class, 'show']);

$router->get('/install', [InstallController::class, 'index']);
$router->post('/install', [InstallController::class, 'run'], [CsrfMiddleware::class]);

$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login'], [CsrfMiddleware::class]);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register'], [CsrfMiddleware::class]);
$router->post('/logout', [AuthController::class, 'logout'], [AuthMiddleware::class, CsrfMiddleware::class]);

$router->get('/dashboard', [SobrietyController::class, 'dashboard'], [AuthMiddleware::class]);
$router->get('/progress', [SobrietyController::class, 'progress'], [AuthMiddleware::class]);
$router->post('/checkin', [SobrietyController::class, 'checkin'], [AuthMiddleware::class, CsrfMiddleware::class]);

$router->get('/sos', [SOSController::class, 'show'], [AuthMiddleware::class]);
$router->post('/sos/trigger', [SOSController::class, 'trigger'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/sos/resolve', [SOSController::class, 'resolve'], [AuthMiddleware::class, CsrfMiddleware::class]);

$router->get('/accountability', [AccountabilityController::class, 'index'], [AuthMiddleware::class]);
$router->post('/accountability/invite', [AccountabilityController::class, 'invite'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/accountability/accept/{token}', [AccountabilityController::class, 'showAccept']);
$router->post('/accountability/accept/{token}', [AccountabilityController::class, 'accept'], [CsrfMiddleware::class]);
$router->get('/partner', [PartnerController::class, 'dashboard'], [AuthMiddleware::class]);
$router->post('/partner/encourage/{pairId}', [PartnerController::class, 'encourage'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/partner/overrides/{overrideId}/review', [PartnerController::class, 'reviewOverride'], [AuthMiddleware::class, CsrfMiddleware::class]);

$router->get('/devotional', [DevotionalController::class, 'today'], [AuthMiddleware::class]);
$router->post('/devotional/ai', [DevotionalController::class, 'generate'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/devotional/archive', [DevotionalController::class, 'archive'], [AuthMiddleware::class]);
$router->get('/devotional/{day}', [DevotionalController::class, 'show'], [AuthMiddleware::class]);

$router->get('/purpose', [PurposeController::class, 'index'], [AuthMiddleware::class]);
$router->post('/purpose/safety-plan', [PurposeController::class, 'saveSafetyPlan'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/purpose/goals', [PurposeController::class, 'storeGoal'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/purpose/goals/{goalId}/complete', [PurposeController::class, 'completeGoal'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/purpose/testimony', [PurposeController::class, 'saveTestimony'], [AuthMiddleware::class, CsrfMiddleware::class]);

$router->get('/privacy', [PrivacyController::class, 'index'], [AuthMiddleware::class]);
$router->post('/privacy/export', [PrivacyController::class, 'export'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/privacy/delete', [PrivacyController::class, 'deleteAccount'], [AuthMiddleware::class, CsrfMiddleware::class]);

$router->get('/health', [HealthController::class, 'index'], [AuthMiddleware::class]);
$router->get('/settings/ai', [AISettingsController::class, 'edit'], [AuthMiddleware::class]);
$router->post('/settings/ai', [AISettingsController::class, 'update'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/community', [CommunityController::class, 'index'], [AuthMiddleware::class]);
$router->post('/community/messages', [CommunityController::class, 'post'], [AuthMiddleware::class, CsrfMiddleware::class]);

$router->get('/guard', [BlockerController::class, 'index'], [AuthMiddleware::class]);
$router->get('/guard/mobile', [BlockerController::class, 'mobile'], [AuthMiddleware::class]);
$router->post('/guard/extension/download', [BlockerController::class, 'downloadExtension'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/guard/devices', [BlockerController::class, 'createDevice'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/guard/rules', [BlockerController::class, 'storeRule'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/guard/override', [BlockerController::class, 'requestOverride'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/blocked', [BlockerController::class, 'blockedPage']);
$router->post('/blocker/check', [BlockerController::class, 'check']);

$router->get('/admin', [AdminController::class, 'dashboard'], [AuthMiddleware::class, AdminMiddleware::class]);
$router->get('/admin/analytics', [AdminController::class, 'analytics'], [AuthMiddleware::class, SuperAdminMiddleware::class]);
$router->get('/admin/donations', [AdminController::class, 'donations'], [AuthMiddleware::class, SuperAdminMiddleware::class]);
$router->post('/admin/donations', [AdminController::class, 'saveDonations'], [AuthMiddleware::class, SuperAdminMiddleware::class, CsrfMiddleware::class]);
$router->get('/admin/settings', [AdminController::class, 'settings'], [AuthMiddleware::class, SuperAdminMiddleware::class]);
$router->post('/admin/settings', [AdminController::class, 'saveSettings'], [AuthMiddleware::class, SuperAdminMiddleware::class, CsrfMiddleware::class]);
$router->get('/admin/content', [ContentController::class, 'index'], [AuthMiddleware::class, SuperAdminMiddleware::class]);
$router->post('/admin/content/devotionals', [ContentController::class, 'storeDevotional'], [AuthMiddleware::class, SuperAdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/content/resources', [ContentController::class, 'storeResource'], [AuthMiddleware::class, SuperAdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/content/meetings', [ContentController::class, 'storeMeeting'], [AuthMiddleware::class, SuperAdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/content/messages/{messageId}/toggle', [ContentController::class, 'toggleMessage'], [AuthMiddleware::class, SuperAdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/testimonies/{testimonyId}/visibility', [AdminController::class, 'updateTestimonyVisibility'], [AuthMiddleware::class, AdminMiddleware::class, CsrfMiddleware::class]);
$router->post('/admin/overrides/{overrideId}/review', [AdminController::class, 'reviewOverride'], [AuthMiddleware::class, AdminMiddleware::class, CsrfMiddleware::class]);
