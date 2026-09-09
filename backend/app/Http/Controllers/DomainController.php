<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDomainRequest;
use App\Models\Domain;
use App\Models\Site;
use App\Support\DnsTxtLookup;

class DomainController extends Controller
{
    public function show(Site $site)
    {
        $this->authorize('view', $site);

        // Wrapped in a key on purpose: a bare null body comes back as {} once
        // Symfony's JsonResponse is done with it, which reads as "a domain
        // exists" on the client.
        return response()->json(['domain' => $site->customDomain]);
    }

    public function store(StoreDomainRequest $request, Site $site)
    {
        // One domain per site - replacing means the old host stops resolving,
        // which is the owner's call to make.
        $site->customDomain?->delete();

        $domain = $site->customDomain()->create($request->validated());

        return response()->json($domain->refresh(), 201);
    }

    public function verify(Domain $domain, DnsTxtLookup $dns)
    {
        $this->authorize('update', $domain);

        $values = $dns->txtValues($domain->txt_record_name);

        if (! in_array($domain->verification_token, $values, true)) {
            return response()->json([
                'message' => 'TXT-запис не знайдено. DNS може оновлюватись до кількох годин.',
                'domain' => $domain,
            ], 422);
        }

        $domain->forceFill(['verified_at' => now()])->save();

        return response()->json($domain);
    }

    public function destroy(Domain $domain)
    {
        $this->authorize('delete', $domain);

        $domain->delete();

        return response()->noContent();
    }
}
