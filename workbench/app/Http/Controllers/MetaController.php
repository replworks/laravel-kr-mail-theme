<?php

namespace Workbench\App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Workbench\App\Support\Preview;

final class MetaController
{
    public function __invoke(Request $request): JsonResponse
    {
        $mail = Preview::render(
            $request->query('kind'),
            $request->query('body'),
            $request->query('scheme'),
        );

        return response()->json([
            'subject' => $mail->subject,
            'text' => $mail->text,
        ]);
    }
}
