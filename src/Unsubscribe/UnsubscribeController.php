<?php

namespace Replworks\KrMailTheme\Unsubscribe;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Replworks\KrMailTheme\BrandInformation;

final class UnsubscribeController
{
    public function __invoke(Request $request): Response
    {
        if (! $request->hasValidSignature()) {
            return $this->failed($request);
        }

        $userIdentifier = (string) $request->query('user', '');

        if ($userIdentifier === '') {
            return $this->failed($request);
        }

        if ((new ConsentRepository)->revokeMarketing($userIdentifier)) {
            event(new Unsubscribed($userIdentifier));
        }

        return $this->succeeded($request);
    }

    private function succeeded(Request $request): Response
    {
        if ($request->isMethod('post')) {
            return response()->noContent(Response::HTTP_OK);
        }

        return response()->view('kr-mail-theme::unsubscribe', [
            'brand' => BrandInformation::fromConfig(),
            'succeeded' => true,
        ]);
    }

    private function failed(Request $request): Response
    {
        if ($request->isMethod('post')) {
            return response()->noContent(Response::HTTP_FORBIDDEN);
        }

        return response()->view('kr-mail-theme::unsubscribe', [
            'brand' => BrandInformation::fromConfig(),
            'succeeded' => false,
        ]);
    }
}
