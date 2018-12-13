<?php
/**
 * RelacaoItemList Listing
 * @author  <your name here>
 */
class RelacaoItemUpdateList extends TPage
{
    private $form; // form
    private $datagrid; // listing
    private $pageNavigation;
    private $formgrid;
    private $loaded;
    private $deleteButton;
    private $filtrado;
    
    /**
     * Class constructor
     * Creates the page, the form and the listing
     */
    public function __construct()
    {
        parent::__construct();
        $this->filtrado = 0;
        // creates the form
        $this->form = new BootstrapFormBuilder('form_RelacaoItem');
        $this->form->setFormTitle('RelacaoItem');
        

        // create the form fields
        //$item_id = new TCombo('item_id', 'procon_com', 'Item', 'Código', 'nome');
        $pesquisa_id = new TEntry('pesquisa_id');
        $estabelecimento_id = new TEntry('estabelecimento_id');
        $item_id = new THidden('item_id');
        $relacao_id = new THidden('relacao_id');


        // add the fields
        $this->form->addFields( [ new TLabel('Pesquisa') ], [ $pesquisa_id ] );
        $this->form->addFields( [ new TLabel('Estabelecimento') ], [ $estabelecimento_id ] );
        $this->form->addFields( [ new TLabel('') ], [ $item_id ] );
        $this->form->addFields( [ new TLabel('') ], [ $relacao_id ] );

        // set sizes
        $item_id->setSize('100%');
        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('RelacaoItem_filter_data') );

         if (!empty($relacao_id))
        {
            $relacao_id->setEditable(FALSE);
        }
        
        if (!empty($pesquisa_id))
        {
            $pesquisa_id->setEditable(FALSE);
        }

        if (!empty($estabelecimento_id))
        {
            $estabelecimento_id->setEditable(FALSE);
        }
        //Back List
        $this->form->addAction(_t('Back'), new TAction(array('RelacaoList','onReload')),'fa:arrow-circle-o-left blue');
        // add the search form actions
        //$btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        //$btn->class = 'btn btn-sm btn-primary';
        //$this->form->addActionLink(_t('New'), new TAction(['RelacaoItemForm', 'onEdit']), 'fa:plus green');

        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        
        //$this->datagrid->disableDefaultClick();

        
        // creates the datagrid columns
        //$column_id = new TDataGridColumn('id', 'Cód.', 'right');
        //$column_relacao_id = new TDataGridColumn('relacao_id', 'Relacao', 'left');
        $column_item_id = new TDataGridColumn('item->nome', 'Item', 'left', '40%');
        $column_item_quantidade = new TDataGridColumn('item->quantidade', 'Un.', 'left', '5%');
        $column_item_unidadeMedida = new TDataGridColumn('item->unidadeMedida->nome', 'Un.', 'left', '15%');
        $column_item_categoria = new TDataGridColumn('item->categoria->nome', 'Categoria', 'left', '20%');
        $column_preco = new TDataGridColumn('preco_widget', 'Preco', 'left', '20%');
        
        $format_value = function($value) {
            if (is_numeric($value)) {
                return number_format($value, 2, ',', '.');
            }
            return $value;
        };
        
       $column_preco->setTransformer($format_value); 
        // add the columns to the DataGrid
       //$this->datagrid->addColumn($column_id);
       // $this->datagrid->addColumn($column_relacao_id);
       $this->datagrid->addColumn($column_item_id);
       $this->datagrid->addColumn($column_item_quantidade);
       $this->datagrid->addColumn($column_item_unidadeMedida);
       $this->datagrid->addColumn($column_item_categoria);
       $this->datagrid->addColumn($column_preco);
       $this->datagrid->style = 'width: 100%';
        
        // creates the datagrid column actions
        //$column_id->setAction(new TAction([$this, 'onReload']), ['order' => 'id']);
       // create EDIT action
        //$action_edit = new TDataGridAction(['RelacaoItemForm', 'onEdit']);
        //$action_edit->setUseButton(TRUE);
        //$action_edit->setButtonClass('btn btn-default');
        //$action_edit->setLabel(_t('Edit'));
        //$action_edit->setImage('fa:pencil-square-o blue fa-lg');
        //$action_edit->setField('id');
        //$this->datagrid->addAction($action_edit);

        // create the datagrid model
        $this->datagrid->createModel();

        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());

        $this->formgrid = new TForm;
        $this->formgrid->add($this->datagrid);

        $this->saveButton = new TButton('update_collection');
        $this->saveButton->setAction(new TAction(array($this, 'onSaveCollection')), AdiantiCoreTranslator::translate('Save'));
        $this->saveButton->class = 'btn btn-sm btn-primary';
        $this->saveButton->style = 'text-algn: center; padding: 5px 30px;';
        $this->saveButton->setImage('fa:save white');
        $this->formgrid->addField($this->saveButton);

        // vertical box container
        $gridpack = new TVBox;
        $gridpack->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $gridpack->add($this->formgrid)->style = 'border-bottom: 1px solid rgba(0, 0, 0, 0.2)';
        //$gridpack->add($this->saveButton)->style = 'text-align: center; padding: 10px 10px';

        $this->transformCallback = array($this, 'onBeforeLoad');

        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add(TPanelGroup::pack('', $gridpack, $this->pageNavigation));


        parent::add($container);

        //$this->onSearch();
    }

    public function pegaID($data){
   
        TSession::setValue('RelacaoItemList_filter_relacao_id',   NULL);
        
        TTransaction::open('procon_com');

        $pesquisa = new Pesquisa($data['pesquisa_id']);
        $estabelecimento = new Estabelecimento($data['estabelecimento_id']);

        $obj = new StdClass;
        $obj->relacao_id = $data['id'];
        $obj->pesquisa_id = $pesquisa->nome;
        $obj->estabelecimento_id = $estabelecimento->nome;
        
        $this->form->setData($obj);
        
        TTransaction::close();

        //mantem o valor de relacao id quando troca pagina de navegação
        TSession::setValue('RelacaoItem_preenche', $obj);
        
        $this->filtrado = 0;
        $this->onSearch($obj);
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
            $data = $this->form->getData();
            // get the parameter $key
            $field = $param['field'];
            $key   = $param['key'];
            $value = $param['value'];

            TTransaction::open('procon_com'); // open a transaction with database
            $object = new RelacaoItem($key); // instantiates the Active Record
            $object->{$field} = $value;
            $object->store(); // update the object in the database
            TTransaction::close(); // close the transaction

            // fill the form with data again
            $this->form->setData($data);

            $this->onReload($param); // reload the listing
            new TMessage('info', "Record Updated");


        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }

    public function filtra(){
        $data = $this->form->getData();
        if(TSession::getValue('RelacaoItem_relacao_id')){
            $filter = new TFilter('relacao_id', '=', "$data->relacao_id"); // create the filter
            TSession::setValue('RelacaoItemList_filter_relacao_id',   $filter);

        }

    }

    /**
     * Register the filter in the session
     */
    public function onSearch($obj)
    {    
        //var_dump($param);
        //$teste = $param['key'];
            
        // get the search form data
        $data = TSession::getValue('RelacaoItem_filter_data');


        // clear session filters
        TSession::setValue('RelacaoItemList_filter_item_id',   NULL);
        TSession::setValue('RelacaoItemList_filter_relacao_id',   NULL);
        TSession::setValue('RelacaoItemList_filter_pesquisa_id',   NULL);

        if (isset($data->item_id) AND ($data->item_id)) {
            $filter = new TFilter('item_id', '=', "$data->item_id"); // create the filter
            TSession::setValue('RelacaoItemList_filter_item_id',   $filter); // stores the filter in the session
        }

        if (isset($data->relacao_id) AND ($data->relacao_id)) {
            $filter = new TFilter('relacao_id', '=', "$data->relacao_id"); // create the filter
            TSession::setValue('RelacaoItemList_filter_relacao_id',   $filter); // stores the filter in the session
        }
        
        //isset($data->relacao_id)== FALSE
        if (($this->filtrado == 0) and ($data->relacao_id == ""))
        {
            $filter = new TFilter('relacao_id', '=', "$obj->relacao_id"); // create the filter
            TSession::setValue('RelacaoItemList_filter_relacao_id',   $filter);
            $this->filtrado = 1;        
        } else {
            TSession::setValue('RelacaoItem_filter_data', $data);
        }
        
        /*if(TSession::getValue('RelacaoItem_relacao_id')){
            $filter = new TFilter('relacao_id', '=', "$relacao_id_form");
            TSession::setValue('RelacaoItemList_filter_relacao_id',   $filter); // stores the filter in the session
        }*/
        // keep the search data in the session
       // $this->form->setData($data);
       // TSession::setValue('RelacaoItem_filter_data', $data);

        $param = array();
        $param['offset']    =0;
        $param['first_page']=1;
        $this->onReload($param);
    }

    /**
     * Load the datagrid with data
     */
    public function onReload($param)
    {
        try
        {    
           $data = TSession::getValue('relacaoItem_filter_data');

            // open a transaction with database 'procon_com'
            TTransaction::open('procon_com');

            // creates a repository for RelacaoItem
            $repository = new TRepository('RelacaoItem');
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


            if (TSession::getValue('RelacaoItemList_filter_item_id')) {
                $criteria->add(TSession::getValue('RelacaoItemList_filter_item_id')); // add the session filter
            }

            if (TSession::getValue('RelacaoItemList_filter_relacao_id')) {
                $criteria->add(TSession::getValue('RelacaoItemList_filter_relacao_id')); // add the session filter
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


            /*$this->datagrid->clear();
            if ($objects)
            {
                // iterate the collection of active records
                foreach ($objects as $object)
                {
                    // add the object inside the datagrid
                   $pesquisa = new Pesquisa($data['pesquisa_id'];
                  // $items

                }
            }*/

            // reset the criteria for record count
            $criteria->resetProperties();
            $count= $repository->count($criteria);

            $this->pageNavigation->setCount($count); // count of records
            $this->pageNavigation->setProperties($param); // order, page
            $this->pageNavigation->setLimit($limit); // limit

            $data = $this->form->getData();
            TSession::setValue('RelacaoItem_filter_data', $data);
            $this->form->setData(TSession::getValue('RelacaoItem_preenche'));

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

    public function onBeforeLoad($objects, $param)
    {
        // update the action parameters to pass the current page to action
        // without this, the action will only work for the first page
        $saveAction = $this->saveButton->getAction();
        $saveAction->setParameters($param); // important!

        $gridfields = array( $this->saveButton );
        

       
       //$column_preco->setTransformer($format_value);
        foreach ($objects as $object)
        {   
            $object->preco_widget = new TEntry('preco' . '_' . $object->id);
            $precoFormatado = number_format($object->preco, 2, '', '');
            $object->preco = number_format($precoFormatado/100, 2, ',', '.');
            $object->preco_widget->setValue( $object->preco);
            $object->preco_widget->setSize('100%');
            $object->preco_widget->setNumericMask(2, ',', '.', true);
            $object->preco_widget->setEditable(FALSE);
          
            $gridfields[] = $object->preco_widget; // important
        }

        $this->formgrid->setFields($gridfields);
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
            $object = new RelacaoItem($key, FALSE); // instantiates the Active Record
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

    public function onSaveCollection($param)
    {
        $data = $this->formgrid->getData(); // get datagrid form data
        $this->formgrid->setData($data); // keep the form filled

        try
        {
            // open transaction
            TTransaction::open('procon_com');

            // iterate datagrid form objects
            foreach ($this->formgrid->getFields() as $name => $field)
            {
                if ($field instanceof TEntry)
                {
                    $parts = explode('_', $name);
                    $id = end($parts);
                    $object = RelacaoItem::find($id);

                    if ($object AND isset($param[$name]))
                    {
                        $object->preco = $data->{$name};
                        $object->store();
                    }
                }
            }
            new TMessage('info', AdiantiCoreTranslator::translate('Records updated'));

            // close transaction
            TTransaction::close();
        }
        catch (Exception $e)
        {
            // show the exception message
            new TMessage('error', $e->getMessage());
        }
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


            