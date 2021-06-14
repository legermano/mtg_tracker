<?php

use Adianti\Database\TRecord;
use Adianti\Database\TTransaction;

class Deck extends TRecord
{
    const TABLENAME  = 'deck';
    const PRIMARYKEY = 'id';
    const IDPOLICY   = 'max';
    const DATABASE   = 'mtg_tracker';

    use SystemChangeLogTrait;

    /**
     * Construct method
     */
    public function __construct($id = NULL)
    {
        parent::__construct($id);
        parent::addAttribute('system_user_id');
        parent::addAttribute('format_id');
        parent::addAttribute('name');
        parent::addAttribute('description');
        parent::addAttribute('colors');
        parent::addAttribute('is_valid');
        parent::addAttribute('creation_date');
    }

    /**
     * Returns the format
     */
    public function get_format()
    {
        //loads the associated object
        if (empty($this->format))
        {
            $this->format = new Format($this->format_id);
        }

        //returns the associated object
        return $this->format;
    }

    /**
     * Returns the format name
     */
    public function get_format_name()
    {
        //loads the associated object
        if (empty($this->format))
        {
            $this->format = new Format($this->format_id);
        }

        //returns the associated object
        return $this->format;
    }

    /**
     * Returns the user
     */
    public function get_user()
    {
        //loads the associated object
        if (empty($this->user))
        {
            $this->user = new SystemUser($this->system_user_id);
        }

        //returns the associated object
        return $this->user;
    }

    /**
     * Return all the cards
     */
    public function getAllCards()
    {
        if (ApplicationTranslator::getLanguage() == 'pt')
        {
            $sql = "SELECT deck_card.id,coalesce(nameptbr,card.name) as t_name, setcode , number,
                           hasalternativedecklimit, uuid, quantity, card.originalname,
                           ( jsonb_exists_any(card.types -> 'types',array['Land']) ) as land
                   ";
        }
        else
        {
            $sql = "SELECT deck_card.id,card.name as t_name, setcode , number,
                           hasalternativedecklimit, uuid, quantity,card.originalname,
                           ( jsonb_exists_any(card.types -> 'types',array['Land']) ) as land
                   ";
        }

        $sql .= "      FROM deck_card
                 INNER JOIN card ON (deck_card.card_uuid = card.uuid)
                      WHERE deck_card.deck_id = {$this->id}";

        $sql = "SELECT c.t_name as name, c.setcode , c.number, c.land, c.originalname,
                       c.hasalternativedecklimit, c.uuid, c.quantity, c.id
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
            $card->quantity    = $result['quantity'];
            $card->description = "{$card->name} ({$card->setcode}) (#{$card->number})";
            $card->unlimit     = $result['hasalternativedecklimit'];
            $card->land        = $result['land'];
            $card->id          = $result['id'];
            $objects[$card->uuid] = $card;
        }

        return $objects;
    }

    public static function checkValid($id)
    {
        TTransaction::open('mtg_tracker');
        $deck   = new Deck($id);
        $format = new Format($deck->format_id);

        $cards = $deck->getAllCards();
        $is_valid = TRUE;
        $colors = array();

        $quantity = 0;

        foreach ($cards as $deckCard)
        {
            $quantity += $deckCard->quantity;
            $card = new Card($deckCard->uuid);
            $formats = json_decode($card->legalities);
            if (!in_array($format->format_key,$formats->legalities))
            {
                $is_valid = FALSE;
            }

            $colorIdentity = str_replace('{','',$card->coloridentity);
            $colorIdentity = str_replace('}','',$colorIdentity);
            $colorIdentity = explode(',',$colorIdentity);
            //All color of the deck
            foreach ($colorIdentity as $key => $color)
            {
                if (!empty($color))
                {
                    $colors[$color] = $color;
                }
            }
        }

        if (     ( isset($format->min_size) AND $quantity < $format->min_size)
              OR ( isset($format->max_size) AND $quantity > $format->max_size)
           )
        {
            $is_valid = FALSE;
        }

        $deck_colors = '{'.implode(',',$colors).'}';

        $deck->is_valid = $is_valid;
        $deck->colors   = $deck_colors;
        $deck->store();

        TTransaction::close();
    }
}