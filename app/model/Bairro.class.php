<?php
/**
 * Bairro Active Record
 * @author  <your-name-here>
 */
class Bairro extends TRecord
{
    const TABLENAME = 'bairro';
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

    
    /**
     * Method getPostos
     */
    public function getPostos()
    {
        $criteria = new TCriteria;
        $criteria->add(new TFilter('bairro_id', '=', $this->id));
        return Posto::getObjects( $criteria );
    }
    


}
