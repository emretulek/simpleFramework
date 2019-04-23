<?php

namespace Helpers;


class Html
{
    public static $metaTags = [
        'lang' => 'tr',
        'charset' => 'UTF-8',
        'title' => '',
        'description' => '',
        'keywords' => '',
        'meta'
    ];

    public static function tag($name, $text, $attr = null)
    {
        return '<' . $name . self::attr($attr) . '>' . $text . '</' . $name . '>';
    }

    public static function tagOpen($name, $attr = null)
    {
        return '<' . $name . ' ' . self::attr($attr) . '>';
    }

    public static function tagClose($name)
    {
        return '</' . $name . '>';
    }

    public static function setLang(string $lang)
    {
        self::$metaTags['lang'] = $lang;
    }

    public static function lang(string $lang = null)
    {
        return $lang ? $lang : self::$metaTags['lang'];
    }

    public static function setCharset(string $charset)
    {
        self::$metaTags['charset'] = $charset;
    }

    public static function charset(string $charset = null)
    {
        $charset = $charset ? $charset : self::$metaTags['charset'];
        return '<meta charset="' . $charset . '">' . PHP_EOL;
    }

    public static function setTitle(string $title)
    {
        self::$metaTags['title'] = $title;
    }

    public static function title(string $title = null)
    {
        $title = $title ? $title : self::$metaTags['title'];
        return '<title>' . $title . '</title>' . PHP_EOL;
    }

    public static function setDescription(string $description)
    {
        self::$metaTags['description'] = $description;
    }

    public static function description(string $description = null)
    {
        $description = $description ? $description : self::$metaTags['description'];
        return '<meta name="description" content="' . $description . '">' . PHP_EOL;
    }

    public static function setKeywords(string $keywords)
    {
        self::$metaTags['keywords'] = $keywords;
    }

    public static function keywords(string $keywords = null)
    {
        $keywords = $keywords ? $keywords : self::$metaTags['keywords'];
        return '<meta name="keywords" content="' . $keywords . '">' . PHP_EOL;
    }

    public static function setMetaTag($name, $value, $content)
    {
        self::$metaTags['meta'][$name] = '<meta ' . $name . '="' . $value . '" content="' . $content . '>' . PHP_EOL;
    }

    public static function metaTag($name)
    {
        return self::$metaTags['meta'][$name];
    }

    public static function a($link, $text, $attr = null)
    {
        return '<a ' . self::attr(['href' => $link], $attr) . '>' . $text . '</a>' . PHP_EOL;
    }

    public static function img($src, $alt = "", $attr = null)
    {
        return '<img ' . self::attr(['src' => $src, 'alt' => $alt], $attr) . '/>' . PHP_EOL;
    }

    public static function list(array $items = [], $attr = null)
    {
        $list = '<ul ' . self::attr($attr) . '>' . PHP_EOL;
        foreach ($items as $item) {
            $list .= '<li>' . $item . '</li>' . PHP_EOL;
        }
        $list .= '</ul>' . PHP_EOL;
        return $list;
    }

    public static function formOpen($action = null, $method = "post", $attr = null)
    {
        return '<form ' . self::attr(['action' => $action, 'method' => $method], $attr) . '>' . PHP_EOL;
    }

    public static function formClose()
    {
        return '</form>' . PHP_EOL;
    }

    public static function input($type, $name, $value = null, $attr = null)
    {
        $value = $_REQUEST[$name] ?? $value;
        return '<input ' . self::attr(['type' => $type, 'name' => $name, 'value' => $value], $attr) . '>' . PHP_EOL;
    }

    public static function file($name, $multiple = false, $attr = null)
    {
        $multiple = $multiple ? ['multiple' => true] : null;
        $name = $multiple ? $name . '[]' : $name;
        return '<input ' . self::attr(['type' => 'file', 'name' => $name], $attr, $multiple) . '>' . PHP_EOL;
    }

    public static function checkBox($name, $value, $checked = false, $attr = null)
    {
        $checked = isset($_REQUEST[$name]) ? true : $checked;
        $checked = $checked ? ['checked' => true] : null;
        return '<input ' . self::attr(['type' => 'checkbox', 'name' => $name, 'value' => $value], $attr, $checked) . '>' . PHP_EOL;
    }

    public static function radio($name, $value, $checked = false, $attr = null)
    {
        $checked = (isset($_REQUEST[$name]) and $_REQUEST[$name] == $value) ? true : $checked;
        $checked = $checked ? ['checked' => true] : null;
        return '<input ' . self::attr(['type' => 'radio', 'name' => $name, 'value' => $value], $attr, $checked) . '>' . PHP_EOL;
    }

    public static function select($name, array $options, $selected = null, $attr = null)
    {
        if (strpos($name, '[]')) {
            if (is_array($attr)) {
                $selected = $_REQUEST[substr($name, 0, -2)] ?? $selected;
                $attr = array_merge($attr, ['multiple' => true]);
            } else {
                $selected = $_REQUEST[$name] ?? $selected;
                $attr = ['multiple' => true];
            }
        }

        $select = '<select ' . self::attr(['name' => $name], $attr) . '>' . PHP_EOL;
        foreach ($options as $value => $text) {
            if (is_array($selected)) {
                $_selected = (in_array($value, $selected)) ? ['selected' => true] : null;
            } else {
                $_selected = ($selected == $value) ? ['selected' => true] : null;
            }
            $select .= '<option ' . self::attr(['value' => $value], $_selected) . '>' . $text . '</option>' . PHP_EOL;
        }
        $select .= '</select>' . PHP_EOL;

        return $select;
    }

    public static function textarea($name, $value = null, $attr = null)
    {
        $value = $_REQUEST[$name] ?? $value;
        return '<textarea ' . self::attr(['name' => $name], $attr) . '>' . $value . '</textarea>' . PHP_EOL;
    }

    public static function label($text, $id = null, $attr = null)
    {
        return '<label ' . self::attr(['for' => $id], $attr) . '>' . $text . '</label>' . PHP_EOL;
    }


    public static function button($text, $attr = null)
    {
        return '<button ' . self::attr(['type' => 'submit'], $attr) . '>' . $text . '</button>' . PHP_EOL;
    }

    private static function attr(...$attrs)
    {
        $attriubtes = "";
        foreach ($attrs as $attr) {
            if (is_array($attr)) {
                foreach ($attr as $key => $value) {
                    $attriubtes .= ' ' . $key . '="' . $value . '"';
                }
            }
        }
        return $attriubtes;
    }
}
