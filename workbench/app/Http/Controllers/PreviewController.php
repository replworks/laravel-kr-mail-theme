<?php

namespace Workbench\App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Workbench\App\Support\Preview;

final class PreviewController
{
    public function __invoke(Request $request): Response
    {
        $mail = Preview::render(
            $request->query('kind'),
            $request->query('body'),
            $request->query('scheme'),
        );

        return response($mail->html)->header('Content-Type', 'text/html; charset=UTF-8');
    }
}
