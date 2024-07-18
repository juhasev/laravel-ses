<?php

declare(strict_types=1);

namespace Juhasev\LaravelSes\Controllers;

use Illuminate\Routing\Redirector;
use Illuminate\Support\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Juhasev\LaravelSes\Contracts\EmailOpenContract;
use Juhasev\LaravelSes\Factories\Events\SesOpenEvent;
use Juhasev\LaravelSes\ModelResolver;

class OpenController extends BaseController
{
    /**
     * @throws Exception
     */
    public function open($beaconIdentifier): JsonResponse|Redirector|RedirectResponse
    {
        try {
            /** @psalm-suppress UndefinedMethod */
            $emailOpen = ModelResolver::get('EmailOpen')::whereBeaconIdentifier($beaconIdentifier)->firstOrFail();
            $emailOpen->opened_at = Carbon::now();
            $emailOpen->save();

            $this->sendEvent($emailOpen);

        } catch (ModelNotFoundException $e) {

            Log::info("Could not find sent email with beacon identifier ($beaconIdentifier). Email open could not be recorded!");

            return response()->json([
                'success' => false,
                'errors' => ['Invalid Beacon']
            ], 404);
        }

        // Serve the actual image
        return redirect(config('app.url')."/ses/to.png");
    }
    
    protected function sendEvent(EmailOpenContract $emailOpen): void
    {
        event(new SesOpenEvent($emailOpen));
    }
}
