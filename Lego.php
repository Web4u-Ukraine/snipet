<?php
/*** налаштування ***/
$path=$_SERVER['DOCUMENT_ROOT'];//папка де лежать основні файли, по замовчування - корінь сайту
$modules='modules';//папка, де лежать модулі, по замовчування - modules
$not=['info.html'];//файли, які не потрібно сканувати, вказуються через кому ['file1.html', 'file2.html']
/*** кінець ***/

$files=scandir($path);

for ($i=2;$i<count($files);$i++){
    $file=$files[$i];
    if (!in_array($file, $not)&&substr_count($file, 'html')>0){
        $str=file_get_contents($path.'/'.$file);
        preg_match_all('|\[_(.*?)_\]|is', $str, $mod);
        foreach ($mod[1] as $block){
            $str_replace=file_get_contents($path.'/'.$modules.'/'.$block.'.html');
            $ch=explode('<!-- [_'.$block.'_] -->', $str);
            $top=$ch[0];
            $ch2=explode('<!-- [/'.$block.'] -->', $ch[1]);
            $footer=$ch2[1];
            $new_str=$top.'<!-- [_'.$block.'_] -->'.$str_replace.'<!-- [/'.$block.'] -->'.$footer;
            file_put_contents($path.'/'.$file, $new_str);
            $str=$new_str;
        }
    }
}

echo 'Success';