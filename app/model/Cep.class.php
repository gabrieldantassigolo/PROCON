<?php
class Cep extends TRecord
{
    const TABLENAME = 'public.esic_logradouros';
    const PRIMARYKEY= 'cep';
    const IDPOLICY =  'serial'; // {max, serial} 
    
    private $municipio;
    private $estado;

    
    function get_municipio_nome()
    {
        if (empty($this->municipio))
            $this->municipio = new Municipio($this->id_municipio);
        return $this->municipio->nome;
    }
    function get_estado_nome()
    {
        if (empty($this->estado))
            $this->estado = new Estado($this->id_estado);
        return $this->estado->nome;
    }
}
