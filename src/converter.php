<?php

/**
 * This parses the FFXIVAppIcons.svg and created CSS and HTML for you.
 * run: php converter.php
 */

// remove these if they exist
@unlink(__DIR__.'/xivicon.css');
@unlink(__DIR__.'/xivicon.html');
@unlink(__DIR__.'/xivicon.json');

// parse SVG
$xml = file_get_contents(__DIR__.'/FFXIVAppIcons.svg');
$xml = new SimpleXMLElement($xml);

// convert to json and save because PHP XML capabilities are trash.
// also JSON is nicer to read
$xml = json_encode($xml, JSON_PRETTY_PRINT);
file_put_contents( __DIR__.'/xivicon.json',$xml);

// reparse the json
$json = file_get_contents(__DIR__.'/xivicon.json');
$json = json_decode($json);

// grab all the unicodes
$codes = [];
foreach ($json->defs->font->glyph as $glyph) {
    $attr = $glyph->{"@attributes"};
    $name = str_ireplace(" ", "_", $attr->{"glyph-name"});
    
    if (!isset($attr->unicode)) {
        continue;
    }

    $codes[$name] = str_ireplace("\\u", "", json_encode($attr->unicode));
}



// write out CSS + HTML
$html = [];
$css = [];
foreach ($codes as $name => $code) {
    $code     = json_decode($code);
    $cssLine  = ".xiv-{$name}:before{content:'\\". $code ."';}\n";
    $htmlLine = "<li><i class=\"xiv-{$name}\"></i>{$name}</li>\n";

    file_put_contents(__DIR__.'/xivicon.css', $cssLine, FILE_APPEND);
    file_put_contents(__DIR__.'/xivicon.html', $htmlLine, FILE_APPEND);

    $html[] = $htmlLine;
    $css[]  = $cssLine;
}

$template = file_get_contents(__DIR__.'/template.html');
$template = str_ireplace('[[HTML]]', implode('', $html), $template);
$template = str_ireplace('[[CSS]]', implode('', $css), $template);
file_put_contents(__DIR__.'/index.html', $template);

echo "Done!\n\n";
