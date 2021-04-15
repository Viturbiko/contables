<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;

    protected $fillable = [
    	'name'
    ];

    protected $casts = [
    	'id' => 'Int',
    	'user_id' => 'Int'
    ];

    public function user(): BelongsTo
    {
    	return $this->belongsTo(User::class);
    }

    public function scopeByLoggedInUser($query)
    {
        if (!request()->user()) {
            return $query;
        }

        return $query->where('user_id', request()->user()->id);
    }
}
