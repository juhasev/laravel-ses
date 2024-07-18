<?php

declare(strict_types=1);

namespace Juhasev\LaravelSes\Controllers;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Log;
use Juhasev\LaravelSes\Contracts\EmailLinkContract;
use Juhasev\LaravelSes\Factories\Events\SesLinkEvent;
use Juhasev\LaravelSes\ModelResolver;

class LinkController extends BaseController
{
    /**
     * @throws Exception
     */
    public function click($linkIdentifier): Redirector|RedirectResponse
    {
        try {
            /** @psalm-suppress UndefinedMethod */
            $emailLink = ModelResolver::get('EmailLink')::whereLinkIdentifier($linkIdentifier)->firstOrFail();

            $emailLink->setClicked(true)->incrementClickCount();

            $this->sendEvent($emailLink);

            return redirect($emailLink->originalUrl());

        } catch (ModelNotFoundException $e) {

            Log::info("Could not find link ($linkIdentifier). Email link click count not incremented!");

            abort(404);
        }
    }

    protected function sendEvent(EmailLinkContract $emailLink): void
    {
        event(new SesLinkEvent($emailLink));
    }
}
