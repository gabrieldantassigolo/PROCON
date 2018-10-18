<?php
/**
 * Relacao Active Record
 * @author  <your-name-here>
 */
class Relacao extends TRecord
{
    const TABLENAME = 'relacao';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    private $pesquisa;
    private $estabelecimento;
    private $item;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('preco');
        parent::addAttribute('pesquisa_id');
        parent::addAttribute('estabelecimento_id');
        parent::addAttribute('item_id');
    }

    
    /**
     * Method set_pesquisa
     * Sample of usage: $relacao->pesquisa = $object;
     * @param $object Instance of Pesquisa
     */
    public function set_pesquisa(Pesquisa $object)
    {
        $this->pesquisa = $object;
        $this->pesquisa_id = $object->id;
    }
    
    /**
     * Method get_pesquisa
     * Sample of usage: $relacao->pesquisa->attribute;
     * @returns Pesquisa instance
     */
    public function get_pesquisa()
    {
        // loads the associated object
        if (empty($this->pesquisa))
            $this->pesquisa = new Pesquisa($this->pesquisa_id);
    
        // returns the associated object
        return $this->pesquisa;
    }
    
    
    /**
     * Method set_estabelecimento
     * Sample of usage: $relacao->estabelecimento = $object;
     * @param $object Instance of Estabelecimento
     */
    public function set_estabelecimento(Estabelecimento $object)
    {
        $this->estabelecimento = $object;
        $this->estabelecimento_id = $object->id;
    }
    
    /**
     * Method get_estabelecimento
     * Sample of usage: $relacao->estabelecimento->attribute;
     * @returns Estabelecimento instance
     */
    public function get_estabelecimento()
    {
        // loads the associated object
        if (empty($this->estabelecimento))
            $this->estabelecimento = new Estabelecimento($this->estabelecimento_id);
    
        // returns the associated object
        return $this->estabelecimento;
    }
    
    
    /**
     * Method set_item
     * Sample of usage: $relacao->item = $object;
     * @param $object Instance of Item
     */
    public function set_item(Item $object)
    {
        $this->item = $object;
        $this->item_id = $object->id;
    }
    
    /**
     * Method get_item
     * Sample of usage: $relacao->item->attribute;
     * @returns Item instance
     */
    public function get_item()
    {
        // loads the associated object
        if (empty($this->item))
            $this->item = new Item($this->item_id);
    
        // returns the associated object
        return $this->item;
    }
    


}
