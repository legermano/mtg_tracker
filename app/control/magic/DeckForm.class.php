<?php

use Adianti\Base\TStandardForm;
use Adianti\Control\TAction;
use Adianti\Core\AdiantiCoreTranslator;
use Adianti\Database\TTransaction;
use Adianti\Registry\TSession;
use Adianti\Validator\TRequiredValidator;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Form\TRadioGroup;
use Adianti\Widget\Form\TText;
use Adianti\Widget\Util\TXMLBreadCrumb;
use Adianti\Widget\Wrapper\TDBUniqueSearch;
use Adianti\Wrapper\BootstrapDatagridWrapper;
use Adianti\Wrapper\BootstrapFormBuilder;

class DeckForm extends TStandardForm
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
        $this->form = new BootstrapFormBuilder('form_Deck');
        $this->form->setFormTitle('Deck');

        //Defines the database
        parent::setDatabase('mtg_tracker');

        //Defines the active record
        parent::setActiveRecord('Deck');

        //Create the form fields
        $id            = new TEntry('id');
        $name          = new TEntry('name');
        $description   = new TText('description');
        $format        = new TDBUniqueSearch("format_id","mtg_tracker","Format","id","name","name asc");
        $creation_date = new TEntry('creation_date');
        $valid         = new TRadioGroup('is_valid');

        $id->setEditable(false);
        $creation_date->setEditable(false);
        $valid->setEditable(false);

        //Add the fields
        $this->form->addFields( [new TLabel('ID')],[$id]);
        $this->form->addFields( [new TLabel(_t('Name'))],[$name]);
        $this->form->addFields( [new TLabel(_t('Description'))],[$description]);
        $this->form->addFields( [new TLabel(_t('Format'))],[$format]);
        $this->form->addFields( [new TLabel(_t('Creation date'))],[$creation_date]);
        $this->form->addFields( [new TLabel(('Legal'))],[$valid]);

        $id->setSize('20%');
        $name->setSize('70%');
        $format->setSize('40%');
        $format->setMinLength(0);
        $creation_date->setSize('30%');

        $valid->addItems(['t' => _t('Yes'), 'f' => _t('No')]);
        $valid->setLayout('horizontal');

        //Validations
        $name->addValidation(_t('Name'), new TRequiredValidator);
        $description->addValidation(_t('Description'), new TRequiredValidator);
        $format->addValidation(_t('Format'), new TRequiredValidator);

        //Add the actions
        $btn = $this->form->addAction(_t('Save'), new TAction(array($this, 'onSave')), 'far:save');
        $btn->class = 'btn btn-sm btn-primary';

        $this->form->addActionLink(_t('Clear'), new TAction(array($this, 'onEdit')), 'fa:eraser red');
        $this->form->addActionLink(_t('Back'), new TAction(array('DeckList','onReload')),'far:arrow-alt-circle-left blue');

        //Creates the deck's card datagrid
        $this->deckCards = new BootstrapDatagridWrapper(new TDataGrid);
        $this->deckCards->style = 'width: 100%';
        $this->deckCards->addColumn( new TDataGridColumn('quantity',    _t('Quantity'), 'right','30'));
        $this->deckCards->addColumn( new TDataGridColumn('description', _t('Name'),     'left'));
        $this->deckCards->createModel();

        $this->form->addFields( [new TLabel(_t('Cards'))],[$this->deckCards]);

        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml','DeckList'));
        $container->add($this->form);

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
                $key = $param['key'];

                TTransaction::open($this->database);
                $class = $this->activeRecord;
                $object = new $class($key);

                //Change the date format when needed
                if (ApplicationTranslator::getLanguage() == 'pt') {
                    $date = new DateTime($object->creation_date);
                    $object->creation_date = $date->format('d/m/Y');
                }
                $object->is_valid = $object->is_valid ? 't' : 'y';
                $this->form->setData($object);

                $cards = $object->getAllCards();
                $this->deckCards->clear();
                foreach ($cards as $card)
                {
                    $this->deckCards->addItem($card);
                }

                TTransaction::close();

                return $object;
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

            $object = new Deck;
            $object->id             = $data->id;
            $object->system_user_id = TSession::getValue('userid');
            $object->format_id      = $data->format_id;
            $object->name           = $data->name;
            $object->description    = $data->description;
            //TODO: Fazer verificação das cores e se é válido
            // $object->colors         = $data->colors;
            // $object->is_valid       = FALSE;
            if (!$object->creation_date)
            {
                $date = new DateTime();
                $object->creation_date  = $date->format('Y-m-d');
            }


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
}