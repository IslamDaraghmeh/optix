<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Patient extends Model implements Auditable
{
    use HasFactory, AuditableTrait;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'birth_date',
        'address',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }

    public function glasses()
    {
        return $this->hasMany(Glass::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
