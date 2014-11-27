<?php

class CORE_View_Helper_ToAge
{
    public function toAge($value)
    {
        if (!$value instanceof Zend_Date)
            $value = new Zend_Date($value);
        return floor($value->sub(Zend_Date::now())->toValue() / 86400 / 365 * -1);
    }
}