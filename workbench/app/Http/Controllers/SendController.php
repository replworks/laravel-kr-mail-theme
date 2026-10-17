<?php

namespace Workbench\App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Replworks\KrMailTheme\RenderedMail;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use Throwable;
use Workbench\App\Support\Preview;

final class SendController
{
    public function __invoke(Request $request): JsonResponse
    {
        $recipient = trim((string) $request->input('recipient', ''));

        if (filter_var($recipient, FILTER_VALIDATE_EMAIL) === false) {
            return response()->json(['state' => 'failed', 'error' => 'ERR-006']);
        }

        $mail = Preview::deliver($request->input('kind'), $request->input('body'));

        try {
            Transport::fromDsn((string) env('MAILPIT_SMTP', 'smtp://127.0.0.1:3025'))->send($this->email($mail, $recipient));
        } catch (Throwable) {
            return response()->json(['state' => 'failed', 'error' => 'ERR-005']);
        }

        return response()->json(['state' => 'delivered']);
    }

    private function email(RenderedMail $mail, string $recipient): Email
    {
        $email = (new Email)
            ->from('dev@example.com')
            ->to($recipient)
            ->subject($mail->subject)
            ->html($mail->html)
            ->text($mail->text);

        foreach ($mail->headers as $name => $value) {
            $email->getHeaders()->addTextHeader($name, $value);
        }

        return $email;
    }
}
