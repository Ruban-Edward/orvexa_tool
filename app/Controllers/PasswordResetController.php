<?php

/**
 * PasswordResetController.php
 *
 * @category   Controller
 * @purpose    "Forgot password" on the sign-in page: request a reset link by username or email,
 *             then choose a new password from the emailed link.
 */

namespace App\Controllers;

use App\Services\InviteMailer;
use App\Services\PasswordResetService;
use CodeIgniter\HTTP\RedirectResponse;

class PasswordResetController extends BaseController
{
    private const SENT_MESSAGE = 'If an account matches what you entered, a reset link is on its way. Check your inbox (and spam folder) in a minute.';

    public function forgot(): string
    {
        return view('user/passwordReset', ['mode' => 'request', 'mailReady' => InviteMailer::isConfigured()]);
    }

    public function sendLink(): RedirectResponse
    {
        $back = redirect()->to(ASSERT_PATH . 'forgot-password');
        $identifier = $this->request->getPost('identifier');
        if (! is_string($identifier) || trim($identifier) === '' || mb_strlen($identifier) > 150) {
            return $back->with('error', 'Enter your username or email address.');
        }
        // At most 5 requests per network every 15 minutes, whatever the account
        if (! service('throttler')->check('pwreset_ip_' . md5($this->request->getIPAddress()), 5, 15 * MINUTE)) {
            return $back->with('error', 'Too many requests from your network. Please wait a few minutes and try again.');
        }

        (new PasswordResetService())->request($identifier, $this->request->getIPAddress());

        return $back->with('success', self::SENT_MESSAGE);
    }

    public function resetForm(string $token = ''): string
    {
        $row = (new PasswordResetService())->findValid($token);

        return view('user/passwordReset', [
            'mode'     => $row ? 'reset' : 'invalid',
            'token'    => $row ? $token : '',
            'username' => $row['external_username'] ?? '',
            'mailReady' => InviteMailer::isConfigured(),
        ]);
    }

    public function reset(): RedirectResponse
    {
        $token = (string) $this->request->getPost('token');
        $back  = redirect()->to(ASSERT_PATH . 'reset-password/' . (preg_match('/^[0-9a-f]{64}$/', $token) ? $token : ''));

        $rules = [
            'new_password'     => ['label' => 'New password', 'rules' => 'required|min_length[8]|max_length[72]'],
            'confirm_password' => [
                'label'  => 'Confirm password',
                'rules'  => 'required|matches[new_password]',
                'errors' => ['matches' => 'The new passwords do not match.'],
            ],
        ];
        if (! $this->validate($rules)) {
            return $back->with('password_errors', $this->validator->getErrors());
        }

        if (! (new PasswordResetService())->reset($token, (string) $this->request->getPost('new_password'))) {
            return $back->with('error', 'This reset link has expired or was already used. Request a new one.');
        }

        return redirect()->to(ASSERT_PATH . 'login')->with('success', 'Your password has been reset. Sign in with your new password.');
    }
}
