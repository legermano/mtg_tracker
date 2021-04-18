<?php
class CardForm extends TStandardForm
{
    protected $form;

    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct($param)
    {
        parent::__construct();

        //Create the form
        $this->form = new BootstrapFormBuilder('form_Card');
        $this->form->setFormTitle(_t('Card'));

        //Defines the database
        parent::setDatabase('all_printings');

        //Defines the active record
        parent::setActiveRecord('Card');

        // echo "<pre>";
        // print_r($param);
        // echo "</pre>";

        //Create the form fields
        $id      = new TEntry('id');
        $name    = new TEntry('name');
        $setCode = new TEntry('setCode');

        $id->setEditable(false);
        $name->setEditable(false);
        $setCode->setEditable(false);

        //Create the notebook
        $notebook = new BootstrapNotebookWrapper( new TNotebook );
        $notebook->setTabsDirection('left');

        $name = $param['originalName'];

        TTransaction::open($this->database);
        $criteria = new TCriteria;
        $criteria->add( new TFilter('isOnlineOnly', '=', '0')); //Only shows phisical cards
        $criteria->add( new TFilter('coalesce(faceName,name)', '=', $name));
        $repository = new TRepository($this->activeRecord);
        $objects    = $repository->load($criteria, FALSE);

        $setRepository = new TRepository('Set');
        $buttonsArray = array();

        foreach ($objects as $key => $object) {
            $set = $object->getSet();
            $object->translate();
            $object->owned_quantity = $object->getQuantityOwned();

            $num         = $key+1;
            $pageMaster  = 'pageM'.$num;
            $$pageMaster = new TTable;
            $pageImage   = 'pageI'.$num;
            $$pageImage  = new TTable;
            $pageDescr   = 'pageD'.$num;
            $$pageDescr = new TTable;

            $notebook->appendPage("<i class='ss ss-fw ss-{$set->keyruneCode} ss-3x ss-{$object->rarity} id='{$num}'></i><b>{$set->name}</b>",$$pageMaster);

            $url = Card::getImage(($object->multiverseId_t), $object->scryfallId, $object->side);
            $image = new TImage($url);
            $image->style = 'max-width: 340px;';

            $row = $$pageImage->addRow();
            $row->addCell($image);

            $row = $$pageDescr->addRowSet();
            $row->addCell(new TLabel(_t('Card Name').':'));
            $row->addCell($object->name);

            $row = $$pageDescr->addRow();
            $row->addCell(new TLabel(_t('Mana Cost').':'));
            $row->addCell(Card::putSymbols($object->manaCost));

            $row = $$pageDescr->addRow();
            $row->addCell(new TLabel(_t('Converted Mana Cost').':'));
            $row->addCell(str_replace('.0','',$object->convertedManaCost));

            $row = $$pageDescr->addRow();
            $row->addCell(new TLabel(_t('Type').':'));
            $row->addCell($object->type);

            $row = $$pageDescr->addRow();
            $row->addCell(new TLabel(_t('Card Text').':'));
            $row->addCell(Card::putSymbols(str_replace("\n","</br>",$object->text)));

            if (!empty($object->loyalty)) {
                $row = $$pageDescr->addRow();
                $row->addCell(new TLabel(_t('Loyalty').':'));
                $row->addCell($object->loyalty);
            }

            $row = $$pageDescr->addRow();
            $row->addCell(new TLabel(_t('Rarity').':'));
            $row->addCell($object->rarity_t);

            if (!empty($object->flavorText)) {
                $row = $$pageDescr->addRow();
            $row->addCell(new TLabel(_t('Flavor Text').':'));
            $row->addCell("<i>{$object->flavorText}</i>");
            }

            $row = $$pageDescr->addRow();
            $row->addCell(new TLabel(_t('Artist').':'));
            $row->addCell($object->artist);

            //Incremental button
            $btnInc  = 'btnInc'.$num;
            $btnDec  = 'btnDec'.$num;
            $$btnInc = new TButton('increment'.$num);
            $$btnInc->setImage('fas:plus');
            $$btnInc->setAction(new TAction(array($this,'incrementQuantity'),['id'=>$object->uuid]));
            $buttonsArray[] = $$btnInc;
            $$btnDec = new TButton('decrement'.$num);
            $$btnDec->setImage('fas:minus');
            $$btnDec->setAction(new TAction(array($this,'decrementQuantity'),['id'=>$object->uuid]));
            $buttonsArray[] = $$btnDec;
            $quantity_owned  = 'qnt_owned'.$num;
            $$quantity_owned = new TEntry('qnt_owned');
            $$quantity_owned->setId($object->uuid);
            $$quantity_owned->setEditable(false);
            $$quantity_owned->setValue($object->owned_quantity);

            $buttons = new TElement('a');
            $buttons->add($$btnDec);
            $buttons->add($$quantity_owned);
            $buttons->add($$btnInc);

            $row = $$pageDescr->addRow();
            $row->addCell(new TLabel('Quantidade:'));
            $row->addCell($buttons);

            $row = $$pageMaster->addRow();
            $row->addCell($$pageImage);
            $row->addCell($$pageDescr,"vertical-align:top");

        }
        TTransaction::close();

        $btnBack = new TButton('back');
        $btnBack->setAction(new TAction(array('CardList','onReload')),_t('Back'));
        $btnBack->setImage('far:arrow-alt-circle-left blue');
        $buttonsArray[] = $btnBack;

        $this->form->setFields($buttonsArray);

        $panel = new TPanelGroup();
        $panel->add($notebook);
        $panel->addFooter($btnBack);

        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml','CardList'));
        $container->add($panel);

        //Add the container to the page
        parent::add($container);
    }

    /**
     * method onEdit()
     * Executed whenever the user clicks at the edit button in the datagrid
     * @param $param An array containing the GET ($_GET) parameters
     */
    public function onEdit($param)
    {
        try
        {
            if(isset($param['key']))
            {
                // $name = $param['originalName'];
                $key = $param['key'];

                // TTransaction::open($this->database);
                // $criteria = new TCriteria;
                // $criteria->add( new TFilter('isOnlineOnly', '=', '0')); //Only shows phisical cards
                // $criteria->add( new TFilter('name', '=', $name));
                // $class = $this->activeRecord;
                // $object = new $class($key);
                // $repository = new TRepository($this->activeRecord);
                // $objects    = $repository->load($criteria, FALSE);

                // $cards = new stdClass;
                // foreach ($objects as $key => $object) {
                //     $idNum      = 'id'.($key+1);
                //     $nameNum    = 'name'.($key+1);
                //     $setCodeNum = 'setCode'.($key+1);

                //     $cards->$idNum      = $object->id;
                //     $cards->$nameNum    = $object->name;
                //     $cards->$setCodeNum = $object->setCode;
                // }

                // $this->form->setData($object);

                // TTransaction::close();

                // echo "<pre>";
                // var_dump($cards);
                // echo "</pre>";
                // return $object;
            }
            else
            {
                $this->form->clear();
            }
        } catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    /**
     * method onSave()
     * Executed whenever the user clicks at the save button
     */
    public function onSave()
    {
        try
        {
            TTransaction::open($this->database);

            $data = $this->form->getData();

            $object = new Format;
            $object->id         = $data->id;
            $object->name       = $data->name;

            $this->form->validate();
            $object->store();
            $data->id = $object->id;
            $this->form->setData($data);

            TTransaction::close();

            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'));

            return $object;

        } catch (Exception $e)  //in case of exception
        {
            //Get the form data
            $object = $this->form->getData($this->activeRecord);
            $this->form->setData($object);
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    public static function incrementQuantity($param)
    {
        TScript::create("document.getElementById('{$param['id']}').value = parseInt(document.getElementById('{$param['id']}').value) + 1");
        OwnedCard::incrementQuantity($param['id']);
    }

    public static function decrementQuantity($param)
    {
        TScript::create(
            "val = parseInt(document.getElementById('{$param['id']}').value);
             if (val > 0)
             {
                document.getElementById('{$param['id']}').value = val - 1;
             }"
        );
        OwnedCard::decrementQuantity($param['id']);
    }
}