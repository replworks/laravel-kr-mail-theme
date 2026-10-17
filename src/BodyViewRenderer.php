<?php

namespace Replworks\KrMailTheme;

final class BodyViewRenderer
{
    public const DEFAULT_DISPLAY_NAME = '회원님';

    public const DISPLAY_NAME_SUFFIX = '님';

    /**
     * @param  array<string, mixed>  $data
     */
    public function render(string $view, array $data = [], ?string $recipientName = null): string
    {
        return view($view, array_merge($data, [
            'recipientDisplayName' => self::displayName($recipientName),
        ]))->render();
    }

    public static function displayName(?string $recipientName): string
    {
        if ($recipientName === null || $recipientName === '') {
            return self::DEFAULT_DISPLAY_NAME;
        }

        return $recipientName.self::DISPLAY_NAME_SUFFIX;
    }
}
