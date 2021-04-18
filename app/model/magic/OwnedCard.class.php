<?php
class OwnedCard extends TRecord
{
    const TABLENAME  = 'owned_card';
    const PRIMARYKEY = 'id';
    const IDPOLICY   = 'max';
    const DATABASE   = 'mtg_tracker';

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

    public static function incrementQuantity($uuid)
    {
        TTransaction::open(self::DATABASE);
        $criteria = new TCriteria();
        $criteria->add(new TFilter('card_uuid','=',$uuid));
        $criteria->add(new TFilter('system_user_id','=',TSession::getValue('userid')));
        $owneds = OwnedCard::getObjects($criteria);

        if ($owneds) {
            foreach ($owneds as $owned) {
                $owned->quantity = $owned->quantity + 1;
                $owned->store();
            }
        }
        else
        {
            $owned = new OwnedCard;
            $owned->system_user_id = TSession::getValue('userid');
            $owned->card_uuid = $uuid;
            $owned->quantity = 1;
            $owned->quantity_foil = 0;
            $owned->store();

        }
        TTransaction::close();
    }

    public static function decrementQuantity($uuid)
    {
        TTransaction::open(self::DATABASE);
        $criteria = new TCriteria();
        $criteria->add(new TFilter('card_uuid','=',$uuid));
        $criteria->add(new TFilter('system_user_id','=',TSession::getValue('userid')));
        $owneds = OwnedCard::getObjects($criteria);

        foreach ($owneds as $owned) {
            if ($owned->quantity > 0) {
                $owned->quantity = $owned->quantity - 1;
                $owned->store();
            }
        }
        TTransaction::close();
    }
}