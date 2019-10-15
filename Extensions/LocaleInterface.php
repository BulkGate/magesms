<?php
namespace BulkGate\Magesms\Extensions;

use DateTime;

/**
 * Interface LocaleInterface
 * @package BulkGate\Magesms\Extensions
 */
interface LocaleInterface
{
    /**
     * @param $price
     * @param null $currency
     * @return string
     */
    public function price($price, $currency = null);

    /**
     * @param $number
     * @return string
     */
    public function float($number);

    /**
     * @param $number
     * @return string
     */
    public function int($number);

    /**
     * @param DateTime $dateTime
     * @return string
     */
    public function datetime(DateTime $dateTime);

    /**
     * @param DateTime $date
     * @return string
     */
    public function date(DateTime $date);

    /**
     * @param DateTime $date
     * @return string
     */
    public function time(DateTime $date);
}
