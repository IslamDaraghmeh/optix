<?php
/**
 * User Model - Handles staff user data, authentication, and roles
 */

namespace App\Models;

class User extends BaseModel
{
    protected string $table = 'users';

    public function findByEmail(string $email)
    {
        return $this->whereOne("email = ?", [$email]);
    }

    public function createUser(array $data): int
    {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        }

        return $this->create($data);
    }

    public function updatePassword(int $id, string $newPassword): bool
    {
        $hashed = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        return $this->update($id, ['password' => $hashed]) > 0;
    }

    public function verifyPassword(int $id, string $password): bool
    {
        $user = $this->find($id);
        return $user && password_verify($password, $user['password']);
    }

    public function getByRole(string $role): array
    {
        return $this->where("role = ? AND status = 'active'", [$role]);
    }

    public function getActiveUsers(?int $locationId = null): array
    {
        $sql = "SELECT u.*, l.name as location_name
                FROM {$this->table} u
                LEFT JOIN locations l ON u.location_id = l.id
                WHERE u.status = 'active' AND u.{$this->deletedAt} IS NULL";

        $params = [];

        if ($locationId) {
            $sql .= " AND u.location_id = ?";
            $params[] = $locationId;
        }

        $sql .= " ORDER BY u.last_name, u.first_name";

        return $this->db->select($sql, $params);
    }

    public function getUserStatistics(): array
    {
        $total = $this->count("status = 'active'");

        $byRole = $this->db->select(
            "SELECT role, COUNT(*) as count
             FROM {$this->table}
             WHERE status = 'active' AND {$this->deletedAt} IS NULL
             GROUP BY role"
        );

        return [
            'total_active' => $total,
            'by_role' => $byRole
        ];
    }

    public function updateLastLogin(int $id): bool
    {
        return $this->update($id, ['last_login' => date(DATETIME_FORMAT)]) > 0;
    }
}
