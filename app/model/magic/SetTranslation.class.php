<?php
class SetTranslation extends TRecord
{
    const TABLENAME = 'set_translations';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max';

    public function __construct($id = null)
    {
        parent::__construct($id);
        parent::addAttribute('language');
        parent::addAttribute('setCode');
        parent::addAttribute('translation');
    }
}