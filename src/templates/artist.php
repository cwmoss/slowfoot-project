<?php
layout('default');

//$links = query('*[_id=="$id"]{articles[]->, pix[]->}[0]', ['id' =>$_id]);
$links = [];
$subtitle = 'untertitel';
// $works = $query(['_type' => 'work', 'artists._ref' => $_id]);
//var_dump($works);

if ($page['works'] ?? null) {
    $works = array_map(function ($w) use ($ref) {
        return $ref($w);
    }, $page['works']);
}
?>
<article>
    <h1><?= $page['firstname'] ?> <?= $page['familyname'] ?> </h1>

    <div class="a-content">
        <p>geb. <?= $page['birthyear'] ?> <?= $page['cityofbirth_de'] ?>
            <? if ($page['deathyear']) { ?><br>gest. <?= $page['deathyear'] ?> <?= $page['cityofdeath_en'] ?><? } ?>
        </p>


        <ul>
            <? if ($works) {
                foreach ($works as $work) {
                    $title = $work['originaltitle'] ?? $work['title_de'] ?? $work['title_en'] ?? 'k.a.'; ?>
                    <li><a href="<?= $path($work) ?>"><?= $title ?></a></li>
            <?php
                }
            } ?>
        </ul>
    </div>


</article>