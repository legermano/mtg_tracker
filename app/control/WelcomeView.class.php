<?php

use Adianti\Control\TPage;
use Adianti\Registry\TSession;
use Adianti\Widget\Base\TElement;

/**
 * WelcomeView
 *
 * @version    1.0
 * @package    control
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class WelcomeView extends TPage
{
    /**
     * Class constructor
     * Creates the page
     */
    function __construct()
    {
        parent::__construct();

        $div = new TElement('div');
        $div->class = 'row';

        $div->add( $g1 = new OwnedPercentageBySet(TSession::getValue('userid')) );

        $g1->class = 'col-sm-12';

        parent::add($div);
    }
}
