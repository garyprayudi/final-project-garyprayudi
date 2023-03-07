<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TSOH extends Model
{
    use HasFactory;

    protected $table = 't_soh';
    protected $primaryKey = 'id';
    public $timestamps = false;
    
    protected $guarded = ['id'];

    public function salesOrderDetail(Type $var = null): HasMany
    {
        return $this->hasMany(TSOD::class, 'so_h_id', 'id');
    }

    public function customer(): HasOne
    {
        return $this->hasOne(MCustomer::class, 'id', 'customer_id');
    }

    public function scopeSearch($query, $search)
    {
        $query->where(function($where) use($search) {
            $where->orWhere('invoice', 'ilike', "%{$search}%")
                ->orWhere('date', 'ilike', "%{$search}%")
                ->orWhereRelation('customer', 'code', 'ilike', "%{$search}%")
            ->orWhereRelation('customer', 'name', 'ilike', "%{$search}%");
        });
    }
}
