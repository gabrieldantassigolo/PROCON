<?php
/**
 * Logradouro Active Record
 * @author  <your-name-here>
 */
class Logradouro extends TRecord
{
    const TABLENAME = 'logradouro';
    const PRIMARYKEY= 'cep';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    private $estado;
    private $municipio;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('id');
        parent::addAttribute('nome');
        parent::addAttribute('cep');
        parent::addAttribute('bairro');
        parent::addAttribute('id_estado');
        parent::addAttribute('id_municipio');
    }

    function get_municipio_nome()
    {
        if (empty($this->municipio))
			TTransaction::open('esicbd');
            $this->municipio = new Municipio($this->id_municipio);
			TTransaction::close();
        return $this->municipio->nome;
    }
    function get_estado_nome()
    {
        if (empty($this->estado))
			TTransaction::open('esicbd');
            $this->estado = new Estado($this->id_estado);
			TTransaction::close();
        return $this->estado->nome;
    }

    /**
     * Method getPostos
     */
    public function getPostos()
    {
        $criteria = new TCriteria;
        $criteria->add(new TFilter('logradouro_id', '=', $this->id));
        return Posto::getObjects( $criteria );
    }
}
