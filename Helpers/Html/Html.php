<?php 


namespace Helpers\Html;


class Html {

    /**
     * Basit html elementleri için kullanım label, span, div, button vb.
     * @param string $tagName
     * @param string|null $text
     * @return Tag
     */
    public static function tag(string $tagName, string $text = null):Tag
    {
        $tag = new Tag($tagName);
        $tag->text($text);

        return $tag;
    }


    /**
     * form elementi oluşturur
     * @param string|null $action
     * @param string $method
     * @param bool $multipart
     * @return Tag
     */
    public static function form(string $action = null, string $method = "post", bool $multipart = false):Tag
    {
        $tag = new Tag('form');
        $tag->attr('method', $method)
            ->attr('encode', $multipart ? 'multipart/form-data' : 'application/x-www-form-urlencoded');

        return $tag;
    }


    /**
     * input elementlerini oluşturur input type file için Html::file methodunu kullanın
     * @param string $type
     * @param string $name
     * @param string|null $value
     * @return Tag
     */
    public static function input(string $type, string $name, string $value = null): Tag
    {
        $tag = new Tag('input', false);
        $tag->attr('type', $type)
            ->attr('name', $name)
            ->val($value);

        return $tag;
    }


    /**
     * input type file
     * @param string $name
     * @param bool $multiple
     * @return Tag
     */
    public static function file(string $name, $multiple = false): Tag
    {
        $tag = new Tag('input', false);
        $tag->attr('type', 'file')
            ->attr('name', $multiple ? $name.'[]': $name);
        $multiple ? $tag->attr('multiple', 'multiple') : null;

        return $tag;
    }

    /**
     * select elementi oluşturur
     * @param string $name select name
     * @param array $options ['value' => 'text', 'option group name' =>['value' => 'text']]
     * @param string|null $selectedOption
     * @param bool $multiple
     * @return Tag
     */
    public static function select(string $name, array $options, string $selectedOption = null, $multiple = false): Tag
    {
        $tag = new Tag('select');
        $tag->attr('name', $multiple ? $name.'[]': $name);
        $multiple ? $tag->attr('multiple', 'multiple') : null;

        foreach ($options as $key => $val){

            if(is_array($val)){

                $optGroup = new Tag('optgroup');
                $optGroup->attr('label', $key);

                foreach ($val as $groupKey => $groupVal){

                    $opt = new Tag('option');
                    $opt->val($groupKey)->text($groupVal);

                    if($groupKey == $selectedOption && $selectedOption !== null){
                        $opt->attr('selected', 'selected');
                    }

                    $optGroup->append($opt);
                }

                $tag->append($optGroup);
            }else{

                $opt = new Tag('option');
                $opt->val($key)->text($val);

                if($key == $selectedOption && $selectedOption !== null){
                    $opt->attr('selected', 'selected');
                }

                $tag->append($opt);
            }
        }

        return $tag;
    }


    /**
     *
     * @param string $name
     * @param string|null $value
     * @param bool|string $checked değer olarak value girilirse checked kabul edilir
     * @return Tag
     */
    public static function checkbox(string $name, string $value = null, $checked = false): Tag
    {
        $tag = new Tag('input', false);
        $tag->attr('type', 'checkbox')
            ->attr('name', $name)
            ->val($value);

        if(($checked === true || (string) $checked == $value) && $value !== null){
            $tag->attr('checked', 'checked');
        }

        return $tag;
    }


    /**
     *
     * @param string $name
     * @param string|null $value
     * @param bool|string $checked değer olarak value girilirse checked kabul edilir
     * @return Tag
     */
    public static function radio(string $name, string $value = null, $checked = false): Tag
    {
        $tag = new Tag('input', false);
        $tag->attr('type', 'radio')
            ->attr('name', $name)
            ->val($value);

        if(($checked === true || (string) $checked == $value) && $value !== null){
            $tag->attr('checked', 'checked');
        }

        return $tag;
    }


    /**
     * textarea oluşturur
     * @param string $name
     * @param string|null $text
     * @return Tag
     */
    public static function textarea(string $name, string $text = null): Tag
    {
        $tag = new Tag('textarea');
        $tag->attr('name', $name)
            ->text($text);

        return $tag;
    }


    /**
     * type submit olan bir button oluşturur
     * @param string $text
     * @return Tag
     */
    public static function button(string $text): Tag
    {
        $tag = new Tag('button');
        $tag->attr('type', 'submit')
            ->text($text);

        return $tag;
    }


    /**
     * a elementiyle link oluşturur
     * @param string $href
     * @param string $text
     * @param string|null $target
     * @return Tag
     */
    public static function a(string $href, string $text, string $target = null): Tag
    {
        $tag = new Tag('a');
        $tag->attr('href', $href)
            ->html($text);

        if($target){
            $tag->attr('target', $target);
        }

        return $tag;
    }


    /**
     * img elementi oluşturur
     * @param $src
     * @param $alt
     * @return Tag
     */
    public static function img(string $src, string $alt = ""): Tag
    {
        $tag = new Tag('img', false);
        $tag->attr('alt', $alt)
            ->attr('src', $src);

        return $tag;
    }


    /**
     * picture elementi altında source ve img elementlerini oluşturur
     * @param string $src
     * @param string $alt
     * @param array $datasets ['min-width:600px' => 'image.jpg', 'min-width:1200px' => 'image2.jpg']
     * @return Tag
     */
    public static function picture(string $src, string $alt, array $datasets = []): Tag
    {
        $tag = new Tag('picture');
        $img = self::img($src, $alt);

        foreach ($datasets as $key => $dataset){
            $source = new Tag('source', false);
            $source->attr('media', '('.$key.')')
                ->attr('srcset', $dataset);
            $tag->append($source);
        }

        $tag->append($img);

        return $tag;
    }

    /**
     * diziye atanmış Tag sınıflarını ard arda ekler
     * @param array $elements
     * @return string
     */
    public static function elements(array $elements): string
    {
        $stringElements = "";
        foreach ($elements as $element){
            $stringElements .= $element;
        }
        return $stringElements;
    }
}
