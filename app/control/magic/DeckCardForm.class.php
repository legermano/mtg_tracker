<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Database\TTransaction;
use Adianti\Registry\TSession;
use Adianti\Widget\Container\THBox;
use Adianti\Widget\Container\TTable;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Datagrid\TDataGridAction;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Datagrid\TPageNavigation;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TCheckGroup;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Form\TRadioGroup;
use Adianti\Widget\Util\TCardView;
use Adianti\Widget\Util\TXMLBreadCrumb;
use Adianti\Wrapper\BootstrapDatagridWrapper;
use Adianti\Wrapper\BootstrapFormBuilder;

class DeckCardForm extends TPage
{
    private $form;
    private $cardsList;
    private $deckCards;
    private $pageNavigation;

    public function __construct($param)
    {
        parent::__construct();

        if (array_key_exists('format_id',$param))
        {
            TSession::setValue('format',strtolower($param['format_id']));
        }

        if (array_key_exists('id',$param))
        {
            if (!empty($param['id']))
            {
                TSession::setValue('deck_id',$param['id']);
            }
        }

        if (array_key_exists('name',$param))
        {
            if (!empty($param['name']))
            {
                TSession::setValue('deck_name',$param['name']);
            }
        }

        //Create the filters
        $this->form = new BootstrapFormBuilder('form_deckCards_filter');
        $this->form->setFormTitle(TSession::getValue('deck_name'));

        $name = new TEntry("card_name");
        $name->setValue(TSession::getValue('card_name'));

        $colors = new TCheckGroup("colors");
        $colors->setValue(TSession::getValue('card_colors'));
        $colors->addItems([
            'W' => _t('White'),
            'G' => _t('Green'),
            'R' => _t('Red'),
            'B' => _t('Black'),
            'U' => _t('Blue')
        ]);
        $colors->setLayout('horizontal');
        $colors->setUseButton();

        $lands = new TRadioGroup('lands');
        $lands->setValue(TSession::getValue('card_lands'));
        $lands->addItems([
            't' => _t('Yes'),
            'f' => _t('No'),
            'b' => _t('Both')
        ]);
        $lands->setLayout('horizontal');
        $lands->setUseButton();

        $this->form->addFields( [new TLabel(_t('Name'))],   [$name]   );
        $this->form->addFields( [new TLabel(_t('Colors'))], [$colors] );
        $this->form->addFields( [new TLabel(_t('Lands'))],  [$lands]  );

        $name->setSize('70%');

        //Add the search from actions
        $this->form->addActionLink(_t('Back'), new TAction(array('DeckList','onReload')),'far:arrow-alt-circle-left #ff9600');
        $this->form->addAction(_t('Save'), new TAction(array($this, 'onSave')), 'far:save #2b982b');
        $this->form->addAction(_t('Find'), new TAction(array($this,'onSearch')), 'fa:search');

        $select_action = new TAction(
            [$this, 'onSelect'],
            [
                'uuid'         => '{uuid}',
                'name'         => '{name}',
                'setcode'      => '{setcode}',
                'number'       => '{number}',
                'unlimit'      => '{unlimt}',
                'originalname' => '{originalname}',
                'land'         => '{land}',
                'id'           => '{id}'
            ]
        );

        $this->cardsList = new TCardView;
        $this->cardsList->setProperty('deck_cards',FALSE);
        $this->cardsList->setTitleAttribute('{name} ({setcode}) (#{number})');
        $this->cardsList->setItemTemplate('<div style="display: flex; justify-content: center;"><img style="height:150px;float:right;margin:5px" src="{image}"></div>');
        $this->cardsList->addAction($select_action, _t('View'), 'fa:plus green');

        //Creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));

        //Creates the page structure using a table
        $tableCardsList = new TTable;
        $tableCardsList->style = 'width: 100%; border-collapse: collapse';
        $tableCardsList->addRow()->addCell($this->form);
        $tableCardsList->addRow()->addCell($this->cardsList);
        $tableCardsList->addRow()->addCell($this->pageNavigation);

        $increaseAction = new TDataGridAction([$this,'onSelect'],    ['uuid' => '{uuid}']);
        $decreaseAction = new TDataGridAction([$this,'decreaseCard'],['uuid' => '{uuid}']);

        //Creates the deck's card datagrid
        $this->deckCards = new BootstrapDatagridWrapper(new TDataGrid);
        $this->deckCards->style = 'width: 100%';
        $this->deckCards->addColumn( new TDataGridColumn('quantity',    _t('Quantity'), 'right','30'));
        $this->deckCards->addColumn( new TDataGridColumn('description', _t('Name'),     'left'));
        $this->deckCards->addAction($increaseAction, _t('Add'),'fa:plus green');
        $this->deckCards->addAction($decreaseAction, _t('Decrease'),'fa:minus red');
        $this->deckCards->createModel();

        $this->quantities = new TLabel("");
        $this->quantities->setFontStyle('b');

        $tableDeckCards = new TTable;
        $tableDeckCards->style = 'width: 100%';
        $row = $tableDeckCards->addRow();
        $row->addCell($this->quantities);
        $tableDeckCards->addRow()->addCell($this->deckCards);

        $hbox = new THBox;
        $hbox->add($tableCardsList)->style .= 'vertical-align:top; width: 60%';
        $hbox->add($tableDeckCards)->style .= 'vertical-align:top; width: 30%';

        //Wrap the page content using vertical box
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', 'DeckList'));
        $vbox->add($hbox);
        parent::add($vbox);
    }

    public function onEdit($param)
    {
        TTransaction::open('mtg_tracker');
        $deck  = new Deck($param['id']);
        $cards = $deck->getAllCards();
        TTransaction::close();
        TSession::setValue('deck_cards', $cards);
        TSession::setValue('offset', 0);
        TSession::setValue('page', 1);
        $this->onSearch();
    }

    public function onSelect($card)
    {
        //Get the card in the deck
        $cards = TSession::getValue('deck_cards');

        if (array_key_exists($card['uuid'],$cards))
        {
            //The normal limit of which card is 4
            if (!$cards[$card['uuid']]->unlimit AND !$cards[$card['uuid']]->land)
            {
                $originalName = $cards[$card['uuid']]->originalname;
                $quantity     = 0;
                foreach ($cards as $c)
                {
                    if ($originalName == $c->originalname)
                    {
                        $quantity += $c->quantity;
                    }
                }

                if ($quantity >= 4)
                {
                    new TMessage('error',"Não é possível ter mais que 4 cópias dessa carta {$quantity}");
                }
                else
                {
                    $cards[$card['uuid']]->quantity++;
                }
            }
            else
            {
                $cards[$card['uuid']]->quantity++;
            }
        }
        else
        {
            $cards[$card['uuid']] = new stdClass;
            $cards[$card['uuid']]->id           = $card['id'];
            $cards[$card['uuid']]->uuid         = $card['uuid'];
            $cards[$card['uuid']]->name         = $card['name'];
            $cards[$card['uuid']]->setcode      = $card['setcode'];
            $cards[$card['uuid']]->number       = $card['number'];
            $cards[$card['uuid']]->description  = $card['name']."(".$card['setcode'].") (#".$card['number'].")";
            $cards[$card['uuid']]->unlimit      = $card['unlimit'];
            $cards[$card['uuid']]->originalname = $card['originalname'];
            $cards[$card['uuid']]->land         = $card['land'];
            $cards[$card['uuid']]->quantity     = 1;
        }
        TSession::setValue('deck_cards',$cards);

        // reload datagrids
        $param = array();
        $param['offset'] = TSession::getValue('offset');
        $param['page']   = TSession::getValue('page');
        $this->onReload($param);
    }

    public function decreaseCard($param)
    {
        //Get the card in the deck
        $cards = TSession::getValue('deck_cards');
        $uuid  = $param['key'];

        //If the current quantity is 1, when decrease is gonna be 0, so just unset
        if ($cards[$uuid]->quantity == 1)
        {
            unset($cards[$uuid]);
        }
        else
        {
            $cards[$uuid]->quantity--;
        }

        TSession::setValue('deck_cards',$cards);

        // reload datagrids
        $param = array();
        $param['offset'] = TSession::getValue('offset');
        $param['page']   = TSession::getValue('page');
        $this->onReload($param);
    }

    /**
     * method onSearch()
     * Register the filter in the session when the user performs a search
     */
    public function onSearch()
    {
        $data = $this->form->getData();
        TSession::setValue('card_name', $data->card_name);
        TSession::setValue('card_lands', $data->lands);
        TSession::setValue('card_colors', $data->colors);
        $this->form->setData($data);
        $param = array();
        $param['offset']     = 0;
        $param['first_page'] = 1;
        $this->onReload($param);
    }

    /**
     * method onSave()
     * Saves the cards on the deck
     */
    public function onSave()
    {
        $cards   = TSession::getValue('deck_cards');
        $deck_id = TSession::getValue('deck_id');
        TTransaction::open('mtg_tracker');
        foreach ($cards as $card)
        {
            $deckCard = new DeckCard;
            $deckCard->id        = $card->id;
            $deckCard->deck_id   = $deck_id;
            $deckCard->card_uuid = $card->uuid;
            $deckCard->quantity  = $card->quantity;
            $deckCard->store();
        }
        TTransaction::close();
        Deck::checkValid($deck_id);
        $param = array();
        $param['offset']     = 0;
        $param['first_page'] = 1;
        $this->onReload($param);
    }

    /**
     * method onReload()
     * Load the datagrid with the database objects
     */
    public function onReload($param = NULL)
    {
        try
        {
            // var_dump($param);
            $limit     = 24;
            $format    = TSession::getValue('format');
            $card_name = TSession::getValue('card_name');
            $lands     = TSession::getValue('card_lands');
            $colors    = TSession::getValue('card_colors');

            // var_dump($data);

            $land = NULL;
            if ($lands == 't')
            {
                $land = TRUE;
            }
            elseif ($lands == 'f')
            {
                $land = FALSE;
            }

            //Get the cards from the format
            $cards = Card::getCardsByFormat($format, $card_name, $limit, $param['offset'], $land, $colors);
            $this->cardsList->clear();
            if ($cards)
            {
                foreach ($cards as $card)
                {
                    $this->cardsList->addItem($card);
                }
            }

            $this->onReloadDeckCards();

            $count = Card::countCardsByFormat($format,$card_name,$land,$colors);

            $this->pageNavigation->setCount($count); // count of records
            $this->pageNavigation->setProperties($param); // order, page
            $this->pageNavigation->setLimit($limit); // limit

            TSession::setValue('offset', ($param['offset'] ?? 0));
            TSession::setValue('page',   ($param['page'] ?? 1));
        }
        catch(Exception $e)
        {
            // shows the exception error message
            new TMessage('error', '<b>Error</b> ' . $e->getMessage());
            // undo all pending operations
            TTransaction::rollback();
        }
    }

    public function onReloadDeckCards()
    {
        $this->deckCards->clear();
        $cards = TSession::getValue('deck_cards');
        $quantity = 0;

        foreach ($cards as $card)
        {
            $this->deckCards->addItem($card);
            $quantity += $card->quantity;
        }

        $format = Format::getFormatByKey(TSession::getValue('format'));

        $min = $format[0]->min_size."(Min)";
        $max = "";
        if ($format[0]->max_size)
        {
            $max = "| ".$format[0]->max_size."(Max)";
        }
        $label = _t('Quantity').": {$quantity} / {$min} {$max}";

        $this->quantities->setValue($label);
    }
}