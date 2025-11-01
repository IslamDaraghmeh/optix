<?php
/**
 * Settings Controller - Manages system settings and configuration
 */

namespace App\Controllers;

use App\Models\Setting;
use App\Models\Location;

class SettingsController extends BaseController
{
    private Setting $settingModel;
    private Location $locationModel;

    public function __construct()
    {
        parent::__construct();
        $this->settingModel = new Setting();
        $this->locationModel = new Location();
    }

    public function index(): void
    {
        $this->requirePermission('view_settings');
        $this->view('settings/index');
    }

    public function general(): void
    {
        $this->requirePermission('edit_settings');

        if ($this->isPost()) {
            $this->requireCsrfToken();

            $settings = [
                'clinic_name' => $this->post('clinic_name'),
                'clinic_email' => $this->post('clinic_email'),
                'clinic_phone' => $this->post('clinic_phone'),
                'timezone' => $this->post('timezone'),
                'currency' => $this->post('currency'),
                'date_format' => $this->post('date_format')
            ];

            foreach ($settings as $key => $value) {
                $this->settingModel->saveSetting($key, $value);
            }

            $this->logActivity('settings_updated', 'Updated general settings');
            $this->flashAndRedirect('success', 'Settings updated successfully', APP_URL . '/settings/general');
        } else {
            $settings = $this->settingModel->getSettings([
                'clinic_name', 'clinic_email', 'clinic_phone', 'timezone', 'currency', 'date_format'
            ]);

            $this->view('settings/general', ['settings' => $settings]);
        }
    }

    public function locations(): void
    {
        $this->requirePermission('manage_locations');

        $locations = $this->locationModel->all();

        if ($this->isPost()) {
            $this->requireCsrfToken();

            $data = [
                'name' => $this->post('name'),
                'address' => $this->post('address'),
                'city' => $this->post('city'),
                'state' => $this->post('state'),
                'zip_code' => $this->post('zip_code'),
                'phone' => $this->post('phone'),
                'email' => $this->post('email'),
                'is_active' => true
            ];

            try {
                $id = $this->locationModel->create($data);
                $this->logActivity('location_created', "Created location ID: {$id}");
                $this->flashAndRedirect('success', 'Location created successfully', APP_URL . '/settings/locations');
            } catch (\Exception $e) {
                $this->flashAndRedirect('error', 'Failed to create location', $this->back());
            }
        } else {
            $this->view('settings/locations', ['locations' => $locations]);
        }
    }

    public function appointmentTypes(): void
    {
        $this->requirePermission('edit_settings');

        if ($this->isPost()) {
            $this->requireCsrfToken();

            $types = $this->post('appointment_types', []);
            $this->settingModel->saveSetting('appointment_types', json_encode($types));

            $this->logActivity('settings_updated', 'Updated appointment types');
            $this->flashAndRedirect('success', 'Appointment types updated', APP_URL . '/settings/appointment-types');
        } else {
            $types = $this->settingModel->getSetting('appointment_types', '[]');
            $this->view('settings/appointment-types', ['types' => json_decode($types, true)]);
        }
    }

    public function emailSettings(): void
    {
        $this->requirePermission('edit_settings');

        if ($this->isPost()) {
            $this->requireCsrfToken();

            $settings = [
                'smtp_host' => $this->post('smtp_host'),
                'smtp_port' => $this->post('smtp_port'),
                'smtp_username' => $this->post('smtp_username'),
                'smtp_password' => $this->post('smtp_password'),
                'smtp_encryption' => $this->post('smtp_encryption'),
                'from_email' => $this->post('from_email'),
                'from_name' => $this->post('from_name')
            ];

            foreach ($settings as $key => $value) {
                $this->settingModel->saveSetting($key, $value);
            }

            $this->logActivity('settings_updated', 'Updated email settings');
            $this->flashAndRedirect('success', 'Email settings updated', APP_URL . '/settings/email');
        } else {
            $settings = $this->settingModel->getSettings([
                'smtp_host', 'smtp_port', 'smtp_username', 'smtp_encryption', 'from_email', 'from_name'
            ]);

            $this->view('settings/email', ['settings' => $settings]);
        }
    }

    public function taxSettings(): void
    {
        $this->requirePermission('edit_settings');

        if ($this->isPost()) {
            $this->requireCsrfToken();

            $settings = [
                'default_tax_rate' => $this->post('default_tax_rate'),
                'tax_included' => $this->post('tax_included', false)
            ];

            foreach ($settings as $key => $value) {
                $this->settingModel->saveSetting($key, $value);
            }

            $this->logActivity('settings_updated', 'Updated tax settings');
            $this->flashAndRedirect('success', 'Tax settings updated', APP_URL . '/settings/tax');
        } else {
            $settings = $this->settingModel->getSettings(['default_tax_rate', 'tax_included']);
            $this->view('settings/tax', ['settings' => $settings]);
        }
    }

    public function backupSettings(): void
    {
        $this->requirePermission('manage_backups');

        if ($this->isPost()) {
            $this->requireCsrfToken();

            $settings = [
                'auto_backup' => $this->post('auto_backup', false),
                'backup_frequency' => $this->post('backup_frequency'),
                'backup_retention_days' => $this->post('backup_retention_days')
            ];

            foreach ($settings as $key => $value) {
                $this->settingModel->saveSetting($key, $value);
            }

            $this->logActivity('settings_updated', 'Updated backup settings');
            $this->flashAndRedirect('success', 'Backup settings updated', APP_URL . '/settings/backup');
        } else {
            $settings = $this->settingModel->getSettings([
                'auto_backup', 'backup_frequency', 'backup_retention_days'
            ]);

            $this->view('settings/backup', ['settings' => $settings]);
        }
    }
}
