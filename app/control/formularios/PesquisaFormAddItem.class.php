<?php



class PesquisaFormAddItem extends TPage
{
    protected $form;      // search form
    protected $form2;
    protected $datagrid;  // listing
    protected $total;
    protected $cartgrid;
    protected $pageNavigation;
    protected $loaded;
    
    //protected $button;
    
    use Adianti\base\AdiantiStandardListTrait;
    
    /**
     * Class constructor
     * Creates the page, the search form and the listing
     */
    public function __construct()
    {
        parent::__construct();
        new TSession;
        
        $this->setDatabase('procon_com');            // defines the database
        $this->setActiveRecord('Item');   // defines the active record
        $this->setDefaultOrder('nome', 'asc');         // defines the default order
        // $this->setCriteria($criteria) // define a standard filter
              
        $this->addFilterField('categoria_id', 'ilike', 'categoria_id'); // filterField, operator, formField
        
        // creates the forms
        $this->form = new BootstrapFormBuilder('form_SearchItens');
        $this->form->setFormTitle('Filtro de Itens');
        
        $this->form2 = new BootstrapFormBuilder('form_ListItens');
        
 
        $pesquisa = new TEntry('pesquisa');
        $nome = new TEntry('nome');
        $categoria_id = new TDBCombo('categoria_id', 'procon_com', 'categoria', 'id', 'nome');
        
        $buscacep = new TAction(array($this, 'onChangePesquisa'));
        
        // add the fields
        $this->form->addFields( [ new TLabel('Pesquisa')  ], [ $pesquisa]);
        $this->form->addFields( [ new TLabel('Nome')      ], [ $nome ] ,
                                [ new TLabel('Categoria') ], [ $categoria_id ]);
                                
        $this->form2->addFields( [ new THidden('Pesquisa')  ], [ $this->total]);

        // set sizes
        $pesquisa->setSize('37%');
        $nome->setSize('100%');
        $categoria_id->setSize('100%');
        
        //Botao Form Search Itens
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $btn->style = 'padding: 4px;';
        
        $btn1 = $this->form2->addAction('Confirmar', new TAction([$this, 'onSearch']), 'fa:check green');
        $btn1->class = 'btn btn-sm btn';
        $btn1->style = 'padding: 4px;';
        
        $nome->setValue(TSession::getValue('item_nome'));
        $categoria_id->setValue(TSession::getValue('item_categoria_id'));
        
        
        if (!empty($pesquisa))
        {
            $pesquisa->setEditable(FALSE);
        }
        
        // creates a DataGrid
        $this->datagrid = new TQuickGrid;
        $this->cartgrid = new TQuickGrid;

        // creates the datagrid columns
        //$this->datagrid->addQuickColumn('ID', 'id', 'right', 30);
        $this->datagrid->addQuickColumn('Nome', 'nome', 'left', 250);
        $this->datagrid->addQuickColumn('Qtd', 'quantidade', 'center', 25);
        $this->datagrid->addQuickColumn('Un', 'unidadeMedida->nome', 'center', 30);
        $this->datagrid->addQuickColumn('categoria_id', 'categoria->nome', 'left', 150);

        //$this->cartgrid->addQuickColumn('ID', 'id', 'right', 30);
        $this->cartgrid->addQuickColumn('Nome', 'nome', 'left', 250);
        $this->cartgrid->addQuickColumn('Qtd', 'quantidade', 'center', 25 );
        $this->cartgrid->addQuickColumn('Un', 'unidadeMedida->nome', 'center', 30 );
        $this->cartgrid->addQuickColumn('categoria_id', 'categoria->nome', 'left', 150 );
        
        // creates datagrid actions
        $this->datagrid->addQuickAction('Select', new TDataGridAction(array($this, 'onSelect')), 'id', 'fa:plus-circle green');
        $this->cartgrid->addQuickAction('Delete', new TDataGridAction(array($this, 'onDelete')), 'id', 'fa:minus-circle red');
        
        // create the datagrid model
        $this->datagrid->createModel();
        $this->cartgrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        // creates the page structure using a table
/*        $table1 = new TTable;
        $table1->addRow()->addCell($this->form);*/
        $table1 = new TTable;
        $table1->addRow()->addCell($this->datagrid);
      
        $this->total = new TLabel('');
        $this->total->setFontStyle('b');
        
        $table2 = new TTable;
        //$table2->addRow()->addCell($this->total);
        $table2->addRow()->addCell($this->cartgrid);
        
        
        //$table3 = new TTable;
        $button = new TButton('action1'); 
        $button->setAction(new TAction(array($this, 'onConfirm')), 'Confirma'); 
        $button->setImage('ico_save.png'); 
        //$table3->addRow()->addCell($button1);
        
        $hbox = new THBox;
        $hbox->add($table1)->style.='vertical-align:top; display: block; width: 50%; float: left;  white-space: pre-rap; padding: 0 8px 0 20px';
        $hbox->add($table2)->style.='vertical-align:top; display: block; width: 50%; float: right; padding-right: 20px';
        
        $this->form2->add($hbox);
        $this->form2->style = 'padding: 10px; border: none; text-decoration: none';
        //$hbox->addRowSet($button);
        
        // wrap the page content using vertical box
        $vbox = new TVBox;
        //$vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $vbox->style = 'width: 100%; background-color: white; text-align: center';
        $vbox->add($this->form)->style = 'padding: 20px 20px 10px 20px; width: 100%; margin-botton: 0px;';
        $vbox->add($this->form2)->style = '';
        //$vbox->add($hbox);
        //$vbox->add($this->button);
        $vbox->add($this->pageNavigation);
        

        parent::add($vbox);
    }
    
    public function onConfirm()
    {
        try
        {
            $this->form->validate();
            $data = $this->form->getData();
            $this->form->setData($data);
            
            $form1_data = TSession::getValue('form_step1_data');
            new TMessage('info', str_replace(',', '<br>', json_encode($data)));
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
    
    public function onLoadFromForm1($data)
    {
        $obj = new StdClass;
        $obj->pesquisa = $data['pesquisa'];
        $this->form->setData($obj);
    }
    
        
    public function onBackForm()
    {
        // Load another page
        AdiantiCoreApplication::loadPage('PesquisaForm', 'onLoadFromSession');
    }
    
    
    public static function onChangePesquisa($param)
    {
		/*try {
        $obj = new StdClass;
		TTransaction::open('procon_com');
		$vemcep = new BuscaPesquisa($param['pesquisa']);
        TTransaction::close();
        TForm::sendData('form_Posto', $obj);
        
		} catch (Exception $e) // in case of exception
        {
            #new TMessage('error', $e->getMessage());
			new TMessage('error', 'O CEP '.$param['cep'].' não foi localizado.<br>Verifique se foi digitado de maneira correta e tente novamente.<br>Em caso de dúvidas, ligue para (67)34117295 das 07h30 às 13h30.<br>Departamento de Tecnologia da Informação.');
            TTransaction::rollback(); // undo all pending operations
        */
        }
    
    
    /**
     * Put a product inside the cart
     */
    public function onSelect($param)
    {    
        // get the cart objects from session 
        $cart_objects = TSession::getValue('cart_objects');
        
        TTransaction::open('procon_com');
        $item = new Item($param['key']); // load the product
        $cart_objects[$item->id] = $item; // add the product inside the array
        TSession::setValue('cart_objects', $cart_objects); // put the array back to the session
        TTransaction::close();
        
        // reload datagrids
        $this->onReload( func_get_arg(0) );
        
        
        
    }
    
    /**
     * Remove a product from the cart
     */
    public function onDelete($param)
    {
        // get the cart objects from session
        $cart_objects = TSession::getValue('cart_objects');
        unset($cart_objects[$param['key']]); // remove the product from the array
        TSession::setValue('cart_objects', $cart_objects); // put the array back to the session
        
        // reload datagrids
        $this->onReload( func_get_arg(0) );
    }
    
    /**
     * method onSearch()
     * Register the filter in the session when the user performs a search
     */
    function onSearch()
    {
        // get the search form data
        $data = $this->form->getData();
        
        TSession::setValue('item_filter_nome', NULL);
        TSession::setValue('item_filter_categoria_id', NULL);

        // check if the user has filled the form
        if ($data->nome)
        {
            // creates a filter using what the user has typed
            $filter = new TFilter('nome', 'like', "%{$data->nome}%");
            
            // stores the filter in the session
            TSession::setValue('item_filter_nome', $filter);
            TSession::setValue('item_nome', $data->nome);
            
        }
        else
        {
            TSession::setValue('item_filter_nome', NULL);
            TSession::setValue('item_nome',   '');
        }

        if ($data->categoria_id)
        {
            // creates a filter using what the user has typed
            $filter = new TFilter('categoria_id', '=', "$data->categoria_id");
            
            // stores the filter in the session
            TSession::setValue('item_filter_categoria_id', $filter);
            TSession::setValue('item_categoria_id',   $data->categoria_id);
            
        }
        else
        {
            TSession::setValue('item_filter_categoria_id', NULL);
            TSession::setValue('item_categoria_id',   '');
        }
        
        // fill the form with data again
        $this->form->setData($data);
        
        $param=array();
        $param['offset']    =0;
        $param['first_page']=1;
        $this->onReload($param);
    }
    
    /**
     * method onReload()
     * Load the datagrid with the database objects
     */
    function onReload($param = NULL)
    {   //$this->form->setData($obj);
    
        try
        {
            // open a transaction with database 'samples'
            TTransaction::open('procon_com');
            
            // creates a repository for Product
            $repository = new TRepository('Item');
            $limit = 10;
            
            // creates a criteria
            $criteria = new TCriteria;
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);
            $criteria->setProperty('order', 'nome');
            
            if (TSession::getValue('item_filter_nome'))
            {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('item_filter_nome'));
            }

            if (TSession::getValue('item_filter_categoria_id'))
            {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('item_filter_categoria_id'));
            }
            
           /* if (TSession::getValue('form_step1_data'))
            {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('item_filter_categoria_id'));
            }*/
            
            // load the objects according to criteria
            $items = $repository->load($criteria);
            $this->datagrid->clear();
            if ($items)
            {
                foreach ($items as $item)
                {
                    // add the object inside the datagrid
                    $this->datagrid->addItem($item);
                }
            }
            
            $this->cartgrid->clear();
            $cart_objects = TSession::getValue('cart_objects');
            //$total = 0;
            if ($cart_objects)
            {
                foreach ($cart_objects as $object)
                {
                    $this->cartgrid->addItem($object);
                    //$total += $object->sale_price;
                }
            }
            
            //$this->total->setValue(number_format($total));   
            
            
            
            $form_step1 = TSession::getValue('form_step1_data');
            $this->form->setData($form_step1);
            
              
            // reset the criteria for record count
            $criteria->resetProperties();
            $count= $repository->count($criteria);
            
            $this->pageNavigation->setCount($count); // count of records
            $this->pageNavigation->setProperties($param); // order, page
            $this->pageNavigation->setLimit($limit); // limit
            
            // close the transaction
            //    $this->form->setData($obj2);
            TTransaction::close();
            $this->loaded = true;
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', '<b>Error</b> ' . $e->getMessage());
            // undo all pending operations
            TTransaction::rollback();
        }
    }
    
    /**
     * method show()
     * Shows the page
     */
    function show()
    {
        // check if the datagrid is already loaded
        if (!$this->loaded)
        {
            $this->onReload( func_get_arg(0) );
        }
        parent::show();
    }
}
?>