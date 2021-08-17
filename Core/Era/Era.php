<?php


namespace Core\Era;


use DateInterval;
use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Exception;

/**
 * Class Era Tarih yapılandırma sınıfı
 * @package Core\Era
 */
class Era
{
    public static string $lang_dir = __DIR__.'/lang';

    public static array $HUMAN_TEXT = [
        'C' => [
            'year' => '%d year :ph|%d years :ph',
            'month' => '%d month :ph|%d months :ph',
            'week' => '%d week :ph|%d weeks :ph',
            'day' => '%d day :ph|%d days :ph',
            'hour' => '%d hour :ph|%d hours :ph',
            'minute' => '%d minute :ph|%d minutes :ph',
            'second' => '%d second :ph|%d seconds :ph',
            ':ph-before' => 'ago',
            ':ph-after' => 'later',
            ':ph-empty' => ''
        ]
    ];

    const FTIME = [
        //Day
        "d" => "%d",
        "D" => "%a",
        "j" => "%e",
        "l" => "%A",
        "N" => "%u",
        "S" => "S",
        "w" => "%w",
        "z" => "%j",
        //Week
        "W" => "%W",
        //Month
        "F" => "%B",
        "m" => "%m",
        "M" => "%b",
        "n" => "%U",
        "t" => "t",
        //Year
        "L" => "L",
        "o" => "%g",
        "Y" => "%Y",
        "y" => "%y",
        //Time
        "a" => "%P",
        "A" => "%p",
        "B" => "B",
        "g" => "%l",
        "G" => "%k",
        "h" => "%I",
        "H" => "%H",
        "i" => "%M",
        "s" => "%S",
        "u" => "u",
        "v" => "v",
        //Timezone
        "e" => "e",
        "I" => "I",
        "O" => "%z",
        "P" => "P",
        "p" => "p",
        "T" => "T",
        "Z" => "Z",
        //Full Date/Time
        "c" => "%Y-%m-%d\T%TP",
        "r" => "%a, %d %b %G %X %z",
        "U" => "%s",
    ];


    /**
     * @since 7.2
     */
    const ATOM = 'Y-m-d\TH:i:sP';

    /**
     * @since 7.2
     */
    const COOKIE = 'l, d-M-Y H:i:s T';

    /**
     * @since 7.2
     */
    const ISO8601 = 'Y-m-d\TH:i:sO';

    /**
     * @since 7.2
     */
    const RFC822 = 'D, d M y H:i:s O';

    /**
     * @since 7.2
     */
    const RFC850 = 'l, d-M-y H:i:s T';

    /**
     * @since 7.2
     */
    const RFC1036 = 'D, d M y H:i:s O';

    /**
     * @since 7.2
     */
    const RFC1123 = 'D, d M Y H:i:s O';

    /**
     * @since 7.2
     */
    const RFC2822 = 'D, d M Y H:i:s O';

    /**
     * @since 7.2
     */
    const RFC3339 = 'Y-m-d\TH:i:sP';

    /**
     * @since 7.2
     */
    const RFC3339_EXTENDED = 'Y-m-d\TH:i:s.vP';

    /**
     * @since 7.2
     */
    const RFC7231 = 'D, d M Y H:i:s \G\M\T';

    /**
     * @since 7.2
     */
    const RSS = 'D, d M Y H:i:s O';

    /**
     * @since 7.2
     */
    const W3C = 'Y-m-d\TH:i:sP';

    /**
     * @var string SQL DateTime format
     */
    const SQL = "Y-m-d H:i:s";

    /**
     * @var string SQL DateTime format
     */
    const SQLMS = "Y-m-d H:i:s.u";

    /**
     * @var string 05 October 2021 Tuesday
     */
    const TEXT_BASED_DATE = "d F Y l";

    /**
     * @var string 05 October 2021
     */
    const TEXT_BASED_DATE_SHORT = "d F Y";

    /**
     * @var string 05 October 2021 Tuesday 21:30
     */
    const TEXT_BASED_DATETIME = "d F Y l H:i";

    /**
     * @var string 05 October 2021 Tuesday
     */
    const TEXT_BASED_DATETIME_SHORT = "d F Y H:i";

    /**
     * @var string Locale format
     */
    const LOCALE_DATE_FORMAT = "%x";

    /**
     * @var string Locale format
     */
    const LOCALE_DATETIME_FORMAT = "%x H:i:s";

    protected string $locale;


    protected string $format = self::SQL;


    protected DateTime $dateTime;

    /**
     * Date constructor.
     * @param string $datetime
     * @param null $timezone
     * @throws Exception
     */
    public function __construct($datetime = 'now', $timezone = null)
    {
        $timezone = $timezone ? new DateTimeZone($timezone) : null;
        $this->dateTime = new DateTime($datetime, $timezone);

        $this->locale = setlocale(LC_TIME, 0);
    }


    /**
     * @param null $timezone
     * @return static
     * @throws Exception
     */
    public static function now($timezone = null): self
    {
        return new self('now', $timezone);
    }


    /**
     * @param null $timezone
     * @return static
     * @throws Exception
     */
    public static function today($timezone = null): self
    {
        return new self('today', $timezone);
    }


    /**
     * @param null $timezone
     * @return static
     * @throws Exception
     */
    public static function tomorrow($timezone = null): self
    {
        return new self('tomorrow', $timezone);
    }


    /**
     * @param null $timezone
     * @return static
     * @throws Exception
     */
    public static function yesterday($timezone = null): self
    {
        return new self('yesterday', $timezone);
    }


    /**
     * @param int $year
     * @param int $month
     * @param int $day
     * @param int $hour
     * @param int $minute
     * @param int $second
     * @param int $milisecond
     * @return Era
     */
    public static function create(int $year = 0, int $month = 0, int $day = 0, int $hour = 0, int $minute = 0, int $second = 0, int $milisecond = 0): self
    {
        $self = new self();
        $self->setDate($year, $month, $day)->setTime($hour, $minute, $second, $milisecond);
        return $self;
    }


    /**
     * @param string $dateTime
     * @return Era
     * @throws Exception
     */
    public static function parse(string $dateTime): self
    {
        return new self($dateTime);
    }

    /**
     * @param string $format
     * @param string $datetime
     * @return Era
     * @throws Exception
     */
    public static function createFromFormat(string $format, string $datetime): self
    {
        $self = new self('now');
        $self->dateTime = DateTime::createFromFormat($format, $datetime);

        return $self;
    }


    /**
     * @return DateTime
     */
    public function getDateTime(): DateTime
    {
        return $this->dateTime;
    }

    /**
     * LC_TIME ile belirtilen dilde tarih çevirisi
     * @param string $format
     * @return false|string
     */
    public function formatLocale(string $format = Era::TEXT_BASED_DATETIME_SHORT)
    {
        $this->format = $format;
        $formatDateToStrftime = $this->formatDateToStrftime($this->format);

        if ($dateTime = strftime($formatDateToStrftime, $this->getTimestamp())) {
            return $dateTime;
        }
        return date($this->format, $this->getTimestamp());
    }


    /**
     * @param $format
     * @return string
     */
    protected function formatDateToStrftime($format): string
    {
        $keys = array_map(function ($item) {
            return '/(?<!%|\\\)' . preg_quote($item) . '/';
        }, array_keys(self::FTIME));

        $strftimeFormat = preg_replace($keys, array_values(self::FTIME), $format, 1, $count);
        $strftimeFormatWithDateParameter = preg_replace_callback('/(?<!%|\\\)\w/', function ($d) {
            return $this->format($d[0]);
        }, $strftimeFormat);

        return str_replace('\\', "", $strftimeFormatWithDateParameter);
    }


    /**
     * @param string|null $locale
     * @return Era|string
     */
    public function locale(?string $locale = null)
    {
        if ($locale === null) {
            return $this->locale;
        }

        if(setlocale(LC_TIME, $locale)) {
            $this->locale = $locale;
        }

        return $this;
    }


    /**
     * @param DateTimeInterface|Era|string $targetDate
     * @param bool $absolute default true
     * @return DateInterval|false
     */
    public function diff($targetDate, $absolute = true)
    {
        if (is_string($targetDate)) {
            $targetDateTime = new DateTime($targetDate);
        } elseif ($targetDate instanceof $this) {
            $targetDateTime = $targetDate->getDateTime();
        } else {
            $targetDateTime = $targetDate;
        }

        return $this->dateTime->diff($targetDateTime, $absolute);
    }


    /**
     * @param DateTimeInterface|Era|string $targetDate
     * @param bool $absolute
     * @return float
     */
    public function diffMilliSecond($targetDate, $absolute = true): float
    {
        return $this->diff($targetDate, $absolute)->f;
    }

    /**
     * @param DateTimeInterface|Era|string $targetDate
     * @param bool $absolute
     * @return int
     */
    public function diffSecond($targetDate, $absolute = true): int
    {
        return $this->diff($targetDate, $absolute)->s;
    }

    /**
     * @param DateTimeInterface|Era|string $targetDate
     * @param bool $absolute
     * @return int
     */
    public function diffMinute($targetDate, $absolute = true): int
    {
        return $this->diff($targetDate, $absolute)->i;
    }


    /**
     * @param DateTimeInterface|Era|string $targetDate
     * @param bool $absolute
     * @return int
     */
    public function diffHour($targetDate, $absolute = true): int
    {
        return $this->diff($targetDate, $absolute)->h;
    }


    /**
     * @param DateTimeInterface|Era|string $targetDate
     * @param bool $absolute
     * @return int
     */
    public function diffDay($targetDate, $absolute = true): int
    {
        return $this->diff($targetDate, $absolute)->d;
    }


    /**
     * @param DateTimeInterface|Era|string $targetDate
     * @param bool $absolute
     * @return int
     */
    public function diffMonth($targetDate, $absolute = true): int
    {
        return $this->diff($targetDate, $absolute)->m;
    }


    /**
     * @param DateTimeInterface|Era|string $targetDate
     * @param bool $absolute
     * @return int
     */
    public function diffYear($targetDate, $absolute = true): int
    {
        return $this->diff($targetDate, $absolute)->y;
    }


    /**
     * @param DateTimeInterface|Era|string $targetDate
     * @param bool $absolute
     * @return int
     */
    public function diffDays($targetDate, $absolute = true): int
    {
        return $this->diff($targetDate, $absolute)->days;
    }


    /**
     * İlk parametre int yıl veya DateInterval
     * @param int|DateInterval $year
     * @param int $month
     * @param int $day
     * @param int $hour
     * @param int $minute
     * @param int $second
     * @return $this
     */
    public function add($year, int $month = 0, int $day = 0, int $hour = 0, int $minute = 0, int $second = 0): self
    {
        if($year instanceof DateInterval){
            $this->dateTime->add($year);
        }else {
            $this->dateTime->modify("+$year year +$month month +$day day +$hour hour +$minute minute +$second second");
        }

        return $this;
    }

    /**
     * @param int $millisecond
     * @return $this
     */
    public function addMilliSecond(int $millisecond): self
    {
        $this->dateTime->modify("+$millisecond millisecond");
        return $this;
    }

    /**
     * @param int $second
     * @return $this
     */
    public function addSecond(int $second): self
    {
        $this->dateTime->modify("+$second second");
        return $this;
    }

    /**
     * @param int $minute
     * @return $this
     */
    public function addminut(int $minute): self
    {
        $this->dateTime->modify("+$minute minute");
        return $this;
    }


    /**
     * @param int $hour
     * @return $this
     */
    public function addHour(int $hour): self
    {
        $this->dateTime->modify("+$hour hour");
        return $this;
    }


    /**
     * @param int $day
     * @return self
     */
    public function addDay(int $day): self
    {
        $this->dateTime->modify("+$day day");
        return $this;
    }

    /**
     * @param int $week
     * @return self
     */
    public function addWeek(int $week): self
    {
        $this->dateTime->modify("+$week week");
        return $this;
    }


    /**
     * @param int $month
     * @return self
     */
    public function addMonth(int $month): self
    {
        $this->dateTime->modify("+$month month");
        return $this;
    }


    /**
     * @param int $year
     * @return self
     */
    public function addYear(int $year): self
    {
        $this->dateTime->modify("+$year year");
        return $this;
    }

    /**
     * @param int|DateInterval $year
     * @param int $month
     * @param int $day
     * @param int $hour
     * @param int $minute
     * @param int $second
     * @return $this
     */
    public function sub($year, int $month = 0, int $day = 0, int $hour = 0, int $minute = 0, int $second = 0): self
    {
        if($year instanceof DateInterval){
            $this->dateTime->add($year);
        }else {
            $this->dateTime->modify("-$year year -$month month -$day day -$hour hour -$minute minute -$second second");
        }

        return $this;
    }


    /**
     * @param int $millisecond
     * @return $this
     */
    public function subMilliSecond(int $millisecond): self
    {
        $this->dateTime->modify("-$millisecond millisecond");
        return $this;
    }

    /**
     * @param int $second
     * @return $this
     */
    public function subSecond(int $second): self
    {
        $this->dateTime->modify("-$second second");
        return $this;
    }

    /**
     * @param int $minute
     * @return $this
     */
    public function subMinut(int $minute): self
    {
        $this->dateTime->modify("-$minute minute");
        return $this;
    }


    /**
     * @param int $hour
     * @return $this
     */
    public function subHour(int $hour): self
    {
        $this->dateTime->modify("-$hour hour");
        return $this;
    }


    /**
     * @param int $day
     * @return self
     */
    public function subDay(int $day): self
    {
        $this->dateTime->modify("-$day day");
        return $this;
    }

    /**
     * @param int $week
     * @return self
     */
    public function subWeek(int $week): self
    {
        $this->dateTime->modify("-$week week");
        return $this;
    }


    /**
     * @param int $month
     * @return self
     */
    public function subMonth(int $month): self
    {
        $this->dateTime->modify("-$month month");
        return $this;
    }


    /**
     * @param int $year
     * @return self
     */
    public function subYear(int $year): self
    {
        $this->dateTime->modify("-$year year");
        return $this;
    }


    /**
     * @return int
     * @throws Exception
     */
    public function age(): int
    {
        return $this->diff(new DateTime())->y;
    }

    /**
     * @param DateInterval $dateInterval
     * @param string $unit[second,minute,hour,day,month,year]
     * @return int
     */
    public static function dateIntervalTo(DateInterval $dateInterval, $unit = "day"):int
    {
        $unit = strtolower($unit);

        if($dateInterval->days === false){
            $dateInterval->days = 365 * $dateInterval->y;
            $dateInterval->days += 30 * $dateInterval->m;
            $dateInterval->days += $dateInterval->d;
        }

        if($unit == "year"){
            return $dateInterval->y;
        }elseif($unit == "month"){
            return $dateInterval->y * 12 + $dateInterval->m;
        }elseif ($unit == "day"){
            return $dateInterval->days;
        }elseif ($unit == "hour"){
            return $dateInterval->days * 24 + $dateInterval->h;
        }elseif ($unit == "minute"){
            return $dateInterval->days * 24 * 60 + $dateInterval->i;
        }elseif ($unit == "second"){
            return $dateInterval->days * 24 * 60 * 60 + $dateInterval->s;
        }

        return $dateInterval->days;
    }

    /**
     * @param bool $short
     * @return false|string
     */
    public function humanDiff($short = false)
    {
        $dateInterval = $this->diff(new DateTime(), false);

        //year
        if ($dateInterval->y) {
            return $this->humanPlaceholder('year', $dateInterval->y, $dateInterval->invert, $short);
            //month
        } elseif ($dateInterval->m) {
            return $this->humanPlaceholder('month', $dateInterval->m, $dateInterval->invert, $short);
        } elseif ($dateInterval->d) {

            //week
            if ($dateInterval->d > 7) {

                $week = floor($dateInterval->d / 7);
                return $this->humanPlaceholder('week', $week, $dateInterval->invert, $short);
            }

            //day
            return $this->humanPlaceholder('day', $dateInterval->d, $dateInterval->invert, $short);
            //hour
        } elseif ($dateInterval->h) {
            return $this->humanPlaceholder('hour', $dateInterval->h, $dateInterval->invert, $short);
            //minute
        } elseif ($dateInterval->i) {
            return $this->humanPlaceholder('minute', $dateInterval->i, $dateInterval->invert, $short);
            //second
        } elseif ($dateInterval->s) {
            return $this->humanPlaceholder('second', $dateInterval->s, $dateInterval->invert, $short);
            //default
        } else {
            return $this->formatLocale(self::TEXT_BASED_DATE_SHORT);
        }
    }


    /**
     * @param string $key
     * @param int $unit
     * @param int $invert
     * @param bool $short
     * @return string
     */
    private function humanPlaceholder(string $key, int $unit, int $invert, bool $short): string
    {
        $placeholder = ':ph-empty';
        $locale = strstr($this->locale, '.', true);
        $locale = $locale ?: $this->locale;
        $locale = strtolower($locale);

        if ($short == false) {
            $placeholder = $invert ? ':ph-after' : ':ph-before';
        }

        if(!isset(self::$HUMAN_TEXT[$locale])){

            if(is_file(self::$lang_dir.DS.$locale.EXT)){
                $locale_file = include_once (self::$lang_dir.DS.$locale.EXT);
                self::$HUMAN_TEXT[$locale] = $locale_file;
            }else{
                self::$HUMAN_TEXT[$locale] = self::$HUMAN_TEXT['C'];
            }
        }

        $HUMAN_TEXT = self::$HUMAN_TEXT[$locale];
        $message = sprintf(translate_parser($HUMAN_TEXT[$key], $unit), $unit);
        return trim(str_replace(':ph', $HUMAN_TEXT[$placeholder], $message));
    }

    /**
     * @param string $format
     * @return string
     */
    public function format(string $format = Era::SQL): string
    {
        return $this->dateTime->format($format);
    }


    /**
     * @param int $year
     * @param int $month
     * @param int $day
     * @return self
     */
    public function setDate(int $year, int $month, int $day): self
    {
        $this->dateTime->setDate($year, $month, $day);
        return $this;
    }


    /**
     * @param int $year
     * @param int $week
     * @param int $dayOfWeek
     * @return $this
     */
    public function setISODate(int $year, int $week, int $dayOfWeek = 1): self
    {
        $this->dateTime->setISODate($year, $week, $dayOfWeek);
        return $this;
    }


    /**
     * @param int $hour
     * @param int $minute
     * @param int $second
     * @param int $microsecond
     * @return $this
     */
    public function setTime(int $hour, int $minute, int $second = 0, int $microsecond = 0): self
    {
        $this->dateTime->setTime($hour, $minute, $second, $microsecond);
        return $this;
    }


    /**
     * @param int $timestamp
     * @return $this
     */
    public function setTimestamp(int $timestamp): self
    {
        $this->dateTime->setTimestamp($timestamp);
        return $this;
    }


    /**
     * @param $timezone
     * @return $this
     */
    public function setTimezone($timezone): self
    {
        if (is_string($timezone)) {
            $timezone = new DateTimeZone($timezone);
        }

        $this->dateTime->setTimezone($timezone);
        return $this;
    }


    public function getDate($format = Era::LOCALE_DATE_FORMAT): string
    {
        return $this->formatLocale($format);
    }


    /**
     * @param string $format
     * @return string
     */
    public function getTime($format = "H:i:s"): string
    {
        return $this->formatLocale($format);
    }


    /**
     * @return int
     */
    public function getOffset(): int
    {
        return $this->dateTime->getOffset();
    }


    /**
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->dateTime->getTimestamp();
    }

    /**
     * @return DateTimeZone
     */
    public function getTimezone(): DateTimeZone
    {
        return $this->dateTime->getTimezone();
    }

    /**
     * DateTimeInterface __wakeup()
     */
    public function __wakeup(): void
    {
        $this->dateTime->__wakeup();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->format();
    }
}
