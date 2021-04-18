<?php
class DeckCard extends TRecord
{
    const TABLENAME  = 'deck_card';
    const PRIMARYKEY = 'id';
    const IDPOLICY   = 'max';

    /**
     * Constructor method
     */
    public function __construct($id = null)
    {
        parent::__construct($id);
        parent::addAttribute('deck_id');
        parent::addAttribute('card_uuid');
        parent::addAttribute('quantity');
    }
}