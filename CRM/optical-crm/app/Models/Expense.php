<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'amount',
        'category',
        'expense_date',
        'payment_method',
        'receipt_number',
        'vendor',
        'notes',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
    ];

    // Expense categories
    public static function getCategories()
    {
        return [
            'office_supplies' => 'Office Supplies',
            'equipment' => 'Equipment',
            'utilities' => 'Utilities',
            'rent' => 'Rent',
            'marketing' => 'Marketing',
            'professional_services' => 'Professional Services',
            'travel' => 'Travel',
            'maintenance' => 'Maintenance',
            'insurance' => 'Insurance',
            'other' => 'Other',
        ];
    }

    // Payment methods
    public static function getPaymentMethods()
    {
        return [
            'cash' => 'Cash',
            'credit_card' => 'Credit Card',
            'debit_card' => 'Debit Card',
            'bank_transfer' => 'Bank Transfer',
            'check' => 'Check',
            'other' => 'Other',
        ];
    }
}
