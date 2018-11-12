<?php
/**
 * EstabelecimentoList Listing
 * @author  <your name here>
 */
class EstabelecimentoList extends TPage
{
    private $form; // form
    private $datagrid; // listing
    private $pageNavigation;
    private $formgrid;
    private $loaded;
    private $deleteButton;
    
    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct()
    {
        parent::__construct();
        parent::include_css('app/resources/estiloformcampo.css'); 
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Estabelecimento');
        $this->form->setFormTitle('Cadastrar Estabelecimento');
        

        // create the form fields
        $nome = new TEntry('nome');
        $razao = new TEntry('razao');
        $logradouro = new TEntry('logradouro');
        $bairro = new TEntry('bairro');


        // add the fields
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome ] );
        $this->form->addFields( [ new TLabel('Razao') ], [ $razao ] );
        $this->form->addFields( [ new TLabel('Logradouro') ], [ $logradouro ] );
        $this->form->addFields( [ new TLabel('Bairro') ], [ $bairro ] );


        // set sizes
        $nome->setSize('70%');
        $razao->setSize('70%');    
        $logradouro->setSize('70%');
        $bairro->setSize('70%');

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('Estabelecimento_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'), new TAction(['EstabelecimentoForm', 'onEdit']), 'fa:plus green');
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_check = new TDataGridColumn('check', '', 'left', '5%');
        $column_nome = new TDataGridColumn('nome', 'Nome', 'left');        
        $column_responsavel = new TDataGridColumn('responsavel', 'Responsavel', 'left');
        $column_email = new TDataGridColumn('email', 'Email', 'left');
        $column_telefone = new TDataGridColumn('telefone', 'Telefone', 'left');
        

        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_check);
        $this->datagrid->addColumn($column_nome);
        $this->datagrid->addColumn($column_responsavel);
        $this->datagrid->addColumn($column_email);
        $this->datagrid->addColumn($column_telefone);



        // creates the datagrid column actions
        $column_nome->setAction(new TAction([$this, 'onReload']), ['order' => 'nome']);
        $column_responsavel->setAction(new TAction([$this, 'onReload']), ['order' => 'responsavel']);
        //$column_estado->setAction(new TAction([$this, 'onReload']), ['order' => 'estado']);
        //$column_municipio->setAction(new TAction([$this, 'onReload']), ['order' => 'municipio']);
        //$column_logradouro->setAction(new TAction([$this, 'onReload']), ['order' => 'logradouro']);
        //$column_bairro->setAction(new TAction([$this, 'onReload']), ['order' => 'bairro']);

        // define the transformer method over image
        /*$column_nome->setTransformer( function($value, $object, $row) {
            return strtoupper($value);
        });*/

        
        // create EDIT action
        $action_edit = new TDataGridAction(['EstabelecimentoForm', 'onEdit']);
        //$action_edit->setUseButton(TRUE);
        //$action_edit->setButtonClass('btn btn-default');
        $action_edit->setLabel(_t('Edit'));
        $action_edit->setImage('fa:pencil-square-o blue fa-lg');
        $action_edit->setField('id');
        $this->datagrid->addAction($action_edit);
        
        // create DELETE action
        $action_del = new TDataGridAction(array($this, 'onDelete'));
        //$action_del->setUseButton(TRUE);
        //$action_del->setButtonClass('btn btn-default');
        $action_del->setLabel(_t('Delete'));
        $action_del->setImage('fa:trash-o red fa-lg');
        $action_del->setField('id');
        $this->datagrid->addAction($action_del);
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        $this->datagrid->disableDefaultClick();
        
        // put datagrid inside a form
        $this->formgrid = new TForm;
        $this->formgrid->add($this->datagrid);
        
        // creates the delete collection button
        $this->deleteButton = new TButton('delete_collection');
        $this->deleteButton->setAction(new TAction(array($this, 'onDeleteCollection')), AdiantiCoreTranslator::translate('Delete selected'));
        $this->deleteButton->setImage('fa:remove red');
        $this->formgrid->addField($this->deleteButton);
        
        $gridpack = new TVBox;
        $gridpack->style = 'width: 100%';
        $gridpack->add($this->formgrid);
        $gridpack->add($this->deleteButton)->style = 'background:whiteSmoke;border:1px solid #cccccc; padding: 3px;padding: 5px;';
        
        $this->transformCallback = array($this, 'onBeforeLoad');


        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add(TPanelGroup::pack('Estabelecimentos', $gridpack, $this->pageNavigation));
        
        parent::add($container);
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
            $object = new Estabelecimento($key); // instantiates the Active Record
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
        new TMessage('info', $data->nome);
        
        // clear session filters
        TSession::setValue('EstabelecimentoList_filter_nome',   NULL);
        TSession::setValue('EstabelecimentoList_filter_razao',   NULL);
        TSession::setValue('EstabelecimentoList_filter_cnpj',   NULL);
        TSession::setValue('EstabelecimentoList_filter_responsavel',   NULL);
        TSession::setValue('EstabelecimentoList_filter_email',   NULL);
        TSession::setValue('EstabelecimentoList_filter_telefone',   NULL);
        TSession::setValue('EstabelecimentoList_filter_cep',   NULL);
        TSession::setValue('EstabelecimentoList_filter_estado',   NULL);
        TSession::setValue('EstabelecimentoList_filter_municipio',   NULL);
        TSession::setValue('EstabelecimentoList_filter_logradouro',   NULL);
        TSession::setValue('EstabelecimentoList_filter_bairro',   NULL);
        TSession::setValue('EstabelecimentoList_filter_numero',   NULL);
        TSession::setValue('EstabelecimentoList_filter_complemento',   NULL);

        if (isset($data->nome) AND ($data->nome)) {
            $filter = new TFilter('nome', 'ilike', "%{$data->nome}%"); // create the filter
            TSession::setValue('EstabelecimentoList_filter_nome',   $filter); // stores the filter in the session
        }


        if (isset($data->razao) AND ($data->razao)) {
            $filter = new TFilter('razao', 'ilike', "%{$data->razao}%"); // create the filter
            TSession::setValue('EstabelecimentoList_filter_razao',   $filter); // stores the filter in the session
        }


        if (isset($data->cnpj) AND ($data->cnpj)) {
            $filter = new TFilter('cnpj', 'like', "%{$data->cnpj}%"); // create the filter
            TSession::setValue('EstabelecimentoList_filter_cnpj',   $filter); // stores the filter in the session
        }


        if (isset($data->responsavel) AND ($data->responsavel)) {
            $filter = new TFilter('responsavel', 'like', "%{$data->responsavel}%"); // create the filter
            TSession::setValue('EstabelecimentoList_filter_responsavel',   $filter); // stores the filter in the session
        }


        if (isset($data->email) AND ($data->email)) {
            $filter = new TFilter('email', 'like', "%{$data->email}%"); // create the filter
            TSession::setValue('EstabelecimentoList_filter_email',   $filter); // stores the filter in the session
        }


        if (isset($data->telefone) AND ($data->telefone)) {
            $filter = new TFilter('telefone', 'like', "%{$data->telefone}%"); // create the filter
            TSession::setValue('EstabelecimentoList_filter_telefone',   $filter); // stores the filter in the session
        }


        if (isset($data->cep) AND ($data->cep)) {
            $filter = new TFilter('cep', 'like', "%{$data->cep}%"); // create the filter
            TSession::setValue('EstabelecimentoList_filter_cep',   $filter); // stores the filter in the session
        }


        if (isset($data->estado) AND ($data->estado)) {
            $filter = new TFilter('estado', 'like', "%{$data->estado}%"); // create the filter
            TSession::setValue('EstabelecimentoList_filter_estado',   $filter); // stores the filter in the session
        }


        if (isset($data->municipio) AND ($data->municipio)) {
            $filter = new TFilter('municipio', 'like', "%{$data->municipio}%"); // create the filter
            TSession::setValue('EstabelecimentoList_filter_municipio',   $filter); // stores the filter in the session
        }


        if (isset($data->logradouro) AND ($data->logradouro)) {
            $filter = new TFilter('logradouro', 'ilike', "%{$data->logradouro}%"); // create the filter
            TSession::setValue('EstabelecimentoList_filter_logradouro',   $filter); // stores the filter in the session
        }


        if (isset($data->bairro) AND ($data->bairro)) {
            $filter = new TFilter('bairro', 'ilike', "%{$data->bairro}%"); // create the filter
            TSession::setValue('EstabelecimentoList_filter_bairro',   $filter); // stores the filter in the session
        }


        if (isset($data->numero) AND ($data->numero)) {
            $filter = new TFilter('numero', 'like', "%{$data->numero}%"); // create the filter
            TSession::setValue('EstabelecimentoList_filter_numero',   $filter); // stores the filter in the session
        }


        if (isset($data->complemento) AND ($data->complemento)) {
            $filter = new TFilter('complemento', 'like', "%{$data->complemento}%"); // create the filter
            TSession::setValue('EstabelecimentoList_filter_complemento',   $filter); // stores the filter in the session
        }

        
        // fill the form with data again
        $this->form->setData($data);
        
        // keep the search data in the session
        TSession::setValue('Estabelecimento_filter_data', $data);
        
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
            
            // creates a repository for Estabelecimento
            $repository = new TRepository('Estabelecimento');
            $limit = 10;
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
            

            if (TSession::getValue('EstabelecimentoList_filter_nome')) {
                $criteria->add(TSession::getValue('EstabelecimentoList_filter_nome')); // add the session filter
            }


            if (TSession::getValue('EstabelecimentoList_filter_razao')) {
                $criteria->add(TSession::getValue('EstabelecimentoList_filter_razao')); // add the session filter
            }


            if (TSession::getValue('EstabelecimentoList_filter_cnpj')) {
                $criteria->add(TSession::getValue('EstabelecimentoList_filter_cnpj')); // add the session filter
            }


            if (TSession::getValue('EstabelecimentoList_filter_responsavel')) {
                $criteria->add(TSession::getValue('EstabelecimentoList_filter_responsavel')); // add the session filter
            }


            if (TSession::getValue('EstabelecimentoList_filter_email')) {
                $criteria->add(TSession::getValue('EstabelecimentoList_filter_email')); // add the session filter
            }


            if (TSession::getValue('EstabelecimentoList_filter_telefone')) {
                $criteria->add(TSession::getValue('EstabelecimentoList_filter_telefone')); // add the session filter
            }


            if (TSession::getValue('EstabelecimentoList_filter_cep')) {
                $criteria->add(TSession::getValue('EstabelecimentoList_filter_cep')); // add the session filter
            }


            if (TSession::getValue('EstabelecimentoList_filter_estado')) {
                $criteria->add(TSession::getValue('EstabelecimentoList_filter_estado')); // add the session filter
            }


            if (TSession::getValue('EstabelecimentoList_filter_municipio')) {
                $criteria->add(TSession::getValue('EstabelecimentoList_filter_municipio')); // add the session filter
            }


            if (TSession::getValue('EstabelecimentoList_filter_logradouro')) {
                $criteria->add(TSession::getValue('EstabelecimentoList_filter_logradouro')); // add the session filter
            }


            if (TSession::getValue('EstabelecimentoList_filter_bairro')) {
                $criteria->add(TSession::getValue('EstabelecimentoList_filter_bairro')); // add the session filter
            }


            if (TSession::getValue('EstabelecimentoList_filter_numero')) {
                $criteria->add(TSession::getValue('EstabelecimentoList_filter_numero')); // add the session filter
            }


            if (TSession::getValue('EstabelecimentoList_filter_complemento')) {
                $criteria->add(TSession::getValue('EstabelecimentoList_filter_complemento')); // add the session filter
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
            $object = new Estabelecimento($key, FALSE); // instantiates the Active Record
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
                    $object = new Estabelecimento;
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
