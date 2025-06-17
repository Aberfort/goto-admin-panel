<?php

namespace App\Http\Controllers;

use App\Models\Website;
use Illuminate\Http\Request;
use App\Http\Resources\WebsiteResource;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;


class WebsiteController extends Controller {
    public function index(Request $request) {
        $apiToken = $request->query('api_token');

        if ($apiToken) {
            $website = Website::where('api_token', $apiToken)->first();

            if (!$website) {
                return response()->json(['error' => 'Invalid token'], 404);
            }

            return response()->json(['data' => [$website]], 200);
        }

        $websites = Website::all();
        return WebsiteResource::collection($websites);
    }


    public function store( Request $request ) {
        $validated = $request->validate( [
            'name'      => 'required|string|max:255',
            'reg_url'   => 'required|url',
            'front_url' => 'required|url',
            'app_url'   => 'required|url',
        ] );

        try {
            $website = Website::create( $validated );

            // Завантажте всі атрибути, включаючи api_token
            $website->refresh();

            return response()->json( [
                'message' => 'Вебсайт успішно додано.',
                'website' => $website
            ], 201 );
        } catch ( \Exception $e ) {
            Log::error( 'Error adding website:', [ 'error' => $e->getMessage() ] );

            return response()->json( [ 'message' => 'Internal Server Error' ], 500 );
        }
    }

    /**
     * Перегенерує API токен для конкретного вебсайту.
     */
    public function regenerateToken( $id ) {
        $website = Website::find( $id );

        if ( ! $website ) {
            return response()->json( [ 'message' => 'Вебсайт не знайдено.' ], 404 );
        }

        try {
            // Генеруємо новий токен
            do {
                $newToken = Str::random( 60 );
            } while ( Website::where( 'api_token', $newToken )->exists() );

            $website->api_token = $newToken;
            $website->save();

            return response()->json( [
                'message' => 'API токен успішно перегенеровано.',
                'website' => $website
            ], 200 );
        } catch ( \Exception $e ) {
            Log::error( 'Error regenerating API token:', [ 'error' => $e->getMessage() ] );

            return response()->json( [ 'message' => 'Internal Server Error' ], 500 );
        }
    }

    public function show($id)
    {
        try {
            $website = Website::findOrFail($id);
            return response()->json($website, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Website not found'], 404);
        }
    }

    public function showByToken($api_token)
    {
        $website = Website::where('api_token', $api_token)->first();

        if (!$website) {
            return response()->json(['error' => 'Invalid token'], 404);
        }

        return new WebsiteResource($website);
    }


    public function update( Request $request, $id ) {
        $website = Website::find( $id );

        if ( ! $website ) {
            return response()->json( [ 'message' => 'Website not found' ], 404 );
        }

        $validated = $request->validate( [
            'name'      => 'sometimes|required|string|unique:websites,name,' . $id,
            'reg_url'   => 'sometimes|required|url',
            'front_url' => 'sometimes|required|url',
            'app_url'   => 'sometimes|required|url',
        ] );

        $website->update( $validated );

        return new WebsiteResource( $website );
    }

    public function destroy( $id ) {
        $website = Website::find( $id );

        if ( ! $website ) {
            return response()->json( [ 'message' => 'Website not found' ], 404 );
        }

        $website->delete();

        return response()->json( [ 'message' => 'Website deleted successfully' ], 200 );
    }
}
