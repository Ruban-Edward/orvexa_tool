<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

/* General */
$routes->get('/', 'AuthController::index', ['filter' => 'loginpage']);

/* Authetication */
$routes->get('/login', 'AuthController::index', ['filter' => 'loginpage']);
$routes->post('/login', 'AuthController::loginValidate');

$routes->get('/logout', 'AuthController::logout', ['filter' => 'auth']);
$routes->get('/no_access', 'AuthController::noAccess');

/**
 * @author     Ruban Edward,Rama Selvan,Hari Sankar,Gokul
 * @date       01 July 2024
 * @purpose    for redirecting to calendar page
 */
$routes->group('meeting', ['filter' => ['auth', 'acl']], function ($routes) {
    $routes->get('calendar', 'MeetingController::calendar');
    $routes->post('eventdetails/(:num)/(:num)', 'MeetingController::getMeetingDetails/$1/$2');
    $routes->post('sprintdetails/(:num)', 'MeetingController::getSprintDetails/$1');
    $routes->post('sprintByProduct/(:num)', 'MeetingController::sprintByProduct/$1');
    $routes->post('teamBySprint/(:num)', 'MeetingController::teamBySprint/$1');
    $routes->post('scheduleMeeting', 'MeetingController::meetingConfirmation');
    $routes->post('updateMeeting', 'MeetingController::meetingUpdation');
    $routes->post('cancelMeeting/(:num)', 'MeetingController::cancelMeetings/$1');
    $routes->post('logMeetingTimes', 'MeetingController::logMeetingTimes');
    $routes->post('mailFunction', 'MeetingController::sendMail');
    $routes->post('createGroupDetails', 'MeetingController::createGroup');
    $routes->post('editGroupDetails', 'MeetingController::editGroup');
    $routes->post('deleteGroupDetails', 'MeetingController::deleteGroup');
    $routes->post('getTeamDetailsById/(:num)', 'MeetingController::getTeamDetailsById/$1');
    $routes->post('getSprintDetails/(:num)', 'MeetingController::getSprintMembersDetails/$1');
    $routes->post('sprintInsert', 'MeetingController::sprintInsert');
    $routes->post('getMembersByProduct/(:num)', 'MeetingController::getMembersByProduct/$1');
    $routes->post('backlogByProduct/(:num)', 'MeetingController::backlogByProduct/$1');
    $routes->post('epicByBacklog/(:num)', 'MeetingController::getEpic/$1');
    $routes->get('checkHoliday', 'MeetingController::checkHoliday');
    $routes->post('getSprintMembersById/(:num)', 'MeetingController::getSprintMembersById/$1');
});

/**
 * @author     Murugadass, Abinandhan , Samuel , Vigneshwari
 * @date       01 July 2024
 * @purpose    for redirecting to backlog page
 */
$routes->group('backlog', ['filter' => ['auth', 'acl']], function ($routes) {
    $routes->get('documents/download/(:segment)', 'BacklogController::download/$1');
    $routes->get('documents/view/(:any)', 'BacklogController::view/$1');

    $routes->get('productbacklogs', 'BacklogController::products');

    $routes->get('refinement', 'RefinementController::refinement');
    $routes->post('backlogGrooming/(:num)', 'RefinementController::backlogGrooming/$1');

    $routes->get('backlogitemdetails', 'BacklogController::backlogItemDetails');
    $routes->post('historyDataDetails', 'BacklogController::historyDataDetails');
    $routes->post('deletedocument', 'BacklogController::deleteDocument');

    $routes->get('backlogitems', 'BacklogController::backlogItems');
    $routes->post('filterBacklogItem', 'BacklogController::filterBacklogItem');
    $routes->post('addbacklog', 'BacklogController::addBacklog');
    $routes->post('getbacklogItemById', 'BacklogController::getbacklogItemById');
    $routes->post('updatebacklog', 'BacklogController::updateBacklog');
    $routes->post('deletebacklogitem', 'BacklogController::deleteBacklog');
    $routes->post('changebacklogstatus', 'BacklogController::changeBacklogStatus');
    $routes->post('historydata', 'BacklogController::historyDataDetails');

    $routes->get('documents/download/(:segment)', 'BacklogController::download/$1');
    $routes->get('documents/view/(:any)', 'BacklogController::view/$1');

    $routes->get('userstories', 'UserStoryController::userStories');
    $routes->post('filterUserStories', 'UserStoryController::filterUserStories');
    $routes->post('addepic', 'BacklogController::addEpic');
    $routes->post('uploaduserstories', 'UserStoryController::uploadUserStory');
    $routes->post('addUserStory', 'UserStoryController::addUserStory');
    $routes->post('updateUserStory', 'UserStoryController::updateUserStory');
    $routes->post('deleteuserstory/(:num)/(:num)/(:num)', 'UserStoryController::deleteUserStory/$1/$2/$3');
    $routes->post('userstoryByEpic/(:num)', 'UserStoryController::userstoryByEpic/$1');
    $routes->get('userstory/details/(:num)', 'UserStoryController::getUserStoryDetails/$1');
    $routes->get('downloadUserStories', 'UserStoryController::downloadUserStories');
    $routes->post('changeuserstorystatus', 'BacklogController::changeUserStoryStatus');
    $routes->get('downloadReference/(:any)', 'UserStoryController::downloadReference/$1');
    $routes->post('comments', 'UserStoryController::comments');

    $routes->get('tasks', 'TaskController::tasks');
    $routes->post('filterTasks', 'TaskController::filterTasks');
    $routes->post('addTasks/(:num)/(:num)/(:num)', 'TaskController::addTasks/$1/$2/$3');
    $routes->post('updateTaskById/(:num)/(:num)/(:num)', 'TaskController::updateTasks/$1/$2/$3');
    $routes->post('deletetask/(:num)/(:num)/(:num)', 'TaskController::deleteTasks/$1/$2/$3');
    $routes->post('getTaskById/(:num)', 'TaskController::getTaskById/$1');

    $routes->post('insertPokerPlanning', 'PockerController::insertPokerPlanning');
    $routes->post('updatereveal', 'PockerController::updatePokerReveal');
    $routes->post('getpoker', 'PockerController::getPokerPlanning');
    $routes->post('addUserStoryPoint', 'UserStoryController::addUserStoryPoint');
    $routes->get('test', 'PockerController::getPokerPlanning');

});

/**
 * @author     Vishva
 * @date       03 July 2024
 * @purpose    For redirecting to sprint module pages
 */
$routes->group('sprint', ['filter' => ['auth', 'acl']], function ($routes) {
    $routes->get('sprintlist', 'SprintController::getSprintList');
    $routes->get('navcreatesprint', 'SprintController::navCreateSprint');
    $routes->post('createsprint', 'SprintController::createSprint');
    $routes->get('navsprintview', 'SprintController::navSprintView');
    $routes->get('navscrumdiary', 'SprintController::navScrumDiary');
    $routes->post('navsprintreview', 'SprintController::navSprintReview');
    $routes->post('scrumdiary', 'SprintController::insertScrumDiary');
    $routes->get('getProductTasks/(:num)', 'SprintController::getProductTasks/$1');

    $routes->get('generate-pdf/(:num)', 'SprintController::generatePdf/$1');

    $routes->get('getMembers/(:num)', 'SprintController::getProductMembers/$1');
    $routes->post('submitUserStories', 'SprintController::submitTaskReview');
    $routes->post('fetchTasks', 'SprintController::submitTaskReview');
    $routes->post('submitSprintReview', 'SprintController::insertSprintReview');
    $routes->post('ReviewSprintPlanDetails', 'SprintController::ReviewSprintPlanDetails');
    $routes->post('sprintretrospective', 'SprintController::insertSprintRetrospective');
    $routes->get('sprintplanning', 'SprintController::getSprintPlanning');
    $routes->get('retrospectivedetails', 'SprintController::getRetrospectiveDetails');
    $routes->post('edit', 'SprintController::edit');
    $routes->post('update', 'SprintController::update');
    $routes->get('fetchReview', 'SprintController::getReviewDetails');
    $routes->post('updatePlan', 'SprintController::updateSprintPlan');
    $routes->post('updateSprintStatus', 'SprintController::updateSprintStatus');
    $routes->get('navsprinthistory', 'SprintController::navSprintHistory');
    $routes->get('fetchMembers', 'SprintController::fetchMembers');
    $routes->post('fetchScrumTasks', 'SprintController::fetchScrumTasks');
    $routes->post('changeSprintStatusById/(:num)', 'SprintController::changeSprintStatusById/$1');
    $routes->post('applySelection', 'SprintController::getSprintSelectionList');
});
$routes->post('applyFilters', 'SprintController::getSprintfilterList');

// $routes->post('createsprint', 'SprintController::createSprint');

/**
 * @author    Stervin Richard, Rahul
 * @date       02 July 2024
 * @purpose    For redirecting to dashboard module pages
 */
$routes->group('dashboard', ['filter' => ['auth', 'acl']], function ($routes) {
    $routes->get('dashboardView', 'DashboardController::dashboardView');
    $routes->post('showProductDashboard', 'DashboardController::showProductDashboard');
    $routes->get('showProductDashboard/(:num)', 'DashboardController::showProductDashboard/$1');

    $routes->post('pendingTasks', 'DashboardController::fetchPendingTaskBySprintId');

});

/**
 * @author     T siva Teja
 * @datetime   12 July 2024
 * @purpose    for redirecting to the report page
 */

$routes->group('report', ['filter' => ['auth', 'acl']], function ($routes) {
    $routes->get('meetingreport/(:any)', "CustomReportController::getReportTable/$1");
    $routes->get("sprintreport/(:any)", "CustomReportController::getReportTable/$1");
    $routes->get("backlogreport/(:any)", "CustomReportController::getReportTable/$1");
    $routes->post('MeetReportfilter/(:any)', 'CustomReportController::getFilterReport/$1');
    $routes->post('SprintReportfilter/(:any)', 'CustomReportController::getFilterReport/$1');
    $routes->post('BacklogReportfilter/(:any)', 'CustomReportController::getFilterReport/$1');
    $routes->post('download/(:any)', 'CustomReportController::downloadFilterAllData/$1');
    $routes->get('pocker', 'PockerController::index');

});

/**
 * @author     Ruban Edward
 * @date       09 July 2024
 * @purpose    To manage users ans set permission for users by admin
 */
$routes->group('admin', ['filter' => ['auth', 'acl']], function ($routes) {
    $routes->get('manageUser', 'AdminController::userList');
    $routes->post('updaterole', 'AdminController::updateRole');
    $routes->get('setPermissionPage', 'AdminController::setPermissionPage');
    $routes->post('setPermissions', 'AdminController::setPermission');
    $routes->post('searchrole', 'AdminController::searchRole');
    $routes->post('syncUser', 'AdminController::userSync');
    $routes->post('getSpecificPermissions', 'AdminController::getSpecificPermissions');
    $routes->post('setNewPermission', 'AdminController::setNewPermission');
    $routes->get('getPermissionName', 'AdminController::getPermissionName');
    $routes->post('deletePermission/(:num)', 'AdminController::deletePermission/$1');
    $routes->get('getPermissionDetails/(:num)', 'AdminController::getPermissionDetails/$1');
    $routes->post('updatePermission', 'AdminController::updatePermission');
    $routes->get('adminSettings', 'SettingsController::adminSettingsPage');
    $routes->post('getRoles', 'SettingsController::getRoles');
    $routes->post('addRole', 'SettingsController::addRole');
    $routes->post('deleteRole', 'SettingsController::deleteRole');
    $routes->post('createHolidays', 'SettingsController::createHolidays');
    $routes->post('holidayFileUpload', 'SettingsController::holidayFileUpload');
    $routes->post('pokerConfig', 'SettingsController::pokerConfigSetting');
    $routes->get('getPokerLimit', 'SettingsController::getPokerLimit');
    $routes->post('setProductOwner', 'SettingsController::setProductOwner');
    $routes->post('getMembersByProductList/(:num)', 'SettingsController::getMembersByProductList/$1');
    $routes->post('setTShirtSize', 'SettingsController::setTShirtSize');
    $routes->post('getTShirtSizeByProduct/(:num)', 'SettingsController::getTShirtSizeByProduct/$1');
});

/**
 * @author     Stervin Richard
 * @datetime   8 Aug 2024
 * @purpose    for show the user notification
 */

$routes->group('notification', ['filter' => 'auth'], function ($routes) {
    $routes->get('notificationDetails', 'NotificationController::notificationDetails');
});

/**
 * @author     T siva Teja
 * @datetime   12 July 2024
 * @purpose    for redirecting to the report page
 */
// for accesssing all the urls
$routes->get('/(:any)', 'AuthController::index', ['filter' => ['auth', 'loginpage']]);

/**
 * Routes Configuration for Notes
 *
 * @category   Routes
 * @package    App
 * @author     Jeril
 * @created    27 August 2024
 * @purpose    Defines routes for managing notes in the application. Includes endpoints for creating and retrieving notes.
 *             These routes are protected by authentication and access control filters.
 */
$routes->group('notes', ['filter' => ['auth', 'acl']], function ($routes) {
    $routes->post('createnotes', 'NotesController::insertNotes');
    $routes->post('getnotes', 'NotesController::getNotes');
});
