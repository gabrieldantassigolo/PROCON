    <?php
/**
 * ItemForm Form
 * @author  <your name here>
 */
class ItemForm extends TPage
{
    protected $form; // form
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
        parent::include_css('app/resources/estiloformcampo.css'); 
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Item');
        $this->form->setFormTitle('Cadastro Produto');
        

        // create the form fields
        $id = new THidden('id');
        $nome = new TEntry('nome');
        $quantidade = new TEntry('quantidade');
        $unidade = new TDBCombo('unidade_id', 'procon_com', 'UnidadeMedida', 'id', 'nome');
        $categoria = new TDBCombo('categoria_id', 'procon_com', 'categoria', 'id', 'nome');

        //mask
        $nome->maxlength = 50;
        

        // add the fields
        $this->form->addFields( [ new TLabel('') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome ] );
        $this->form->addFields( [ new TLabel('Quantidade') ], [ $quantidade ] );
        $this->form->addFields( [ new TLabel('Un. de medida') ], [ $unidade] );
        $this->form->addFields( [ new TLabel('Categoria') ], [ $categoria ] );

        //validation
        $quantidade->addValidation('quantidade', new TNumericValidator);

        // set sizes
        $id->setSize('10%');
        $nome->setSize('70%');
        $quantidade->setSize('35%');
        $unidade->setSize('35%');
        $categoria->setSize('70%');



        if (!empty($id))
        {
            $id->setEditable(FALSE);
        }
        
        /** samples
         $fieldX->addValidation( 'Field X', new TRequiredValidator ); // add validation
         $fieldX->setSize( '100%' ); // set size
         **/
        
        //Back List
        $this->form->addAction(_t('Back'), new TAction(array('ItemList','onReload')),'fa:arrow-circle-o-left blue');
         
        // create the form actions
         $this->form->addAction(_t('New'),  new TAction([$this, 'onEdit']), 'fa:eraser red');
        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:floppy-o');
        $btn->class = 'btn btn-sm btn-primary';
       
        
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        $container->add(new TXMLBreadCrumb('menu.xml', 'ItemList'));
        $container->add($this->form);
        
        parent::add($container);
    }
    
    /*
    * Back on List
    */
    public function onBackForm()
    {
        // Load another page
        AdiantiCoreApplication::loadPage('PesquisaForm', 'onLoadFromSession');
    }

    /**
     * Save form data
     * @param $param Request
     */
    public function onSave( $param )
    {
        try
        {
            TTransaction::open('procon_com'); // open a transaction
            
            /**
            // Enable Debug logger for SQL operations inside the transaction
            TTransaction::setLogger(new TLoggerSTD); // standard output
            TTransaction::setLogger(new TLoggerTXT('log.txt')); // file
            **/
            
            $this->form->validate(); // validate form data
            $data = $this->form->getData(); // get form data as array
            
            $object = new Item;  // create an empty object
            $object->fromArray( (array) $data); // load the object with data
            $object->store(); // save the object
            
            // get the generated id
            $data->id = $object->id;
            
            $this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction
            
            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'));
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            $this->form->setData( $this->form->getData() ); // keep form data
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * Clear form data
     * @param $param Request
     */
    public function onClear( $param )
    {
        $this->form->clear(TRUE);
    }
    
    /**
     * Load object to form data
     * @param $param Request
     */
    public function onEdit( $param )
    {
        try
        {
            if (isset($param['key']))
            {
            
                $key = $param['key'];  // get the parameter $key
                TTransaction::open('procon_com'); // open a transaction
                $object = new Item($key); // instantiates the Active Record
                $this->form->setData($object); // fill the form
                TTransaction::close(); // close the transaction
            }
            else
            {
                $this->form->clear(TRUE);
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
}
