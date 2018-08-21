<?php

namespace Netcore\Aven\Content\Models;

use Illuminate\Database\Eloquent\Model;

class ContentBlock extends Model
{

    /**
     * @var array
     */
    protected $fillable = [
        'sequence_no',
        'block_type',
        'block_id',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function widget()
    {
        return $this->morphTo('block');
    }
}
