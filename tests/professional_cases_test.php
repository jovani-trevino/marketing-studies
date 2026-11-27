<?php
declare(strict_types=1);
require dirname(__DIR__) . '/blog/app/helpers.php';
require dirname(__DIR__) . '/blog/app/professional.php';
require dirname(__DIR__) . '/Jovani/cases.php';
$cases=professional_cases(); $areas=professional_areas(); $ids=[]; $checks=0;
$verify=static function(bool $condition,string $message) use (&$checks): void { if(!$condition) throw new RuntimeException($message); $checks++; };
$verify(count($cases)===38 && count($areas)===7,'Portfolio coverage changed unexpectedly.');
foreach($cases as $case) {
    $verify(!isset($ids[$case['id']]) && preg_match('/^[a-z0-9-]+$/',$case['id'])===1,'Case URL is duplicated or unsafe.');
    $ids[$case['id']]=true;
    $verify(isset($areas[$case['area']]),'Unknown discipline.');
    foreach (['context','objective','approach','takeaway'] as $section) $verify(is_string($case['story'][$section] ?? null) && strlen($case['story'][$section])>40,'Incomplete narrative in '.$case['id']);
    $public=json_encode($case,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);
    $verify(!preg_match('/\b(?:Esquivel|Treviñosa|Heaven|iRealty|Publilum|Apotecaria|Advita|MIGOO|Casmigoo|Junco|ITESA|Gvia|PotencIA|Nam[eé]x|Voiture)\b|(?:[A-Z]:\\\\)|@/iu',$public),'Private client identity or path in '.$case['id']);
    $template=professional_template('case-'.$case['id']);
    $verify($template!==null && str_contains($template['content_markdown'],$case['evidence_kind']) && str_contains($template['content_markdown'],$case['limitation']),'Editorial template lost provenance or evidence limits.');
    $verify(str_contains($template['content_markdown'],$case['story']['context']) && str_contains($template['content_markdown'],$case['story']['takeaway']),'CMS template omitted the expanded narrative.');
    if($case['evidence_kind']==='Proyecto académico') $verify(str_contains($case['limitation'],'académico'),'Academic case presented as commercial work.');
    if($case['chart']) {
        $verify($case['chart']['max']>0 && $case['chart']['unit']!=='','Chart has no meaningful scale.');
        foreach($case['chart']['rows'] as [$label,$value]) $verify(is_numeric($value) && $value>=0 && $value<=$case['chart']['max'],'Chart value exceeds its axis.');
    } else $verify(count($case['steps'])===4,'Qualitative case lacks a process diagram.');
    ob_start(); professional_case_chart($case); $chart=ob_get_clean();
    $verify(str_contains($chart,'figcaption') && !str_contains($chart,'NAN'),'Case visual did not render.');
}
foreach($areas as $area=>$label) $verify(count(array_filter($cases,static fn(array $case):bool=>$case['area']===$area))>=5,'Missing cases in '.$area);
echo "PASS {$checks} catalog, confidentiality, editorial and chart checks for 38 cases.\n";
