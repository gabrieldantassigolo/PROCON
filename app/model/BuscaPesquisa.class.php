<?php
class BuscaPesquisa extends TRecord
{
    const TABLENAME = 'pesquisa_item';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial} 
    
    private $pesquisa;
    private $item;

    public function __construct($id = NULL, $callObjectLoad = TRUE){
		parent::__construct($id, $callObjectLoad);
		parent::addAttribute('id');	
		parent::addAttribute('pesquisa_id');
		parent::addAttribute('item_id');
	} 
    function get_pesquisa()
    {
        if (empty($this->pesquisa))
			TTransaction::open('procon_com');
            $this->pesquisa = new Pesquisa($this->pesquisa_id);
			TTransaction::close();
        return $this->pesquisa->nome;
    }
}

