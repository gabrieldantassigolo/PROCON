$teste = new THBox;
$teste->add($backButton)->style = 'display: inline-table; margin-left: 10px; float: left';
$teste->add($this->saveButton)->style = 'display: inline-table; margin-right: 150px;';

// vertical box container
$gridpack = new TVBox;
$gridpack->style = 'width: 100%';
// $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
$gridpack->add($this->formgrid)->style= 'width:100%';
$gridpack->add($teste)->style = 'text-align: center; justify-content: space-between; background:whiteSmoke; border:1px solid #cccccc; padding: 10px';