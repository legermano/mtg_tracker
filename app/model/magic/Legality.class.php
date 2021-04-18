<?php
class Legality extends TRecord
{
    const TABLENAME = 'legalities';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max';

    public function __construct($id = null)
    {
        parent::__construct($id);
        parent::addAttribute('format');
        parent::addAttribute('status');
        parent::addAttribute('uuid');
    }
}