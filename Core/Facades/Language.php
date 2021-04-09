<?php

namespace Core\Facades;


/**
 * @see Language::load()
 * @method static void load()
 * ---------------------------------------------------------------------
 * @see Language::getName()
 * @method static string getName()
 * ---------------------------------------------------------------------
 * @see Language::getKey()
 * @method static string getKey()
 * ---------------------------------------------------------------------
 * @see Language::getLocale()
 * @method static string getLocale()
 * ---------------------------------------------------------------------
 * @see Language::setDefault()
 * @method static bool setDefault($key)
 * ---------------------------------------------------------------------
 * @see Language::getDefault()
 * @method static array getDefault()
 * ---------------------------------------------------------------------
 * @see Language::setActive()
 * @method static bool setActive(string $key)
 * ----------------------------------------------------------------------
 * @see Language::getActive()
 * @method static array getActive()
 * -----------------------------------------------------------------------
 * @see Language::addFile()
 * @method static bool|array addFile(string $key, string $file_path)
 * -----------------------------------------------------------------------
 * @see Language::translate()
 * @method static mixed|array translate(string $key, ...$args)
 * -----------------------------------------------------------------------
 * @see Language::addTranslate()
 * @method static mixed addTranslate(string $key, $value)
 * ------------------------------------------------------------------------
 * @see Language::exists()
 * @method static bool exists(string $lang_key)
 * -----------------------------------------------------------------------
 * @see Language::add()
 * @method static void add(string $key, string $name, string $locale = null)
 * ------------------------------------------------------------------------
 * @see Language::remove()
 * @method static void remove(string $lang_key)
 * ------------------------------------------------------------------------
 * @see Language::list()
 * @method static array list()
 * -----------------------------------------------------------------------
 * @see Language::routePrefix()
 * @method static string routePrefix()
 * -----------------------------------------------------------------------
 * @mixin Language
 * @see Language
 */
class Language extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return \Core\Language\Language::class;
    }
}
