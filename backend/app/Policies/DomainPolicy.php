<?php

namespace App\Policies;

use App\Models\Domain;
use App\Models\Site;
use App\Models\User;

class DomainPolicy
{
    public function view(User $user, Domain $domain): bool
    {
        return $user->id === $domain->site->user_id;
    }

    // Called as authorize('create', [Domain::class, $site]).
    public function create(User $user, Site $site): bool
    {
        return $user->id === $site->user_id;
    }

    public function update(User $user, Domain $domain): bool
    {
        return $user->id === $domain->site->user_id;
    }

    public function delete(User $user, Domain $domain): bool
    {
        return $user->id === $domain->site->user_id;
    }
}
