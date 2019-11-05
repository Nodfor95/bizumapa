<?php

class CustomPublicMindMapList extends TStandardList
{
    protected $form;     // registration form
    protected $datagrid; // listing
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
        
        parent::setDatabase('permission');            // defines the database
        parent::setActiveRecord('CustomPublicMindMap');   // defines the active record
        parent::setDefaultOrder('id', 'asc');         // defines the default order
        parent::addFilterField('id', '=', 'id'); // filterField, operator, formField
        parent::addFilterField('name', 'like', 'name'); // filterField, operator, formField
        parent::addFilterField('theme_id', '=', 'theme_id'); // filterField, operator, formField
        parent::addFilterField('subject_matter_id', '=', 'subject_matter_id'); // filterField, operator, formField
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_CustomPublicMindMap');
        $this->form->setFormTitle('Buscar Mapa Mental');
        
        // create the form fields
        $id = new TEntry('id');
        $name = new TEntry('name');
        $theme_id = new TDBCombo('theme_id', 'permission', 'CustomTheme', 'id', 'name', 'name');
        $subject_matter_id = new TDBCombo('subject_matter_id', 'permission', 'CustomSubjectMatter', 'id', 'name', 'name');
        
        // add the fields
        $this->form->addFields( [new TLabel('ID')], [$id] );
        $this->form->addFields( [new TLabel('Nome')], [$name] );
        $this->form->addFields( [new TLabel('Matéria')], [$theme_id] );
        $this->form->addFields( [new TLabel('Assunto')], [$subject_matter_id] );

        $id->setSize('30%');
        $name->setSize('70%');
        $theme_id->setSize('70%');
        $subject_matter_id->setSize('70%');

        $theme_id->enableSearch();
        $theme_id->setChangeAction( new TAction( array($this, 'onThemeChange' )) );
        $subject_matter_id->enableSearch();
        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('CustomPublicMindMap_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction(array($this, 'onSearch')), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addAction('Novo Mapa Público',  new TAction(array('CustomPublicMindMapForm', 'onEdit')), 'bs:plus-sign green');
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->datatable = 'true';
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(320);
        
        // creates the datagrid columns
        $column_id                  = new TDataGridColumn('id', 'ID', 'center', 50);
        $column_name                = new TDataGridColumn('name', 'Mapa', 'left');
        $column_theme_name          = new TDataGridColumn('theme->name', 'Matéria', 'left');
        $column_subject_matter_name = new TDataGridColumn('subject_matter->name', 'Assunto', 'left');

        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_name);
        $this->datagrid->addColumn($column_theme_name);
        $this->datagrid->addColumn($column_subject_matter_name);


        // creates the datagrid column actions
        $order_id = new TAction(array($this, 'onReload'));
        $order_id->setParameter('order', 'id');
        $column_id->setAction($order_id);
        
        $order_name = new TAction(array($this, 'onReload'));
        $order_name->setParameter('order', 'name');
        $column_name->setAction($order_name);

        // create EDIT action
        $action_edit = new TDataGridAction(array('CustomPublicMindMapForm', 'onEdit'));
        $action_edit->setButtonClass('btn btn-default');
        $action_edit->setLabel(_t('Edit'));
        $action_edit->setImage('fa:pencil-square-o blue fa-lg');
        $action_edit->setField('id');
        $this->datagrid->addAction($action_edit);
        
        // create DELETE action
        $action_del = new TDataGridAction(array($this, 'onDelete'));
        $action_del->setButtonClass('btn btn-default');
        $action_del->setLabel(_t('Delete'));
        $action_del->setImage('fa:trash-o red fa-lg');
        $action_del->setField('id');
        $this->datagrid->addAction($action_del);
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // create the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->enableCounters();
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        $panel = new TPanelGroup;
        $panel->add($this->datagrid);
        $panel->addFooter($this->pageNavigation);
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($panel);
        
        parent::add($container);
    }

    public static function onThemeChange($param)
    {
        try
        {
            TTransaction::open('permission');
            if (!empty($param['theme_id']))
            {
                $criteria = TCriteria::create( ['theme_id' => $param['theme_id'] ] );
                
                // formname, field, database, model, key, value, ordercolumn = NULL, criteria = NULL, startEmpty = FALSE
                TDBCombo::reloadFromModel('form_search_CustomPublicMindMap', 'subject_matter_id', 'permission', 'CustomSubjectMatter', 'id', '{name}', 'name', $criteria, TRUE);
            }
            else
            {
                TCombo::clearField('form__search_CustomPublicMindMap', 'subject_matter_id');
            }
            
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }

}
