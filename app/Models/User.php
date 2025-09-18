<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Model;

class User extends Model implements AuthenticatableContract, JWTSubject
{
    use Authenticatable;

    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password'];

    public const IS_REQUESTER = 1;
    public const IS_COR_SEC = 2;
    public const IS_LEGAL = 3;
    public const IS_DIRECTOR_1 = 4;
    public const IS_DIRECTOR_2 = 5;
    public const IS_ADMIN = 6;

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}