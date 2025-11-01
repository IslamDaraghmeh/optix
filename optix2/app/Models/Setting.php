<?php
/**
 * Setting Model - Manages system settings and configuration
 */

namespace App\Models;

class Setting extends BaseModel
{
    protected string $table = 'settings';
    protected bool $useSoftDeletes = false;
    protected bool $useTimestamps = true;

    public function getSetting(string $key, $default = null)
    {
        $setting = $this->whereOne("setting_key = ?", [$key]);
        return $setting ? $setting['setting_value'] : $default;
    }

    public function getSettings(array $keys): array
    {
        if (empty($keys)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($keys), '?'));
        $sql = "SELECT setting_key, setting_value
                FROM {$this->table}
                WHERE setting_key IN ({$placeholders})";

        $results = $this->db->select($sql, $keys);

        $settings = [];
        foreach ($results as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        return $settings;
    }

    public function saveSetting(string $key, $value): bool
    {
        $existing = $this->whereOne("setting_key = ?", [$key]);

        if ($existing) {
            return $this->db->update(
                $this->table,
                ['setting_value' => $value, 'updated_at' => date(DATETIME_FORMAT)],
                "setting_key = ?",
                [$key]
            ) > 0;
        } else {
            return $this->db->insert($this->table, [
                'setting_key' => $key,
                'setting_value' => $value,
                'created_at' => date(DATETIME_FORMAT),
                'updated_at' => date(DATETIME_FORMAT)
            ]) > 0;
        }
    }

    public function deleteSetting(string $key): bool
    {
        return $this->db->delete($this->table, "setting_key = ?", [$key]) > 0;
    }

    public function getAllSettings(): array
    {
        $results = $this->db->select("SELECT setting_key, setting_value FROM {$this->table}");

        $settings = [];
        foreach ($results as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        return $settings;
    }
}
