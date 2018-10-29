<?php
/**
 * RelacaoItemUpdateList Listing
 * @author  <your name here>
 */
class RelacaoItemUpdateList extends TPage
{
    protected $form;     // registration form
    protected $datagrid; // listing
    protected $pageNavigation;
    protected $formgrid;
    protected $saveButton;
    
    use Adianti\base\AdiantiStandardListTrait;
    
    /**
     * Page constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->setDatabase('procon_com');            // defines the database
        $this->setActiveRecord('RelacaoItem');   // defines the active record
        $this->setDefaultOrder('id', 'asc');         // defines the default order
        // $this->setCriteria($criteria) // define a standard filter

        $this->addFilterField('item_id', '=', 'item_id'); // filterField, operator, formField
        $this->addFilterField('preco', 'like', 'preco'); // filterField, operator, formField
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_RelacaoItem');
        $this->form->setFormTitle('RelacaoItem');
        

        // create the form fields
        $item_id = new TDBUniqueSearch('item_id', 'procon_com', 'Item', 'id', 'nome');
        //$preco = new TEntry('preco');


        // add the fields
        $this->form->addFields( [ new TLabel('Item') ], [ $item_id ] );
        //$this->form->addFields( [ new TLabel('Preco') ], [ $preco ] );


        // set sizes
        $item_id->setSize('100%');
        //$preco->setSize('100%');

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('RelacaoItem_filter_data') );
        
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        // $this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_item_id = new TDataGridColumn('item->nome', 'Item', 'right');
        $column_preco = new TDataGridColumn('preco_widget', 'Preco', 'left');


        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_item_id);
        $this->datagrid->addColumn($column_preco);

        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // create the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        $this->datagrid->disableDefaultClick();
        
        // put datagrid inside a form
        $this->formgrid = new TForm;
        $this->formgrid->add($this->datagrid);
        
        // creates the delete collection button
        $this->saveButton = new TButton('update_collection');
        $this->saveButton->setAction(new TAction(array($this, 'onSaveCollection')), AdiantiCoreTranslator::translate('Save'));
        $this->saveButton->setImage('fa:save green');
        $this->formgrid->addField($this->saveButton);
        
        $gridpack = new TPanelGroup;
        $gridpack->style = 'width: 100%';
        $gridpack->add($this->formgrid);
        $gridpack->addFooter($this->saveButton);//->style = 'background:whiteSmoke;border:1px solid #cccccc; padding: 3px;padding: 5px;';
        
        $this->setTransformer(array($this, 'onBeforeLoad'));
        


        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add(TPanelGroup::pack('', $gridpack, $this->pageNavigation));
        
        parent::add($container);
    }
    
    public function pegaID($data){
        $pesquisa_item = new PesquisaItem();
        $pesquisa_item->pesquisa_id = $data['pesquisa_id'];
        
    }
    
    /**
     * Transform datagrid objects
     * Create one widget per element
     */
    public function onBeforeLoad($objects, $param)
    {
        // update the action parameters to pass the current page to action
        // without this, the action will only work for the first page
        $saveAction = $this->saveButton->getAction();
        $saveAction->setParameters($param); // important!
        
        $gridfields = array( $this->saveButton );
        
        foreach ($objects as $object)
        {
            $object->preco_widget = new TEntry('preco' . '_' . $object->id);
            $object->preco_widget->setValue( $object->preco );
            $object->preco_widget->setSize('100%');
            $gridfields[] = $object->preco_widget; // important
        }
        
        $this->formgrid->setFields($gridfields);
    }
    

    /**
     * Save the datagrid objects
     */
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

}
