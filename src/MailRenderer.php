<?php

namespace Replworks\KrMailTheme;

use Replworks\KrMailTheme\Exceptions\MissingRecipientIdentifier;
use Replworks\KrMailTheme\Markdown\MarkdownConverter;
use Replworks\KrMailTheme\Unsubscribe\UnsubscribeUrlGenerator;

final class MailRenderer
{
    private MarkdownConverter $markdown;

    private CssInliner $cssInliner;

    private BrandInformation $brand;

    public function __construct(
        ?MarkdownConverter $markdown = null,
        ?CssInliner $cssInliner = null,
        ?BrandInformation $brand = null,
    ) {
        $this->markdown = $markdown ?? new MarkdownConverter;
        $this->cssInliner = $cssInliner ?? CssInliner::fromTheme();
        $this->brand = $brand ?? BrandInformation::fromConfig();
    }

    public function render(
        MailKind $kind,
        string $subject,
        string $bodyMarkdown,
        ?string $userIdentifier = null,
        ?string $forcedScheme = null,
    ): RenderedMail {
        if ($kind === MailKind::Advertising && ($userIdentifier === null || $userIdentifier === '')) {
            throw MissingRecipientIdentifier::forAdvertisingMail();
        }

        $bodyHtml = $this->markdown->toHtml($bodyMarkdown);
        $bodyText = $this->markdown->toText($bodyMarkdown);

        $unsubscribeUrl = null;
        $headers = [];

        if ($kind === MailKind::Advertising) {
            $unsubscribeUrl = UnsubscribeUrlGenerator::generate($userIdentifier);
            $headers = [
                'List-Unsubscribe' => '<'.$unsubscribeUrl.'>',
                'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
            ];
        }

        $layout = $kind === MailKind::Advertising
            ? 'kr-mail-theme::advertising'
            : 'kr-mail-theme::informational';

        $html = $this->cssInliner->inline(view($layout, [
            'brand' => $this->brand,
            'body' => $bodyHtml,
            'unsubscribeUrl' => $unsubscribeUrl,
        ])->render(), $forcedScheme);

        $text = view('kr-mail-theme::text', [
            'brand' => $this->brand,
            'body' => $bodyText,
            'unsubscribeUrl' => $unsubscribeUrl,
        ])->render();

        return new RenderedMail(
            SubjectFormatter::format($kind, $subject),
            $html,
            $text,
            $headers,
        );
    }
}
