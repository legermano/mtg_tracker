<?php
use Adianti\Database\TRecord;
class Format extends TRecord
{
    const TABLENAME = 'format';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max';

    use SystemChangeLogTrait;

    public function __construct($id = null)
    {
        parent::__construct($id);
        parent::addAttribute('name');
        parent::addAttribute('format_key');
        parent::addAttribute('min_size');
        parent::addAttribute('max_size');
        parent::addAttribute('sideboard');
    }
}