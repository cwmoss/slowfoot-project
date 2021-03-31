<?php
layout('default');

//$links = query('*[_id=="$id"]{articles[]->, pix[]->}[0]', ['id' =>$_id]);
//var_dump($links);
$links = [];
$title = $page['originaltitle'] ?? $page['title_de'] ?? $page['title_en'] ?? 'k.a.';
?>
<article>
<h1><?=$title?></h1>

<?if ($page['default_image']) {?>
	<img class="maybenot" data-src="<?=$page['default_image']?>" style="display:none;">
<?}?>

<div class="a-content">



<p><strong><?=$page['title_de'] ?: $page['originaltitle']?></strong><br><br><?=$page['description_de']?></p>

<div class="material">
	<?=$page['material_de']?> <?=$page['measurement_width']?> x <?=$page['measurement_height']?> x <?=$page['measurement_depth']?> <?=$page['measurement_unit']?>
</div>

<p><strong><?=$page['title_en']?></strong><br><br><?=$page['description_en']?></p>

<div class="material">
	<?=$page['material_en']?> <?=$page['measurement_width']?> x <?=$page['measurement_height']?> x <?=$page['measurement_depth']?> <?=$page['measurement_unit']?>
</div>


<div class="production">
	<?=$page['production_date']?><br>
	<?=$page['creditline']?> <?=$page['acquiry_date']?><br>
	<?=$page['exploitation_rights']?>
</div>

<div class="artists">
	<?if ($page['artists']) {
    foreach ($page['artists'] as $artref) {
        $art = $ref($artref); ?>
		<section><a href="<?=$path($art)?>"> <?=$art['firstname']?> <?=$art['familyname']?></a></section>
	<?
    }
}?>
	</div>

<div class="tags">
	<ul class="tags">
		<?//var_dump($page['tags']);?>
		<?if ($page['tags']) {
    foreach ($page['tags'] as $t) {
        $tag = $ref($t); ?>
			<li><a href="<?=$path($tag)?>"><?=$tag['title']?></a></li>
			
		<?
    }
}?>
	</ul>
	
</div>

</div>

</article>

<?php
/*
  "measurement_height":80.5,"inventorynr_sorted":"P 00001\/000","creditline":"erworben\/acquired in","title_en":"Construction Design for an Airport",

       "in_exhibition":"yes","exploitation_rights":"Bildrecht, Wien","material_de":"Bronze, Glas","measurement_depth":85,
       "originaltitle":"Konstruktion f\u00fcr einen Flughafen","default_image":"https:\/\/www.mumok.at\/imageobject.php?objid=1","inventorynr":"P 1\/0","measurement_unit":"cm"}
*/
?>
