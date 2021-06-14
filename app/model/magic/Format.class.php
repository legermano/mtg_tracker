<?php

use Adianti\Database\TCriteria;
use Adianti\Database\TFilter;
use Adianti\Database\TRecord;
use Adianti\Database\TRepository;
use Adianti\Database\TTransaction;

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

    public static function getFormatByKey($key)
    {
        TTransaction::open('mtg_tracker');

        $criteria = new TCriteria;
        $criteria->add(new TFilter('format_key', '=', $key));

        $repository = new TRepository('Format');
        $format     = $repository->load($criteria, FALSE);

        TTransaction::close();
        return $format;
    }
}