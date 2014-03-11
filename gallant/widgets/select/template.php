<?
$data = $this->data();
$select = $this->opt('select');
if($data) foreach($data as $item){
	$key = $item[$this->opt('key')];
	$value = $item[$this->opt('value')];
	$selected = ($key == $select)?' selected="selected"':'';
	?>
	<option value="<?=$key?>"<?=$selected?>><?=$value?></option>
	<?
}