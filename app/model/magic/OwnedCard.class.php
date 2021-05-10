<?php
use Adianti\Database\TRecord;
use Adianti\Database\TTransaction;
use Adianti\Database\TCriteria;
use Adianti\Database\TFilter;
use Adianti\Registry\TSession;
class OwnedCard extends TRecord
{
    const TABLENAME  = 'owned_card';
    const PRIMARYKEY = 'id';
    const IDPOLICY   = 'max';
    const DATABASE   = 'mtg_tracker';

    use SystemChangeLogTrait;

    public function __construct($id = null)
    {
        parent::__construct($id);
        parent::addAttribute('system_user_id');
        parent::addAttribute('card_uuid');
        parent::addAttribute('quantity');
        parent::addAttribute('quantity_foil');
    }

    public function get_system_user()
    {
        //loads the associated object
        if (empty($this->user))
        {
            TTransaction::open('permission');
            $this->user = SystemUser::find($this->system_user_id);
            TTransaction::close();
        }

        //returns the associated object
        return $this->user;
    }

    public function get_card()
    {
        //loads the associated object
        if (empty($this->card))
        {
            TTransaction::open('all_printings');
            $this->card = Card::find($this->card_uuid);
            TTransaction::close();
        }

        //returns the associated object
        return $this->card;
    }

    public static function increment($uuid,$foil = false)
    {
        TTransaction::open(self::DATABASE);
        $card = new Card($uuid);
        $criteria = new TCriteria();
        $criteria->add(new TFilter('setcode','=',$card->setcode));
        $criteria->add(new TFilter('number', '=',$card->number));
        $cards = Card::getObjects($criteria);
        foreach ($cards as $card) {
            OwnedCard::incrementQuantity($card->uuid,$foil);
        }
        TTransaction::close();
    }

    public static function incrementQuantity($uuid,$foil = false)
    {
        TTransaction::open(self::DATABASE);
        $criteria = new TCriteria();
        $criteria->add(new TFilter('card_uuid','=',$uuid));
        $criteria->add(new TFilter('system_user_id','=',TSession::getValue('userid')));
        $owneds = OwnedCard::getObjects($criteria);

        if ($owneds) {
            foreach ($owneds as $owned) {
                if ($foil)
                {
                    $owned->quantity_foil = $owned->quantity_foil + 1;
                }
                else
                {
                    $owned->quantity = $owned->quantity + 1;
                }

                $owned->store();
            }
        }
        else
        {
            $owned = new OwnedCard;
            $owned->system_user_id = TSession::getValue('userid');
            $owned->card_uuid      = $uuid;
            $owned->quantity       = $foil ? 0 : 1;
            $owned->quantity_foil  = $foil ? 1 : 0;
            $owned->store();

        }
        TTransaction::close();
    }

    public static function decrement($uuid,$foil = false)
    {
        TTransaction::open(self::DATABASE);
        $card = new Card($uuid);
        $criteria = new TCriteria();
        $criteria->add(new TFilter('setcode','=',$card->setcode));
        $criteria->add(new TFilter('number', '=',$card->number));
        $cards = Card::getObjects($criteria);
        foreach ($cards as $card) {
            OwnedCard::decrementQuantity($card->uuid,$foil);
        }
        TTransaction::close();
    }

    public static function decrementQuantity($uuid,$foil = false)
    {
        TTransaction::open(self::DATABASE);
        $criteria = new TCriteria();
        $criteria->add(new TFilter('card_uuid','=',$uuid));
        $criteria->add(new TFilter('system_user_id','=',TSession::getValue('userid')));
        $owneds = OwnedCard::getObjects($criteria);

        foreach ($owneds as $owned) {
            if (!$foil && $owned->quantity > 0) {
                $owned->quantity = $owned->quantity - 1;
                $owned->store();
            }

            if ($foil && $owned->quantity_foil > 0) {
                $owned->quantity_foil = $owned->quantity_foil - 1;
                $owned->store();
            }
        }
        TTransaction::close();
    }
}