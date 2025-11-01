<?php
/**
 * User Controller - Manages staff users, roles, and profiles
 */

namespace App\Controllers;

use App\Models\User;

class UserController extends BaseController
{
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
    }

    public function index(): void
    {
        $this->requirePermission('view_users');

        $page = (int)$this->get('page', 1);
        $role = $this->get('role', null);

        $where = $role ? "role = '{$role}'" : null;
        $users = $this->userModel->paginate($page, 20, $where, [], 'last_name', 'ASC');

        $this->view('users/index', ['users' => $users, 'role' => $role]);
    }

    public function create(): void
    {
        $this->requirePermission('create_users');

        if ($this->isPost()) {
            $this->requireCsrfToken();

            $rules = [
                'email' => 'required|email',
                'first_name' => 'required',
                'last_name' => 'required',
                'role' => 'required',
                'password' => 'required|min:8'
            ];

            if (!$this->validate($this->post(), $rules)) {
                $this->flashAndRedirect('error', 'Validation failed', $this->back());
            }

            $data = [
                'email' => $this->post('email'),
                'first_name' => $this->post('first_name'),
                'last_name' => $this->post('last_name'),
                'role' => $this->post('role'),
                'phone' => $this->post('phone'),
                'location_id' => $this->post('location_id'),
                'status' => 'active',
                'password' => $this->post('password')
            ];

            try {
                $id = $this->userModel->createUser($data);
                $this->logActivity('user_created', "Created user ID: {$id}");
                $this->flashAndRedirect('success', 'User created successfully', APP_URL . '/users');
            } catch (\Exception $e) {
                $this->flashAndRedirect('error', 'Failed to create user', $this->back());
            }
        } else {
            $this->view('users/create');
        }
    }

    public function edit(int $id): void
    {
        $this->requirePermission('edit_users');

        $user = $this->userModel->find($id);
        if (!$user) {
            $this->error404('User not found');
        }

        if ($this->isPost()) {
            $this->requireCsrfToken();

            $data = array_filter([
                'first_name' => $this->post('first_name'),
                'last_name' => $this->post('last_name'),
                'email' => $this->post('email'),
                'phone' => $this->post('phone'),
                'role' => $this->post('role'),
                'location_id' => $this->post('location_id'),
                'status' => $this->post('status')
            ], fn($v) => $v !== null);

            try {
                $this->userModel->update($id, $data);
                $this->logActivity('user_updated', "Updated user ID: {$id}");
                $this->flashAndRedirect('success', 'User updated successfully', APP_URL . '/users');
            } catch (\Exception $e) {
                $this->flashAndRedirect('error', 'Failed to update user', $this->back());
            }
        } else {
            $this->view('users/edit', ['user' => $user]);
        }
    }

    public function delete(int $id): void
    {
        $this->requirePermission('delete_users');
        $this->requireCsrfToken();

        try {
            $this->userModel->delete($id);
            $this->logActivity('user_deleted', "Deleted user ID: {$id}");
            $this->json(['success' => true, 'message' => 'User deleted successfully']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to delete user'], 500);
        }
    }

    public function changePassword(int $id): void
    {
        $this->requirePermission('change_password');
        $this->requireCsrfToken();

        $currentUser = $this->getCurrentUser();

        if ($id != $currentUser['id'] && !$this->hasPermission('edit_users')) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $currentPassword = $this->post('current_password');
        $newPassword = $this->post('new_password');

        if (!$this->userModel->verifyPassword($id, $currentPassword)) {
            $this->json(['success' => false, 'message' => 'Current password is incorrect'], 400);
        }

        try {
            $this->userModel->updatePassword($id, $newPassword);
            $this->logActivity('password_changed', "Changed password for user ID: {$id}");
            $this->json(['success' => true, 'message' => 'Password changed successfully']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to change password'], 500);
        }
    }

    public function profile(): void
    {
        $this->requireAuth();

        $user = $this->getCurrentUser();
        $this->view('users/profile', ['user' => $user]);
    }

    public function updateProfile(): void
    {
        $this->requireAuth();
        $this->requireCsrfToken();

        $currentUser = $this->getCurrentUser();
        $userId = $currentUser['id'];

        $data = [
            'first_name' => $this->post('first_name'),
            'last_name' => $this->post('last_name'),
            'phone' => $this->post('phone')
        ];

        try {
            $this->userModel->update($userId, $data);
            $this->logActivity('profile_updated', "Updated own profile");
            $this->flashAndRedirect('success', 'Profile updated successfully', APP_URL . '/users/profile');
        } catch (\Exception $e) {
            $this->flashAndRedirect('error', 'Failed to update profile', $this->back());
        }
    }

    public function roles(): void
    {
        $this->requirePermission('view_roles');

        $statistics = $this->userModel->getUserStatistics();
        $this->view('users/roles', ['statistics' => $statistics]);
    }
}
