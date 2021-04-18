<?php
class Format extends TRecord
{
    const TABLENAME = 'format';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max';

    public function __construct($id = null)
    {
        parent::__construct($id);
        parent::addAttribute('name');
        parent::addAttribute('format_key');
    }
}