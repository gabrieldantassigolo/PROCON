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
        parent::addAttribute('preco');
    }

    public function set_item(Item $object)
    {
        $this->item = $object;
        $this->item_id = $object->id;
    }
    
    /**
     * Method get_distribuidor
     * Sample of usage: $filme->distribuidor->attribute;
     * @returns Distribuidor instance
     */
    public function get_item()
    {
        // loads the associated object
        if (empty($this->item))
            $this->item = new item($this->item_id);
    
        // returns the associated object
        return $this->item;
    }
    
    public function set_pesquisa(Item $object)
    {
        $this->pesquisa = $object;
        $this->pesquisa_id = $object->id;
    }
    
    /**
     * Method get_distribuidor
     * Sample of usage: $filme->distribuidor->attribute;
     * @returns Distribuidor instance
     */
    public function get_pesquisa()
    {
        // loads the associated object
        if (empty($this->pesquisa))
            $this->pesquisa = new item($this->pesquisa_id);
    
        // returns the associated object
        return $this->pesquisa;
    }
    
    public function set_item(Item $object)
    {
        $this->item = $object;
        $this->item_id = $object->id;
    }
    
    /**
     * Method get_distribuidor
     * Sample of usage: $filme->distribuidor->attribute;
     * @returns Distribuidor instance
     */
    public function get_item()
    {
        // loads the associated object
        if (empty($this->item))
            $this->item = new item($this->item_id);
    
        // returns the associated object
        return $this->item;
    }
}
