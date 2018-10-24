<?php
/**
 * PesquisaForm Form
 * @author  <your name here>
 */
class PesquisaForm extends TPage
{
    protected $form; // form
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Pesquisa');
        $this->form->setFormTitle('Pesquisa');
        

        // create the form fields
        $id = new THidden('id');
        $pesquisa = new TEntry('pesquisa');


        // add the fields
        $this->form->addFields( [ new TLabel('') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Nome') ], [ $pesquisa ] );

        //$nome->addValidation('Nome', new TRequiredValidator);
        
        $pesquisa->addValidation('pesquisa', new TMinLengthValidator, array(3));

        // set sizes
        $pesquisa->setSize('100%');
        
        
        
        
        if (!empty($id))
        {
            $id->setEditable(FALSE);
        }
        
        /** samples
         $fieldX->addValidation( 'Field X', new TRequiredValidator ); // add validation
         $fieldX->setSize( '100%' ); // set size
         **/
         
        // create the form actions
        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:floppy-o');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addAction(_t('New'),  new TAction([$this, 'onEdit']), 'fa:eraser red');
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        //$container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $this->form->addAction('Next', new TAction(array($this, 'onNextForm')), 'fa:chevron-circle-right green');
        
        parent::add($container);
    }
    
    public function onLoadFromSession()
    {
        $data = TSession::getValue('form_step1_data');
        $this->form->setData($data);
    }

    public function onNextForm(){
        
        
        try
        {
            $this->form->validate();
            //$this->form->validate();
            $data = $this->form->getData();
            // store data in the session
            TSession::setValue('form_step1_data', $data);
            
            // Load another page
            AdiantiCoreApplication::loadPage('PesquisaFormAddItem', 'onLoadFromForm1', (array) $data);
    
         
            
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    
        //AdiantiCoreApplication::loadPage('PesquisaFormAddItem', 'onChangePesquisa', $param['key']);
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
            
            $object = new Pesquisa;  // create an empty object
            $object->fromArray( (array) $data); // load the object with data
            $object->store(); // save the object
            
            
            // get the generated id
            $data->id = $object->id;
            
            $object->delete($object->id);
            
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
                $object = new Pesquisa($key); // instantiates the Active Record
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
