<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Exam extends Model implements Auditable
{
    use HasFactory, AuditableTrait;

    protected $fillable = [
        'patient_id',
        'exam_date',
        'right_eye_sphere',
        'right_eye_cylinder',
        'right_eye_axis',
        'left_eye_sphere',
        'left_eye_cylinder',
        'left_eye_axis',
        'notes',
    ];

    protected $casts = [
        'exam_date' => 'date',
        'right_eye_sphere' => 'decimal:2',
        'right_eye_cylinder' => 'decimal:2',
        'left_eye_sphere' => 'decimal:2',
        'left_eye_cylinder' => 'decimal:2',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
