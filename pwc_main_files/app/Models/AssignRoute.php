<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignRoute extends Model
{
    use HasFactory;

    protected $fillable = [
        'route_id',
        'staff_id',
    ];
    public function routeStaff(){
        return $this->hasOne(StaffRoute::class,'id','route_id');
    }
    public function getClientCount(){
        return $this->hasMany(ClientRoute::class,'route_id','route_id');
    }
    public function staff(){
        return $this->belongsTo(User::class, 'staff_id', 'id');
    }
}

