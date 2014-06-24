<?
$data = $this->getData();

$select = $this->getOption('select');
$key_select = $this->getOption('key');
$val_select = $this->getOption('value');

if($data) foreach($data as $item){
	if(is_array($item)){
		$key = $item[$key_select];
		$value = $item[$val_select];
	}else if(is_object($item)){
		$key = $item->$key_select;
		$value = $item->$val_select;
	}
	$selected = ($key == $select)?' selected="selected"':'';
	?>
	<option value="<?=$key?>"<?=$selected?>><?=$value?></option>
	<?
}