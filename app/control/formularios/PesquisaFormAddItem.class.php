<?php
/**
 * CheckoutFormView
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class PesquisaFormAddItem extends TPage
{
    private $form;      // search form
    private $datagrid;  // listing
    private $total;
    private $cartgrid;
    private $pageNavigation;
    private $loaded;
    
    /**
     * Class constructor
     * Creates the page, the search form and the listing
     */
    public function __construct()
    {
        parent::__construct();
        new TSession;
        
        // creates the form
        $this->form = new TForm('form_search_product');
        
        // create the form fields
        $nome       = new TEntry('nome');        
        $tipo       = new TEntry('tipo');


        $nome->setSize(170);
        $tipo->setSize(170);

        $nome->setValue(TSession::getValue('item_nome'));
        $tipo->setValue(TSession::getValue('item_tipo'));
        
        $table = new TTable;
        
        $row = $table->addRow();
        $cell=$row->addCell('');
        $cell->width= 50;
        $row->addCell($nome);
        $row->addCell($tipo);
        
        // creates the action button
        $search=new TButton('find');
        $search->setAction(new TAction(array($this, 'onSearch')), 'Find');
        $search->setImage('fa:search');
        
        $row->addCell($search);
        $this->form->add($table);
        $this->form->setFields(array($nome, $tipo ,$search));
        
        // creates a DataGrid
        $this->datagrid = new TQuickGrid;
        $this->cartgrid = new TQuickGrid;

        // creates the datagrid columns
        $this->datagrid->addQuickColumn('ID', 'id', 'right', 30);
        $this->datagrid->addQuickColumn('Nome', 'nome', 'right', 100);
        $this->datagrid->addQuickColumn('Quantidade', 'quantidade', 'left', 30);
        $this->datagrid->addQuickColumn('Unidade', 'unidade', 'left', 30);
        $this->datagrid->addQuickColumn('Tipo', 'tipo', 'left', 100);

        $this->cartgrid->addQuickColumn('ID', 'id', 'right', 30);
        $this->cartgrid->addQuickColumn('Nome', 'nome', 'left', 100);
        $this->cartgrid->addQuickColumn('Quantidade', 'quantidade', 'right', 30 );
        $this->cartgrid->addQuickColumn('Unidade', 'Unidade', 'right', 30 );
        $this->cartgrid->addQuickColumn('Tipo', 'tipo', 'right', 30 );
        
        // creates datagrid actions
        $this->datagrid->addQuickAction('Select', new TDataGridAction(array($this, 'onSelect')), 'id', 'fa:check-circle-o green');
        $this->cartgrid->addQuickAction('Delete', new TDataGridAction(array($this, 'onDelete')), 'id', 'fa:trash red');
        
        // create the datagrid model
        $this->datagrid->createModel();
        $this->cartgrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        // creates the page structure using a table
        $table1 = new TTable;
        $table1->addRow()->addCell($this->form)->height='50';
        $table1->addRow()->addCell($this->datagrid);
        
        $this->total = new TLabel('');
        $this->total->setFontStyle('b');
        
        $table2 = new TTable;
        $table2->addRow()->addCell($this->total)->height = '50';
        $table2->addRow()->addCell($this->cartgrid);
        
        $hbox = new THBox;
        $hbox->add($table1)->style.='vertical-align:top';
        $hbox->add($table2)->style.='vertical-align:top';
        
        // wrap the page content using vertical box
        $vbox = new TVBox;
        //$vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $vbox->add($hbox);
        $vbox->add($this->pageNavigation);

        parent::add($vbox);
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
        TSession::setValue('item_filter_tipo', NULL);

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

        if ($data->tipo)
        {
            // creates a filter using what the user has typed
            $filter = new TFilter('nome', 'like', "%{$data->tipo}%");
            
            // stores the filter in the session
            TSession::setValue('item_filter_tipo', $filter);
            TSession::setValue('item_tipo',   $data->tipo);
            
        }
        else
        {
            TSession::setValue('item_filter_tipo', NULL);
            TSession::setValue('item_tipo',   '');
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
    {
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
            $criteria->setProperty('order', 'id');
            
            if (TSession::getValue('item_filter_nome'))
            {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('item_filter_nome'));
            }

            if (TSession::getValue('item_filter_tipo'))
            {
                // add the filter stored in the session to the criteria
                $criteria->add(TSession::getValue('item_filter_tipo'));
            }
            
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