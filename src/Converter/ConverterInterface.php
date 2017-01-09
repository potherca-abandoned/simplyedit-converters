<?php

namespace Potherca\SimplyEdit\Converter;

use Potherca\SimplyEdit\Common\DomNavigator;

interface ConverterInterface
{
    public function __construct(DomNavigator $navigator);

    /**
     * @throws Exception
     */
    public function convert();
}

/*EOF*/
