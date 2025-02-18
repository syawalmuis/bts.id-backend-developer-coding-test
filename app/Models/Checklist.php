<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Checklist extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'cover'
    ];

    protected $hidden = [
        'user_id'
    ];

    public function items()
    {
        return $this->hasMany(ChecklistItem::class)->where('parent_id', null);
    }
}
