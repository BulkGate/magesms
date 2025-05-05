<?php
namespace BulkGate\Magesms\Extensions;

use DateTime;

/**
 * Class LocaleSimple
 * @package BulkGate\Magesms\Extensions
 */
class LocaleSimple extends Strict implements LocaleInterface
{
    /** @var string */
    private $date_format;

    /** @var string */
    private $time_format = "H:i:s";

    public function __construct($date_format = "d/m/Y", $time_format = "H:i:s")
    {
        $this->date_format = $date_format;
        $this->time_format = $time_format;
    }

    public function price($price, $currency = null)
    {
        if ($currency === null) {
            return $this->float($price);
        }

        return $this->float($price).' '.$currency;
    }

    public function float($number)
    {
        return (string) number_format((float) $number, 2);
    }

    public function int($number)
    {
        return (string) (int) $number;
    }

    public function datetime(DateTime $dateTime)
    {
        return $dateTime->format($this->date_format.' '.$this->time_format);
    }

    public function date(DateTime $date)
    {
        return $date->format($this->date_format);
    }

    public function time(DateTime $date)
    {
        return $date->format($this->time_format);
    }
}
