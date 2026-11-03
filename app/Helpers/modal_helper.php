<?php

if (! function_exists('modal_attrs')) {
    function modal_attrs(array $attrs): string
    {
        $out = '';
        foreach ($attrs as $key => $value) {
            if ($value === false || $value === null) {
                continue;
            }

            $out .= $value === true
                ? ' ' . esc((string) $key)
                : ' ' . esc((string) $key) . '="' . esc((string) $value, 'attr') . '"';
        }

        return $out;
    }
}

if (! function_exists('modal_part')) {
    function modal_part(string $part, string $tag, string $class, array $desktopProps, array $mobileProps, string $extra = ''): void
    {
        $desktopJson = json_encode($desktopProps, JSON_THROW_ON_ERROR);
        $mobileJson = json_encode($mobileProps, JSON_THROW_ON_ERROR);

        echo '<' . $tag
            . ' data-rm-part="' . esc($part, 'attr') . '"'
            . ' data-rm-class="' . esc($class, 'attr') . '"'
            . ' data-rm-desktop="' . esc($desktopJson, 'attr') . '"'
            . ' data-rm-mobile="' . esc($mobileJson, 'attr') . '"'
            . $extra . '>';
    }
}

if (! function_exists('modal_open')) {
    function modal_open(string $id, bool $defaultOpen = false, array $dialogProps = [], array $drawerProps = []): void
    {
        modal_part('root', 'div', '', $dialogProps, $drawerProps,
            ' id="' . esc($id, 'attr') . '" tabindex="-1" aria-hidden="true" data-rm'
            . ($defaultOpen ? ' data-rm-default-open' : ''));
        echo '<div data-rm-part="dialog"><div data-rm-part="content-wrap">';
    }
}

if (! function_exists('modal_end')) {
    function modal_end(): void
    {
        echo '</div></div></div>';
    }
}

if (! function_exists('modal_trigger')) {
    function modal_trigger(string $id, string $label, string $class = '', array $popoverProps = [], array $drawerProps = []): void
    {
        echo '<button type="button" class="' . esc($class, 'attr') . '" data-rm-open="#' . esc($id, 'attr') . '"'
            . modal_attrs($popoverProps + $drawerProps) . '>' . esc($label) . '</button>';
    }
}

if (! function_exists('modal_trigger_attrs')) {
    function modal_trigger_attrs(string $id): string
    {
        return ' data-rm-open="#' . esc($id, 'attr') . '"';
    }
}

if (! function_exists('modal_close')) {
    function modal_close(string $label, string $class = '', array $popoverProps = [], array $drawerProps = []): void
    {
        echo '<button type="button" class="' . esc($class, 'attr') . '" data-rm-close'
            . modal_attrs($popoverProps + $drawerProps) . '>' . esc($label) . '</button>';
    }
}

if (! function_exists('modal_close_attrs')) {
    function modal_close_attrs(): string
    {
        return ' data-rm-close';
    }
}

if (! function_exists('modal_content_open')) {
    function modal_content_open(string $class = '', array $popoverProps = [], array $drawerProps = []): void
    {
        modal_part('content', 'div', $class, $popoverProps, $drawerProps);
    }
}

if (! function_exists('modal_header_open')) {
    function modal_header_open(string $class = ''): void
    {
        modal_part('header', 'div', $class, [], []);
    }
}

if (! function_exists('modal_body_open')) {
    function modal_body_open(string $class = ''): void
    {
        modal_part('body', 'div', $class, [], []);
    }
}

if (! function_exists('modal_footer_open')) {
    function modal_footer_open(string $class = ''): void
    {
        modal_part('footer', 'div', $class, [], []);
    }
}

if (! function_exists('modal_section_end')) {
    function modal_section_end(): void
    {
        echo '</div>';
    }
}

if (! function_exists('modal_title')) {
    function modal_title(string $text, string $class = '', array $popoverProps = [], array $drawerProps = []): void
    {
        modal_part('title', 'h2', $class, $popoverProps, $drawerProps);
        echo esc($text) . '</h2>';
    }
}

if (! function_exists('modal_description')) {
    function modal_description(string $text, string $class = '', array $popoverProps = [], array $drawerProps = []): void
    {
        modal_part('description', 'p', $class, $popoverProps, $drawerProps);
        echo esc($text) . '</p>';
    }
}
