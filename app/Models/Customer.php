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
        "fullname",
        "email",
        "phone_no",
        // "alt_phone_no",
        // "address",
        // "state",
        // "city",
        // "pincode",
        // "dob",
        "gender"
    ];
    protected $table = 'customers';

    // public function latestSale()
    // {
    //     return $this->hasOne(Sale::class)->latest('sale_date');
    // }

    public function scopeInactive($query)
    {
        $oneMonthAgo = now()->subMonth(); // Get the date/time 1 month ago

        return $query->whereDoesntHave('latestSale', function ($query) use ($oneMonthAgo) {
            $query->where('sale_date', '>=', $oneMonthAgo);
        });
    }

    public function latestSale()
    {
        return $this->hasOne(Sale::class)
            ->select('customer_id', 'sale_date')
            ->latest('sale_date');
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'customer_id');
    }
}
