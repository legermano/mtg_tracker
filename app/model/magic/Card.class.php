<?php
class Card extends TRecord
{
    const TABLENAME = 'cards';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max';
    const DATABASE = 'all_printings';
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
        parent::addAttribute('artist');
        parent::addAttribute('asciiName');
        parent::addAttribute('availability');
        parent::addAttribute('borderColor');
        parent::addAttribute('cardKingdomFoilId');
        parent::addAttribute('cardKingdomId');
        parent::addAttribute('colorIdentity');
        parent::addAttribute('colorIndicator');
        parent::addAttribute('colors');
        parent::addAttribute('convertedManaCost');
        parent::addAttribute('duelDeck');
        parent::addAttribute('edhrecRank');
        parent::addAttribute('faceConvertedManaCost');
        parent::addAttribute('faceName');
        parent::addAttribute('flavorName');
        parent::addAttribute('flavorText');
        parent::addAttribute('frameEffects');
        parent::addAttribute('frameVersion');
        parent::addAttribute('hand');
        parent::addAttribute('hasAlternativeDeckLimit');
        parent::addAttribute('hasContentWarning');
        parent::addAttribute('hasFoil');
        parent::addAttribute('hasNonFoil');
        parent::addAttribute('isAlternative');
        parent::addAttribute('isFullArt');
        parent::addAttribute('isOnlineOnly');
        parent::addAttribute('isOversized');
        parent::addAttribute('isPromo');
        parent::addAttribute('isReprint');
        parent::addAttribute('isReserved');
        parent::addAttribute('isStarter');
        parent::addAttribute('isStorySpotlight');
        parent::addAttribute('isTextless');
        parent::addAttribute('isTimeshifted');
        parent::addAttribute('keywords');
        parent::addAttribute('layout');
        parent::addAttribute('leadershipSkills');
        parent::addAttribute('life');
        parent::addAttribute('loyalty');
        parent::addAttribute('manaCost');
        parent::addAttribute('mcmId');
        parent::addAttribute('mcmMetaId');
        parent::addAttribute('mtgArenaId');
        parent::addAttribute('mtgjsonV4Id');
        parent::addAttribute('mtgoFoilId');
        parent::addAttribute('mtgoId');
        parent::addAttribute('multiverseId');
        parent::addAttribute('name');
        parent::addAttribute('number');
        parent::addAttribute('originalReleaseDate');
        parent::addAttribute('originalText');
        parent::addAttribute('originalType');
        parent::addAttribute('otherFaceIds');
        parent::addAttribute('power');
        parent::addAttribute('printings');
        parent::addAttribute('promoTypes');
        parent::addAttribute('purchaseUrls');
        parent::addAttribute('rarity');
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
        parent::addAttribute('variations');
        parent::addAttribute('watermark');
    }

    public static function getCards($name = "", $setCode = "", $limit = 10, $offset = 0, $translate = false)
    {
        // Removes the " symbol
        // Some cards have this on the name and it breaks the sql statement
        $name    = str_replace("\"","",$name);
        $setCode = str_replace("\"","",$setCode);

        if (ApplicationTranslator::getLanguage() == 'pt')
        {
            $sql = "SELECT a.*, coalesce(group_concat(distinct(c.name)),a.name) as t_name, count(a.id) as quantity
                    FROM cards a
                    LEFT JOIN foreign_data b ON (a.uuid = b.uuid)
                    LEFT JOIN foreign_data c ON (a.uuid = c.uuid AND c.language = 'Portuguese (Brazil)')
                    WHERE a.isOnlineOnly = 0
                    AND ( COALESCE(a.faceName,a.name) like \"%{$name}%\" OR b.name like \"%{$name}%\")
                    AND a.setCode like \"%{$setCode}%\"
                    GROUP BY a.name
                    ORDER BY t_name
                    LIMIT {$limit}
                    OFFSET {$offset}
                    ";
        }
        else
        {
            $sql = "SELECT a.*, count(a.id) as quantity
                    FROM cards a
                    LEFT JOIN foreign_data b ON (a.uuid = b.uuid)
                    WHERE a.isOnlineOnly = 0
                    AND ( COALESCE(a.faceName,a.name) like \"%{$name}%\" OR b.name like \"%{$name}%\")
                    AND a.setCode like \"%{$setCode}%\"
                    GROUP BY a.name
                    ORDER BY a.name
                    LIMIT {$limit}
                    OFFSET {$offset}
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
            $card->name         = $card->faceName ?? $card->name;
            $card->originalName = $card->name;
            if ($translate)
            {
                $card->translateByName($card->name);
            }
            array_push($objects,$card);
        }

        return $objects;

    }

    public static function getImage($multiverseId,$scryFallId,$side)
    {
        if ($multiverseId && false)
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

    public static function getImageByName($name,$id = null)
    {
        if ($id)
        {
            $imgURL = "https://gatherer.wizards.com/Handlers/Image.ashx?type=card&multiverseid={$id}";
        }
        else
        {
            // Removes the " symbol
            // Some cards have this on the name and it breaks the sql statement
            $name = str_replace("\"","",$name);

            //Query the oldest set that has this card and get the images ID
            $sql = "SELECT multiverseId, scryfallId, side
                    FROM cards a, sets b
                    WHERE coalesce(a.faceName,a.name) LIKE \"%{$name}%\"
                      AND a.setCode = b.code
                      AND (multiverseId IS NOT NULL OR scryfallId IS NOT NULL)
                    ORDER BY b.releaseDate LIMIT 1";

            //Default image, if theres none in the image bank
            $imgURL = "https://gatherer.wizards.com/Handlers/Image.ashx?type=card&multiverseid=32066";

            try
            {
                //Open transaction and fetch the results
                TTransaction::open(self::DATABASE);
                $conn = TTransaction::get();
                $sth = $conn->prepare($sql);
                $sth->execute();
                $result = $sth->fetchAll();

                if ($result[0])
                {
                    $multiverseId = $result[0]['multiverseId'];
                    $scryFallId   = $result[0]['scryfallId'];
                    $side         = $result[0]['side'];
                }

                TTransaction::close();

                //Use the image from Wizards, if theres a code
                if ($multiverseId)
                {
                    $imgURL = "https://gatherer.wizards.com/Handlers/Image.ashx?type=card&multiverseid={$multiverseId}";
                }
                else if ($scryFallId)
                {
                    $imgURL = "https://api.scryfall.com/cards/{$scryFallId}?format=image";
                    if ($side == 'a') {
                        $imgURL .= '&face=front';
                    }
                    else if($side == 'b')
                    {
                        $imgURL .= '&face=back';
                    }
                }

            }
            catch (Exception $e)
            {
                // undo all pending operations
                TTransaction::rollback();
            }
        }

        return $imgURL;
    }

    public function translate()
    {
        $this->rarity_t = _t(ucfirst($this->rarity));
        if(empty($this->isTranslated) && (ApplicationTranslator::getLanguage() == 'pt'))
        {
            $language = 'Portuguese (Brazil)';
            $criteria = new TCriteria();
            $criteria->add(new TFilter('uuid','=',$this->uuid));
            $criteria->add(new TFilter('language','like',$language));

            $translations = ForeignData::getObjects($criteria);

            foreach ($translations as $translation)
            {
                $this->name           = empty($translation->name)         ? $this->name           : $translation->name;
                $this->text           = empty($translation->text)         ? $this->text           : $translation->text;
                $this->flavorText     = empty($translation->flavorText)   ? $this->flavorText     : $translation->flavorText;
                $this->type           = empty($translation->type)         ? $this->type           : $translation->type;
                $this->multiverseId_t = empty($translation->multiverseid) ? $this->multiverseId_t : $translation->multiverseid;

                $this->isTranslated = true;
            }
        }
    }

    public function translateByName($name)
    {
        $this->rarity_t = _t(ucfirst($this->rarity));
        if(empty($this->isTranslated) && (ApplicationTranslator::getLanguage() == 'pt'))
        {
            // Removes the " symbol
            // Some cards have this on the name and it breaks the sql statement
            $name = str_replace("\"","",$name);
            $language = 'Portuguese (Brazil)';

            $criteria = new TCriteria();
            $criteria->add(new TFilter('uuid','in',"(SELECT uuid FROM cards WHERE COALESCE(faceName,name) like \"{$name}\")"));
            $criteria->add(new TFilter('language','like',$language));

            $translations = ForeignData::getObjects($criteria);

            foreach ($translations as $translation)
            {
                $this->name           = empty($translation->name)         ? $this->name           : $translation->name;
                $this->text           = empty($translation->text)         ? $this->text           : $translation->text;
                $this->flavorText     = empty($translation->flavorText)   ? $this->flavorText     : $translation->flavorText;
                $this->type           = empty($translation->type)         ? $this->type           : $translation->type;
                $this->multiverseId_t = empty($translation->multiverseid) ? $this->multiverseId_t : $translation->multiverseid;

                $this->isTranslated = true;
            }
        }
    }

    public function getDescription()
    {
        $description = "{$this->name} {$this->manaCost} (".str_replace('.0','',$this->convertedManaCost).") <br>
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

    public static function countCardsByName($name)
    {
        TTransaction::open(self::DATABASE);
        $conn = TTransaction::get();
        $sql = "SELECT count(*) AS \"count\" FROM cards WHERE name = \"{$name}\" AND isOnlineOnly = 0";
        $sth = $conn->prepare($sql);
        $sth->execute();
        $result = $sth->fetchAll();
        TTransaction::close();
        return $result[0]['count'];
    }

    public function getSet()
    {
        $criteria = new TCriteria();
        $criteria->add(new TFilter('code','=',$this->setCode));

        $sets = Set::getObjects($criteria);
        $set  = $sets[0];
        $set->keyruneCode = strtolower($set->keyruneCode);
        return $set;
    }

    public static function str_contains($haystack, $needle) {
        return $needle !== '' && mb_strpos($haystack, $needle) !== false;
    }

    public function getQuantityOwned()
    {
        TTransaction::open('mtg_tracker');
        $criteria = new TCriteria();
        $criteria->add(new TFilter('card_uuid','=',$this->uuid));
        $criteria->add(new TFilter('system_user_id','=',TSession::getValue('userid')));
        $owned = OwnedCard::getObjects($criteria);
        TTransaction::close();

        $quantity = 0;
        if ($owned) {
            $quantity = $owned['0']->quantity;
        }

        return $quantity;
    }
}