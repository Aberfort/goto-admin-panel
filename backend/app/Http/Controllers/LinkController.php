<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLinkRequest;
use App\Http\Requests\UpdateLinkRequest;
use App\Models\Link;
use App\Models\Site;

class LinkController extends Controller
{
    public function index(Site $site)
    {
        $this->authorize('view', $site);

        return $site->links()->latest()->get();
    }

    public function store(StoreLinkRequest $request, Site $site)
    {
        $link = $site->links()->create($request->validated());

        return response()->json($link, 201);
    }

    public function show(Link $link)
    {
        $this->authorize('view', $link);

        return $link;
    }

    public function update(UpdateLinkRequest $request, Link $link)
    {
        $link->update($request->validated());

        return $link;
    }

    public function destroy(Link $link)
    {
        $this->authorize('delete', $link);

        $link->delete();

        return response()->json(null, 204);
    }

    public function toggle(Link $link)
    {
        $this->authorize('update', $link);

        $link->update(['is_active' => ! $link->is_active]);

        return $link;
    }
}
