<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    protected $fillable = [
        "admin_id",
        "user_id",
        "employee_fullname",
        "email",
        "phone_no",
        "alt_phone_no",
        "address",
        "state",
        "city",
        "pincode",
        "dob",
        "gender"
    ];
    protected $table = 'customers';
}
