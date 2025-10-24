<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Model;

class User extends Model implements AuthenticatableContract, JWTSubject
{
    use Authenticatable;

    // protected $fillable = ['name', 'email', 'password','po'];
    
    protected $hidden = ['password'];
    protected $guarded = ['id'];
    public const IS_REQUESTER = 1;
    public const IS_COR_SEC = 2;
    public const IS_LEGAL = 3;
    public const IS_DIRECTOR_1 = 4;
    public const IS_DIRECTOR_2 = 5;
    public const IS_ADMIN = 6;

    public static $POSITION = [1=>'Requester',2=>'Corporation Secretary',3=>'Legal',4=>'Director 1',5=>'Director 2',6=>'Administrator'];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}