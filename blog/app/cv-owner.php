<?php

declare(strict_types=1);

function render_owner_cv(string $method): void
{
    $profiles = json_decode((string) file_get_contents(__DIR__ . '/cv-profiles.json'), true, flags: JSON_THROW_ON_ERROR);
    $key = $_GET['profile'] ?? 'strategy';
    if (!is_string($key) || !array_key_exists($key, $profiles)) {
        http_response_code(404);
        if ($method !== 'HEAD') echo 'No existe ese perfil de currículum.';
        return;
    }
    header('Content-Type: text/html; charset=UTF-8');
    if ($method === 'HEAD') return;

    $profile = $profiles[$key];
    $html = (string) file_get_contents(dirname(__DIR__, 2) . '/cv/cv.html');
    $options = '';
    foreach ($profiles as $id => $entry) {
        $options .= '<option value="' . e($id) . '"' . ($id === $key ? ' selected' : '') . '>' . e($entry['label']) . '</option>';
    }
    $controls = '<section class="profile-controls cv-page print-hidden mx-auto mb-4 rounded-[18px] border border-brand-100 bg-white/80 p-4">'
        . '<p><a href="' . e(url_path('/admin')) . '">← Volver al CMS</a></p><h2>Versiones privadas del CV</h2>'
        . '<p>' . e($profile['description']) . '</p><form action="' . e(url_path('/admin/cv')) . '" method="get">'
        . '<label for="profile">Área profesional</label> <select id="profile" name="profile">' . $options . '</select> '
        . '<button type="submit">Ver versión</button></form></section>';
    $html = str_replace('<!-- OWNER_CV_CONTROLS -->', $controls, $html);
    $html = str_replace('<title>Jovani Treviño | CV</title>', '<title>Jovani Treviño | ' . e($profile['label']) . '</title>', $html);

    $nodes = [
        'cv-tagline' => $profile['tagline'], 'cv-summary' => $profile['summary'], 'cv-profile-copy' => $profile['profileCopy'],
        'cv-competencies' => $profile['competencies'], 'cv-tools' => $profile['tools'],
    ];
    foreach ($profile['experience'] as $employer => $experience) {
        $nodes[$employer . '-title'] = $experience['title'];
        $nodes[$employer . '-bullets'] = $experience['bullets'];
    }
    foreach ($nodes as $id => $value) {
        $content = is_array($value)
            ? implode('', array_map(static fn (string $item): string => '<li>' . e($item) . '</li>', $value))
            : e($value);
        $pattern = '~(<(p|ul|h[1-6])\\b[^>]*\\bid="' . preg_quote($id, '~') . '"[^>]*>).*?(</\\2>)~s';
        $html = preg_replace_callback($pattern, static fn (array $matches): string => $matches[1] . $content . $matches[3], $html, 1);
    }
    echo $html;
}
