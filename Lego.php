<?php
/*** налаштування ***/

/*** для оновлення модулів ***/
$path = $_SERVER['DOCUMENT_ROOT'];//папка де лежать основні файли, по замовчування - корінь сайту
$modules = 'modules';//папка, де лежать модулі, по замовчування - modules
$not = ['info.html'];//файли, які не потрібно сканувати, вказуються через кому ['file1.html', 'file2.html']

/*** для підключення скриптів ***/
$path_css='css';
$path_js='js';
$path_img='img';
$file_replace_css='modules/header.html';
$file_replace_js='modules/footer.html';

/*** кінець ***/
?>
<link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.2/css/select2.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.0.0-beta1/jquery.min.js" type="text/javascript"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/js/bootstrap.min.js" type="text/javascript"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.2/js/select2.min.js" type="text/javascript"></script>
<style>
    .content{
        width: 960px;
        margin: 30px auto;
    }
</style>
<script>
    $(function(){
        $(".select").select2();

        $("#name_sk").change(function(){
            $.ajax({
                url: 'Ajax.php',
                type: 'post',
                data: {flag: 'version', value: $(this).val()},
                success: function(res){
                    $("#version").html(res).select2();
                }
            })
        });

        $("#version").change(function(){
            $.ajax({
                url: 'Ajax.php',
                type: 'post',
                data: {flag: 'list_files', value: $(this).val(), url: $("#name_sk").val()},
                success: function(res){
                    $("#result").html(res);
                    $("#btn-final").removeAttr('disabled');
                }
            })
        });
    });
</script>
<div class="content text-center">
<? if (!$_REQUEST['action']) { ?>
    <a href="/Lego.php?action=msp" class="btn btn-success">Оновити модулі</a>&nbsp;&nbsp;&nbsp;
    <a href="/Lego.php?action=cnm" class="btn btn-info">Підключити скрипт</a>
    <?php
} else {
    ?>
    <a href="/Lego.php" class="btn btn-primary">На головну</a>
    <br/>
    <br/>
    <br/>
    <?
}

if ($_REQUEST['action']=='cnm'){
    if (!$_REQUEST['step']) {
        $str = file_get_contents('https://cdnjs.com/libraries/');
        preg_match_all('|<tr(.*?)<\/tr>|is', $str, $sk);
        foreach ($sk[1] as $mod) {
            preg_match_all('|href="(.*?)">|is', $mod, $url);
            preg_match_all('|">(.*?)<\/a>|is', $mod, $name);
            $arr[$url[1][0]] = trim(strip_tags($name[1][0])) == '' ? '--' : trim(strip_tags($name[1][0]));
        }
        ?>
        <form action="/Lego.php?action=cnm&step=final" method="post" class="form-horizontal" id="form-add-sk">
            <div class="form-group">
                <label class="col-md-4">Виберіть потрібний скрипт</label>
                <div class="col-md-8">
                    <select class="form-control select" id="name_sk">
                        <? foreach ($arr as $k => $v) {
                            ?>
                            <option value="<?= $k ?>"><?= $v ?></option>
                            <?
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-4">Виберіть потрібну версію</label>
                <div class="col-md-8">
                    <select class="form-control select" id="version">

                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-4">Виберіть потрібні файли</label>
                <div class="col-md-8" id="result">

                </div>
            </div>

            <button class="btn btn-success" id="btn-final" disabled>Підключити</button>
        </form>
        <?
    } else if ($_REQUEST['step']=='final'){
        foreach ($_POST['conect'] as $file){
            $file=trim($file);
            if (substr_count($file, '.css')>0||substr_count($file, '.map')>0){
                $name=explode('/', $file);
                $name=trim(array_pop($name));
                $str=file_get_contents($file);
                file_put_contents($path_css.'/'.$name, $str);
                $header=file_get_contents($file_replace_css);
                $new_header=str_replace('</head>', '<link href="'.$path_css.'/'.$name.'" rer="stylesheet"></head>', $header);
                file_put_contents($file_replace_css, $new_header);
            } else if (substr_count($file, '.js')>0){
                $name=explode('/', $file);
                $name=trim(array_pop($name));
                $str=file_get_contents($file);
                file_put_contents($path_js.'/'.$name, $str);
                $header=file_get_contents($file_replace_js);
                $new_header=str_replace('</body>', '<script src="'.$path_js.'/'.$name.'" type="text/javascript"></script></body>', $header);
                file_put_contents($file_replace_js, $new_header);
            } else {
                $name=explode('/', $file);
                $name=trim(array_pop($name));
                copy($file, $path_img.'/'.$name);
            }
        }

        echo 'Готово! Файли підключені';
    }
}

if ($_REQUEST['action']=='msp') {
    $files = scandir($path);

    for ($i = 2; $i < count($files); $i++) {
        $file = $files[$i];
        if (!in_array($file, $not) && substr_count($file, 'html') > 0) {
            $str = file_get_contents($path . '/' . $file);
            preg_match_all('|\[_(.*?)_\]|is', $str, $mod);
            foreach ($mod[1] as $block) {
                $str_replace = file_get_contents($path . '/' . $modules . '/' . $block . '.html');
                $ch = explode('<!-- [_' . $block . '_] -->', $str);
                $top = $ch[0];
                $ch2 = explode('<!-- [/' . $block . '] -->', $ch[1]);
                $footer = $ch2[1];
                $new_str = $top . '<!-- [_' . $block . '_] -->' . $str_replace . '<!-- [/' . $block . '] -->' . $footer;
                file_put_contents($path . '/' . $file, $new_str);
                $str = $new_str;
            }
        }
    }

    echo 'Модулі оновлені';
}
?>
</div>
