<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'document',
        'email',
        'password',
    ];

    public function userAccounts()
    {
        return $this->hasMany(UserAccount::class, 'user_id');
    }

    public function accounts()
    {
        return $this->hasManyThrough(
            Account::class,
            UserAccount::class,
            'user_id',
            'id',
            'id',
            'account_id'
        );
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'user_id');
    }

}
