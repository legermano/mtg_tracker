<?php
class ForeignData extends TRecord
{
    const TABLENAME = 'foreign_data';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max';

    public function __construct($id = null)
    {
        parent::__construct($id);
        parent::addAttribute('flavorText');
        parent::addAttribute('language');
        parent::addAttribute('multiverseid');
        parent::addAttribute('name');
        parent::addAttribute('text');
        parent::addAttribute('type');
        parent::addAttribute('uuid');
    }
}