<?php

namespace App\Models;

use App\Events\ConcernCreated;
use App\User;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Concern.
 *
 * @property int                             $id
 * @property int                             $user_id         用户ID
 * @property int                             $concern_user_id 关注的用户ID
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \App\User                       $concernUser
 * @property \App\User                       $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Concern newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Concern newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Concern query()
 * @mixin \Eloquent
 */
class Concern extends Model
{
    protected $guarded = [];

    protected $dispatchesEvents = [
        'created' => ConcernCreated::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function concernUser()
    {
        return $this->belongsTo(User::class, 'concern_user_id');
    }
}
