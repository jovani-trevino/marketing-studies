<?php
declare(strict_types=1);

function professional_catalog(): array
{
    $root = dirname(__DIR__) . '/content/';
    return ['areas'=>json_decode((string) file_get_contents($root . 'professional-areas.json'), true, 512, JSON_THROW_ON_ERROR), 'cases'=>json_decode((string) file_get_contents($root . 'professional-cases.json'), true, 512, JSON_THROW_ON_ERROR), 'topics'=>json_decode((string) file_get_contents($root . 'editorial-topics.json'), true, 512, JSON_THROW_ON_ERROR)];
}

function professional_template(string $id): ?array
{
    $catalog = professional_catalog();
    foreach ($catalog['cases'] as $case) {
        if ('case-' . $case['id'] !== $id) continue;
        $body = 'Tipo de caso: ' . $case['evidence_kind'] . ".\n\n## Contexto y reto\n\n" . $case['challenge'] . "\n\n" . $case['story']['context'] . "\n\n## Objetivo\n\n" . $case['story']['objective'] . "\n\n## Mi participación\n\n" . $case['role'] . "\n\n## Intervención\n\n";
        foreach ($case['actions'] as $action) $body .= '- ' . $action . "\n";
        $body .= "\n## Resultados\n\nPeriodo: " . $case['period'] . ".\n\n";
        foreach ($case['metrics'] as [$value,$label,$note]) $body .= '- ' . $value . ' ' . $label . ' · ' . $note . "\n";
        $body .= "\n" . $case['result'] . "\n\n## Cómo leer este trabajo\n\n" . $case['story']['approach'] . "\n\n## Lectura profesional\n\n" . $case['story']['takeaway'] . "\n\n## Alcance de la evidencia\n\n" . $case['limitation'] . "\n\nReferencia: " . $case['source'];
        return ['title'=>$case['title'], 'excerpt'=>$case['summary'], 'content_markdown'=>$body, 'category'=>'casos-de-estudio'];
    }
    foreach ($catalog['topics'] as $topic) {
        if ($topic['id'] !== $id) continue;
        $body = $id === 'article-01' ? (string) file_get_contents(dirname(__DIR__) . '/content/article-01.md') : "## Pregunta de negocio\n\n\n## Evidencia y contexto\n\n\n## Interpretación\n\n\n## Aplicación práctica\n\n\n## Fuentes y límites\n\n";
        return ['title'=>$topic['title'], 'excerpt'=>$id === 'article-01' ? 'Cómo interpretar exposición, interacción y conversaciones sin confundir actividad digital con resultados comerciales.' : '', 'content_markdown'=>$body, 'category'=>''];
    }
    return null;
}

function professional_tasks(): array
{
    return [
        'cv'=>'CV y posicionamiento profesional',
        'portfolio'=>'Casos y portafolio público anonimizado',
        'signature'=>'Firma de correo y presentación personal',
        'identity'=>'Perfiles profesionales, ORCID y enlaces',
        'credentials'=>'Localizar credenciales y comprobar fechas de vigencia',
        'associations'=>'Verificar membresías y asociaciones actuales',
        'branding'=>'Localizar originales de renovación de identidad',
        'photos'=>'Sesión de retratos profesionales actuales',
        'social'=>'Completar revisión histórica de cuentas sociales',
    ];
}

function professional_workspace(): void
{
    $catalog = professional_catalog();
    $tasks = professional_tasks();
    $ids = array_merge(array_keys($tasks), array_column($catalog['topics'], 'id'));
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        if (!verify_csrf()) { http_response_code(403); echo 'Formulario expirado.'; return; }
        $id = is_string($_POST['task'] ?? null) ? $_POST['task'] : '';
        $status = is_string($_POST['status'] ?? null) ? $_POST['status'] : '';
        if (!in_array($id, $ids, true) || !in_array($status, ['pending','working','review','done'], true)) { http_response_code(422); echo 'Estado no válido.'; return; }
        save_setting('professional_task_' . $id, $status);
        flash('Avance actualizado.', 'success');
        redirect_to(url_path('/admin/professional#progress-' . rawurlencode($id)), 303);
    }
    view('admin/professional', ['metaTitle'=>'Mi desarrollo profesional', 'catalog'=>$catalog, 'tasks'=>$tasks, 'progress'=>settings()]);
}
