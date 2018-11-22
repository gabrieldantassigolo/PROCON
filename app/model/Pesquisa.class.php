<?php
/**
 * Pesquisa Active Record
 * @author  <your-name-here>
 */
class Pesquisa extends TRecord
{
    const TABLENAME = 'pesquisa';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    private $items;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);    
        parent::addAttribute('nome');
        parent::addAttribute('data_criacao');
    }

    
    /**
     * Method addItem
     * Add a Item to the Pesquisa
     * @param $object Instance of Item
     */
    public function addItem(Item $object)
    {
        $this->items[] = $object;
    }
    
    /**
     * Method getItems
     * Return the Pesquisa' Item's
     * @return Collection of Item
     */
    public function getItems()
    {
        return $this->items;
    }

    
    /**
     * Method getRelacaos
     */
    public function getRelacaos()
    {
        $criteria = new TCriteria;
        $criteria->add(new TFilter('pesquisa_id', '=', $this->id));
        return Relacao::getObjects( $criteria );
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
        $repository = new TRepository('PesquisaItem');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('pesquisa_id', '=', $id));
        $pesquisa_items = $repository->load($criteria);
        if ($pesquisa_items)
        {
            foreach ($pesquisa_items as $pesquisa_item)
            {
                $item = new Item( $pesquisa_item->item_id );
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
    
        // delete the related PesquisaItem objects
        $criteria = new TCriteria;
        $criteria->add(new TFilter('pesquisa_id', '=', $this->id));
        $repository = new TRepository('PesquisaItem');
        $repository->delete($criteria);
        // store the related PesquisaItem objects
        if ($this->items)
        {
            foreach ($this->items as $item)
            {
                $pesquisa_item = new PesquisaItem;
                $pesquisa_item->item_id = $item->id;
                $pesquisa_item->pesquisa_id = $this->id;
                $pesquisa_item->store();
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
        // delete the related PesquisaItem objects
        $repository = new TRepository('PesquisaItem');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('pesquisa_id', '=', $id));
        $repository->delete($criteria);
        
    
        // delete the object itself
        parent::delete($id);
    }


}
