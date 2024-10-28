<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
     const ROLE_LOCATION = 2;
    const ROLE_COMPANY = 1;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'ghl_api_key',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function dropshipzonetoken()
    {
        return $this->hasOne(DropshipzoneToken::class, 'user_id');
    }
    public function crmauth()
    {
        return $this->hasOne(CrmAuths::class, 'user_id');
    }
    public function companyCrmAuth()
    {
        return $this->hasOne(CrmAuths::class, 'user_id')->where('user_type','company');
    }
}
