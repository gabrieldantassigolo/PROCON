<?php
/**
 * RelacaoForm Form
 * @author  <your name here>
 */
class RelacaoForm extends TPage
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
        $this->form = new BootstrapFormBuilder('form_Relacao');
        $this->form->setFormTitle('Adicionar Relação');
        

        // create the form fields
        $id = new TEntry('id');
        $pesquisa_id = new TDBCombo('pesquisa_id', 'procon_com', 'Pesquisa', 'id', 'nome');
        $estabelecimento_id = new TDBCombo('estabelecimento_id', 'procon_com', 'Estabelecimento', 'id', 'nome');
        $data_criacao = new TEntry('data_criacao');


        // add the fields
        //$this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Pesquisa') ], [ $pesquisa_id ] );
        $this->form->addFields( [ new TLabel('Estabelecimento') ], [ $estabelecimento_id ] );
        $this->form->addFields( [ new TLabel('Data Criacao') ], [ $data_criacao ] );



        // set sizes
        $id->setSize('70%');
        $pesquisa_id->setSize('70%');
        $estabelecimento_id->setSize('70%');
        $data_criacao->setSize('35%');

        //mask
        $data_criacao->setMask('dd/mm/yyyy');
       // TRequiredValidator();
        
        //setData
        $datanow = date('d/m/Y');
        $data_criacao->setValue($datanow);
        
        if($data_criacao < $datanow)
        {
            new TMessage('info', 'Data Inválida!');    
        }
        
        if (!empty($id))
        {
            $id->setEditable(FALSE);
        }
        
        /** samples
         $fieldX->addValidation( 'Field X', new TRequiredValidator ); // add validation
         $fieldX->setSize( '100%' ); // set size
         **/
        //Back List
        $this->form->addAction(_t('Back'), new TAction(array('RelacaoList','onReload')),'fa:arrow-circle-o-left blue');
        
        // create the form actions
        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:floppy-o');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addAction(_t('New'),  new TAction([$this, 'onEdit']), 'fa:eraser red');
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        $container->add(new TXMLBreadCrumb('menu.xml', 'RelacaoList'));
        $container->add($this->form);
        
        parent::add($container);
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
            
            $object = new Relacao;  // create an empty object
            $object->fromArray( (array) $data); // load the object with data
            $object->store(); // save the object
            
            // get the generated id
            $data->id = $object->id;
            
           //IMPORTANTE
           //define o RelacaoItem utilizando as informações do formulário de relação
            $pesquisa = new Pesquisa($data->pesquisa_id);
            $items = $pesquisa->getItems();
            foreach($items as $item){
                $object = new RelacaoItem;
                $object->relacao_id = $data->id;
                $object->item_id = $item->id;
                $object->store();
            }
            
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
                $object = new Relacao($key); // instantiates the Active Record
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
