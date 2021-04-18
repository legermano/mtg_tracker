<?php
class CardPrice extends TRecord
{
    const TABLENAME = 'card_price';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max';

    public function __construct($id = null)
    {
        parent::__construct($id);
        parent::addAttribute('card_uuid');
        parent::addAttribute('prices');
    }
}