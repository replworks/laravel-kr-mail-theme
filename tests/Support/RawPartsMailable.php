<?php

namespace Replworks\KrMailTheme\Tests\Support;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Support\HtmlString;

class RawPartsMailable extends Mailable
{
    /**
     * @param  array<string, string>  $textHeaders
     */
    public function __construct(
        public string $rawHtml,
        public string $rawText,
        public array $textHeaders = [],
        public string $mailSubject = '[검증] raw HTML, TEXT, headers',
    ) {}

    public function build(): static
    {
        return $this
            ->subject($this->mailSubject)
            ->html($this->rawHtml)
            ->text(new HtmlString($this->rawText));
    }

    public function headers(): Headers
    {
        return new Headers(text: $this->textHeaders);
    }
}
