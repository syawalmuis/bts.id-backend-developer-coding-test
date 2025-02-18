<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistItem extends Model
{
    protected $fillable = [
        'checklist_id',
        'parent_id',
        'name',
        'description',
        'is_completed'
    ];

    public function checklist()
    {
        return $this->belongsTo(Checklist::class);
    }

    public function parent()
    {
        return $this->belongsTo(ChecklistItem::class, 'parent_id');
    }

    public function childrens()
    {
        return $this->hasMany(ChecklistItem::class, 'parent_id');
    }
}
