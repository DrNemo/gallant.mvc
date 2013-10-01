<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="<?=G::lang()->getLang()?>">
<head>
    <title><?=$this->getMeta('title')?></title>
    <meta name="Description" content="<?=$this->getMeta('description')?>" />
    <meta name="Keywords" content="<?=$this->getMeta('keywords')?>" />

    <?=$this->includeJs()?>
    <?=$this->includeCss()?>
    </head>
    <body><?=$content?></body>
<body>