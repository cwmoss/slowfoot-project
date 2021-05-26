<page-query>
    artist(familyname matches "F*")
    order(familyname) 
    limit(3)
</page-query>

<?php

layout('default');
//paginate("artist");

// artist(tags in ["top"]) order(familyname) limit(20)
?>

<h1>Artists</h1>


<section>
    <?foreach ($collection['items'] as $art) {
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

