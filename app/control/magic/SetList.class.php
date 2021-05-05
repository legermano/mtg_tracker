<?php

use Adianti\Base\TStandardList;
use Adianti\Control\TAction;
use Adianti\Registry\TSession;
use Adianti\Widget\Container\TPanelGroup;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Datagrid\TDataGridAction;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Datagrid\TPageNavigation;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Form\TRadioGroup;
use Adianti\Widget\Util\TXMLBreadCrumb;
use Adianti\Wrapper\BootstrapDatagridWrapper;
use Adianti\Wrapper\BootstrapFormBuilder;

class SetList extends TStandardList
{
    public function __construct()
    {
        parent::__construct();

        parent::setDatabase('mtg_tracker');
        parent::setActiveRecord('Set');
        parent::setDefaultOrder('name','asc');

        parent::addFilterField('code','ilike','code');
        parent::addFilterField('name','ilike','name');
        parent::addFilterField('block','ilike','block');
        parent::addFilterField('isonlineonly','=','online');

        //Create the form
        $this->form = new BootstrapFormBuilder('form_search_Set');
        $this->form->setFormTitle(_t('Sets'));

        //Create the form fields
        $code   = new TEntry('code');
        $name   = new TEntry('name');
        $block  = new TEntry('block');
        $online = new TRadioGroup('online');
        $online->addItems(['t' => 'Sim', 'f' => 'Não', '' => 'Ambos']);
        $online->setLayout('horizontal');

        //Add the fields
        $this->form->addFields( [new TLabel(_t('Code'))], [$code]);
        $this->form->addFields( [new TLabel(_t('Name'))], [$name]);
        $this->form->addFields( [new TLabel(_t('Block'))], [$block]);
        $this->form->addFields( [new TLabel(_t('Online only'))], [$online]);

        $code->setSize('70%');
        $name->setSize('70%');
        $block->setSize('70%');
        $online->setSize('100%');

        //Keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('Set_filter_data'));

        //Add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction(array($this,'onSearch')), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';

        //Create the datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid());
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(320);

        //Create the datagrid columns
        $column_code  = new TDataGridColumn('code', _t('Code'), 'center', 50);
        $column_name  = new TDataGridColumn('name', _t('Name'), 'left');
        $column_block = new TDataGridColumn('block',_t('Block'),'left');

        //Add the columns to the Datagrid
        $this->datagrid->addColumn($column_code);
        $this->datagrid->addColumn($column_name);
        $this->datagrid->addColumn($column_block);

        //Create the datagrid actions
        $order_code = new TAction(array($this,'onReload'));
        $order_code->setParameter('order','code');
        $column_code->setAction($order_code);

        $order_name = new TAction(array($this,'onReload'));
        $order_name->setParameter('order','name');
        $column_name->setAction($order_name);

        //Creates the view action
        $action_view = new TDataGridAction(Array('SetForm','onEdit'));
        $action_view->setButtonClass('btn btn-default');
        $action_view->setLabel(_t('View'));
        $action_view->setImage('fa:search blue ');
        $action_view->setField('code');
        $this->datagrid->addAction($action_view);

        //Creates the datagrid model
        $this->datagrid->createModel();

        //Create the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->enableCounters();
        $this->pageNavigation->setAction(new TAction(array($this,'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());

        $panel = new TPanelGroup();
        $panel->add($this->datagrid)->style = 'overflow-x:auto';
        $panel->addFooter($this->pageNavigation);

        //Vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($panel);

        parent::add($container);
    }
}