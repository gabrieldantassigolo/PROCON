<?php
/**
 * RelacaoItem Active Record
 * @author  <your-name-here>
 */
class RelacaoItem extends TRecord
{
    const TABLENAME = 'relacao_item';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('relacao_id');
        parent::addAttribute('item_id');
    }


}
