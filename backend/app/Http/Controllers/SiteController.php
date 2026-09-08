<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSiteRequest;
use App\Http\Requests\UpdateSiteRequest;
use App\Models\Site;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()
            ->sites()
            ->withCount('links')
            ->latest()
            ->get();
    }

    public function store(StoreSiteRequest $request)
    {
        $site = $request->user()->sites()->create($request->validated());

        return response()->json($site, 201);
    }

    public function show(Site $site)
    {
        $this->authorize('view', $site);

        return $site->loadCount('links');
    }

    public function update(UpdateSiteRequest $request, Site $site)
    {
        $site->update($request->validated());

        return $site;
    }

    public function destroy(Site $site)
    {
        $this->authorize('delete', $site);

        $site->delete();

        return response()->json(null, 204);
    }
}
