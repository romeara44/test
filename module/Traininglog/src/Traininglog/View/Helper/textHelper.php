<?php
namespace Traininglog\View\Helper;

use Zend\View\Helper\AbstractHelper;

class textHelper extends AbstractHelper
{
    public function __invoke($text, $length = 100, $options = array())
    {
        $default = array(
            'ending' => '...', 'exact' => false
        );
        $options = array_merge($default, $options);
        extract($options);

        if (mb_strlen($text) <= $length) {
            return $text;
        } else {
            $truncate = mb_substr($text, 0, $length - mb_strlen($ending));
        }

        if (!$exact) {
            $spacepos = mb_strrpos($truncate, ' ');
            if (isset($spacepos)) {
                $truncate = mb_substr($truncate, 0, $spacepos);
            }
        }
        $truncate .= $ending;
        return $truncate;
    }
}
?>