<?php

namespace App\Http\Controllers;

use App\Http\Requests\SiteRequest;
use App\Models\Site;

class SiteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sites = Site::with('urls')->get();
        return response()->json($sites);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SiteRequest $request)
    {
        $validated = $request->validate([
            'domain' => 'required|unique:sites,domain',
        ]);

        $site = Site::create($validated);
        return response()->json($site, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $site = Site::with('urls')->findOrFail($id);
        return response()->json($site);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SiteRequest $request, $id)
    {
        $site = Site::findOrFail($id);
        $validated = $request->validate([
            'domain' => 'required|unique:sites,domain,' . $id,
        ]);

        $site->update($validated);
        return response()->json($site);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $site = Site::findOrFail($id);
        $site->delete();
        return response()->json(null, 204);
    }
}
