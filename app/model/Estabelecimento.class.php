<?php
/**
 * Estabelecimento Active Record
 * @author  <your-name-here>
 */
class Estabelecimento extends TRecord
{
    const TABLENAME = 'estabelecimento';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
        parent::addAttribute('razao');
        parent::addAttribute('cnpj');
        parent::addAttribute('telefone');
        parent::addAttribute('estado');
        parent::addAttribute('municipio');
        parent::addAttribute('logradouro');
        parent::addAttribute('bairro');
        parent::addAttribute('numero');
        parent::addAttribute('complemento');
        parent::addAttribute('cep');
        parent::addAttribute('responsavel');
        parent::addAttribute('email');
    }

    
    /**
     * Method getRelacaos
     */
    public function getRelacaos()
    {
        $criteria = new TCriteria;
        $criteria->add(new TFilter('estabelecimento_id', '=', $this->id));
        return Relacao::getObjects( $criteria );
    }
    


}
