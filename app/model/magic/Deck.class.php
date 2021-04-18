<?
class Deck extends TRecord
{
    const TABLENAME  = 'deck';
    const PRIMARYKEY = 'id';
    const IDPOLICY   = 'max';    

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
        $criteria = new TCriteria;
        $criteria->add(new TFilter('deck_id','=',$this->$id));

        $repository = new TRepository('DeckCard');
        $cards = $repository->load($criteria);
        
        return $cards;
    }
}