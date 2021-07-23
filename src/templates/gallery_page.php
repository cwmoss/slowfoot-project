<?php
layout('default');

//$links = query('*[_id=="$id"]{articles[]->, pix[]->}[0]', ['id' =>$_id]);
$links = [];
$title = $page['title'];
$subtitle = 'untertitel';
$images = [];
if ($page['gallery'] && $page['gallery']['images']) {
    $images = $page['gallery']['images'];
}
$local_img = $asset_from_file('DSC_0491-2.jpg');
?>
<article>
    
<h2><?=$title?>  </h2>

<p></p>

<p><?=markdown($text)?></p>

<p><?=$image($local_img, 'gallery')?></p>

<p><?=$image($local_img, ['profile'=>'gallery', 'caption'=>'motion'])?></p>

<p><?=$image($local_img, 'small')?></p>

<p><?=$image($local_img, ['s' => '900x100', 'mode' => 'fill', 'fp' => [0.4862745098039215, 0]])?></p>
<p><?=$image($local_img, ['s' => '900x100', 'mode' => 'fill', 'fp' => [0.4862745098039215, 0.49]])?></p>
<p><?=$image($local_img, ['s' => '100x900', 'mode' => 'fill', 'fp' => [0.4862745098039215, 0.49]])?></p>
<p><?=$image($local_img, ['s' => '100x900', 'mode' => 'fill', 'fp' => [0.4862745098039215, 0]])?></p>
<p><?=$image($local_img, ['s' => '600x300', 'mode' => 'fill'])?></p>



<ul>
	<?if ($images) {
    foreach ($images as $img) {
        $asset = $ref($img['asset']); ?>
		<li>
            <img src="<?=$asset['url']?>?w=800&q=90" width="800">
            <p><?=$img['caption']?></p>
        </li>
	<?php
    }
}?>
</ul>



</article>