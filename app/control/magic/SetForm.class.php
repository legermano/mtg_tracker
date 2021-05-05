<?php

use Adianti\Base\TStandardForm;
use Adianti\Control\TAction;
use Adianti\Database\TTransaction;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Form\TRadioGroup;
use Adianti\Widget\Util\TXMLBreadCrumb;
use Adianti\Wrapper\BootstrapFormBuilder;

class SetForm extends TStandardForm
{
    function __construct()
    {
        parent::__construct();

        //Crates the form
        $this->form = new BootstrapFormBuilder('form_Set');
        $this->form->setFormTitle(_t('Set'));

        //Defines the database
        parent::setDatabase('mtg_tracker');

        //Defines the active record
        parent::setActiveRecord('Set');

        //Creates the form fields
        $code   = new TEntry('code');
        $code->setEditable(false);
        $name   = new TEntry('name');
        $name->setEditable(false);
        $block  = new TEntry('block');
        $block->setEditable(false);
        $size   = new TEntry('basesetsize');
        $size->setEditable(false);
        $date   = new TEntry('releasedate');
        $date->setEditable(false);
        $online = new TRadioGroup('isonlineonly');
        $online->setEditable(false);
        $online->addItems(['t' => 'Sim', 'f' => 'Não']);
        $online->setLayout('horizontal');

        //Add the fields
        $this->form->addFields( [new TLabel(_t('Code'))],[$code]);
        $this->form->addFields( [new TLabel(_t('Name'))],[$name]);
        $this->form->addFields( [new TLabel(_t('Block'))],[$block]);
        $this->form->addFields( [new TLabel(_t('Size'))],[$size]);
        $this->form->addFields( [new TLabel(_t('Release date'))],[$date]);
        $this->form->addFields( [new TLabel(_t('Online only'))],[$online]);

        $code->setSize('20%');
        $name->setSize('40%');
        $block->setSize('40%');
        $size->setSize('20%');
        $date->setSize('20%');
        $online->setSize('100%');

        $this->form->addActionLink(_t('Back'), new TAction(array('SetList','onReload')),'far:arrow-alt-circle-left blue');

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

                $object->isonlineonly = $object->isonlineonly ? 't' : 'f';
                $date = new DateTime($object->releasedate);
                if (ApplicationTranslator::getLanguage() == 'pt')
                {
                    $object->releasedate = $date->format('d/m/Y');
                }
                else
                {
                    $object->releasedate = $date->format('Y-m-d');
                }

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
}