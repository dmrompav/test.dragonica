<?php
$languages = [];
$languages_string = '';
$html = file_get_contents(__DIR__.'/templates/1.html');
$htaccess = explode("\n", file_get_contents(__DIR__.'/.htaccess'));
foreach ($htaccess as $line) {
    if (preg_match('/^RewriteRule \^(\w{2}).{0,}/', $line, $match)) {
        if (isset($match[1]) && file_exists(__DIR__.'/translates/'.$match[1].'.json')) {
            array_push($languages, $match[1]);
            $lang_icon = $match[1];
            if ($match[1] == 'en') $lang_icon = 'gb';
            $languages_string .= '<li class="plain-link"><a href="/'.$match[1].'"><span class="flags flag-icon flag-icon-'.$lang_icon.'"></span></a></li>';
        }
    }
}
$country = strtolower($_SERVER['HTTP_CF_IPCOUNTRY']);
$request = strtolower(preg_replace('/\//', '', $_SERVER['REQUEST_URI']));
if ($request == '/' || !in_array($request, $languages)) {
    // detect language for this $country
    $lang = 'ru';
    header('Location: /'.$lang);
    exit;
} else {
    // translation
    $translation = json_decode(file_get_contents(__DIR__.'/translates/'.$request.'.json'), true);
    // preparing html
    $html = preg_replace('/%play_link%/', $translation['config']['play_link'], $html);
    $html = preg_replace('/%year%/', date('Y', time()), $html);
    $html = preg_replace('/%languages_string%/', $languages_string, $html);
    $html = preg_replace('/%script%/', "var translation = ".json_encode($translation['translation_js']).";", $html);
    foreach ($translation['translation'] as $key => $value) {
        $html = preg_replace('/%'.$key.'%/', $value, $html);
    }
    // result
    echo $html;
}
