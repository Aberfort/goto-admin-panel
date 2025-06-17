<?php

namespace App\Http\Controllers;

use App\Http\Requests\UrlRequest;

class UrlController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($siteId)
    {
        $site = Site::with('urls')->findOrFail($siteId);
        return response()->json($site->urls);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UrlRequest $request, $siteId)
    {
        $site = Site::findOrFail($siteId);

        $validated = $request->validate([
            'type' => 'required|string',
            'url' => 'required|url',
        ]);

        $url = $site->urls()->create($validated);
        return response()->json($url, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $url = Url::with('site')->findOrFail($id);
        return response()->json($url);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UrlRequest $request, $id)
    {
        $url = Url::findOrFail($id);

        $validated = $request->validate([
            'type' => 'required|string',
            'url' => 'required|url',
        ]);

        $url->update($validated);
        return response()->json($url);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $url = Url::findOrFail($id);
        $url->delete();
        return response()->json(null, 204);
    }
}
