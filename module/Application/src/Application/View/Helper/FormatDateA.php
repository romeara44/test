<?php

namespace Application\View\Helper;
use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Model\ViewModel;

class FormatDateA extends AbstractHelper
{
    public function __invoke($date, $full = false, $withZero = false, $withTime = false)
    {
        $myDate = '';
        try {
            $myDate = new \DateTime($date);
            if ($full) {
                if ($withTime) {
                    $myDate = $myDate->format('l, F jS, Y &#97;&#116; h:ia');
                } else {
                    $myDate = $myDate->format('l, F jS, Y');
                }
            } else {
                $myDate = $myDate->format('m/d/Y');

                if (!$withZero) {
                    $myDate = str_replace('/0', '/', $myDate);
                    if ($myDate[0] == 0) {
                        $myDate = substr($myDate, 1, strlen($myDate) - 1);
                    }
                }
            }

        }
        catch (Zend_Date_Exception $e) {
            $myDate = '';
        }

        return $myDate;
    }


}