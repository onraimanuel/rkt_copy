<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Merchants extends Model
{
 
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'merchants'; // sesuaikan dengan nama tabel yang sesuai

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
    'merchant_id',	
	'user_id',
    'nama_merchant',
    'deskripsi_toko',	
    'kontak_toko',	
    'foto_merchant',
    'is_verified',
    'on_vacation',
    'created_at',
    'updated_at'
    ];

    protected $primaryKey = 'merchant_id';

    public function stocks()
    {
        return $this->hasMany(Stocks::class, 'merchant_id');
    }

}
