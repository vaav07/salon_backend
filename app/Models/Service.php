<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;
    protected $fillable = [
        "admin_id",
        "service_name",
        "description",
        "price",
    ];
    protected $table = 'services';

    public function invoices()
    {
        return $this->belongsToMany(Sale::class, 'sale_services');
    }
}
