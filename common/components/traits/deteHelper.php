<?php
namespace common\components\traits;

use DateTime;

trait deteHelper
{
    public function strToTs($date, $format = 'd-m-Y')
    {
        $d = DateTime::createFromFormat($format, $date);

        if ($d && $d->format($format) == $date) {
            return $d->getTimestamp();
        }

        return null;
    }
}