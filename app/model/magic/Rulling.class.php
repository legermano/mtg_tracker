<?php
class Rulling extends TRecord
{
    const TABLENAME = 'rullings';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max';

    public function __construct($id = null)
    {
        parent::__construct($id);
        parent::addAttribute('date');
        parent::addAttribute('text');
        parent::addAttribute('uuid');
    }
}