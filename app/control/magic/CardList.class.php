<?php
class CardList extends TStandardList
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

        parent::setDatabase('all_printings');    //Defines the detabase
        parent::setActiveRecord('Card');     //Defines the active record
        parent::setDefaultOrder('name','asc'); //Defines the default order

        parent::addFilterField('coalesce(faceName,name)','like','name');
        parent::addFilterField('setCode','=','setCode');

        //Create the form
        $this->form = new BootstrapFormBuilder('form_search_Card');
        $this->form->setFormTitle(_t('Cards'));

        //Create the form fields
        $name    = new TEntry("name");
        $criteriaSet = new TCriteria;
        $criteriaSet->add(new TFilter('isOnlineOnly', '=', '0'));
        $setCode = new TDBUniqueSearch("setCode","all_printings","Set","code","name","name asc",$criteriaSet);

        //Add the fields
        $this->form->addFields( [new TLabel(_t('Name'))], [$name]);
        $this->form->addFields( [new TLabel(_t('Set'))],  [$setCode]);
        $name->setSize('70%');
        $setCode->setSize('70%');
        $setCode->setMask('({code}) {name}');
        $setCode->setMinLength(1);

        //Keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('Card_filter_data'));

        //Add the search from actions
        $btn = $this->form->addAction(_t('Find'), new TAction(array($this,'onSearch')), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';

        //Creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid());
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(320);

        //Creates the datagrid columns
        $column_image        = new TDataGridColumn('image','','left');
        $column_description  = new TDataGridColumn('description','','left');

        //Add the columns to the DataGrid
        $column_img = $this->datagrid->addColumn($column_image);
        $column_dsc = $this->datagrid->addColumn($column_description);

        // Defines the trasformer method over image
        $column_img->setTransformer(function($image) {
            $image = new TImage($image);
            $image->style = 'max-width: 140px';
            return $image;
        });

        $column_dsc->setTransformer(function($value, $object, $row){
            $div = new TElement('a');
            $div->add($object->description);
            return $div;
        });

        //Create the EDIT actoin
        $action_edit = new TDataGridAction(array('CardForm', 'onEdit'));
        $action_edit->setButtonClass('btn btn-default');
        $action_edit->setLabel(_t('View'));
        $action_edit->setImage('fa:search blue ');
        $action_edit->setField('originalName');
        $action_edit->setField('id');
        $this->datagrid->addAction($action_edit);

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

            $criteria->add( new TFilter('isOnlineOnly', '=', '0')); //Only shows phisical cards
            $criteria->setProperties($param_criteria); // order, offset
            $criteria->setProperty('limit', $limit);
            $criteria->setProperty('group', 'name'); // group by the name of the card

            if ($this->formFilters)
            {
                foreach ($this->formFilters as $filterKey => $filterField)
                {
                    $logic_operator = isset($this->logic_operators[$filterKey]) ? $this->logic_operators[$filterKey] : TExpression::AND_OPERATOR;

                    if (TSession::getValue($this->activeRecord.'_filter_'.$filterField))
                    {
                        // add the filter stored in the session to the criteria
                        $criteria->add(TSession::getValue($this->activeRecord.'_filter_'.$filterField), $logic_operator);
                        $$filterField = TSession::getValue($this->activeRecord.'_filter_'.$filterField);
                    }
                }
            }

            // load the objects according to criteria
            // $objects = $repository->load($criteria, FALSE);
            $fields  = $this->form->getFields();
            $name    = $fields['name']->getValue();
            $setCode = $fields['setCode']->getValue();
            $offset  = $param_criteria["offset"] ?? 0;
            $objects = Card::getCards($name,$setCode,$limit,$offset,true);

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
                    $object->image = Card::getImageByName(($object->originalName ?? $object->name),$object->multiverseId_t);
                    $object->getDescription();
                    // add the object inside the datagrid
                    $this->datagrid->addItem($object);
                }
            }

            //Custom count of total results
            $conn = TTransaction::get();
            $sql = "SELECT count(*) AS \"count\" FROM cards WHERE id in (
                        SELECT a.id
                        FROM cards a
                        LEFT JOIN foreign_data b ON (a.uuid = b.uuid)
                        WHERE a.isOnlineOnly = 0
                          AND ( COALESCE(a.faceName,a.name) like \"%{$name}%\" OR b.name like \"%{$name}%\")
                          AND a.setCode like \"%{$setCode}%\"
                        GROUP BY a.name
                    )";
            $sth = $conn->prepare($sql);
            $sth->execute();
            $result = $sth->fetchAll();
            $count  = $result[0]["count"];

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
}