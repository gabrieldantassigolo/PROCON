<?php
/**
 * EstabelecimentoForm Form
 * @author  <your name here>
 */
class EstabelecimentoForm extends TPage
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
        $this->form = new BootstrapFormBuilder('form_Estabelecimento');
        $this->form->setFormTitle('Cadastro de Estabelecimento');
        

        // create the form fields
        $id = new THidden('id');
        $nome = new TEntry('nome');
        $razao = new TEntry('razao');
        $cnpj = new TEntry('cnpj');
        $responsavel = new TEntry('responsavel');
        $responsavel2 = new TEntry('responsavel2');
        $email = new TEntry('email');
        $telefone = new TEntry('telefone');
        $cep = new TEntry('cep');
        $estado = new TEntry('estado');
        $municipio = new TEntry('municipio');
        $logradouro = new TEntry('logradouro');
        $bairro = new TEntry('bairro');
        $numero = new TEntry('numero');
        $complemento = new TEntry('complemento');
        
        // Fields Mask
        $cnpj->setMask('99.999.999/9999-99');
        $nome->maxlength = 50;
        $razao->maxlength = 50;
        $telefone->setMask('(99)9999-9999');
        $cep->setMask('99999-999');
		$buscacep = new TAction(array($this, 'onChangeCep'));
		$cep->setExitAction($buscacep);
        $numero->setMask('9999');
        $complemento->forceUpperCase();
	    $logradouro->setEditable(FALSE);
		$bairro->setEditable(FALSE);
		$municipio->setEditable(FALSE);
		$estado->setEditable(FALSE);
        
        // add the fields
        $this->form->addFields( [ new TLabel('')], [$id]);
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome ] );
        $this->form->addFields( [ new TLabel('Razao') ], [ $razao ] );
        $this->form->addFields( [ new TLabel('Cnpj') ], [ $cnpj ] );
        $this->form->addFields( [ new TLabel('1º Responsavel') ], [ $responsavel ] );
        $this->form->addFields( [ new TLabel('2º Responsavel ') ], [ $responsavel2 ] );
        $this->form->addFields( [ new TLabel('Email') ], [ $email ] );
        $this->form->addFields( [ new TLabel('Telefone') ], [ $telefone ] );
        $this->form->addFields( [ new TLabel('Cep') ], [ $cep ] );
        $this->form->addFields( [ new TLabel('Estado') ], [ $estado ] );
        $this->form->addFields( [ new TLabel('Municipio') ], [ $municipio ] );
        $this->form->addFields( [ new TLabel('Logradouro') ], [ $logradouro ] );
        $this->form->addFields( [ new TLabel('Bairro') ], [ $bairro ] );
        $this->form->addFields( [ new TLabel('Numero') ], [ $numero ] );
        $this->form->addFields( [ new TLabel('Complemento') ], [ $complemento ] );


        // Validation
        $nome->addValidation('Nome', new TMaxLengthValidator, array(50));
        $nome->addValidation('Nome', new TMaxLengthValidator, array(50));
        $responsavel->addValidation('Responsavel', new TMaxLengthValidator, array(50));
        $responsavel->addValidation('Responsavel2', new TMaxLengthValidator, array(50));
        $email->addValidation('Email', new TEmailValidator);
        $telefone->addValidation('Telefone', new TRequiredValidator);
        $cep->addValidation('Cep', new TRequiredValidator);
        $numero->addValidation('Numero', new TRequiredValidator);
        $cnpj->addValidation('Cnpj', new TCNPJValidator);
        
        // set sizes
        $bairro->setSize('70%');
        $nome->setSize('70%');
        $razao->setSize('70%');
        $cnpj->setSize('70%');
        $responsavel->setSize('70%');
        $responsavel2->setSize('70%');
        $email->setSize('70%');
        $telefone->setSize('35%'); 
        $cep->setSize('35%');
        $logradouro->setSize('35%');
        $estado->setSize('70%');
        $municipio->setSize('70%');
        $numero->setSize('10%');
        $complemento->setSize('70%');

        if (!empty($id))
        {
            $id->setEditable(FALSE);
        }
        
        /** samples
         $fieldX->addValidation( 'Field X', new TRequiredValidator ); // add validation
         $fieldX->setSize( '100%' ); // set size
         **/
         
         //Back List
        $this->form->addAction(_t('Back'), new TAction(array('EstabelecimentoList','onReload')),'fa:arrow-circle-o-left blue');
        // create the form actions
        $this->form->addAction(_t('New'),  new TAction([$this, 'onEdit']), 'fa:eraser red');
        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:floppy-o');
        $btn->class = 'btn btn-sm btn-primary';
        
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 90%';
        $container->add(new TXMLBreadCrumb('menu.xml', 'EstabelecimentoList'));
        $container->add($this->form);
        
        parent::add($container);
    }
    
    public static function onChangeCep($param)
    {
		try {
        $obj = new StdClass;
		TTransaction::open('esicbd');
		$vemcep = new CepBusca($param['cep']);
        $obj->logradouro = $vemcep->nome;	
		$obj->id_logradouro = $vemcep->id;	
        $obj->bairro = $vemcep->bairro;	
		$obj->municipio = $vemcep->municipio_nome;
		$obj->id_municipio = $vemcep->id_municipio;
		$obj->estado = $vemcep->estado_sigla;
		$obj->id_estado = $vemcep->id_estado;
        TTransaction::close();
        TForm::sendData('form_Estabelecimento', $obj);
        
		} catch (Exception $e) // in case of exception
        {
            #new TMessage('error', $e->getMessage());
			new TMessage('error', 'O CEP '.$param['cep'].' não foi localizado.<br>Verifique se foi digitado de maneira correta e tente novamente.<br>Em caso de dúvidas, ligue para (67)34117295 das 07h30 às 13h30.<br>Departamento de Tecnologia da Informação.');
            TTransaction::rollback(); // undo all pending operations
        }
        
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
            
            $object = new Estabelecimento;  // create an empty object
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
                $object = new Estabelecimento($key); // instantiates the Active Record
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
