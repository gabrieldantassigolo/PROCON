<?php
class CepBusca extends TRecord
{
    const TABLENAME = 'public.esic_logradouros';
    const PRIMARYKEY= 'cep';
    const IDPOLICY =  'serial'; // {max, serial} 
    
    private $municipio;
    private $estado;

    public function __construct($id = NULL, $callObjectLoad = TRUE){
		parent::__construct($id, $callObjectLoad);
		parent::addAttribute('id');
		parent::addAttribute('nome');	
		parent::addAttribute('cep');
		parent::addAttribute('bairro');
		parent::addAttribute('id_municipio');
		parent::addAttribute('id_estado');
	} 
    function get_municipio_nome()
    {
        if (empty($this->municipio))
			TTransaction::open('esicbd');
            $this->municipio = new Municipio($this->id_municipio);
			TTransaction::close();
        return $this->municipio->nome;
    }
    function get_estado_sigla()
    {
        if (empty($this->estado))
			TTransaction::open('esicbd');
            $this->estado = new Estado($this->id_estado);
			TTransaction::close();
        return $this->estado->sigla;
    }
    
    function get_estado_nome()
    {
        if (empty($this->estado))
			TTransaction::open('esicbd');
            $this->estado = new Estado($this->id_estado);
			TTransaction::close();
        return $this->estado->nome;
    }
}
