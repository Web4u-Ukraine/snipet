<?php
switch ($_POST['flag']){
    case 'list_files':
        $str=file_get_contents('https://cdnjs.com'.$_POST['url'].'/'.$_POST['value']);
        preg_match_all('|<tr class="library">(.*?)<\/tr>|is', $str, $opt);
        foreach ($opt[1] as $txt){
            $ff=strip_tags($txt);
            ?>
            <div class="well text-left"><input type="checkbox" value="<?= $ff ?>" name="conect[]"> <?= $ff ?></div>
            <?
        }
        break;

    case 'version':
        $str=file_get_contents('https://cdnjs.com'.$_POST['value']);
        preg_match_all('|<select class="form-control version-selector" style="float: right;">(.*?)<\/select>|is', $str, $opt);
        echo '<option value="" selected>--</option>'.$opt[1][0];
        break;
}