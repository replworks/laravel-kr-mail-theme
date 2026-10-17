<?php

namespace Replworks\KrMailTheme\Markdown;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\MarkdownConverter as CommonMarkConverter;
use League\CommonMark\Node\Block\Paragraph;

final class MarkdownConverter
{
    private CommonMarkConverter $converter;

    private TextRenderer $textRenderer;

    public function __construct(?TextRenderer $textRenderer = null)
    {
        $environment = new Environment([
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
        ]);
        $environment->addExtension(new CommonMarkCoreExtension);
        $environment->addExtension(new TableExtension);
        $environment->addRenderer(Paragraph::class, new HtmlRenderer);

        $this->converter = new CommonMarkConverter($environment);
        $this->textRenderer = $textRenderer ?? new TextRenderer;
    }

    public function toHtml(string $markdown): string
    {
        return (string) $this->converter->convert($markdown);
    }

    public function toText(string $markdown): string
    {
        return $this->textRenderer->render(
            $this->converter->convert($markdown)->getDocument()
        );
    }
}
