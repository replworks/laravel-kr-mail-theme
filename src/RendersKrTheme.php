<?php

namespace Replworks\KrMailTheme;

use Illuminate\Mail\Mailables\Headers;
use Illuminate\Support\HtmlString;

trait RendersKrTheme
{
    /** @var array<string, string> */
    protected array $krThemeHeaders = [];

    /**
     * @param  array<string, mixed>  $bodyData
     */
    public function krTheme(
        MailKind $kind,
        string $subject,
        string $bodyView,
        array $bodyData = [],
        ?string $userIdentifier = null,
        ?string $recipientName = null,
    ): static {
        $markdown = (new BodyViewRenderer)->render($bodyView, $bodyData, $recipientName);

        $rendered = (new MailRenderer)->render($kind, $subject, $markdown, $userIdentifier);

        $this->subject($rendered->subject);
        $this->html($rendered->html);
        $this->text(new HtmlString($rendered->text));
        $this->krThemeHeaders = $rendered->headers;

        return $this;
    }

    public function headers(): Headers
    {
        return new Headers(text: $this->krThemeHeaders);
    }
}
