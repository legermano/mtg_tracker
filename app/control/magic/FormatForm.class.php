<?php
class FormatForm extends TStandardForm
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
        $this->form = new BootstrapFormBuilder('form_Format');
        $this->form->setFormTitle(_t('Format'));

        //Defines the database
        parent::setDatabase('mtg_tracker');

        //Defines the active record
        parent::setActiveRecord('Format');

        //Create the form fields
        $id   = new TEntry('id');
        $name = new TEntry('name');
        $key  = new TEntry('format_key');

        $id->setEditable(false);

        //Add the fields
        $this->form->addFields( [new TLabel('ID')],[$id]);
        $this->form->addFields( [new TLabel(_t('Name'))],[$name]);
        $this->form->addFields( [new TLabel(_t('Key'))],[$key]);

        $id->setSize('30%');
        $name->setSize('70%');

        //Validations
        $name->addValidation(_t('Name'), new TRequiredValidator);
        $key->addValidation(_t('Key'), new TRequiredValidator);

        //Add the actions
        $btn = $this->form->addAction(_t('Save'), new TAction(array($this, 'onSave')), 'far:save');
        $btn->class = 'btn btn-sm btn-primary';

        $this->form->addActionLink(_t('Clear'), new TAction(array($this, 'onEdit')), 'fa:eraser red');
        $this->form->addActionLink(_t('Back'), new TAction(array('FormatList','onReload')),'far:arrow-alt-circle-left blue');

        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml','FormatList'));
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

                $this->form->setData($object);

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

            $object = new Format;
            $object->id         = $data->id;
            $object->name       = $data->name;
            $object->format_key = $data->format_key;

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