<?php

namespace Replworks\KrMailTheme\Markdown;

use League\CommonMark\Extension\CommonMark\Node\Block\BlockQuote;
use League\CommonMark\Extension\CommonMark\Node\Inline\HtmlInline;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\Table\Table;
use League\CommonMark\Node\Block\Document;
use League\CommonMark\Node\Block\Paragraph;
use League\CommonMark\Node\Node;

final class TextRenderer
{
    public function render(Document $document): string
    {
        $blocks = [];

        foreach ($document->children() as $child) {
            $block = $this->block($child);

            if ($block !== '') {
                $blocks[] = $block;
            }
        }

        return implode("\n\n", $blocks)."\n";
    }

    private function block(Node $node): string
    {
        if ($node instanceof Paragraph) {
            return $this->inline($node);
        }

        if ($node instanceof BlockQuote) {
            $inner = [];

            foreach ($node->children() as $child) {
                $inner[] = $this->block($child);
            }

            $lines = [];

            foreach (explode("\n", implode("\n\n", $inner)) as $line) {
                $lines[] = '> '.$line;
            }

            return implode("\n", $lines);
        }

        if ($node instanceof Table) {
            return $this->table($node);
        }

        return $this->inline($node);
    }

    private function table(Table $table): string
    {
        $lines = [];

        foreach ($table->children() as $section) {
            foreach ($section->children() as $row) {
                $cells = [];

                foreach ($row->children() as $cell) {
                    $cells[] = $this->inline($cell);
                }

                $lines[] = implode(' | ', $cells);
            }
        }

        return implode("\n", $lines);
    }

    private function inline(Node $node): string
    {
        $text = '';

        foreach ($node->children() as $child) {
            if ($child instanceof Link) {
                $label = $this->inline($child);
                $url = $child->getUrl();

                $text .= $label === $url ? $url : $label.': '.$url;
            } elseif ($child instanceof HtmlInline) {
                $text .= strip_tags($child->getLiteral());
            } elseif ($child->firstChild() !== null) {
                $text .= $this->inline($child);
            } else {
                $text .= $child->getLiteral() ?? '';
            }
        }

        return $text;
    }
}
