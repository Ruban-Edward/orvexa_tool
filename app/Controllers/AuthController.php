<?php
namespace App\Controllers;
use App\Models\ProjectModel;
use Config\SprintModelConfig;
use CodeIgniter\Events\Events;

/**
 * @author Orvexa <orvexa@infinitisoftware.net>
 * 
 * @modified-by Orvexa <orvexa@infinitisoftware.net>
 * @created-date 13-06-2024
 * @modified-date 13-06-2024
 * 
 */
class AuthController extends BaseController
{
    /**
     * AuthController::index()
     * returns default page (login screen)
     * 
     * @return string
     */ 
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOGIN_LOCK_SECONDS = 900;

    protected $backlogModel;
    protected $session;
    protected $userModel;

    public function __construct(){
        // object of the BacklogModel
        $this->backlogModel = model(\App\Models\Backlog\BacklogModel::class);
        $this->session=session();
        // object of the UserModel
        $this->userModel = model(\App\Models\User\UserModel::class);    
    }

    /**
     * @author Stervin Richard 
     * AuthController::index()
     *
     * to view the login page 
     */ 
    public function index(): string
    {   
        return view('user/login');
    }

    /**
     * @author Stervin Richard 
     * AuthController::loginValidate()
     *
     * Check user login authentication with the scrum database.
     * If the user is valid then allow access to the scrum master portal.
     */ 
    public function loginValidate()
    {
        // validation rules of input fields with custom messages
        $rules = [
            "username" => [
                "label" => "Username",
                "rules" => "required|min_length[3]",
                "errors" => [
                    "required" => "The {field} field is required",
                    "min_length" => "The {field} field must be at least 3 characters in length"
                ]
            ],
            "password" => [
                "label" => "Password",
                "rules" => "required|min_length[8]",
                "errors" => [
                    "required" => "The {field} field is required",
                    "min_length" => "The {field} field must be at least 8 characters in length"
                ]
            ]
        ];
        // check validation and send error message
        if (!$this->validate($rules)) {
            $errorMessage = $this->validator->getErrors();
            session()->setFlashdata('validation', $errorMessage);
            return redirect()->to(ASSERT_PATH.'login');
        }
        $userdata = $this->request->getPost();
        $username = trim($userdata['username']);
        $password = $userdata['password'];
        $userData = [
            'username' => $username,
            'password' => $password
        ];

        // Brute-force protection: 5 failed attempts per username+IP lock the form for 15 minutes.
        $throttleKey = 'login_' . md5(strtolower($username) . '|' . $this->request->getIPAddress());
        $cache       = cache();
        $failures    = (int) ($cache->get($throttleKey) ?? 0);
        if ($failures >= self::MAX_LOGIN_ATTEMPTS) {
            session()->setFlashdata('error', 'Too many failed attempts. Please try again in 15 minutes.');
            return redirect()->to(ASSERT_PATH . 'login');
        }

        //Check user login details with scrum database
        $user = $this->userModel->getUser($userData);
        // A valid user only needs a name, an employee id and a role. The Redmine API key
        // is optional (several accounts legitimately have none) and must not block login.
        if (! empty($user) && isset($user[0]->first_name, $user[0]->external_employee_id, $user[0]->r_role_id)) {
            $firstName = $user[0]->first_name;
            $employeeId = $user[0]->external_employee_id;
            $roleId = $user[0]->r_role_id;
            $cache->delete($throttleKey);
        }
        else{
            $cache->save($throttleKey, $failures + 1, self::LOGIN_LOCK_SECONDS);
            $errorMessage = "Invalid username or password";
            session()->setFlashdata('error', $errorMessage);
            return redirect()->to(ASSERT_PATH . 'login');
        }
        $url = $this->session->get('url');
        // Prevent session fixation: issue a fresh session id once authenticated
        $this->session->regenerate(true);
        //Set User details in session
        $user_session = [
            'first_name' => $firstName,
            'employee_id' => $employeeId,
            'role_id' => $roleId,
            'is_user_logged' => true
        ];
        $this->session->set($user_session);
        // set the user login action in scrum db
        $this->addLogin(LOGIN_ACTION);
        // redirect to the originally requested page – only to a local path (no open redirect)
        if (is_string($url) && preg_match('#^[A-Za-z0-9_\-./?=&%]+$#', $url) && ! str_contains($url, '//')) {
            return redirect()->to(ASSERT_PATH . ltrim($url, '/'));
        }
        //Redirect to user dashboard page
        return redirect()->to(ASSERT_PATH . 'dashboard/dashboardView');
    }
    
    /**
    * AuthController::logout()
    *
    * Logout user and destroy the session details
    */ 
    public function logout()
    {
        // set the user logout action in db
        $this->addLogin(LOGOUT_ACTION);
        $this->session->destroy();
        return redirect()->to(ASSERT_PATH);
    }

    /**
     * @author Stervin Richard 
     * AuthController::addLogin()
     *
     * set the user action in scrum db 
     */ 
    public function addLogin($actionData){
        $action = formActionData(__FUNCTION__, $this->session->get('employee_id'), 0, $actionData);
        Events::trigger('log_actions', $action);
    }

    /**
    * AuthController::noAccess()
    *
    * Logout user
    */ 

    /**
     * @author Ruban Edward 
     * AuthController::noAccess()
     *
     * If the user does not have the permission for the page it redirects to this page
     */ 
    public function noAccess()
    {
        return view('unauthorized');
    }

}