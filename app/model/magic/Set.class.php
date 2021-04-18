<?php
class Set extends TRecord
{
    const TABLENAME = 'sets';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max';

    public function __construct($id = null)
    {
        parent::__construct($id);
        parent::addAttribute('baseSetSize');
        parent::addAttribute('block');
        parent::addAttribute('booster');
        parent::addAttribute('code');
        parent::addAttribute('isFoilOnly');
        parent::addAttribute('isForeignOnly');
        parent::addAttribute('isNonFoilOnly');
        parent::addAttribute('isOnlineOnly');
        parent::addAttribute('isPartialPreview');
        parent::addAttribute('keyruneCode');
        parent::addAttribute('mcmId');
        parent::addAttribute('mcmIdExtras');
        parent::addAttribute('mcmName');
        parent::addAttribute('mtgoCode');
        parent::addAttribute('name');
        parent::addAttribute('parentCode');
        parent::addAttribute('releaseDate');
        parent::addAttribute('tcgplayerGroupId');
        parent::addAttribute('totalSetSize');
        parent::addAttribute('type');
    }
}