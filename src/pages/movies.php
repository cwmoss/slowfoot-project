<page-query>
    movie()
    # order(familyname) 
    limit(50)
</page-query>

<?php

layout('default');
//paginate("artist");

// artist(tags in ["top"]) order(familyname) limit(20)
?>

<h1>Artists</h1>


<section>
    <?foreach ($collection['items'] as $art) {?>

        <aside>
            <h3><a href="<?=$path($art)?>"><?=$art['original_title']?></a></h3>
            <p><?=$art['release_date']?><br>
           
            </p>
            
        </aside>
    <?php
}?>

    </section>

<?=$partial('pagination', ['page' => 'movies', 'info' => $collection['info']])?>

