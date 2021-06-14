<?php

use Adianti\Base\TStandardList;
use Adianti\Control\TAction;
use Adianti\Core\AdiantiCoreTranslator;
use Adianti\Database\TCriteria;
use Adianti\Database\TExpression;
use Adianti\Database\TFilter;
use Adianti\Database\TRepository;
use Adianti\Database\TTransaction;
use Adianti\Registry\TSession;
use Adianti\Widget\Container\TPanelGroup;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Datagrid\TDataGridAction;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Datagrid\TPageNavigation;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Util\TXMLBreadCrumb;
use Adianti\Widget\Wrapper\TDBMultiSearch;
use Adianti\Wrapper\BootstrapDatagridWrapper;
use Adianti\Wrapper\BootstrapFormBuilder;

class DeckList extends TStandardList
{
    public function __construct()
    {
        parent::__construct();

        parent::setDatabase('mtg_tracker');
        parent::setActiveRecord('Deck');
        parent::setDefaultOrder('name','asc');

        parent::addFilterField('name','ilike','name');
        parent::addFilterField('format_id','=','format');
        parent::addFilterField('system_user_id','=','user');

        //Create the form
        $this->form = new BootstrapFormBuilder('form_search_deck');
        $this->form->setFormTitle('Decks');

        //Create the form fields
        $name   = new TEntry('name');
        $format = new TDBMultiSearch("format","mtg_tracker","Format","format_key","name","name asc");

        //Add the fields
        $this->form->addFields( [new TLabel(_t('Name'))],   [$name]);
        $this->form->addFields( [new TLabel(_t('Format'))], [$format]);

        $name->setSize('70%');
        $format->setSize('70%');
        $format->setMinLength(0);

        //Keep the form filled during navigation
        $this->form->setData( TSession::getValue('Deck_filter_data'));

        //Add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction(array($this,'onSearch')), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addAction(_t('New'), new TAction(array('DeckForm', 'onEdit')), 'fa:plus green');

        //Create the datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid());
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(320);

        //Create the datagrid columns
        $column_name        = new TDataGridColumn('name',        _t('Name'),        'center', 200);
        $column_description = new TDataGridColumn('description', _t('Description'), 'center');
        $column_format      = new TDataGridColumn('format_id'  , _t('Format'),      'center');
        $column_valid       = new TDataGridColumn('is_valid'   , 'Legal',           'center');

        //Add the columns to the datagrid
        $this->datagrid->addColumn($column_name);
        $this->datagrid->addColumn($column_description);
        $this->datagrid->addColumn($column_format);
        $this->datagrid->addColumn($column_valid);

        //Create the datagrid actions
        $order_name = new TAction(array($this,'onReload'));
        $order_name->setParameter('order','name');
        $column_name->setAction($order_name);

        $order_format = new TAction(array($this,'onReload'));
        $order_format->setParameter('order','format');
        $column_format->setAction($order_format);


        //Creates the view action
        $action_view = new TDataGridAction(Array('DeckForm','onEdit'));
        $action_view->setButtonClass('btn btn-default');
        $action_view->setLabel(_t('Edit'));
        $action_view->setImage('fa:edit blue ');
        $action_view->setField('id');
        $this->datagrid->addAction($action_view);

        //Card list edit
        $card_list_action = new TDataGridAction(Array('DeckCardForm','onEdit'));
        $card_list_action->setButtonClass('btn btn-default');
        $card_list_action->setLabel(_t('Cards'));
        $card_list_action->setImage('fa:plus blue ');
        $card_list_action->setField('format_id');
        $card_list_action->setField('name');
        $card_list_action->setField('id');
        $this->datagrid->addAction($card_list_action);

        //Create the DELETE action
        $action_del = new TDataGridAction(array($this, 'onDelete'));
        $action_del->setButtonClass('btn btn-default');
        $action_del->setLabel(_t('Delete'));
        $action_del->setImage('far:trash-alt red ');
        $action_del->setField('id');
        $this->datagrid->addAction($action_del);

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

    /**
     * Load the datagrid with the database objects
     */
    public function onReload($param = NULL)
    {
        if (!isset($this->datagrid))
        {
            return;
        }

        try
        {
            if (empty($this->database))
            {
                throw new Exception(AdiantiCoreTranslator::translate('^1 was not defined. You must call ^2 in ^3', AdiantiCoreTranslator::translate('Database'), 'setDatabase()', AdiantiCoreTranslator::translate('Constructor')));
            }

            if (empty($this->activeRecord))
            {
                throw new Exception(AdiantiCoreTranslator::translate('^1 was not defined. You must call ^2 in ^3', 'Active Record', 'setActiveRecord()', AdiantiCoreTranslator::translate('Constructor')));
            }

            $param_criteria = $param;

            // open a transaction with database
            TTransaction::open($this->database);

            // instancia um repositório
            $repository = new TRepository($this->activeRecord);
            $limit = isset($this->limit) ? ( $this->limit > 0 ? $this->limit : NULL) : 10;

            // creates a criteria
            $criteria = isset($this->criteria) ? clone $this->criteria : new TCriteria;
            if ($this->order)
            {
                $criteria->setProperty('order',     $this->order);
                $criteria->setProperty('direction', $this->direction);
            }


            if (is_array($this->orderCommands) && !empty($param['order']) && !empty($this->orderCommands[$param['order']]))
            {
                $param_criteria['order'] = $this->orderCommands[$param['order']];
            }

            $criteria->setProperties($param_criteria); // order, offset
            $criteria->setProperty('limit', $limit);
            $criteria->add(new TFilter('system_user_id', '=', TSession::getValue('userid')));

            if ($this->formFilters)
            {
                foreach ($this->formFilters as $filterKey => $filterField)
                {
                    $logic_operator = isset($this->logic_operators[$filterKey]) ? $this->logic_operators[$filterKey] : TExpression::AND_OPERATOR;

                    if (TSession::getValue($this->activeRecord.'_filter_'.$filterField))
                    {
                        // add the filter stored in the session to the criteria
                        $criteria->add(TSession::getValue($this->activeRecord.'_filter_'.$filterField), $logic_operator);
                    }
                }
            }

            // load the objects according to criteria
            $objects = $repository->load($criteria, FALSE);

            if (is_callable($this->transformCallback))
            {
                call_user_func($this->transformCallback, $objects, $param);
            }

            $this->datagrid->clear();
            if ($objects)
            {
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    $format = new Format($object->format_id);
                    $object->format_id = $format->name;
                    $object->is_valid  = $object->is_valid ? _t('Yes') : _t('No');
                    // add the object inside the datagrid
                    $this->datagrid->addItem($object);
                }
            }

            // reset the criteria for record count
            $criteria->resetProperties();
            $count = $repository->count($criteria);

            if (isset($this->pageNavigation))
            {
                $this->pageNavigation->setCount($count); // count of records
                $this->pageNavigation->setProperties($param); // order, page
                $this->pageNavigation->setLimit($limit); // limit
            }

            if (is_callable($this->afterLoadCallback))
            {
                $information = ['count' => $count];
                call_user_func($this->afterLoadCallback, $this->datagrid, $information);
            }

            // close the transaction
            TTransaction::close();
            $this->loaded = true;

            return $objects;
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            // undo all pending operations
            TTransaction::rollback();
        }
    }

    /**
     * method Delete()
     * Delete a record
     */
    public function Delete($param)
    {
        try
        {
            // get the parameter $key
            $key=$param['key'];
            // open a transaction with database
            TTransaction::open($this->database);

            $class = $this->activeRecord;

            // instantiates object
            $object = new $class($key);

            // deletes the object from the database
            $object->delete();

            // close the transaction
            TTransaction::close();

            // reload the listing
            $this->onReload( $param );
            // shows the success message
            new TMessage('info', AdiantiCoreTranslator::translate('Record deleted'));
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            // undo all pending operations
            TTransaction::rollback();
        }
    }
}