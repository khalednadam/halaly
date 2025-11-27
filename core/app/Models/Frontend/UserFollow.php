<?php

namespace App\Models\Frontend;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserFollow extends Model
{
    use HasFactory;

    protected $fillable = ['follower_id', 'following_id'];

    /**
     * Get the user who is following (customer)
     */
    public function follower()
    {
        return $this->belongsTo(User::class, 'follower_id', 'id');
    }

    /**
     * Get the user being followed (vendor)
     */
    public function following()
    {
        return $this->belongsTo(User::class, 'following_id', 'id');
    }
}

