<?php

namespace App\Models\ActionItem;

use App\Models\ActionItem\ActionItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActionItemArea extends Model
{
    use HasFactory;
    protected $table = 'actionItemArea';
    protected $primaryKey = 'actionItemAreaId';

    /**
     * Get all of the actionItem for the ActionItemArea
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function actionItem()
    {
        return $this->hasMany(ActionItem::class, 'actionItemAreaId', 'actionItemAreaId');
    }
}
