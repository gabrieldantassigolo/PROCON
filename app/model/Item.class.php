<?php
/**
 * Item Active Record
 * @author  <your-name-here>
 */
class Item extends TRecord
{
    const TABLENAME = 'item';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
   
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('nome');
        parent::addAttribute('quantidade');
        parent::addAttribute('unidade_id');
        parent::addAttribute('categoria_id');
    }
    
    public function set_categoria(Categoria $object)
    {
        $this->categoria = $object;
        $this->categoria_id = $object->id;
    }
    
    /**
     * Method get_distribuidor
     * Sample of usage: $filme->distribuidor->attribute;
     * @returns Distribuidor instance
     */
    public function get_categoria()
    {
        // loads the associated object
        if (empty($this->categoria))
            $this->categoria = new categoria($this->categoria_id);
    
        // returns the associated object
        return $this->categoria;
    }

   public function set_unidadeMedida(UnidadeMedida $object)
    {
        $this->unidadeMedida = $object;
        $this->unidadeMedida_id = $object->id;
    }
    
    /**
     * Method get_distribuidor
     * Sample of usage: $filme->distribuidor->attribute;
     * @returns Distribuidor instance
     */
    public function get_unidadeMedida()
    {
        // loads the associated object
        if (empty($this->unidadeMedida))
            $this->unidadeMedida = new unidadeMedida($this->unidade_id);
    
        // returns the associated object
        return $this->unidadeMedida;
    }
    
    
}
