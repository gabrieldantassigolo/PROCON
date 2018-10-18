<?php
/**
 * PesquisaItem Active Record
 * @author  <your-name-here>
 */
class PesquisaItem extends TRecord
{
    const TABLENAME = 'pesquisa_item';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('pesquisa_id');
        parent::addAttribute('item_id');
    }


}
