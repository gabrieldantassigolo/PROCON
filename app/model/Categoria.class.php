<?php
/**
 * Item Active Record
 * @author  <your-name-here>
 */
class Categoria extends TRecord
{
    const TABLENAME = 'categoria';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
    }
    
    public function load($id)
    {
        $this->items = parent::loadComposite('Item', 'categoria_id', $id);
    
        // load the object itself
        return parent::load($id);
    }

}
