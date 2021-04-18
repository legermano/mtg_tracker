<?php
class Meta extends TRecord
{
    const TABLENAME = 'meta';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max';

    public function __construct($id = null)
    {
        parent::__construct($id);
        parent::addAttribute('date');
        parent::addAttribute('version');
    }
}