<?php

namespace App\Models\ActionItem;

use Illuminate\Database\Eloquent\Model;
use App\Models\ActionItem\ActionItemData;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActionItem extends Model
{
    use HasFactory;

    protected $table = 'actionItem';
    protected $primaryKey = 'actionItemId';

    /**
     * Get all of the actionItemData for the ActionItem
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function actionItemData()
    {
        return $this->hasMany(ActionItemData::class, 'actionItemId', 'actionItemId');
    }
}
