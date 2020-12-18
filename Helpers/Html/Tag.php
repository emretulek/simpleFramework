<?php
/**
 * @Created 10.05.2020 06:20:12
 * @Project simpleFramework
 * @Author Mehmet Emre Tülek <memretulek@gmail.com>
 * @Class Tag
 * @package Helpers\Html
 */


namespace Helpers\Html;


class Tag
{

    private ?array $virtualTag;

    private string $tag;

    private string $attributes;


    public function __construct(string $tagName, bool $closed = true)
    {
        return $this->creat($tagName, $closed);
    }

    /**
     * Yeni element oluşturur
     * @param string $tagName
     * @param bool $closed
     * @return $this
     */
    private function creat(string $tagName, bool $closed = true): Tag
    {
        $this->virtualTag['name'] = $tagName;
        $this->virtualTag['closed'] = $closed;
        $this->virtualTag['content'] = "";
        $this->virtualTag['attributes'] = [];

        return $this;
    }


    /**
     * Element taglari arasındaki içeriği text olarak oluşturur,
     * değer girilmemiş ise içeriği döndürür
     * @param string|null $text
     * @return $this|string
     */
    public function text(string $text = null)
    {
        if (is_null($text)) {
            return strip_tags($this->virtualTag['content']);
        }

        $this->virtualTag['content'] = htmlspecialchars($text);

        return $this;
    }


    /**
     * Element taglari arasındaki içeriği html taglari ile birlikte oluşturur,
     * değer girilmemiş ise içeriği döndürür
     * @param string|null $html
     * @return $this|string
     */
    public function html(string $html = null)
    {
        if (is_null($html)) {
            return $this->virtualTag['content'];
        }

        $this->virtualTag['content'] = $html;

        return $this;
    }


    /**
     * Elementin varsa value, yoksa content o da yoksa içeriğine değer atar,
     * değer girilmemiş ise var olan değeri döndürür
     * @param string|null $value
     * @return $this|string|null
     */
    public function val(string $value = null)
    {

        if (is_null($value)) {
            return $this->virtualTag['attributes']['value'] ?? $this->virtualTag['attributes']['content'] ?? $this->text();
        }

        return $this->attr('value', $value);
    }


    /**
     * Element özellik ekleme ve değiştirme
     * @param $attributes
     * @param string|null $value
     * @return $this|string|null
     */
    public function attr($attributes, string $value = null)
    {
        if (is_string($attributes) && !is_null($value)) {

            $this->virtualTag['attributes'] = array_merge($this->virtualTag['attributes'], [$attributes => $value]);
        } elseif (is_string($attributes) && is_null($value)) {

            return $this->virtualTag['attributes'][$attributes] ?? null;
        } elseif (is_array($attributes)) {

            foreach ($attributes as $attr => $val) {

                $this->virtualTag['attributes'] = array_merge($this->virtualTag['attributes'], [$attr => $val]);
            }
        }

        return $this;
    }

    /**
     * elemente yeni class ekle
     * @param string $className
     * @return $this|null
     */
    public function addClass(string $className)
    {
        if ($class = $this->attr('class')) {
            return $this->attr('class', $class . ' ' . $className);
        }

        return $this->attr('class', $className);
    }


    /**
     * elementten class kaldır
     * @param string $className
     * @return $this|null
     */
    public function removeClass(string $className)
    {
        if ($class = $this->attr('class')) {
            $classList = explode(" ", $class);
            $classKey = array_search($className, $classList);
            unset($classList[$classKey]);
            $class = implode(" ", $classList);

            return $this->attr('class', $class);
        }

        return $this;
    }


    /**
     * element özellik silme
     *
     * @param $attributes
     * @return $this
     */
    public function removeAttr($attributes):Tag
    {
        if (is_string($attributes)) {

            if (array_key_exists($attributes, $this->virtualTag['attributes'])) {

                unset($this->virtualTag['attributes'][$attributes]);
            }
        } elseif (is_array($attributes)) {
            foreach ($attributes as $attr) {

                unset($this->virtualTag['attributes'][$attr]);
            }
        }
        return $this;
    }


    /**
     * Elementin içine sondan başlayarak girilen değeri ekler
     * @param $html
     * @return $this
     */
    public function append(string $html): Tag
    {
        if ($this->virtualTag['closed']) {
            $this->virtualTag['content'] .= $html;
        }

        return $this;
    }


    /**
     * Elementin içine baştan başlayarak girilen değeri ekler
     * @param $html
     * @return $this
     */
    public function prepend(string $html): Tag
    {
        if ($this->virtualTag['closed']) {
            $this->virtualTag['content'] = $html . $this->virtualTag['content'];
        }

        return $this;
    }


    /**
     * @return $this
     */
    public function remove(): Tag
    {
        $this->virtualTag = null;
        $this->tag = "";
        return $this;
    }


    /**
     * Özellikleri elemente ekler
     */
    private function saveAttributes():void
    {
        $attributes = [];
        foreach ($this->virtualTag['attributes'] as $attr => $value) {

            $attributes[] = $attr . '="' . str_replace("\"", "&quot;", $value) . '"';
        }

        $this->attributes = $attributes ? " ".implode(" ", $attributes) : '';
    }


    /**
     * kapatılması gereken elementler
     */
    private function closedTagCreator():void
    {
        $this->saveAttributes();
        $this->tag = '<' . $this->virtualTag['name'] . $this->attributes . '>' . $this->virtualTag['content'] . '</' . $this->virtualTag['name'] . '>'.PHP_EOL;
    }


    /**
     * kapatılmayan elementler
     */
    private function singleTagCreator():void
    {
        $this->saveAttributes();
        $this->tag = '<' . $this->virtualTag['name'] . $this->attributes . '/>'.PHP_EOL;
    }


    /**
     * Tag html çıktısı
     * @return string
     */
    public function __toString():string
    {
        if ($this->virtualTag) {
            if ($this->virtualTag['closed']) {
                $this->closedTagCreator();
            } else {
                $this->singleTagCreator();
            }
        }

        return $this->tag;
    }
}
