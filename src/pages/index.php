<page-query paginate="20">
    artist(familyname matches "F*")
    order(familyname) 
    limit(50)
</page-query -->

<?php

layout('default');
//paginate("artist");
$items = $q("SELECT body FROM docs WHERE _type='artist' AND json_extract(body, '$.familyname') like 'F%'");
// artist(tags in ["top"]) order(familyname) limit(20)
#print_r($page);
?>

<h1>Artists</h1>
<? #print_r($page);
?>

<section>
    <?foreach ($page as $art) {
    $work = $ref($art['works'][0]); ?>
        <aside>
            <h3><a href="<?=$path($art)?>"><?=$art['firstname']?> <?=$art['familyname']?></a></h3>
            <p><?=$art['birthyear']?><br>
            <span class="lighter"><?=count($art['works'])?> <?=count($art['works']) > 1 ? 'works' : 'work'; ?></span>
            </p>
            <img class="maybenot" src="<?=$work['default_image']?>">
        </aside>
    <?php
}?>

    </section>

<?=$partial('pagination', ['page' => 'index', 'info' => $collection['info']])?>

