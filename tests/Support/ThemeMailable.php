<?php

namespace Replworks\KrMailTheme\Tests\Support;

use Illuminate\Mail\Mailable;
use Replworks\KrMailTheme\MailKind;
use Replworks\KrMailTheme\RendersKrTheme;

class ThemeMailable extends Mailable
{
    use RendersKrTheme;

    /**
     * @param  array<string, mixed>  $bodyData
     */
    public function __construct(
        public MailKind $kind,
        public string $mailSubject,
        public string $bodyView,
        public array $bodyData = [],
        public ?string $userIdentifier = null,
        public ?string $recipientName = null,
    ) {}

    public function build(): static
    {
        return $this->krTheme(
            $this->kind,
            $this->mailSubject,
            $this->bodyView,
            $this->bodyData,
            $this->userIdentifier,
            $this->recipientName,
        );
    }
}
