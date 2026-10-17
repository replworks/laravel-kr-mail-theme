<?php

namespace Replworks\KrMailTheme\Markdown;

use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\Block\ParagraphRenderer;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

final class HtmlRenderer implements NodeRendererInterface
{
    private ParagraphRenderer $paragraph;

    public function __construct()
    {
        $this->paragraph = new ParagraphRenderer;
    }

    public function render(Node $node, ChildNodeRendererInterface $childRenderer)
    {
        if ($this->isButton($node)) {
            return $this->renderButton($node, $childRenderer);
        }

        return $this->paragraph->render($node, $childRenderer);
    }

    private function isButton(Node $node): bool
    {
        $only = null;

        foreach ($node->children() as $child) {
            if ($only !== null) {
                return false;
            }

            $only = $child;
        }

        return $only instanceof Link;
    }

    private function renderButton(Node $node, ChildNodeRendererInterface $childRenderer): HtmlElement
    {
        $cell = new HtmlElement('td', [
            'align' => 'center',
            'style' => 'background-color:#3182f6;border-radius:6px;padding:12px 24px;',
        ], $childRenderer->renderNodes($node->children()));

        $row = new HtmlElement('tr', [], $cell);

        return new HtmlElement('table', [
            'role' => 'presentation',
            'cellpadding' => '0',
            'cellspacing' => '0',
            'border' => '0',
        ], $row);
    }
}
