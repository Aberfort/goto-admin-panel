<?php

namespace App\Policies;

use App\Models\Link;
use App\Models\Site;
use App\Models\User;

class LinkPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Link $link): bool
    {
        return $user->id === $link->site->user_id;
    }

    // Called as authorize('create', [Link::class, $site]) - a link can
    // only ever be created under a site the user owns.
    public function create(User $user, Site $site): bool
    {
        return $user->id === $site->user_id;
    }

    public function update(User $user, Link $link): bool
    {
        return $user->id === $link->site->user_id;
    }

    public function delete(User $user, Link $link): bool
    {
        return $user->id === $link->site->user_id;
    }
}
