<?php

namespace App\Http\Controllers;

use App\Actions\RecordLinkClick;
use App\Models\Link;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectController extends Controller
{
    public function go(string $code, Request $request, RecordLinkClick $recordLinkClick): RedirectResponse
    {
        $link = Link::where('short_code', $code)->where('is_active', true)->first();

        if (! $link) {
            abort(Response::HTTP_NOT_FOUND);
        }

        $recordLinkClick->handle($link, $request);

        return redirect()->away($link->target_url, Response::HTTP_FOUND);
    }
}
