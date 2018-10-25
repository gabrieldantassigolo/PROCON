<?php
class Estado extends TRecord
{
    const TABLENAME = 'public.esic_estados';
    const PRIMARYKEY = 'id';
    const IDPOLICY = 'max'; // {max, serial}

}
