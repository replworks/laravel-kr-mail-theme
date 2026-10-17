<?php

namespace Replworks\KrMailTheme;

final class RenderedMail
{
    /**
     * @param  array<string, string>  $headers
     */
    public function __construct(
        public readonly string $subject,
        public readonly string $html,
        public readonly string $text,
        public readonly array $headers = [],
    ) {}
}
