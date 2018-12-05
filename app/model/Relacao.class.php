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
    private $items;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('pesquisa_id');
        parent::addAttribute('estabelecimento_id');
        parent::addAttribute('data_criacao');
        parent::addAttribute('editavel');
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
     * Method addItem
     * Add a Item to the Relacao
     * @param $object Instance of Item
     */
    public function addItem(Item $object)
    {
        $this->items[] = $object;
    }
    
    /**
     * Method getItems
     * Return the Relacao' Item's
     * @return Collection of Item
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Reset aggregates
     */
    public function clearParts()
    {
        $this->items = array();
    }

    /**
     * Load the object and its aggregates
     * @param $id object ID
     */
    public function load($id)
    {
    
        // load the related Item objects
        $repository = new TRepository('RelacaoItem');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('relacao_id', '=', $id));
        $relacao_items = $repository->load($criteria);
        if ($relacao_items)
        {
            foreach ($relacao_items as $relacao_item)
            {
                $item = new Item( $relacao_item->item_id );
                $this->addItem($item);
            }
        }
    
        // load the object itself
        return parent::load($id);
    }

    /**
     * Store the object and its aggregates
     */
    public function store()
    {
        // store the object itself
        parent::store();
    
        // delete the related RelacaoItem objects
        $criteria = new TCriteria;
        $criteria->add(new TFilter('relacao_id', '=', $this->id));
        $repository = new TRepository('RelacaoItem');
        $repository->delete($criteria);
        // store the related RelacaoItem objects
        if ($this->items)
        {
            foreach ($this->items as $item)
            {
                $relacao_item = new RelacaoItem;
                $relacao_item->item_id = $item->id;
                $relacao_item->relacao_id = $this->id;
                $relacao_item->store();
            }
        }
    }

    /**
     * Delete the object and its aggregates
     * @param $id object ID
     */
    public function delete($id = NULL)
    {
        $id = isset($id) ? $id : $this->id;
        // delete the related RelacaoItem objects
        $repository = new TRepository('RelacaoItem');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('relacao_id', '=', $id));
        $repository->delete($criteria);
        
    
        // delete the object itself
        parent::delete($id);
    }


}
