<?php
class FormatList extends TStandardList
{
    protected $form;
    protected $datagrid;
    protected $pageNavigation;
    protected $formgrid;
    protected $deleteButton;
    protected $transformCallback;

    /**
     * Page constructor
     */
    public function __construct()
    {
        parent::__construct();

        parent::setDatabase('mtg_tracker');    //Defines the detabase
        parent::setActiveRecord('Format');     //Defines the active record
        parent::setDefaultOrder('name','asc'); //Defines the default order

        parent::addFilterField('id','=','id');
        parent::addFilterField('name','like','name');
        parent::addFilterField('format_key','like','format_key');

        //Create the form
        $this->form = new BootstrapFormBuilder('form_search_Format');
        $this->form->setFormTitle(_t('Formats'));

        //Create the form fields
        $name = new TEntry("name");
        $format_key  = new TEntry("format_key");

        //Add the fields
        $this->form->addFields( [new TLabel(_t('Name'))], [$name]);
        $this->form->addFields( [new TLabel(_t('Key'))], [$format_key]);
        $name->setSize('70%');
        $format_key->setSize('70%');

        //Keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('Format_filter_data'));

        //Add the search from actions
        $btn = $this->form->addAction(_t('Find'), new TAction(array($this,'onSearch')), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addAction(_t('New'), new TAction(array('FormatForm', 'onEdit')), 'fa:plus green');

        //Creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid());
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(320);

        //Creates the datagrid columns
        $column_id = new TDataGridColumn('id','Id','center', 50);
        $column_name = new TDataGridColumn('name',_t('Name'),'left');
        $column_key = new TDataGridColumn('format_key',_t('Key'),'left');

        $column_key->enableAutoHide(500);

        //Add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_name);
        $this->datagrid->addColumn($column_key);

        //Creates the datagrid column actions
        $order_id = new TAction(array($this,'onReload'));
        $order_id->setParameter('order','id');
        $column_id->setAction($order_id);

        $order_name = new TAction(array($this,'onReload'));
        $order_name->setParameter('order','name');
        $column_name->setAction($order_name);

        $order_key = new TAction(array($this,'onReload'));
        $order_key->setParameter('order','format_key');
        $column_key->setAction($order_key);

        //Create the EDIT actoin
        $action_edit = new TDataGridAction(array('FormatForm', 'onEdit'));
        $action_edit->setButtonClass('btn btn-default');
        $action_edit->setLabel(_t('Edit'));
        $action_edit->setImage('far:edit blue ');
        $action_edit->setField('id');
        $this->datagrid->addAction($action_edit);

        //Create the DELETE action
        $action_del = new TDataGridAction(array($this, 'onDelete'));
        $action_del->setButtonClass('btn btn-default');
        $action_del->setLabel(_t('Delete'));
        $action_del->setImage('far:trash-alt red ');
        $action_del->setField('id');
        $this->datagrid->addAction($action_del);

        //Create the datagrid model
        $this->datagrid->createModel();

        //Create the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->enableCounters();
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());

        $panel = new TPanelGroup;
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