<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Redis;

class UserObserver
{
    public function updated(User $user): void
    {
        // Имя изменилось — публикуем событие для денормализации в boardy_api.comments.
        if ($user->wasChanged('name')) {
            Redis::publish('user.renamed', json_encode([
                'id'       => $user->id,
                'new_name' => $user->name,
            ]));
        }
    }
}
