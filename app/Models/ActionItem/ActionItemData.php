<?php

namespace App\Models\ActionItem;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActionItemData extends Model
{
    use HasFactory;

    protected $table = 'actionItemData';
    protected $primaryKey = 'actionItemDataId';
    protected $fillable = ['actionItemId', 'userId', 'itemCompleted', 'itemNotes'];
}
