<?php

namespace Replworks\KrMailTheme\Tests\Support;

use Illuminate\Support\Facades\Http;
use Replworks\KrMailTheme\RenderedMail;
use RuntimeException;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use Throwable;

final class MailpitClient
{
    public function __construct(
        private readonly string $baseUrl = 'http://127.0.0.1:38025',
        private readonly string $smtpDsn = 'smtp://127.0.0.1:3025',
    ) {}

    public static function fromEnv(): self
    {
        return new self(
            (string) (getenv('MAILPIT_URL') ?: 'http://127.0.0.1:38025'),
            (string) (getenv('MAILPIT_SMTP') ?: 'smtp://127.0.0.1:3025'),
        );
    }

    public function baseUrl(): string
    {
        return $this->baseUrl;
    }

    public function reachable(): bool
    {
        try {
            return Http::timeout(2)->get($this->baseUrl.'/api/v1/info')->successful();
        } catch (Throwable) {
            return false;
        }
    }

    public function empty(): void
    {
        Http::delete($this->baseUrl.'/api/v1/messages')->throw();
    }

    /**
     * @param  array<string, string>  $headers
     */
    public function send(RenderedMail $mail, string $to = 'dev@example.com', string $from = 'dev@example.com'): string
    {
        $email = (new Email)
            ->from($from)
            ->to($to)
            ->subject($mail->subject)
            ->html($mail->html)
            ->text($mail->text);

        foreach ($mail->headers as $name => $value) {
            $email->getHeaders()->addTextHeader($name, $value);
        }

        return $this->deliver($email);
    }

    public function deliver(Email $email): string
    {
        Transport::fromDsn($this->smtpDsn)->send($email);

        for ($attempt = 0; $attempt < 20; $attempt++) {
            $messages = Http::get($this->baseUrl.'/api/v1/messages')->json('messages') ?? [];

            if (! empty($messages[0]['ID'])) {
                return (string) $messages[0]['ID'];
            }

            usleep(300000);
        }

        throw new RuntimeException('Mailpit received no message at '.$this->baseUrl);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function messages(): array
    {
        return (array) Http::get($this->baseUrl.'/api/v1/messages')->throw()->json('messages');
    }

    public function html(string $id): string
    {
        return Http::get($this->baseUrl.'/view/'.$id.'.html')->throw()->body();
    }

    public function text(string $id): string
    {
        return Http::get($this->baseUrl.'/view/'.$id.'.txt')->throw()->body();
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function headers(string $id): array
    {
        return (array) Http::get($this->baseUrl.'/api/v1/message/'.$id.'/headers')->throw()->json();
    }

    /**
     * @return array<string, mixed>
     */
    public function htmlCheck(string $id): array
    {
        return (array) Http::get($this->baseUrl.'/api/v1/message/'.$id.'/html-check')->throw()->json();
    }
}
