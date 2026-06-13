<?php

/**
 * Helpers - General
 *
 * @package TAB\Sunset_Realtors
 */

declare(strict_types=1);

namespace TAB\Sunset_Realtors\Helpers\General;

/**
 * Escape svg
 */
function get_kses_extended_ruleset(): array
{
    $kses_defaults = wp_kses_allowed_html('post');

    $svg_args = [
        'svg'   => [
            'class'           => true,
            'data-prefix'     => true,
            'data-icon'       => true,
            'aria-hidden'     => true,
            'aria-labelledby' => true,
            'role'            => true,
            'xmlns'           => true,
            'width'           => true,
            'height'          => true,
            'viewbox'         => true, // <= Must be lower case!
        ],
        'g'     => ['fill' => true],
        'title' => ['title' => true],
        'path'  => [
            'd'    => true,
            'fill' => true,
        ],
    ];
    return array_merge($kses_defaults, $svg_args);
}

/**
 * Create a list of classes separated by space
 *
 * @param array $class_list String list array representing css classes.
 * @param bool  $start_space Add a space to the start of the string list.
 *
 * @return string
 */
function get_names(array $class_list, bool $start_space = false): string
{

    // Remove empty strings.
    $classes = array_filter($class_list, fn($value) => ! is_null($value) && '' !== $value);
    $start   = $start_space ? ' ' : '';

    if (empty($classes)) {
        return '';
    }

    // Remove duplicate classes and create list of classes separated by a space.
    $classes = array_unique($classes);

    $classes = implode(' ', $classes);
    return $start . $classes;
}
