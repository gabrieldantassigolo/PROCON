<?php
/**
 * RelacaoItemList Listing
 * @author  <your name here>
 */
class RelacaoItemUpdateListUser extends TPage
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
        $this->form->setFormTitle('Alterar Preços');
        

        // create the form fields
        //$item_id = new TCombo('item_id', 'procon_com', 'Item', 'Código', 'nome');
        $pesquisa_id = new TEntry('pesquisa_id');
        $item_id = new TEntry('item_id');
        $relacao_id = new THidden('relacao_id');


        // add the fields
        /*$this->form->addFields( [ new TLabel('Pesquisa') ], [ $pesquisa_id ] );
        $this->form->addFields( [ new TLabel('Item') ], [ $item_id ] );*/
        $this->form->addFields( [ new TLabel('Aviso: após a confirmação da alteração dos valores, novas alterações só serão possíveis com a permissão do Administrador!') ], [ $relacao_id ] );

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
        //Back List
        //$this->form->addAction(_t('Back'), new TAction(array('RelacaoListUser','onReload')),'fa:arrow-circle-o-left blue');

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
        
       $this->datagrid->style = 'width: 100%; border-bottom: 1px solid rgba(0, 0, 0, 0.2)';

        // create the datagrid model
        $this->datagrid->createModel();

        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
                
        //button back
        $buttonBack = new TButton('buttonBack');
        //$buttonBack->setAction(new TAction(['RelacaoListUser', 'onReload']));
        $buttonBack->setAction(new TAction(array('RelacaoListUser', 'onReload')), 'Voltar');
        $buttonBack->setImage('fa:arrow-circle-o-left blue');
        $buttonBack->style = 'padding: 5px 30px;';

        //button save
        $this->saveButton = new TButton('update_collection');
        $this->saveButton->setAction(new TAction(array($this, 'onControlSaveCollection')), AdiantiCoreTranslator::translate('Save'));
        $this->saveButton->setImage('fa:save white');
        $this->saveButton->class = 'btn btn-sm btn-primary';
        $this->saveButton->style = 'padding: 5px 30px;';
        
        $this->formgrid = new TForm;
        //$this->formgrid->addField($buttonBack);
        $this->formgrid->add($this->datagrid);
        
        $this->formgrid->addField($buttonBack);
        $this->formgrid->addField($this->saveButton);
        
        $buttons = new THBox;
        $buttons->add($buttonBack)->style = 'border: 1px solid rgba(0, 0, 0, 0.2);display: inline-table; margin-left: 0px; float: left'; 
        $buttons->add($this->saveButton)->style = 'display: inline-table; margin-right: 150px;';

        // vertical box container
        $gridpack = new TVBox;
        $gridpack->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        //$gridpack->add($buttonBack)->style = 'display-block: float; left;';
        $gridpack->add($this->formgrid);
        $gridpack->add($buttons)->style = 'text-align: center; justify-content: space-between; padding: 10px 0;';
        //$gridpack->add($this->saveButton)->style = 'text-align: center; padding: 10px;';
       
        
        $this->transformCallback = array($this, 'onBeforeLoad');
        
        $message = 'Observação: Após a confirmação dos valores, somente será possível nova alteração com a permissão do Administrador do sistema!';
        
        $aviso = new TVBox;
        $aviso->add($message);

        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        //$container->add($this->form);
        $container->add($aviso)->style = 'font-weight: bold; text-align: center; margin-bottom: 10px; background-color: rgb(255, 255, 153); padding: 20px';
        $container->add(TPanelGroup::pack('', $gridpack, $this->pageNavigation));


        parent::add($container);

        //$this->onSearch();
    }

    public function pegaID($param){
        TSession::setValue('RelacaoItemList_filter_relacao_id',   NULL);
        TSession::setValue('sessaoTeste',   NULL);
        
        TTransaction::open('procon_com');

        $obj = new StdClass;
        $obj->relacao_id = $param['id'];
        $obj->pesquisa_id = $param['pesquisa_id'];

        $this->form->setData($obj);

        TTransaction::close();

        //mantem o valor de relacao id quando troca pagina de navegação
        TSession::setValue('RelacaoItem_filter_data', $obj);

        TSession::setValue('sessaoTeste', $param);
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
//    public function onSearch($obj)
//    {
//        //var_dump($param);
//        //$teste = $param['key'];
//
//        // get the search form data
//        $data = $this->form->getData();
//
//        // clear session filters
//        TSession::setValue('RelacaoItemList_filter_item_id',   NULL);
//        TSession::setValue('RelacaoItemList_filter_relacao_id',   NULL);
//        TSession::setValue('RelacaoItemList_filter_pesquisa_id',   NULL);
//
//        if (isset($data->item_id) AND ($data->item_id)) {
//            $filter = new TFilter('item_id', '=', "$data->item_id"); // create the filter
//            TSession::setValue('RelacaoItemList_filter_item_id',   $filter); // stores the filter in the session
//        }
//
//        if (isset($data->relacao_id) AND ($data->relacao_id)) {
//            $filter = new TFilter('relacao_id', '=', "$data->relacao_id"); // create the filter
//            TSession::setValue('RelacaoItemList_filter_relacao_id',   $filter); // stores the filter in the session
//        }
//
//        //isset($data->relacao_id)== FALSE
//        if (($this->filtrado == 0) and ($data->relacao_id == ""))
//        {
//            $filter = new TFilter('relacao_id', '=', "$obj->relacao_id"); // create the filter
//            TSession::setValue('RelacaoItemList_filter_relacao_id',   $filter);
//            $this->filtrado = 1;
//        }
//
//        /*if(TSession::getValue('RelacaoItem_relacao_id')){
//            $filter = new TFilter('relacao_id', '=', "$relacao_id_form");
//            TSession::setValue('RelacaoItemList_filter_relacao_id',   $filter); // stores the filter in the session
//        }*/
//        // keep the search data in the session
//        $this->form->setData($data);
//        TSession::setValue('RelacaoItem_filter_data', $data);
//
//        $param = array();
//        $param['offset']    =0;
//        $param['first_page']=1;
//        $this->onReload($param);
//    }
    public function onSearch($param)
    {
        //var_dump($param);
        //$teste = $param['key'];

        // get the search form data
        $data = $this->form->getData();

        // clear session filters
        TSession::setValue('RelacaoItemList_filter_item_id',   NULL);
        TSession::setValue('RelacaoItemList_filter_relacao_id',   NULL);
        TSession::setValue('RelacaoItemList_filter_pesquisa_id',   NULL);

        if (isset($data->item_id) AND ($data->item_id)) {
            $filter = new TFilter('item_id', '=', "$data->item_id"); // create the filter
            TSession::setValue('RelacaoItemList_filter_item_id',   $filter); // stores the filter in the session
        }

        if (isset($data->relacao_id) AND ($data->relacao_id)) {
            $filter = new TFilter('relacao_id', '=', "$param->id"); // create the filter
            TSession::setValue('RelacaoItemList_filter_relacao_id',   $filter); // stores the filter in the session
        }

        //isset($data->relacao_id)== FALSE
        if (($this->filtrado == 0) and ($data->relacao_id == ""))
        {
            $filter = new TFilter('relacao_id', '=', "$param->relacao_id"); // create the filter
            TSession::setValue('RelacaoItemList_filter_relacao_id',   $filter);
            $this->filtrado = 1;
        }

        /*if(TSession::getValue('RelacaoItem_relacao_id')){
            $filter = new TFilter('relacao_id', '=', "$relacao_id_form");
            TSession::setValue('RelacaoItemList_filter_relacao_id',   $filter); // stores the filter in the session
        }*/
        // keep the search data in the session
        //$this->form->setData($data);
        //TSession::setValue('RelacaoItem_filter_data', $data);

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

           $data = $this->form->getData();


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
            $this->form->setData(TSession::getValue('RelacaoItem_relacao_id'));

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
            $object->preco_widget->setMaxLength(5);
            $precoFormatado = number_format($object->preco, 2, '', '');
            $object->preco = number_format($precoFormatado/100, 2, ',', '.');
            $object->preco_widget->setValue( $object->preco );
            $object->preco_widget->setSize('100%');
            $object->preco_widget->setNumericMask(2, ',', '.', true);

            /*$precoFormatado = number_format($object->preco, 2, '', '');
            $object->preco = number_format($precoFormatado/100, 2, ',', '.');*/
                      
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

    public function onControlSaveCollection($param)
    {
        try {
            $dadosPrev = TSession::getValue('sessaoTeste');

            TTransaction::open('procon_com');
            $relacao = new Relacao($dadosPrev['id']);
            TTransaction::close();

            //Testa pra ver se é editavel
            if (!($relacao->editavel == 'Bloqueado')) {

                $data = $this->formgrid->getData(); // get datagrid form data
                TSession::setValue('RelacaoItem_filter_data', $data);
                TSession::setValue('RelacaoItem_filter_data1', $data);
                $this->formgrid->setData($data);

                $maiorCinco = false;
                $precos = TSession::getValue('RelacaoItem_filter_data1');
                foreach ($precos as $preco) {
                    if (strlen($preco) > 6) {
                        $maiorCinco = true;
                    }
                }

                if ($maiorCinco == true) {
                    throw new Exception('Todos os preços definidos devem possuir menos de 5 dígitos. Exemplo: (999,99)');
                }

                $action_yes = new TAction([$this, 'onSaveCollection'], $param);
                new TQuestion('Deseja confirmar os dados?', $action_yes);
            } else {
                new TMessage('error', "Essa relação de preços não pode ser alterada, contate o administrador
                    no telefone 12341234 para mais informações");
            }
        } catch (Exception $e)
        {
            // show the exception message
            new TMessage('error', $e->getMessage());
        }
    }

    public function changeEditavel($relacao){
        $relacao->updateEditavel();
    }

    public function onSaveCollection($param)
    {
        $data = TSession::getValue('RelacaoItem_filter_data1'); // get datagrid form data
        $this->formgrid->setData($data);

        $dadosPrev = TSession::getValue('sessaoTeste');
        $maiorCinco = false;

            try
            {
                //altera o estado de editavel para true
                if($dadosPrev['id'])
                {
                    TTransaction::open('procon_com');
                    $obj = new Relacao($dadosPrev['id']);
                    //$this->changeEditavel($obj);
                    TTransaction::close();
                }
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
                TTransaction::close();

                // close transaction

                $pos_action = new TAction(array('RelacaoListUser', 'onReload'));
                new TMessage('info', 'Dados Salvos!', $pos_action);
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


            