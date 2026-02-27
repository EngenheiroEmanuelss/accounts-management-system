<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';
    protected $fillable = [
        'type',
        'status',
        'amount',
        'origin_account_id',
        'destination_account_id',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function extracts()
    {
        return $this->hasMany(Extract::class);
    }

    public function createExtract(string $description, $userId)
    {
        $this->extracts()->create([
            'transaction_id' => $this->id,
            'description'    => $description,
            'user_id'        => $userId
        ]);
    }
}
