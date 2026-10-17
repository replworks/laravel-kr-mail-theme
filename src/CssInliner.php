<?php

namespace Replworks\KrMailTheme;

use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

final class CssInliner
{
    public const SCHEME_LIGHT = 'light';

    public const SCHEME_DARK = 'dark';

    public const MOBILE_MAX_WIDTH = 375;

    public function __construct(
        private readonly string $baseCss,
        private readonly string $darkCss,
        private readonly string $mobileCss,
    ) {}

    public static function fromTheme(?string $themePath = null): self
    {
        $themePath ??= __DIR__.'/../resources/theme';

        return new self(
            (string) file_get_contents($themePath.'/base.css'),
            (string) file_get_contents($themePath.'/dark.css'),
            (string) file_get_contents($themePath.'/mobile.css'),
        );
    }

    public function inline(string $html, ?string $forcedScheme = null): string
    {
        $inlined = (new CssToInlineStyles)->convert($html, $this->baseCss);

        return $this->injectStyleElement($inlined, $this->styleSheet($forcedScheme));
    }

    private function styleSheet(?string $forcedScheme): string
    {
        $css = '';

        if ($forcedScheme === self::SCHEME_DARK) {
            $css .= "html, body { color-scheme: dark; }\n".trim($this->darkCss)."\n";
        } elseif ($forcedScheme === self::SCHEME_LIGHT) {
            $css .= "html, body { color-scheme: light; }\n";
        } else {
            $css .= "@media (prefers-color-scheme: dark) {\n".trim($this->darkCss)."\n}\n";
        }

        $css .= '@media only screen and (max-width: '.self::MOBILE_MAX_WIDTH."px) {\n".trim($this->mobileCss)."\n}\n";

        return $css;
    }

    private function injectStyleElement(string $html, string $css): string
    {
        $style = "<style>\n".$css."</style>\n";

        if (str_contains($html, '</head>')) {
            return str_replace('</head>', $style.'</head>', $html);
        }

        return $style.$html;
    }
}
