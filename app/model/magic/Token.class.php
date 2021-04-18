<?php
class Token extends TRecord
{
    const TABLENAME = 'tokens';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max';

    public function __construct($id = null)
    {
        parent::__construct($id);
        parent::addAttribute('artist');
        parent::addAttribute('asciiName');
        parent::addAttribute('availability');
        parent::addAttribute('borderColor');
        parent::addAttribute('colorIdentity');
        parent::addAttribute('colors');
        parent::addAttribute('edhrecRank');
        parent::addAttribute('faceName');
        parent::addAttribute('flavorText');
        parent::addAttribute('frameEffects');
        parent::addAttribute('frameVersion');
        parent::addAttribute('hasFoil');
        parent::addAttribute('hasNonFoil');
        parent::addAttribute('isFullArt');
        parent::addAttribute('isPromo');
        parent::addAttribute('isReprint');
        parent::addAttribute('keywords');
        parent::addAttribute('layout');
        parent::addAttribute('mcmId');
        parent::addAttribute('mtgArenaId');
        parent::addAttribute('mtgjsonV4Id');
        parent::addAttribute('multiverseId');
        parent::addAttribute('name');
        parent::addAttribute('number');
        parent::addAttribute('originalText');
        parent::addAttribute('originalType');
        parent::addAttribute('power');
        parent::addAttribute('promoTypes');
        parent::addAttribute('reverseRelated');
        parent::addAttribute('scryfallId');
        parent::addAttribute('scryfallIllustrationId');
        parent::addAttribute('scryfallOracleId');
        parent::addAttribute('setCode');
        parent::addAttribute('side');
        parent::addAttribute('subtypes');
        parent::addAttribute('supertypes');
        parent::addAttribute('tcgplayerProductId');
        parent::addAttribute('text');
        parent::addAttribute('toughness');
        parent::addAttribute('type');
        parent::addAttribute('types');
        parent::addAttribute('uuid');
        parent::addAttribute('watermark');
    }
}