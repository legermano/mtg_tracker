<?php
use Adianti\Database\TRecord;
class Set extends TRecord
{
    const TABLENAME = 'set';
    const PRIMARYKEY= 'code';
    const IDPOLICY =  'max';

    public function __construct($id = null)
    {
        parent::__construct($id);
        parent::addAttribute('basesetsize');
        parent::addAttribute('block');
        parent::addAttribute('isfoilonly');
        parent::addAttribute('isforeignonly');
        parent::addAttribute('isnonfoilonly');
        parent::addAttribute('isonlineonly');
        parent::addAttribute('ispartialpreview');
        parent::addAttribute('keyrunecode');
        parent::addAttribute('mcmid');
        parent::addAttribute('mcmidextras');
        parent::addAttribute('mcmname');
        parent::addAttribute('mtgocode');
        parent::addAttribute('name');
        parent::addAttribute('parentcode');
        parent::addAttribute('releasedate');
        parent::addAttribute('tcgplayergroupid');
        parent::addAttribute('totalsetsize');
        parent::addAttribute('type');
    }
}