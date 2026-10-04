<?php

/*
| --------------------------------------------------------------------
| App Namespace
| --------------------------------------------------------------------
|
| This defines the default Namespace that is used throughout
| CodeIgniter to refer to the Application directory. Change
| this constant to change the namespace that all application
| classes should use.
|
| NOTE: changing this will require manually modifying the
| existing namespaces of App\* namespaced-classes.
 */
defined('APP_NAMESPACE') || define('APP_NAMESPACE', 'App');

/*
| --------------------------------------------------------------------------
| Composer Path
| --------------------------------------------------------------------------
|
| The path that Composer's autoload file is expected to live. By default,
| the vendor folder is in the Root directory, but you can customize that here.
 */
defined('COMPOSER_PATH') || define('COMPOSER_PATH', ROOTPATH . 'vendor/autoload.php');

/*
|--------------------------------------------------------------------------
| Timing Constants
|--------------------------------------------------------------------------
|
| Provide simple ways to work with the myriad of PHP functions that
| require information to be in seconds.
 */
defined('SECOND') || define('SECOND', 1);
defined('MINUTE') || define('MINUTE', 60);
defined('HOUR') || define('HOUR', 3600);
defined('DAY') || define('DAY', 86400);
defined('WEEK') || define('WEEK', 604800);
defined('MONTH') || define('MONTH', 2_592_000);
defined('YEAR') || define('YEAR', 31_536_000);
defined('DECADE') || define('DECADE', 315_360_000);

/*
| --------------------------------------------------------------------------
| Exit Status Codes
| --------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
| While there is no universal standard for error codes, there are some
| broad conventions.  Three such conventions are mentioned below, for
| those who wish to make use of them.  The CodeIgniter defaults were
| chosen for the least overlap with these conventions, while still
| leaving room for others to be defined in future versions and user
| applications.
|
| The three main conventions used for determining exit status codes
| are as follows:
|
|    Standard C/C++ Library (stdlibc):
|       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
|       (This link also contains other GNU-specific conventions)
|    BSD sysexits.h:
|       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
|    Bash scripting:
|       http://tldp.org/LDP/abs/html/exitcodes.html
|
 */
defined('EXIT_SUCCESS') || define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR') || define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG') || define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE') || define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS') || define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') || define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT') || define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE') || define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN') || define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX') || define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code
/**
 * @deprecated Use \CodeIgniter\Events\Events::PRIORITY_LOW instead.
 */
define('EVENT_PRIORITY_LOW', 200);

/**
 * @deprecated Use \CodeIgniter\Events\Events::PRIORITY_NORMAL instead.
 */
define('EVENT_PRIORITY_NORMAL', 100);

/**
 * @deprecated Use \CodeIgniter\Events\Events::PRIORITY_HIGH instead.
 */
define('EVENT_PRIORITY_HIGH', 10);

define('ASSERT_PATH', "http://localhost:8080/");
define('CSS_PATH', "http://localhost:8080/assets/css/");
define('JS_PATH', "http://localhost:8080/assets/js/");

//defing the owner id
define('R_OWNER_ID', 1);

// Define your constant here
define('ROLE_ID', '8');
//Custom Report View
define('RECORDS_LIMIT', 5000);
//Get Sprint details
define('SPRINT_STATUS', [18, 19, 22]);

//Get Module id of backlog
define('BACKLOG_MODULE', 5);

//Get Module id of user story
define('USERSTORY_MODULE', 6);

//Statuses of backlogitem

define('USERSTORIES_CREATED', 4);
define('BRAINSTORMING', 5);
define('PARTIALLY_BRAINSTORMED', 6);
define('BRAINSTORMING_COMPLETED', 7);
define('READY_FOR_SPRINT', 8);
define('IN_SPRINT_PARTIAL', 9);
define('IN_SPRINT', 10);
define('COMPLETED', 12);

define('READY_FOR_BRAINSTORMING', 14);
define('SPRINT_STATUS_FOR_MEETING', [19, 20, 23]);

//Get Priority
define('PRIORITY', ['LOW', 'MEDIUM', 'LARGE']);

//action type id in login or logout time
define('ADD', 1);

//module id for login or logout time
define('MODULE_ID', 1);

//login action data
define('LOGIN_ACTION', 'login');

//logout action data
define('LOGOUT_ACTION', 'logout');

//Get CustomField id of issues
define('CUSTOM_FIELD', ['designation' => 10, 'customer' => 16, 'team' => 20, 'phase' => 21, 'current_status' => 22, 'airline' => 23, 'assign_team' => 24, 'main_activity' => 37, 'module_category' => 38, 'user_story_id' => 52]);

//User API key label
define('USER_API', 'api');

define('FIBONACCI_LIMIT', 10);
