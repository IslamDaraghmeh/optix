<?php
/**
 * Application Constants
 *
 * Global constants used throughout the application
 *
 * @package Optix
 * @version 1.0
 */

// User Roles
define('ROLE_ADMIN', 'admin');
define('ROLE_DOCTOR', 'doctor');
define('ROLE_OPTOMETRIST', 'optometrist');
define('ROLE_OPTICIAN', 'optician');
define('ROLE_RECEPTIONIST', 'receptionist');
define('ROLE_MANAGER', 'manager');
define('ROLE_CASHIER', 'cashier');

// User Permissions
define('PERM_MANAGE_USERS', 'manage_users');
define('PERM_MANAGE_PATIENTS', 'manage_patients');
define('PERM_VIEW_PATIENTS', 'view_patients');
define('PERM_MANAGE_EXAMS', 'manage_exams');
define('PERM_VIEW_EXAMS', 'view_exams');
define('PERM_MANAGE_PRESCRIPTIONS', 'manage_prescriptions');
define('PERM_VIEW_PRESCRIPTIONS', 'view_prescriptions');
define('PERM_MANAGE_APPOINTMENTS', 'manage_appointments');
define('PERM_VIEW_APPOINTMENTS', 'view_appointments');
define('PERM_MANAGE_INVENTORY', 'manage_inventory');
define('PERM_VIEW_INVENTORY', 'view_inventory');
define('PERM_MANAGE_POS', 'manage_pos');
define('PERM_PROCESS_SALES', 'process_sales');
define('PERM_VIEW_REPORTS', 'view_reports');
define('PERM_MANAGE_SETTINGS', 'manage_settings');
define('PERM_MANAGE_INSURANCE', 'manage_insurance');

// Appointment Statuses
define('APPOINTMENT_SCHEDULED', 'scheduled');
define('APPOINTMENT_CONFIRMED', 'confirmed');
define('APPOINTMENT_CHECKED_IN', 'checked_in');
define('APPOINTMENT_IN_PROGRESS', 'in_progress');
define('APPOINTMENT_COMPLETED', 'completed');
define('APPOINTMENT_CANCELLED', 'cancelled');
define('APPOINTMENT_NO_SHOW', 'no_show');

// Appointment Types
define('APPOINTMENT_TYPE_COMPREHENSIVE', 'comprehensive');
define('APPOINTMENT_TYPE_ROUTINE', 'routine');
define('APPOINTMENT_TYPE_CONTACT_LENS', 'contact_lens');
define('APPOINTMENT_TYPE_FOLLOW_UP', 'follow_up');
define('APPOINTMENT_TYPE_EMERGENCY', 'emergency');

// Transaction Statuses
define('TRANSACTION_PENDING', 'pending');
define('TRANSACTION_COMPLETED', 'completed');
define('TRANSACTION_REFUNDED', 'refunded');
define('TRANSACTION_VOIDED', 'voided');
define('TRANSACTION_PARTIAL_REFUND', 'partial_refund');

// Payment Methods
define('PAYMENT_CASH', 'cash');
define('PAYMENT_CREDIT_CARD', 'credit_card');
define('PAYMENT_DEBIT_CARD', 'debit_card');
define('PAYMENT_CHECK', 'check');
define('PAYMENT_INSURANCE', 'insurance');
define('PAYMENT_HSA_FSA', 'hsa_fsa');
define('PAYMENT_GIFT_CARD', 'gift_card');

// Payment Statuses
define('PAYMENT_PENDING', 'pending');
define('PAYMENT_COMPLETED', 'completed');
define('PAYMENT_FAILED', 'failed');
define('PAYMENT_REFUNDED', 'refunded');

// Product Categories
define('CATEGORY_FRAMES', 'frames');
define('CATEGORY_LENSES', 'lenses');
define('CATEGORY_CONTACT_LENSES', 'contact_lenses');
define('CATEGORY_SUNGLASSES', 'sunglasses');
define('CATEGORY_ACCESSORIES', 'accessories');
define('CATEGORY_SOLUTIONS', 'solutions');

// Prescription Types
define('PRESCRIPTION_EYEGLASSES', 'eyeglasses');
define('PRESCRIPTION_CONTACT_LENSES', 'contact_lenses');
define('PRESCRIPTION_READING', 'reading');
define('PRESCRIPTION_DISTANCE', 'distance');
define('PRESCRIPTION_PROGRESSIVE', 'progressive');

// Prescription Statuses
define('PRESCRIPTION_ACTIVE', 'active');
define('PRESCRIPTION_EXPIRED', 'expired');
define('PRESCRIPTION_CANCELLED', 'cancelled');

// Insurance Claim Statuses
define('CLAIM_DRAFT', 'draft');
define('CLAIM_SUBMITTED', 'submitted');
define('CLAIM_PENDING', 'pending');
define('CLAIM_APPROVED', 'approved');
define('CLAIM_REJECTED', 'rejected');
define('CLAIM_PAID', 'paid');

// Lab Order Statuses
define('LAB_ORDER_PENDING', 'pending');
define('LAB_ORDER_SUBMITTED', 'submitted');
define('LAB_ORDER_IN_PRODUCTION', 'in_production');
define('LAB_ORDER_COMPLETED', 'completed');
define('LAB_ORDER_DELIVERED', 'delivered');
define('LAB_ORDER_CANCELLED', 'cancelled');

// Inventory Adjustment Reasons
define('ADJUSTMENT_SALE', 'sale');
define('ADJUSTMENT_PURCHASE', 'purchase');
define('ADJUSTMENT_RETURN', 'return');
define('ADJUSTMENT_DAMAGE', 'damage');
define('ADJUSTMENT_LOSS', 'loss');
define('ADJUSTMENT_CORRECTION', 'correction');
define('ADJUSTMENT_TRANSFER', 'transfer');

// Examination Statuses
define('EXAM_DRAFT', 'draft');
define('EXAM_IN_PROGRESS', 'in_progress');
define('EXAM_COMPLETED', 'completed');
define('EXAM_SIGNED', 'signed');

// Flash Message Types
define('FLASH_SUCCESS', 'success');
define('FLASH_ERROR', 'error');
define('FLASH_WARNING', 'warning');
define('FLASH_INFO', 'info');

// HTTP Status Codes
define('HTTP_OK', 200);
define('HTTP_CREATED', 201);
define('HTTP_BAD_REQUEST', 400);
define('HTTP_UNAUTHORIZED', 401);
define('HTTP_FORBIDDEN', 403);
define('HTTP_NOT_FOUND', 404);
define('HTTP_METHOD_NOT_ALLOWED', 405);
define('HTTP_UNPROCESSABLE_ENTITY', 422);
define('HTTP_INTERNAL_SERVER_ERROR', 500);

// Date Formats
define('DATE_FORMAT', 'Y-m-d');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');
define('TIME_FORMAT', 'H:i:s');
define('DISPLAY_DATE_FORMAT', 'm/d/Y');
define('DISPLAY_DATETIME_FORMAT', 'm/d/Y h:i A');

// File Upload Types
define('FILE_TYPE_IMAGE', ['jpg', 'jpeg', 'png', 'gif']);
define('FILE_TYPE_DOCUMENT', ['pdf', 'doc', 'docx']);
define('FILE_TYPE_ALL_ALLOWED', array_merge(FILE_TYPE_IMAGE, FILE_TYPE_DOCUMENT));

// Default Values
define('DEFAULT_AVATAR', '/images/default-avatar.png');
define('DEFAULT_PRODUCT_IMAGE', '/images/default-product.png');

return [
    'roles' => [
        ROLE_ADMIN => 'Administrator',
        ROLE_DOCTOR => 'Doctor',
        ROLE_OPTOMETRIST => 'Optometrist',
        ROLE_OPTICIAN => 'Optician',
        ROLE_RECEPTIONIST => 'Receptionist',
        ROLE_MANAGER => 'Manager',
        ROLE_CASHIER => 'Cashier',
    ],
    'appointment_statuses' => [
        APPOINTMENT_SCHEDULED => 'Scheduled',
        APPOINTMENT_CONFIRMED => 'Confirmed',
        APPOINTMENT_CHECKED_IN => 'Checked In',
        APPOINTMENT_IN_PROGRESS => 'In Progress',
        APPOINTMENT_COMPLETED => 'Completed',
        APPOINTMENT_CANCELLED => 'Cancelled',
        APPOINTMENT_NO_SHOW => 'No Show',
    ],
    'payment_methods' => [
        PAYMENT_CASH => 'Cash',
        PAYMENT_CREDIT_CARD => 'Credit Card',
        PAYMENT_DEBIT_CARD => 'Debit Card',
        PAYMENT_CHECK => 'Check',
        PAYMENT_INSURANCE => 'Insurance',
        PAYMENT_HSA_FSA => 'HSA/FSA',
        PAYMENT_GIFT_CARD => 'Gift Card',
    ],
];
