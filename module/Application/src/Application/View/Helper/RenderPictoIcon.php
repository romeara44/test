<?php

namespace Application\View\Helper;
use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Model\ViewModel;

class RenderPictoIcon extends AbstractHelper
{
    private $exts = array('doc', 'pdf', 'zip', 'excel');
    public function __invoke($filename)
    {
        $ext = substr($filename, strrpos($filename, '.') + 1);

        if (in_array($ext, $this->exts)) {
            return 'i-' . $ext;
        }

        return 'i-file';
    }


}