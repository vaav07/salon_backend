<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;
    protected $fillable = [
        "admin_id",
        "user_id",
        "fullname",
        "email",
        "phone_no",
        "alt_phone_no",
        "address",
        "state",
        "city",
        "pincode",
        "dob",
        "gender",
        "date_of_joining"
    ];
    protected $table = 'employees';
}
