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

    private $preco;
    
    
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


    public function preco_for($rel_id, $item_id)
    {
        // load the related Item objects
        $repository = new TRepository('RelacaoItem');
        $criteria = new TCriteria;
        $criteria->add(new TFilter('relacao_id', '=', $rel_id), TExpression::AND_OPERATOR);
        $criteria->add(new TFilter('item_id',    '=', $item_id),TExpression::AND_OPERATOR);
        $teste = $repository->load($criteria);
        return $teste;
        //        if ($relacao_items)
//        {
//            foreach ($relacao_items as $relacao_item)
//            {
//                $item = new Item( $relacao_item->item_id );
//                $this->addItem($item);
//            }
//        }
    }

}
