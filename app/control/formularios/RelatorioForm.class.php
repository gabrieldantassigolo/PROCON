<?php
/**
 * RelacaoList Listing
 * @author  <your name here>
 */
class RelatorioForm extends TPage
{
    private $form; // form
    private $datagrid; // listing
    private $pageNavigation;
    private $formgrid;
    private $loaded;
    private $deleteButton;
    private $relatorioButton;
    
    
    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct()
    {
        parent::__construct();
        parent::include_css('app/resources/estiloformcampo.css');
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Relacao');
        $this->form->setFormTitle('Relatório');
        

        // create the form fields
        $id = new TDBCombo('id', 'procon_com', 'Relacao', 'id', 'nome');
        $pesquisa_id = new TDBCombo('pesquisa_id', 'procon_com', 'Pesquisa', 'id', 'nome', NULL , NULL , FALSE);
        $estabelecimento_id = new TDBCombo('estabelecimento_id', 'procon_com', 'Estabelecimento', 'id', 'nome');
        $data = new TDate('data_criacao');
        
        $pesquisa_id->setDefaultOption('Selecione uma pesquisa');    
    
        //Clear Section
        TSession::setValue('Relatorio_filter_data',   NULL);
        
        // add the fields
        $this->form->addFields( [ new TLabel('Pesquisa') ], [ $pesquisa_id ] );
        //$this->form->addFields( [ new TLabel('Estabelecimento') ], [ $estabelecimento_id ] );
        //$this->form->addFields( [ new TLabel('Data') ], [ $data ] );


        // set sizes
        $pesquisa_id->setSize('70%');
        //$estabelecimento_id->setSize('70%');
        //$data->setSize('70%');
        
        //$data->setMask('dd/mm/yyyy');
        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('Relatorio_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_check = new TDataGridColumn('check', '', 'center', '5%' );
        //$column_id = new TDataGridColumn('id', 'Id', 'right');
        $column_pesquisa = new TDataGridColumn('pesquisa->nome', 'Pesquisa', 'left', '40%');
        $column_estabelecimento = new TDataGridColumn('estabelecimento->nome', 'Estabelecimento', 'left', '40%');
        $column_data = new TDataGridColumn('data_criacao', 'Data', 'left', '15%');
        
        $column_data->setTransformer( function($value, $object, $row) {
            if ($value)
            {
                try
                {
                    $date = new DateTime($value);
                    return $date->format('d/m/Y');
                }
                catch (Exception $e)
                {
                    return $value;
                }
            }
            return $value;
        });
    
    
        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_check);
        //$this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_pesquisa);
        $this->datagrid->addColumn($column_estabelecimento);
        $this->datagrid->addColumn($column_data);


        // creates the datagrid column actions
        //$column_id->setAction(new TAction([$this, 'onReload']), ['order' => 'id']);
        $column_data->setAction(new TAction([$this, 'onReload']), ['order' => 'data_criacao']);
             
       
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        //$this->datagrid->disableDefaultClick();
        
        
        // put datagrid inside a form
        $this->formgrid = new TForm;
        $this->formgrid->add($this->datagrid);
        
        /*$btn2 = $this->formgrid->addAction(_t('Find'), new TAction([$this, 'onGerarRelatorio']), 'fa:search');
        $btn2->class = 'btn btn-sm btn-primary';*/
        
        $this->relatorioButton = new TButton('relatorio_button'); 
        $this->relatorioButton->setAction(new TAction([$this, 'onGerarRelatorio']), 'Gerar Relatorio');
        $this->relatorioButton->setImage('fa:clipboard blue');
       
        $this->formgrid->addField($this->relatorioButton);
        
        // creates the delete collection button
        $this->deleteButton = new TButton('delete_collection');
        $this->deleteButton->setAction(new TAction(array($this, 'onDeleteCollection')), AdiantiCoreTranslator::translate('Delete selected'));
        $this->deleteButton->setImage('fa:remove red');
        $this->formgrid->addField($this->deleteButton);
        
    
        $gridpack = new TVBox;
        $gridpack->style = 'width: 100%';
        $gridpack->add($this->formgrid);
        
        
        $gridpack->add($this->deleteButton)->style = 'background:whiteSmoke;border:1px solid #cccccc; padding: 3px;padding: 5px;';
        
        $gridpack->add($this->relatorioButton)->style = 'text-align: center; margin: 15px; font-size: 1em;';
        
        $this->transformCallback = array($this, 'onBeforeLoad');
        
        //$this->datagrid->clear();
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        if($this->loaded = TRUE)
        {
            $container->add(TPanelGroup::pack('Relações', $gridpack, $this->pageNavigation));
        }
        
        
        parent::add($container);
    }
    
    public function onGerarRelatorio()
    {
        AdiantiCoreApplication::loadPage('');
    }
    
    public function onUpdateItens($data)
    {     
        TTransaction::open('procon_com');       
        $obj = new StdClass;
        $obj->relacao_id = $data['id'];
        
        $pesquisa = new Pesquisa($data['pesquisa_id']);
        $obj->pesquisa_id = $pesquisa->nome;
       
        TTransaction::close();

        TSession::setValue('RelacaoItem_relacao_id', $obj);
        AdiantiCoreApplication::loadPage('RelacaoItemUpdateList', 'pegaID', $data);
    }
    
    /**
     * Inline record editing
     * @param $param Array containing:
     *              key: object ID value
     *              field name: object attribute to be updated
     *              value: new attribute content 
     */
    public function onInlineEdit($param)
    {
        try
        {
            // get the parameter $key
            $field = $param['field'];
            $key   = $param['key'];
            $value = $param['value'];
            
            TTransaction::open('procon_com'); // open a transaction with database
            $object = new Relacao($key); // instantiates the Active Record
            $object->{$field} = $value;
            $object->store(); // update the object in the database
            TTransaction::close(); // close the transaction
            
            $this->onReload($param); // reload the listing
            new TMessage('info', "Record Updated");
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * Register the filter in the session
     */
    public function onSearch()
    {
        // get the search form data
        $data = $this->form->getData();
        
        // clear session filters
        TSession::setValue('RelatorioForm_filter_pesquisa_id',   NULL);

        if (isset($data->pesquisa_id) AND ($data->pesquisa_id)) {
            $filter = new TFilter('pesquisa_id', '=', "$data->pesquisa_id"); // create the filter
            TSession::setValue('RelatorioForm_filter_pesquisa_id',   $filter); // stores the filter in the session
        }

        
        // fill the form with data again
        $this->form->setData($data);
        
        // keep the search data in the session
        TSession::setValue('Relatorio_filter_data', $data);
        
        $param = array();
        $param['offset']    =0;
        $param['first_page']=1;
        $this->onReload($param);
    }
    
    /**
     * Load the datagrid with data
     */
    public function onReload($param = NULL)
    {
        try
        {
            // open a transaction with database 'procon_com'
            TTransaction::open('procon_com');
            
            // creates a repository for Relacao
            $repository = new TRepository('Relacao');
            $limit = 5;
            // creates a criteria
            $criteria = new TCriteria;
            
            // default order
            if (empty($param['order']))
            {
                $param['order'] = 'id';
                $param['direction'] = 'asc';
            }
            $criteria->setProperties($param); // order, offset
            
            $criteria->setProperty('limit', $limit);
            

            if (TSession::getValue('RelatorioForm_filter_pesquisa_id')) {
                $criteria->add(TSession::getValue('RelatorioForm_filter_pesquisa_id')); // add the session filter
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
                    // add the object inside the datagrid
                    $this->datagrid->addItem($object);
                }
            }
            
            // reset the criteria for record count
            $criteria->resetProperties();
            $count= $repository->count($criteria);
            
            $this->pageNavigation->setCount($count); // count of records
            $this->pageNavigation->setProperties($param); // order, page
            $this->pageNavigation->setLimit($limit); // limit
            
            // close the transaction
            TTransaction::close();
            $this->loaded = true;
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
     * Ask before deletion
     */
    public static function onDelete($param)
    {
        // define the delete action
        $action = new TAction([__CLASS__, 'Delete']);
        $action->setParameters($param); // pass the key parameter ahead
        
        // shows a dialog to the user
        new TQuestion(TAdiantiCoreTranslator::translate('Do you really want to delete ?'), $action);
    }
    
    /**
     * Delete a record
     */
    public static function Delete($param)
    {
        try
        {
            $key=$param['key']; // get the parameter $key
            TTransaction::open('procon_com'); // open a transaction with database
            $object = new Relacao($key, FALSE); // instantiates the Active Record
            $object->delete(); // deletes the object from the database
            TTransaction::close(); // close the transaction
            
            $pos_action = new TAction([__CLASS__, 'onReload']);
            new TMessage('info', TAdiantiCoreTranslator::translate('Record deleted'), $pos_action); // success message
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * Ask before delete record collection
     */
    public function onDeleteCollection( $param )
    {
        $data = $this->formgrid->getData(); // get selected records from datagrid
        $this->formgrid->setData($data); // keep form filled
        
        if ($data)
        {
            $selected = array();
            
            // get the record id's
            foreach ($data as $index => $check)
            {
                if ($check == 'on')
                {
                    $selected[] = substr($index,5);
                }
            }
            
            if ($selected)
            {
                // encode record id's as json
                $param['selected'] = json_encode($selected);
                
                // define the delete action
                $action = new TAction(array($this, 'deleteCollection'));
                $action->setParameters($param); // pass the key parameter ahead
                
                // shows a dialog to the user
                new TQuestion(AdiantiCoreTranslator::translate('Do you really want to delete ?'), $action);
            }
        }
    }
    
    /**
     * method deleteCollection()
     * Delete many records
     */
    public function deleteCollection($param)
    {
        // decode json with record id's
        $selected = json_decode($param['selected']);
        
        try
        {
            TTransaction::open('procon_com');
            if ($selected)
            {
                // delete each record from collection
                foreach ($selected as $id)
                {
                    $object = new Relacao;
                    $object->delete( $id );
                }
                $posAction = new TAction(array($this, 'onReload'));
                $posAction->setParameters( $param );
                new TMessage('info', AdiantiCoreTranslator::translate('Records deleted'), $posAction);
            }
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }


    /**
     * Transform datagrid objects
     * Create the checkbutton as datagrid element
     */
    public function onBeforeLoad($objects, $param)
    {
        // update the action parameters to pass the current page to action
        // without this, the action will only work for the first page
        $deleteAction = $this->deleteButton->getAction();
        $deleteAction->setParameters($param); // important!
        
        $gridfields = array( $this->deleteButton );
        
        foreach ($objects as $object)
        {
            $object->check = new TCheckButton('check' . $object->id);
            $object->check->setIndexValue('on');
            $gridfields[] = $object->check; // important
        }
        
        $this->formgrid->setFields($gridfields);
    }

    
    /**
     * method show()
     * Shows the page
     */
    public function show()
    {
        // check if the datagrid is already loaded
        if (!$this->loaded AND (!isset($_GET['method']) OR !(in_array($_GET['method'],  array('onReload', 'onSearch')))) )
        {
            if (func_num_args() > 0)
            {
                $this->onReload( func_get_arg(0) );
            }
            else
            {
                $this->onReload();
            }
        }
        parent::show();
    }
}

