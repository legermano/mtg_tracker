<?php

use Adianti\Database\TRecord;
use Adianti\Database\TTransaction;
use Adianti\Registry\TSession;
class Card extends TRecord
{
    const TABLENAME = 'card';
    const PRIMARYKEY= 'uuid';
    const IDPOLICY =  'max';
    const DATABASE = 'mtg_tracker';
    const SYMBOLS = array(
        '{T}'       => 'T',
        '{Q}'       => 'Q',
        '{E}'       => 'E',
        '{PW}'      => 'PW',
        '{CHAOS}'   => 'CHAOS',
        '{A}'       => 'A',
        '{X}'       => 'X',
        '{Y}'       => 'Y',
        '{Z}'       => 'Z',
        '{0}'       => '0',
        '{½}'       => 'HALF',
        '{1}'       => '1',
        '{2}'       => '2',
        '{3}'       => '3',
        '{4}'       => '4',
        '{5}'       => '5',
        '{6}'       => '6',
        '{7}'       => '7',
        '{8}'       => '8',
        '{9}'       => '9',
        '{10}'      => '10',
        '{11}'      => '11',
        '{12}'      => '12',
        '{13}'      => '13',
        '{14}'      => '14',
        '{15}'      => '15',
        '{16}'      => '16',
        '{17}'      => '17',
        '{18}'      => '18',
        '{19}'      => '19',
        '{20}'      => '20',
        '{100}'     => '100',
        '{1000000}' => '1000000',
        '{∞}'       => 'INFINITY',
        '{W/U}'     => 'WU',
        '{W/B}'     => 'WB',
        '{B/R}'     => 'BR',
        '{B/G}'     => 'BG',
        '{U/B}'     => 'UB',
        '{U/R}'     => 'UR',
        '{R/G}'     => 'RG',
        '{R/W}'     => 'RW',
        '{G/W}'     => 'GW',
        '{G/U}'     => 'GU',
        '{2/W}'     => '2W',
        '{2/U}'     => '2U',
        '{2/B}'     => '2B',
        '{2/R}'     => '2R',
        '{2/G}'     => '2G',
        '{P}'       => 'P',
        '{W/P}'     => 'WP',
        '{U/P}'     => 'UP',
        '{B/P}'     => 'BP',
        '{R/P}'     => 'RP',
        '{G/P}'     => 'GP',
        '{HW}'      => 'HW',
        '{HR}'      => 'HR',
        '{W}'       => 'W',
        '{U}'       => 'U',
        '{B}'       => 'B',
        '{R}'       => 'R',
        '{G}'       => 'G',
        '{C}'       => 'C',
        '{S}'       => 'S'
    );

    public function __construct($id = null)
    {
        parent::__construct($id);
        parent::addAttribute('allnames');
        parent::addAttribute('artist');
        parent::addAttribute('availability');
        parent::addAttribute('bordercolor');
        parent::addAttribute('cardKingdomfoilid');
        parent::addAttribute('cardkingdomid');
        parent::addAttribute('coloridentity');
        parent::addAttribute('colorindicator');
        parent::addAttribute('colors');
        parent::addAttribute('convertedmanacost');
        parent::addAttribute('dueldeck');
        parent::addAttribute('edhrecRank');
        parent::addAttribute('faceconvertedmanacost');
        parent::addAttribute('facename');
        parent::addAttribute('flavorname');
        parent::addAttribute('flavortext');
        parent::addAttribute('flavortextptbr');
        parent::addAttribute('frameeffects');
        parent::addAttribute('frameversion');
        parent::addAttribute('hand');
        parent::addAttribute('hasalternativedecklimit');
        parent::addAttribute('hascontentwarning');
        parent::addAttribute('hasfoil');
        parent::addAttribute('hasnonfoil');
        parent::addAttribute('isalternative');
        parent::addAttribute('isfullart');
        parent::addAttribute('isonlineonly');
        parent::addAttribute('isoversized');
        parent::addAttribute('ispromo');
        parent::addAttribute('isreprint');
        parent::addAttribute('isreserved');
        parent::addAttribute('isstarter');
        parent::addAttribute('isstoryspotlight');
        parent::addAttribute('istextless');
        parent::addAttribute('istimeshifted');
        parent::addAttribute('keywords');
        parent::addAttribute('layout');
        parent::addAttribute('leadershipskills');
        parent::addAttribute('legalities');
        parent::addAttribute('life');
        parent::addAttribute('loyalty');
        parent::addAttribute('manacost');
        parent::addAttribute('mcmId');
        parent::addAttribute('mcmmetaid');
        parent::addAttribute('mtgarenaid');
        parent::addAttribute('mtgjsonv4id');
        parent::addAttribute('mtgofoilid');
        parent::addAttribute('mtgoid');
        parent::addAttribute('multiverseid');
        parent::addAttribute('multiverseidptbr');
        parent::addAttribute('name');
        parent::addAttribute('nameptbr');
        parent::addAttribute('number');
        parent::addAttribute('originalname');
        parent::addAttribute('originalreleasedate');
        parent::addAttribute('originaltext');
        parent::addAttribute('originaltype');
        parent::addAttribute('otherfaceids');
        parent::addAttribute('power');
        parent::addAttribute('printings');
        parent::addAttribute('prices');
        parent::addAttribute('promotypes');
        parent::addAttribute('purchaseurls');
        parent::addAttribute('rarity');
        parent::addAttribute('rullings');
        parent::addAttribute('scryfallid');
        parent::addAttribute('scryfallillustrationid');
        parent::addAttribute('scryfalloracleid');
        parent::addAttribute('setcode');
        parent::addAttribute('setname');
        parent::addAttribute('side');
        parent::addAttribute('subtypes');
        parent::addAttribute('supertypes');
        parent::addAttribute('tcgplayerproductid');
        parent::addAttribute('text');
        parent::addAttribute('textptbr');
        parent::addAttribute('toughness');
        parent::addAttribute('type');
        parent::addAttribute('typeptbr');
        parent::addAttribute('types');
        parent::addAttribute('variations');
        parent::addAttribute('watermark');
    }

    public static function getCards($name = "", $setCode = NULL, $format = NULL, $ownsCard = false, $limit = 10, $offset = 0)
    {
        // Removes the " symbol
        // Some cards have this on the name and it breaks the sql statement
        $name    = str_replace("'","''",$name);
        $user_id = TSession::getValue('userid');
        $limit   = $limit  ?? 10;
        $offset  = $offset ?? 0;

        if ($format)
        {
            $format = implode("','",$format);
            $format = "'{$format}'";
        }

        if ($setCode)
        {
            $setCode = implode("','",$setCode);
            $setCode = "'{$setCode}'";
        }

        // If the current language is portugues
        // Needs to run a different query to order by name correctly
        if (ApplicationTranslator::getLanguage() == 'pt')
        {
            $sql = "SELECT manacost, convertedmanacost,
                           split_part(coalesce(string_agg(distinct(nameptbr),'@'),coalesce(facename,name)),'@',1)                        as t_name,
                           split_part(coalesce(string_agg(distinct(flavortextptbr),'@'),string_agg(distinct(flavortext),'@')),'@',1)     as t_flavortext,
                           split_part(coalesce(string_agg(distinct(multiverseidptbr),'@'),string_agg(distinct(multiverseid),'@')),'@',1) as t_multiverseid,
                           split_part(coalesce(string_agg(distinct(textptbr),'@'),string_agg(distinct(text),'@')),'@',1)                 as t_text,
                           split_part(coalesce(string_agg(distinct(typeptbr),'@'),string_agg(distinct(type),'@')),'@',1)                 as t_type,
                           split_part(string_agg(distinct(side),'@'),'@',1)                                                              as t_side,
                           split_part(string_agg(distinct(scryfallid),'@'),'@',1)                                                        as t_scryfallid,
                           split_part(string_agg(distinct(uuid),'@'),'@',1)                                                              as t_uuid,
                           split_part(string_agg(distinct(originalname),'@'),'@',1)                                                      as t_originalname
                      FROM card
                   ";
        }
        else
        {
            $sql = "SELECT manacost, convertedmanacost,
                           split_part(coalesce(facename,name),'@',1)                as t_name,
                           split_part(string_agg(distinct(flavortext),'@'),'@',1)   as t_flavortext,
                           split_part(string_agg(distinct(multiverseid),'@'),'@',1) as t_multiverseid,
                           split_part(string_agg(distinct(text),'@'),'@',1)         as t_text,
                           split_part(string_agg(distinct(type),'@'),'@',1)         as t_type,
                           split_part(string_agg(distinct(side),'@'),'@',1)         as t_side,
                           split_part(string_agg(distinct(scryfallid),'@'),'@',1)   as t_scryfallid,
                           split_part(string_agg(distinct(uuid),'@'),'@',1)         as t_uuid,
                           split_part(string_agg(distinct(originalname),'@'),'@',1) as t_originalname
                      FROM card
                   ";
        }

        if($ownsCard)
        {
            $sql .= "INNER JOIN owned_card ON (
                         owned_card.card_uuid = card.uuid AND
                         owned_card.system_user_id = {$user_id} AND
                         ( owned_card.quantity > 0 OR owned_card.quantity_foil > 0)
                        )
                    ";
        }

        $sql .= "WHERE isonlineonly = 'f'
                   AND (allnames ->> 'names' ilike '%{$name}%' OR coalesce(facename,name) ilike '%{$name}%') ".
                ($setCode == NULL ? "" : "AND setcode in ({$setCode})").
                ($format  == NULL ? "" : "AND jsonb_exists_any(legalities -> 'legalities',array[{$format}]) ") .
                "GROUP BY name, facename, manacost, convertedmanacost
                 ORDER BY t_name
                 LIMIT {$limit}
                 OFFSET {$offset}
                ";


        $sql = "SELECT c.manacost as manacost, c.convertedmanacost as convertedmanacost,
                       c.t_name as name, c.t_flavortext as flavortext, c.t_multiverseid as multiverseid,
                       c.t_text as text, c.t_type as type, c.t_side as side, c.t_scryfallid as scryfallid,
                       c.t_uuid as uuid, c.t_originalname as originalname
                  FROM (
               ".$sql." ) as c";

        //Open transaction and fetch the results
        TTransaction::open(self::DATABASE);
        $conn = TTransaction::get();
        $sth = $conn->prepare($sql);
        $sth->execute();
        $results = $sth->fetchAll();
        TTransaction::close();

        $objects = array();
        foreach ($results as $result)
        {
            $card = new Card;
            $card->fromArray($result);
            $card->id = $card->uuid;
            $card->getDescription();
            $card->image = self::getImage($card->multiverseid,$card->scryfallid,$card->side);
            if (!empty($setCode)) {
                $card->setcode = $setCode;
            }
            array_push($objects,$card);
        }

        return $objects;

    }

    public static function getCard($name,$setCode)
    {
        // Removes the " symbol
        // Some cards have this on the name and it breaks the sql statement
        $name    = str_replace("'","''",$name);
        $user_id = TSession::getValue('userid');

        if (ApplicationTranslator::getLanguage() == 'pt')
        {
            $sql = "SELECT manaCost, convertedManaCost, side, scryfallId, uuid,
                           originalName, setCode, loyalty, rarity, artist, prices,
                           keyruneCode,
                           coalesce(owned_card.quantity,0)                 as quantity,
                           coalesce(owned_card.quantity_foil,0)            as quantity_foil,
                           coalesce(nameptbr,coalesce(faceName,card.name)) as name,
                           coalesce(flavorTextptbr,flavorText)             as flavortext,
                           coalesce(multiverseIdptbr,multiverseId)         as multiverseid,
                           coalesce(textptbr,text)                         as text,
                           coalesce(typeptbr,card.type)                    as type,
                           set.name                                        as setname,
                           (legalities ->> 'legalities')                   as legalities,
                           card.number                                     as number
                      FROM card
                INNER JOIN set ON (set.code = card.setCode)
                 LEFT JOIN owned_card ON (owned_card.card_uuid = card.uuid AND owned_card.system_user_id = {$user_id})
                     WHERE card.isOnlineOnly = 'f'
                       AND originalName = '{$name}'
                       AND card.setCode ilike '%{$setCode}%'
                     ORDER BY set.releaseDate asc,card.number::INTEGER asc
                   ";
        }
        else
        {
            $sql = "SELECT manaCost, convertedManaCost, side, scryFallId, uuid,
                           originalname, setcode, loyalty, rarity, artist, prices,
                           flavortext,multiverseid,text,card.type, keyrunecode,
                           coalesce(owned_card.quantity,0)      as quantity,
                           coalesce(owned_card.quantity_foil,0) as quantity_foil,
                           coalesce(faceName,card.name)         as name,
                           set.name                             as setname,
                           (legalities ->> 'legalities')        as legalities,
                           card.number                          as number
                      FROM card
                INNER JOIN set ON (set.code = card.setcode)
                 LEFT JOIN owned_card ON (owned_card.card_uuid = card.uuid AND owned_card.system_user_id = {$user_id})
                     WHERE card.isOnlineOnly = 'f'
                       AND originalname = '{$name}'
                       AND card.setcode ilike '%{$setCode}%'
                       ORDER BY set.releasedate asc,card.number::INTEGER asc
                   ";
        }

        //Open transaction and fetch the results
        TTransaction::open(self::DATABASE);
        $conn = TTransaction::get();
        $sth = $conn->prepare($sql);
        $sth->execute();
        $results = $sth->fetchAll();
        TTransaction::close();

        $objects = array();
        foreach ($results as $result)
        {
            $card = new Card;
            $card->fromArray($result);
            $card->id            = $card->uuid;
            $card->quantity      = $result["quantity"];
            $card->quantity_foil = $result["quantity_foil"];
            $card->image         = self::getImage($card->multiverseid,$card->scryfallid,$card->side);
            $card->setname       = $result["setname"];
            $card->keyrunecode   = strtolower($result["keyrunecode"]);
            $card->getDescription();
            array_push($objects,$card);
        }

        return $objects;
    }

    public static function getCardsBySet($setCode)
    {
        if (ApplicationTranslator::getLanguage() == 'pt')
        {
            $sql = "SELECT split_part(coalesce(string_agg(distinct(nameptbr),'@'),coalesce(faceName,card.name)),'@',1)                   as t_name,
                           split_part(coalesce(string_agg(distinct(multiverseIdptbr),'@'),string_agg(distinct(multiverseId),'@')),'@',1) as t_multiverseId,
                           split_part(string_agg(distinct(scryFallId),'@'),'@',1)                                                        as t_scryFallId,
                           split_part(string_agg(distinct(side),'@'),'@',1)                                                              as t_side,
                           split_part(string_agg(distinct(originalName),'@'),'@',1)                                                      as t_originalName,
                           split_part(string_agg(distinct(setcode),'@'),'@',1)                                                           as t_setcode,
                           min(number)                                                                                                   as t_number
                      FROM card
                INNER JOIN set ON (set.code = card.setcode)
                     WHERE card.setcode = '{$setCode}'
                     GROUP BY card.name, faceName, number
                     ORDER BY t_name
                   ";
        }
        else
        {
            $sql = "SELECT split_part(coalesce(faceName,card.name),'@',1)           as t_name,
                           split_part(string_agg(distinct(multiverseId),'@'),'@',1) as t_multiverseId,
                           split_part(string_agg(distinct(scryFallId),'@'),'@',1)   as t_scryFallId,
                           split_part(string_agg(distinct(side),'@'),'@',1)         as t_side,
                           split_part(string_agg(distinct(originalName),'@'),'@',1) as t_originalName,
                           split_part(string_agg(distinct(setcode),'@'),'@',1)      as t_setcode,
                           min(number)                                              as t_number
                      FROM card
                INNER JOIN set ON (set.code = card.setcode)
                     WHERE card.setcode = '{$setCode}'
                     GROUP BY card.name, faceName, number
                     ORDER BY t_name
                   ";
        }

        $sql = "SELECT c.t_name as name, c.t_multiverseId as multiverseid, c.t_setcode as setcode, c.t_number,
                       c.t_scryFallId as scryfallid, c.t_side as side, c.t_originalName as originalname
                  FROM (
               ".$sql." ) as c ORDER BY t_number::INTEGER ASC, t_side ASC";

        //Open transaction and fetch the results
        TTransaction::open(self::DATABASE);
        $conn = TTransaction::get();
        $sth = $conn->prepare($sql);
        $sth->execute();
        $results = $sth->fetchAll();
        TTransaction::close();

        $objects = array();
        foreach ($results as $result)
        {
            $card = new Card;
            $card->fromArray($result);
            $card->number = $result['t_number'];
            $card->image         = self::getImage($card->multiverseid,$card->scryfallid,$card->side);
            $card->getDescription();
            array_push($objects,$card);
        }

        return $objects;
    }

    public static function getImage($multiverseId,$scryFallId,$side)
    {
        if ($multiverseId)
        {
            $url = "https://gatherer.wizards.com/Handlers/Image.ashx?type=card&multiverseid={$multiverseId}";
        } else
        {
            $url = "https://api.scryfall.com/cards/{$scryFallId}?format=image";
            if ($side == 'a') {
                $url .= '&face=front';
            }
            else if($side == 'b')
            {
                $url .= '&face=back';
            }
        }

        return $url;
    }

    public function getDescription()
    {
        $description = "{$this->name} {$this->manacost} (".str_replace('.00','',$this->convertedmanacost).") <br>
                        {$this->type} <br>".str_replace("\n","</br>",$this->text);
        $this->description = self::putSymbols($description);
    }

    public static function putSymbols($text)
    {
        $path = "app/images/symbols/";
        $imgTag = '<img src="{src}" border="0" style="max-width: 15px; max-height: 15px"/>';
        $aSymbols = self::SYMBOLS;

        foreach ($aSymbols as $key => $value)
        {
            if (self::str_contains($text,$key))
            {
                $tag  = str_replace("{src}",($path.$value.".svg"),$imgTag);
                $text = str_replace($key,$tag,$text);
            }
        }

        return $text;
    }

    public static function countCardsByName($name,$setCode = NULL,$format = NULL,$ownsCard = false)
    {
        $name    = str_replace("\"","",$name);
        $name    = str_replace("'","''",$name);
        $user_id = TSession::getValue('userid');

        if ($setCode)
        {
            $setCode = implode("','",$setCode);
            $setCode = "'{$setCode}'";
        }

        if ($format)
        {
            $format = implode("','",$format);
            $format = "'{$format}'";
        }

        TTransaction::open(self::DATABASE);
        $conn = TTransaction::get();
        $sql = "SELECT count(a.*) AS \"count\"
                  FROM (
                      SELECT card.name
                      FROM card
                      INNER JOIN set ON (set.code = card.setCode)".
                      ($ownsCard ? "INNER JOIN owned_card ON (owned_card.card_uuid = card.uuid AND owned_card.system_user_id = {$user_id} AND ( owned_card.quantity > 0 OR owned_card.quantity_foil > 0)) " : "").
                      "WHERE card.isOnlineOnly = 'f'
                        AND (allNames ->> 'names' ilike '%{$name}%' OR coalesce(faceName,card.name) ilike '%{$name}%') ".
                        ($setCode == NULL ? "" : "AND setcode in ({$setCode}) ").
                        ($format  == NULL ? "" : "AND jsonb_exists_any(legalities -> 'legalities',array[{$format}]) ") .
                      "GROUP BY card.name
                  ) as a";
        $sth = $conn->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll();
        TTransaction::close();
        return $result[0]['count'];
    }

    public static function str_contains($haystack, $needle) {
        return $needle !== '' && mb_strpos($haystack, $needle) !== false;
    }
}