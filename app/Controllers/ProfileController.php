<?php

/**
 * ProfileController.php
 *
 * @category   Controller
 * @purpose    The signed-in user's own page: who they are, their role and products,
 *             their personal details (name, username, date of birth) and password.
 */

namespace App\Controllers;

use App\Models\User\MyWorkModel;
use App\Models\User\UserModel;
use CodeIgniter\Events\Events;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\Response;
use Config\SprintModelConfig;

class ProfileController extends BaseController
{
    private const MAX_PASSWORD_ATTEMPTS = 5;
    private const PASSWORD_LOCK_SECONDS = 900;

    public function index(): string
    {
        $userId  = (int) session()->get('employee_id');
        $profile = model(UserModel::class)->getProfile($userId) ?? [];

        return $this->template_view('user/profile', [
            'profile'   => $profile,
            'products'  => model(\App\Models\Backlog\BacklogModel::class)->getUserProductDetails($userId),
            'productRoles' => model(UserModel::class)->getProductRoles($userId),
            'openTasks' => model(MyWorkModel::class)->countOpen($userId, (new SprintModelConfig())->pendingTaskStatuses),
        ], 'My profile', ['My profile' => '']);
    }

    /**
     * Save the user's own name, username and date of birth.
     */
    public function updateDetails(): RedirectResponse
    {
        $back = redirect()->to(ASSERT_PATH . 'profile#details');
        $userId = (int) session()->get('employee_id');
        $post = $this->request->getPost();

        $rules = [
            'first_name'    => ['label' => 'First name', 'rules' => 'required|max_length[50]'],
            'last_name'     => ['label' => 'Last name', 'rules' => 'permit_empty|max_length[50]'],
            'username'      => ['label' => 'Username', 'rules' => 'required|min_length[3]|max_length[50]|regex_match[/^[A-Za-z0-9._-]+$/]',
                                'errors' => ['regex_match' => 'Use only letters, numbers, dots, dashes and underscores.']],
            'date_of_birth' => ['label' => 'Date of birth', 'rules' => 'permit_empty|valid_date[Y-m-d]'],
        ];
        $errors = $this->validateData($post, $rules) ? [] : $this->validator->getErrors();

        $username = trim((string) ($post['username'] ?? ''));
        if (!isset($errors['username']) && model(UserModel::class)->usernameTaken($username, $userId)) {
            $errors['username'] = 'This username is already taken. Please choose another one.';
        }
        $dob = trim((string) ($post['date_of_birth'] ?? ''));
        if ($dob !== '' && !isset($errors['date_of_birth']) && ($dob > date('Y-m-d') || $dob < '1900-01-01')) {
            $errors['date_of_birth'] = 'Enter a date of birth between 1900 and today.';
        }
        if ($errors) {
            return $back->withInput()->with('details_errors', $errors);
        }

        $before = model(UserModel::class)->getProfile($userId);
        $details = [
            'first_name'        => trim($post['first_name']),
            'last_name'         => trim($post['last_name'] ?? ''),
            'external_username' => $username,
            'date_of_birth'     => $dob === '' ? null : $dob,
        ];
        model(UserModel::class)->updateOwnDetails($userId, $details);
        session()->set('first_name', $details['first_name']);

        $changes = [];
        foreach (['first_name' => 'first name', 'last_name' => 'last name', 'external_username' => 'username', 'date_of_birth' => 'date of birth'] as $field => $label) {
            if ((string) ($before[$field] ?? '') !== (string) ($details[$field] ?? '')) {
                $changes[] = $label;
            }
        }
        if ($changes) {
            Events::trigger('log_actions', [
                'r_action_type_id' => 2,
                'r_module_id'      => 15, // manage users
                'reference_id'     => (int) $before['user_id'],
                'product_id'       => 0,
                'action_data'      => 'Updated own profile: ' . implode(', ', $changes),
            ]);
        }

        $message = in_array('username', $changes, true)
            ? 'Your details were saved. Sign in with your new username from now on.'
            : 'Your details were saved.';

        return $back->with('success', $message);
    }

    /**
     * Live check used by the profile form while typing a username.
     */
    public function checkUsername(): Response
    {
        $username = trim((string) $this->request->getGet('username'));
        $valid = (bool) preg_match('/^[A-Za-z0-9._-]{3,50}$/', $username);
        $taken = $valid && model(UserModel::class)->usernameTaken($username, (int) session()->get('employee_id'));

        return $this->response->setJSON(['valid' => $valid, 'available' => $valid && !$taken]);
    }

    public function changePassword(): RedirectResponse
    {
        $back   = redirect()->to(ASSERT_PATH . 'profile#password');
        $userId = (int) session()->get('employee_id');

        $throttleKey = 'pwchange_' . $userId;
        $failures    = (int) (cache()->get($throttleKey) ?? 0);
        if ($failures >= self::MAX_PASSWORD_ATTEMPTS) {
            return $back->with('error', 'Too many attempts. Please try again in 15 minutes.');
        }

        $rules = [
            'current_password' => ['label' => 'Current password', 'rules' => 'required'],
            'new_password'     => [
                'label'  => 'New password',
                'rules'  => 'required|min_length[8]|max_length[72]|differs[current_password]',
                'errors' => ['differs' => 'Choose a new password that is different from the current one.'],
            ],
            'confirm_password' => [
                'label'  => 'Confirm password',
                'rules'  => 'required|matches[new_password]',
                'errors' => ['matches' => 'The new passwords do not match.'],
            ],
        ];
        if (! $this->validate($rules)) {
            return $back->with('password_errors', $this->validator->getErrors());
        }

        $changed = model(UserModel::class)->changePassword(
            $userId,
            (string) $this->request->getPost('current_password'),
            (string) $this->request->getPost('new_password')
        );
        if (! $changed) {
            cache()->save($throttleKey, $failures + 1, self::PASSWORD_LOCK_SECONDS);

            return $back->with('password_errors', ['current_password' => 'Your current password is incorrect.']);
        }

        cache()->delete($throttleKey);
        session()->regenerate(true);
        session()->remove('must_change_password');

        return $back->with('success', 'Your password has been changed.');
    }
}
