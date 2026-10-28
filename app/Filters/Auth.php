<?php
namespace App\Filters;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

/** 
 * @author yuvansri <yuvansri@infinitisoftware.net>
 * 
*/
class Auth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = Services::auth();
        if (! $session->isLoggedIn()){
            if(current_url(true) != base_url()){
                session()->set(['url' => str_replace(base_url(),"",current_url(true))]);
            }
            return redirect('/');
        }

        // Deactivated by an admin: end the session on the next request
        $employeeId = (int) session('employee_id');
        if ($employeeId && cache('user_blocked_' . $employeeId)) {
            session()->destroy();
            return redirect()->to(ASSERT_PATH . 'login')->with('error', 'Your account has been deactivated.');
        }

        // Temporary password: only the profile page (to change it) is reachable
        if (session('must_change_password')) {
            $path = trim($request->getUri()->getRoutePath(), '/');
            $allowed = ['profile', 'profile/password', 'logout', 'notification/notificationDetails', 'notification/unreadCount'];
            if (!in_array($path, $allowed, true)) {
                return $request->isAJAX()
                    ? service('response')->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Please change your password first.'])
                    : redirect()->to(ASSERT_PATH . 'profile#password')->with('error', 'Please choose a new password to continue.');
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $aruments = null)
    {
        return $response;
    }
}