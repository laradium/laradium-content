<?php

namespace Laradium\Laradium\Content\Models;

use Illuminate\Database\Eloquent\Model;

class ContentBlock extends Model
{

    /**
     * @var array
     */
    protected $fillable = [
        'sequence_no',
        'title',
        'block_type',
        'block_id',
        'is_active',
        'class',
        'style',
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
    public function block()
    {
        return $this->morphTo();
    }
}
