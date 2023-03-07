<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TReceipt extends Model
{
    use HasFactory;

    protected $table = 't_receipts';
    protected $primaryKey = 'id';
    public $timestamps = false;
    
    protected $guarded = ['id'];

    public function customer(): HasOne
    {
        return $this->hasOne(MCustomer::class, 'id', 'customer_id');
    }

    public function scopeSearch($query, $search)
    {
        $query->where(function($where) use($search) {
            $where->orWhere('code', 'ilike', "%{$search}%")
                ->orWhere('name', 'ilike', "%{$search}%")
                ->orWhereRelation('customer', 'code', 'ilike', "%{$search}%")
            ->orWhereRelation('customer', 'name', 'ilike', "%{$search}%");
        });
    }
}
