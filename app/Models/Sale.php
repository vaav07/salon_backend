<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;
    protected $table = 'sales';

    protected $fillable = [
        'admin_id',
        'user_id',
        'employee_id',
        'customer_id',
        'sale_date',
        'sale_time',
        'payment_method',
        'total_price',
    ];

    public function services()
    {
        return $this->belongsToMany(Service::class, 'sale_services');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }


    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
