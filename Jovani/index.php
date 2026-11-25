<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

jovani_boot_blog('es');
jovani_send_noindex_headers();

$site = $GLOBALS['site'];
$locale = $GLOBALS['locale'];
$socialLinks = social_links($site);

$featured = featured_post();
$featuredId = $featured ? (int) $featured['id'] : 0;
$recentPool = published_posts(9, 0, $featuredId);
$latestPosts = array_values(array_filter(
    $recentPool,
    static fn (array $post): bool => (($post['category_canonical_slug'] ?? $post['category_slug'] ?? '') !== 'casos-de-estudio')
));
if (count($latestPosts) < 3) {
    $latestPosts = $recentPool;
}
$latestPosts = array_slice($latestPosts, 0, 3);

$siteTitle = 'Jovani Treviño | Marketing Intelligence, Growth & Digital Craft';
$description = 'Sitio oficial de Jovani Treviño: estrategia de marketing, performance, SEO, creatividad digital y experiencias web con enfoque comercial.';
$logo = media_url($site['logo_url'] ?? '/static/brand/marketing-studies-logo.png');
$favicon = media_url('/static/brand/favicon.png');
$portraitUrl = jovani_local_asset_url('assets/profile-avatar.png');
$studioPortraitUrl = jovani_local_asset_url('assets/profile-studio.png');
$homeUrl = jovani_app_url('/');
$cvUrl = jovani_app_url('/cv');
$blogUrl = url_path('/', $locale);
$contactEmail = trim((string) ($site['contact_email'] ?? '')) ?: 'jovani@marketing-studies.com';
$orcidId = '';
$orcidProfileUrl = $orcidId !== '' ? 'https://orcid.org/' . $orcidId : 'https://orcid.org';

$heroStats = [
    ['value' => '9+', 'label' => 'anos construyendo estrategia, performance y ejecucion digital'],
    ['value' => '4', 'label' => 'frentes activos entre growth, SEO, paid media y web'],
    ['value' => '3', 'label' => 'industrias trabajadas con criterio comercial y posicionamiento'],
];

// Add future certifications by appending another item to this array.
$certifications = [
    [
        'name' => 'Google Ads',
        'short' => 'Google Ads',
        'type' => 'Certificacion',
        'issuer' => 'Google Skillshop',
        'issued' => 'Certificado profesional',
        'credential_id' => '',
        'description' => 'Acreditacion enfocada en planeacion, activacion y optimizacion de campanas dentro del ecosistema Google.',
        'logo' => jovani_local_asset_url('assets/certs/google-ads.png'),
        'certificate_image' => jovani_local_asset_url('assets/certs/google-ads.png'),
        'href' => 'https://skillshop.exceedlms.com',
    ],
    [
        'name' => 'Google Analytics',
        'short' => 'Google Analytics',
        'type' => 'Certificacion',
        'issuer' => 'Google Skillshop',
        'issued' => 'Certificado profesional',
        'credential_id' => '',
        'description' => 'Base tecnica para lectura de comportamiento digital, medicion de eventos y toma de decisiones informadas.',
        'logo' => jovani_local_asset_url('assets/certs/google-analytics.png'),
        'certificate_image' => jovani_local_asset_url('assets/certs/google-analytics.png'),
        'href' => 'https://skillshop.exceedlms.com',
    ],
    [
        'name' => 'Microsoft Advertising',
        'short' => 'Microsoft Ads',
        'type' => 'Certificacion',
        'issuer' => 'Microsoft Advertising Learning Lab',
        'issued' => 'Certificado profesional',
        'credential_id' => '',
        'description' => 'Conocimiento validado para publicidad en buscadores y plataformas del ecosistema Microsoft.',
        'logo' => jovani_local_asset_url('assets/certs/microsoft-advertising.png'),
        'certificate_image' => jovani_local_asset_url('assets/certs/microsoft-advertising.png'),
        'href' => 'https://learninglab.about.ads.microsoft.com',
    ],
];

$professionalAssociations = [
    [
        'name' => 'Colegio de Comunicadores del Estado de Nuevo Leon',
        'short' => 'CCNL',
        'type' => 'Asociacion profesional',
        'description' => 'Vinculo con comunidad profesional de comunicacion, mercadotecnia y practica colegiada en Nuevo Leon.',
        'logo' => jovani_local_asset_url('assets/certs/ccnl.png'),
        'href' => 'https://colegiocomunicadoresnl.org',
    ],
    [
        'name' => 'American Marketing Association',
        'short' => 'AMA',
        'type' => 'Asociacion internacional',
        'description' => 'Comunidad global para actualizacion profesional, pensamiento estrategico y estandares de practica en marketing.',
        'logo' => jovani_local_asset_url('assets/certs/ama.png'),
        'href' => 'https://www.ama.org',
    ],
];

$credentialGroups = [
    [
        'id' => 'asociaciones',
        'title' => 'Asociaciones profesionales',
        'copy' => 'Comunidades que refuerzan actualizacion, criterio profesional y vinculacion con la industria.',
        'items' => $professionalAssociations,
    ],
    [
        'id' => 'certificaciones',
        'title' => 'Certificaciones',
        'copy' => 'Acreditaciones orientadas a pauta, medicion y performance digital.',
        'items' => $certifications,
    ],
];

$credentials = array_merge($certifications, $professionalAssociations);
$credentialsSummary = [
    'count' => count($professionalAssociations) . ' / ' . count($certifications),
    'headline' => 'Asociaciones / certificaciones',
    'copy' => 'Un bloque listo para escalar con nuevas acreditaciones, diplomas, membresias o verificaciones profesionales.',
];

$proofCards = [
    [
        'title' => 'Vision estrategica con ejecucion real',
        'copy' => 'No separo planeacion de operacion. Pienso en objetivos, embudo, mensaje, experiencia y rendimiento como una sola conversacion.',
    ],
    [
        'title' => 'Lectura comercial con sensibilidad creativa',
        'copy' => 'Trabajo marcas y piezas con criterio visual, pero siempre conectadas a posicionamiento, conversion o crecimiento.',
    ],
    [
        'title' => 'Perfil hibrido para equipos pequenos o versatiles',
        'copy' => 'Puedo entrar desde performance, SEO, contenido, UX o front-end y sumar valor sin perder consistencia entre frentes.',
    ],
];

$aboutPoints = [
    'Especializado en Marketing Intelligence, performance, SEO, paid media y sistemas de contenido.',
    'Capaz de traducir datos, audiencias y contexto competitivo en decisiones ejecutables.',
    'Experiencia construyendo sitios, landings, creativos y activos digitales con enfoque de conversion.',
    'Interes genuino por el craft visual, la claridad narrativa y la consistencia de marca.',
];

$cvFocusAreas = [
    [
        'title' => 'Marketing Intelligence & Performance',
        'copy' => 'Version central para roles de estrategia, growth, performance, demand generation y direccion digital.',
        'points' => ['Analitica aplicada', 'Paid media', 'Optimización comercial'],
    ],
    [
        'title' => 'SEO & Content Performance',
        'copy' => 'Lectura enfocada en visibilidad organica, arquitectura de contenido, experiencia y conversion desde buscadores.',
        'points' => ['SEO on-page', 'Content systems', 'Search intent'],
    ],
    [
        'title' => 'Paid Marketing Specialist',
        'copy' => 'Narrativa para adquisicion, media buying, testing creativo, calidad del lead y eficiencia de inversion.',
        'points' => ['Meta Ads', 'Google Ads', 'ROAS / CAC'],
    ],
    [
        'title' => 'Front-End Junior & Digital Experience',
        'copy' => 'Perfil que conecta HTML, CSS, Bootstrap, UX basica y criterio visual con objetivos de marketing.',
        'points' => ['Responsive UI', 'Landing pages', 'UX thinking'],
    ],
];

$specialties = [
    [
        'icon' => 'bi bi-graph-up-arrow',
        'title' => 'Growth & Performance',
        'copy' => 'Campanas, audiencias, medicion y optimizacion para aterrizar crecimiento con lectura real de negocio.',
    ],
    [
        'icon' => 'bi bi-search-heart',
        'title' => 'SEO & Content Systems',
        'copy' => 'Estructura, semantica, experiencia y contenido para construir visibilidad organica con valor comercial.',
    ],
    [
        'icon' => 'bi bi-badge-ad',
        'title' => 'Paid Media & Creative Testing',
        'copy' => 'Ejecucion de Meta Ads, Google Ads y TikTok Ads con enfoque en mensaje, relevancia, presupuesto y rendimiento.',
    ],
    [
        'icon' => 'bi bi-window-sidebar',
        'title' => 'Web, UX & Front-End',
        'copy' => 'Experiencias responsive, landings y piezas digitales que comunican mejor, se sienten actuales y convierten mejor.',
    ],
];

$impactStats = [
    [
        'value' => '9+',
        'label' => 'anos desarrollando estrategia, performance, contenido y activos digitales con enfoque comercial.',
    ],
    [
        'value' => '3',
        'label' => 'contextos de negocio trabajados entre industria musical, automotriz y real estate.',
    ],
    [
        'value' => '4',
        'label' => 'lecturas profesionales activables para presentar mejor tu valor segun la vacante.',
    ],
    [
        'value' => (string) count($latestPosts),
        'label' => 'articulos recientes que refuerzan tu capa editorial dentro de Marketing Studies.',
    ],
];

$caseHighlights = [
    [
        'eyebrow' => 'Growth musical',
        'title' => 'Expansion de audiencias para proyecto artistico',
        'summary' => 'Integracion de pauta, contenido, distribucion digital y presencia visual para sostener relevancia alrededor de lanzamientos y comunidad.',
        'points' => [
            'Campanas multicanal para promocion artistica y alcance con intencion.',
            'Operacion de streaming y activos digitales con lectura de consumo y tendencias.',
            'Coherencia entre identidad, contenido y expansion de audiencia.',
        ],
    ],
    [
        'eyebrow' => 'Performance comercial',
        'title' => 'Captacion de prospectos con criterio de conversion',
        'summary' => 'Arquitectura de campanas y lectura de comportamiento para mejorar relevancia publicitaria, calidad del lead y eficiencia de inversion.',
        'points' => [
            'Diseno de campanas en Meta Ads y Google Ads para demanda y prospectacion.',
            'Analisis de audiencias, embudos y mensajes para decisiones mas finas.',
            'Coordinacion entre performance, contenido y objetivos comerciales.',
        ],
    ],
    [
        'eyebrow' => 'UX y relacion comercial',
        'title' => 'Experiencia digital y seguimiento para real estate',
        'summary' => 'Trabajo orientado a captacion, relacion con prospectos y materiales de marca para reforzar confianza durante el proceso comercial.',
        'points' => [
            'Email marketing y Google Ads para adquisicion y nutricion.',
            'UX, identidad visual y materiales de soporte para ventas.',
            'Seguimiento comercial con enfoque en experiencia del usuario.',
        ],
    ],
];

$experience = [
    [
        'company' => 'Grupo Publilum',
        'period' => '2016 - Actual',
        'type' => 'Principal',
        'tab' => 'laboral',
        'title' => 'Estrategia de Growth Marketing y Expansión de Audiencias',
        'company_summary' => 'Grupo con operación comercial y proyectos de marca donde he conectado comunicación, presencia digital y crecimiento de audiencias.',
        'objective' => 'Fortalecer posicionamiento, comunidad y rendimiento digital mediante campañas, contenidos y activos orientados a crecimiento.',
        'intro' => 'Proyecto principal donde he integrado estrategia digital, promoción artística, pauta, contenidos, presencia web y lectura de audiencias para fortalecer el crecimiento de una marca musical con operación multicanal.',
        'functions' => [
            'Planeación de campañas de lanzamiento, posicionamiento y promoción musical en canales digitales.',
            'Gestión de pauta en Meta Ads, TikTok Ads y Google Ads con enfoque en alcance, interacción y crecimiento de comunidad.',
            'Coordinación de contenidos, activos visuales, agenda digital, streaming y presencia web.',
        ],
        'wins' => [
            'Construcción de una presencia digital más clara, consistente y orientada a comunidad.',
            'Mayor estructura para lanzamientos, comunicación de marca y activaciones de audiencia.',
            'Integración de datos, contenido y pauta para tomar mejores decisiones de promoción.',
        ],
    ],
    [
        'company' => 'Althacare',
        'period' => 'Proyecto principal',
        'type' => 'Principal',
        'tab' => 'laboral',
        'title' => 'Marketing Digital, Performance y Estructura Comercial',
        'company_summary' => 'Marca de servicios donde la confianza, la claridad comercial y la comunicación digital son claves para generar oportunidades.',
        'objective' => 'Ordenar la comunicación digital y activar campañas para captación, confianza y seguimiento comercial.',
        'intro' => 'Participación enfocada en fortalecer la presencia digital de la marca, ordenar mensajes comerciales y activar campañas orientadas a captación, confianza y seguimiento de prospectos.',
        'functions' => [
            'Desarrollo de estrategia de comunicación digital para servicios con alto componente de confianza.',
            'Estructuración de contenidos, piezas promocionales y activos para campañas de captación.',
            'Lectura de audiencias, mensajes y puntos de conversión para mejorar la comunicación comercial.',
        ],
        'wins' => [
            'Mayor claridad en la propuesta de valor y en la forma de presentar los servicios.',
            'Mejor alineación entre contenido, pauta y objetivos comerciales.',
            'Base digital más ordenada para campañas, seguimiento y toma de decisiones.',
        ],
    ],
    [
        'company' => 'Cajun Publicidad',
        'period' => 'Proyecto principal',
        'type' => 'Principal',
        'tab' => 'laboral',
        'title' => 'Estrategia Digital, Contenido y Performance',
        'company_summary' => 'Empresa de publicidad y comunicación donde el reto fue traducir capacidades creativas en mensajes y activos comerciales.',
        'objective' => 'Alinear creatividad, pauta y presencia digital para comunicar servicios con mayor claridad y generar oportunidades.',
        'intro' => 'Experiencia orientada a campañas, creatividad digital y activos de comunicación para conectar mejor la propuesta de valor con audiencias y objetivos comerciales.',
        'functions' => [
            'Planeación de campañas digitales y piezas de comunicación para promoción de servicios.',
            'Desarrollo de contenidos, mensajes y activos visuales con enfoque comercial.',
            'Optimización de presencia digital y seguimiento de oportunidades generadas por pauta.',
        ],
        'wins' => [
            'Comunicación más estructurada para presentar capacidades y servicios.',
            'Mejor integración entre creatividad, pauta y conversión.',
            'Activos digitales más claros para apoyar prospección y ventas.',
        ],
    ],
    [
        'company' => 'iRealty CRM Real Estate',
        'period' => '2014 - 2016',
        'type' => 'Principal',
        'tab' => 'laboral',
        'title' => 'Marketing, CRM y Experiencia Comercial',
        'company_summary' => 'Plataforma y operación inmobiliaria centrada en CRM, captación, relación comercial y seguimiento de prospectos.',
        'objective' => 'Conectar marketing, CRM y experiencia de cliente para mejorar captación, nutrición y seguimiento comercial.',
        'intro' => 'Etapa enfocada en captación, CRM, seguimiento comercial y experiencia digital para el sector inmobiliario, conectando marketing, tecnología y relación con prospectos.',
        'functions' => [
            'Implementación de iniciativas de captación, nutrición y retención de prospectos.',
            'Apoyo en campañas de Google Ads, email marketing, UX e identidad corporativa.',
            'Desarrollo de materiales comerciales y flujos de comunicación para seguimiento de clientes.',
        ],
        'wins' => [
            'Mayor estructura para seguimiento comercial y comunicación con prospectos.',
            'Integración de CRM, campañas y materiales de venta en un mismo proceso.',
            'Refuerzo de la experiencia del cliente dentro del ciclo comercial inmobiliario.',
        ],
    ],
    [
        'company' => 'Publilum Cocinas & Closets',
        'period' => 'Proyecto freelance',
        'type' => 'Freelance',
        'tab' => 'consultoria',
        'title' => 'Presencia Digital y Comunicación Comercial',
        'company_summary' => 'Marca de cocinas y closets personalizados con necesidad de comunicar producto, confianza y capacidad de ejecución.',
        'objective' => 'Mejorar la presentación digital de servicios y productos para apoyar prospección y decisión de compra.',
        'intro' => 'Proyecto enfocado en ordenar la comunicación digital de una marca de cocinas y closets, destacando producto, confianza y capacidad de ejecución.',
        'functions' => [
            'Desarrollo de mensajes comerciales para productos y servicios personalizados.',
            'Organización de activos digitales para mejorar presentación de marca.',
            'Apoyo en contenidos orientados a prospección y confianza.',
        ],
        'wins' => [
            'Comunicación más clara para mostrar propuesta de valor y diferenciadores.',
            'Activos digitales mejor alineados con intención comercial.',
        ],
    ],
    [
        'company' => 'Autos Esquivel',
        'period' => 'Proyecto freelance',
        'type' => 'Freelance',
        'tab' => 'consultoria',
        'title' => 'Marketing Intelligence & Performance',
        'company_summary' => 'Proyecto automotriz enfocado en captación de prospectos, lectura comercial y eficiencia de campañas.',
        'objective' => 'Elevar la eficiencia de inversión y conectar pauta, mensaje y calidad de prospecto.',
        'intro' => 'Proyecto orientado a performance, captación y lectura de conversión para elevar eficiencia de inversión y relevancia comercial en el sector automotriz.',
        'functions' => [
            'Diseño y optimización de campañas enfocadas en demanda y captación de prospectos.',
            'Análisis de audiencias, embudos y conversión para ajustar segmentación, mensaje y presupuesto.',
            'Coordinación de performance y contenido con objetivos comerciales.',
        ],
        'wins' => [
            'Mejor lectura de calidad de prospecto y eficiencia de inversión.',
            'Campañas más ordenadas para conectar pauta, mensaje y oportunidad comercial.',
        ],
    ],
    [
        'company' => 'SmileMe Mty',
        'period' => 'Proyecto freelance',
        'type' => 'Freelance',
        'tab' => 'consultoria',
        'title' => 'Contenido, Captación y Presencia Digital',
        'company_summary' => 'Marca de servicios de salud/estética donde la comunicación debía equilibrar claridad, confianza y conversión.',
        'objective' => 'Construir mensajes y activos digitales que ayudaran a presentar servicios y activar demanda.',
        'intro' => 'Proyecto enfocado en comunicación digital para servicios de salud/estética, cuidando confianza, claridad del mensaje y activación comercial.',
        'functions' => [
            'Estructuración de mensajes para campañas y contenidos digitales.',
            'Apoyo en activos visuales y comunicación orientada a conversión.',
            'Lectura de audiencias para mejorar relevancia de contenidos y promociones.',
        ],
        'wins' => [
            'Mayor claridad en mensajes de captación y presentación de servicios.',
            'Mejor consistencia entre contenido, pauta y confianza de marca.',
        ],
    ],
    [
        'company' => 'Vidriera Treviño',
        'period' => 'Proyecto freelance',
        'type' => 'Freelance',
        'tab' => 'consultoria',
        'title' => 'Presencia Digital y Activos Comerciales',
        'company_summary' => 'Empresa local de servicios donde la prioridad fue mejorar presentación digital, confianza y contacto comercial.',
        'objective' => 'Ordenar la presencia digital y los mensajes comerciales para facilitar prospección local.',
        'intro' => 'Proyecto orientado a fortalecer presencia digital y materiales comerciales para una empresa local con enfoque en servicio, confianza y atención al cliente.',
        'functions' => [
            'Organización de mensajes y activos digitales para presentar servicios.',
            'Apoyo en comunicación comercial y contenido para canales digitales.',
            'Estructuración básica de presencia digital con enfoque local.',
        ],
        'wins' => [
            'Mejor presentación de servicios y comunicación de confianza.',
            'Base digital más clara para prospección y contacto comercial.',
        ],
    ],
];

$experienceTabs = [
    ['id' => 'laboral', 'label' => 'Experiencia laboral'],
    ['id' => 'consultoria', 'label' => 'Consultoría externa'],
];

$approach = [
    [
        'title' => 'Analisis antes de ejecucion',
        'copy' => 'Empiezo por objetivo, contexto, audiencia y fricciones del embudo para no producir piezas sin direccion.',
    ],
    [
        'title' => 'Craft visual con criterio',
        'copy' => 'La forma importa. Me interesa que la experiencia, el contenido y la presencia visual eleven la percepcion de marca.',
    ],
    [
        'title' => 'Optimizacion sostenida',
        'copy' => 'Mido, comparo, itero y documento aprendizajes para que el crecimiento no dependa solo de intuicion o suerte.',
    ],
];

$ethicsPrinciples = [
    [
        'title' => 'Transparencia y atribucion',
        'copy' => 'Presentar credenciales, afiliaciones, colaboraciones y resultados con contexto claro, sin inflar alcances ni ocultar condiciones relevantes.',
    ],
    [
        'title' => 'Datos con consentimiento',
        'copy' => 'Trabajar analitica, CRM, audiencias y automatizaciones respetando permisos, privacidad y uso proporcional de la informacion.',
    ],
    [
        'title' => 'Evidencia antes que promesa',
        'copy' => 'Separar hipotesis, aprendizaje y resultado comprobado para que cada caso o recomendacion pueda auditarse con honestidad.',
    ],
    [
        'title' => 'Respeto profesional',
        'copy' => 'Colaborar con apertura, trato justo y responsabilidad hacia clientes, audiencias, colegas, proveedores y comunidades profesionales.',
    ],
];

$ethicsReferences = [
    [
        'label' => 'ORCID iD',
        'href' => $orcidProfileUrl,
        'copy' => $orcidId !== '' ? $orcidId : 'Listo para vincular un identificador verificado desde el CMS.',
        'icon' => 'bi bi-person-badge',
    ],
    [
        'label' => 'Codigo de conducta ORCID',
        'href' => 'https://info.orcid.org/work-with-us/code-of-conduct/',
        'copy' => 'Referencia oficial sobre conducta etica, integridad y cumplimiento.',
        'icon' => 'bi bi-shield-check',
    ],
    [
        'label' => 'Guia de visualizacion ORCID',
        'href' => 'https://info.orcid.org/documentation/integration-guide/orcid-id-display-guidelines/',
        'copy' => 'Buenas practicas para mostrar un ORCID iD cuando este verificado.',
        'icon' => 'bi bi-link-45deg',
    ],
];

$toolGroups = [
    [
        'title' => 'Ads, SEO y analitica',
        'items' => [
            ['name' => 'Meta Ads', 'logo' => 'https://cdn.simpleicons.org/meta/0866FF', 'tone' => '#0866ff'],
            ['name' => 'Google Ads', 'logo' => 'https://cdn.simpleicons.org/googleads/4285F4', 'tone' => '#4285f4'],
            ['name' => 'TikTok Ads', 'logo' => 'https://cdn.simpleicons.org/tiktok/111111', 'tone' => '#111111'],
            ['name' => 'Bing Ads', 'icon' => 'bi bi-microsoft', 'tone' => '#008373'],
            ['name' => 'Google Marketing Platform', 'logo' => 'https://cdn.simpleicons.org/googlemarketingplatform/3C4043', 'tone' => '#34a853'],
            ['name' => 'Power BI', 'icon' => 'bi bi-bar-chart-fill', 'tone' => '#f2c811'],
        ],
    ],
    [
        'title' => 'Diseno y contenido',
        'items' => [
            ['name' => 'Photoshop', 'mark' => 'Ps', 'tone' => '#31a8ff'],
            ['name' => 'Illustrator', 'mark' => 'Ai', 'tone' => '#ff9a00'],
            ['name' => 'After Effects', 'mark' => 'Ae', 'tone' => '#9999ff'],
            ['name' => 'Premiere', 'mark' => 'Pr', 'tone' => '#ea77ff'],
            ['name' => 'InDesign', 'mark' => 'Id', 'tone' => '#ff3366'],
            ['name' => 'Fotografia y video', 'icon' => 'bi bi-camera-reels-fill', 'tone' => '#c9853b'],
        ],
    ],
    [
        'title' => 'Web y UX',
        'items' => [
            ['name' => 'HTML5', 'logo' => 'https://cdn.simpleicons.org/html5/E34F26', 'tone' => '#e34f26'],
            ['name' => 'CSS3', 'logo' => 'https://cdn.simpleicons.org/css/1572B6', 'tone' => '#1572b6'],
            ['name' => 'Bootstrap 5', 'logo' => 'https://cdn.simpleicons.org/bootstrap/7952B3', 'tone' => '#7952b3'],
            ['name' => 'Visual Studio Code', 'icon' => 'bi bi-code-square', 'tone' => '#007acc'],
            ['name' => 'Responsive design', 'icon' => 'bi bi-phone-fill', 'tone' => '#0d3d69'],
            ['name' => 'UX thinking', 'icon' => 'bi bi-bezier2', 'tone' => '#c9853b'],
        ],
    ],
    [
        'title' => 'Operacion digital',
        'items' => [
            ['name' => 'Mailchimp', 'logo' => 'https://cdn.simpleicons.org/mailchimp/FFE01B', 'tone' => '#ffe01b'],
            ['name' => 'Bitrix CRM', 'icon' => 'bi bi-diagram-3-fill', 'tone' => '#2fc6f6'],
            ['name' => 'Hootsuite', 'logo' => 'https://cdn.simpleicons.org/hootsuite/000000', 'tone' => '#000000'],
            ['name' => 'OneRPM', 'icon' => 'bi bi-vinyl-fill', 'tone' => '#d64242'],
            ['name' => 'Office 365', 'icon' => 'bi bi-microsoft', 'tone' => '#d83b01'],
            ['name' => 'Workflows editoriales', 'icon' => 'bi bi-kanban-fill', 'tone' => '#0d3d69'],
        ],
    ],
];

$portfolio = [
    [
        'category' => 'Web editorial',
        'focus' => 'Proyecto destacado',
        'title' => 'Sitio musical con narrativa, agenda y presencia de marca.',
        'copy' => 'Arquitectura visual pensada para presentar lanzamientos, identidad y comunidad con mucha mas claridad y personalidad.',
        'image' => jovani_local_asset_url('assets/portfolio/web-music.jpg'),
        'meta' => 'UX, contenido y sistema visual',
        'role' => 'Estrategia, arquitectura web y direccion visual',
        'deliverables' => 'Mapa de secciones, narrativa de marca, activos visuales y estructura responsive.',
        'outcome' => 'Presencia digital mas clara para lanzamientos, agenda y comunidad.',
        'accent' => '#d7a35f',
        'layout' => 'is-featured',
        'tags' => ['Narrativa', 'Responsive', 'Comunidad'],
    ],
    [
        'category' => 'Web B2B',
        'focus' => 'Presencia corporativa',
        'title' => 'Landing industrial con lectura comercial y tono mas sobrio.',
        'copy' => 'Una pieza digital construida para ordenar capacidades, materiales y confianza sin perder ritmo visual.',
        'image' => jovani_local_asset_url('assets/portfolio/web-industrial.jpg'),
        'meta' => 'Arquitectura, mensaje y conversion',
        'role' => 'UX, propuesta de valor y estructura comercial',
        'deliverables' => 'Landing, jerarquia de contenido, CTAs y bloques de confianza.',
        'outcome' => 'Lectura mas rapida de capacidades y mejor soporte para captacion B2B.',
        'accent' => '#5f8fd9',
        'layout' => 'is-spotlight',
        'tags' => ['B2B', 'Estructura', 'Confianza'],
    ],
    [
        'category' => 'Brand system',
        'focus' => 'Identidad',
        'title' => 'Sistema visual y territorio de marca.',
        'copy' => 'Logotipos, aplicaciones y coherencia visual para sostener recordacion y caracter.',
        'image' => jovani_local_asset_url('assets/portfolio/brand-logo.png'),
        'meta' => 'Branding, aplicaciones y consistencia',
        'role' => 'Identidad visual y criterio de aplicacion',
        'deliverables' => 'Logo, usos basicos, direccion visual y piezas de soporte.',
        'outcome' => 'Marca mas reconocible y facil de trasladar a canales digitales.',
        'accent' => '#ce6b56',
        'layout' => 'is-compact',
        'tags' => ['Logo', 'Sistema', 'Marca'],
    ],
    [
        'category' => 'Campaign creative',
        'focus' => 'Piezas promocionales',
        'title' => 'Creativos hechos para lectura rapida e impacto.',
        'copy' => 'Posters y materiales que convierten mensaje en presencia promocional con mucha mas fuerza visual.',
        'image' => jovani_local_asset_url('assets/portfolio/campaign-posters.jpg'),
        'meta' => 'Promocion, composicion y ritmo visual',
        'role' => 'Direccion creativa y adaptacion promocional',
        'deliverables' => 'Posters, formatos de campana y variaciones por mensaje.',
        'outcome' => 'Piezas con lectura rapida para activaciones, eventos y pauta.',
        'accent' => '#e18447',
        'layout' => 'is-compact',
        'tags' => ['Campanas', 'Promocion', 'Visual'],
    ],
    [
        'category' => 'Social & ads',
        'focus' => 'Contenido activable',
        'title' => 'Banners y formatos para pauta y contenido social.',
        'copy' => 'Assets pensados para alinear formato, objetivo y consistencia entre social media y media buying.',
        'image' => jovani_local_asset_url('assets/portfolio/social-banner.png'),
        'meta' => 'Social content, pauta y sistema',
        'role' => 'Contenido social, pauta y adaptacion de formatos',
        'deliverables' => 'Banners, variaciones creativas y lineamientos de publicacion.',
        'outcome' => 'Mayor consistencia entre piezas organicas, paid media y campanas.',
        'accent' => '#4878d9',
        'layout' => 'is-compact',
        'tags' => ['Social', 'Ads', 'Formatos'],
    ],
    [
        'category' => 'Motion craft',
        'focus' => 'Exploracion audiovisual',
        'title' => 'Motion y visuales para reforzar presencia en video.',
        'copy' => 'Piezas animadas que ayudan a abrir campañas, reforzar identidad y darle otra capa de energia a la marca.',
        'image' => jovani_local_asset_url('assets/portfolio/motion-ornamenta.jpg'),
        'meta' => 'Motion, presencia y apertura visual',
        'role' => 'Motion craft, composicion y lenguaje audiovisual',
        'deliverables' => 'Animaciones, recursos de apertura y exploraciones visuales.',
        'outcome' => 'Identidad con mas energia para video, lanzamientos y contenido dinamico.',
        'accent' => '#9d78df',
        'layout' => 'is-compact',
        'tags' => ['Motion', 'Video', 'Identidad'],
    ],
];
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= e($siteTitle) ?></title>
    <meta name="description" content="<?= e($description) ?>">
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet, noimageindex, notranslate, max-snippet:0, max-image-preview:none">
    <meta name="googlebot" content="noindex, nofollow, noarchive, nosnippet, noimageindex, notranslate, max-snippet:0, max-image-preview:none">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= e($siteTitle) ?>">
    <meta property="og:description" content="<?= e($description) ?>">
    <meta property="og:image" content="<?= e(absolute_url($portraitUrl)) ?>">
    <meta name="theme-color" content="#0b2f57">
    <link rel="icon" type="image/png" href="<?= e($favicon) ?>">
    <link rel="apple-touch-icon" href="<?= e($favicon) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=DM+Sans:wght@400;500;700&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(jovani_local_asset_url('site.css')) ?>">
</head>
<body class="jt-page">
    <div class="jt-bg-orb jt-bg-orb-a" aria-hidden="true"></div>
    <div class="jt-bg-orb jt-bg-orb-b" aria-hidden="true"></div>

    <header class="jt-header">
        <div class="jt-header-row">
            <a class="jt-sidebar-orcid" href="<?= e($orcidProfileUrl) ?>" target="_blank" rel="noopener noreferrer" aria-label="ORCID de Jovani Treviño">
                <i class="bi bi-person-vcard" aria-hidden="true"></i>
                <span>ORCID</span>
                <?php if ($orcidId !== ''): ?>
                    <strong><?= e($orcidId) ?></strong>
                <?php endif; ?>
            </a>

            <a class="jt-sidebar-profile" href="<?= e($homeUrl) ?>" aria-label="Foto de perfil de Jovani Treviño">
                <img src="<?= e($portraitUrl) ?>" alt="Jovani Treviño">
            </a>

            <a class="jt-sidebar-name" href="<?= e($homeUrl) ?>" aria-label="Marketing Intelligence - Jovani Treviño">
                <small>Marketing Intelligence</small>
                <span class="jt-sidebar-fullname">Jovani Treviño</span>
            </a>

            <button class="jt-nav-toggle" type="button" aria-expanded="false" aria-controls="jt-nav" data-nav-toggle>
                <span></span>
                <span></span>
                <span></span>
                <span class="visually-hidden">Abrir menu</span>
            </button>

            <nav class="jt-nav" id="jt-nav" data-nav>
                <a href="#perfil"><i class="bi bi-person-badge"></i><span>Sobre mí</span></a>
                <a href="#experiencia"><i class="bi bi-briefcase"></i><span>Trayectoria</span></a>
                <a href="#portafolio"><i class="bi bi-grid-1x2"></i><span>Portafolio</span></a>
                <a href="#asociaciones"><i class="bi bi-people"></i><span>Asociaciones</span></a>
                <a href="#certificaciones"><i class="bi bi-patch-check"></i><span>Certificaciones</span></a>
                <a href="#blog"><i class="bi bi-journal-text"></i><span>Blog</span></a>
                <a href="#etica"><i class="bi bi-shield-check"></i><span>Código de Ética</span></a>
            </nav>

            <div class="jt-sidebar-foot" aria-hidden="true">
                <span>Marketing Intelligence</span>
                <strong>Research / Strategy / Growth</strong>
            </div>
        </div>
    </header>

    <main class="jt-main">
        <section class="jt-hero" id="perfil" data-slide>
            <div class="jt-container jt-hero-grid">
                <div class="jt-hero-copy" data-reveal>
                    <span class="jt-kicker">Sobre mí / Marketing Intelligence</span>
                    <h1>
                        <span>Marketing Intelligence, con</span>
                        <span>criterio de negocio</span>
                    </h1>
                    <p class="jt-lead">
                        Licenciado en Ciencias de la Comunicación con especialidad en Mercadotecnia por la Universidad
                        Autónoma de Nuevo León, titulado con honores. Especialista en Marketing Intelligence & Performance,
                        enfocado en crecimiento impulsado por datos, analítica avanzada, automatización e inteligencia comercial.
                    </p>

                    <div class="jt-about-text">
                        <p>
                            A lo largo de mi trayectoria he construido un perfil multidisciplinario que integra marketing digital,
                            publicidad de pago, desarrollo web, analítica, inteligencia de negocios y tecnologías emergentes para
                            diseñar ecosistemas digitales donde estrategia, datos y tecnología trabajan de forma conjunta.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="jt-section" id="cv">
            <div class="jt-container">
                <div class="jt-section-heading" data-reveal>
                    <span class="jt-kicker">CV adaptable</span>
                    <h2>Un curriculum que cambia el enfoque sin perder consistencia profesional.</h2>
                    <p>La pagina de CV permite presentar el mismo historial desde diferentes lecturas: estrategia, SEO, paid media o front-end junior.</p>
                </div>
                <div class="jt-cv-grid">
                    <?php foreach ($cvFocusAreas as $focus): ?>
                        <article class="jt-cv-card" data-reveal>
                            <h3><?= e($focus['title']) ?></h3>
                            <p><?= e($focus['copy']) ?></p>
                            <div class="jt-chip-wrap">
                                <?php foreach ($focus['points'] as $point): ?>
                                    <span><?= e($point) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
                <div class="jt-cv-actions" data-reveal>
                    <a class="jt-btn jt-btn-primary" href="<?= e($cvUrl) ?>">Abrir CV interactivo</a>
                    <a class="jt-btn jt-btn-secondary" href="#portafolio">Ver evidencia visual</a>
                </div>
            </div>
        </section>

        <section class="jt-section" id="propuesta">
            <div class="jt-container">
                <div class="jt-about-grid">
                    <div class="jt-about-panel" data-reveal>
                        <span class="jt-kicker">Propuesta de valor</span>
                        <h2>Un perfil hibrido entre estrategia, performance, contenido y ejecucion digital.</h2>
                        <p class="jt-about-lead">
                            Me interesa que cada proyecto tenga una base clara: entender el negocio, la audiencia y la friccion real,
                            para despues convertir eso en piezas, campañas, sitios o sistemas de contenido que si empujen resultados.
                        </p>
                        <p>
                            Esa mezcla me permite entrar a una marca desde varios frentes sin perder coherencia:
                            puedo pensar el crecimiento, activar medios, estructurar contenido, diseñar la experiencia y bajar todo a una presencia digital mas solida.
                        </p>
                    </div>

                    <div class="jt-about-side" data-reveal>
                        <div class="jt-proof-grid">
                            <?php foreach ($proofCards as $proof): ?>
                                <article class="jt-proof-card">
                                    <h3><?= e($proof['title']) ?></h3>
                                    <p><?= e($proof['copy']) ?></p>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="jt-insight-panel" data-reveal>
                    <div class="jt-point-list">
                        <?php foreach ($aboutPoints as $point): ?>
                            <span><?= e($point) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>

        <section class="jt-section" id="especialidades">
            <div class="jt-container">
                <div class="jt-section-heading" data-reveal>
                    <span class="jt-kicker">Especialidades</span>
                    <h2>Las areas donde mejor conecto pensamiento estrategico, operacion y presencia visual.</h2>
                </div>
                <div class="jt-specialty-grid">
                    <?php foreach ($specialties as $specialty): ?>
                        <article class="jt-specialty-card" data-reveal>
                            <div class="jt-specialty-icon"><i class="<?= e($specialty['icon']) ?>"></i></div>
                            <h3><?= e($specialty['title']) ?></h3>
                            <p><?= e($specialty['copy']) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="jt-section" id="experiencia" data-slide>
            <div class="jt-container">
                <div class="jt-section-heading" data-reveal>
                    <span class="jt-kicker">Experiencia profesional</span>
                    <h2>Trayectoria profesional.</h2>
                </div>

                <div class="jt-experience-tabs" role="tablist" aria-label="Tipo de trayectoria" data-reveal>
                    <?php foreach ($experienceTabs as $tabIndex => $tab): ?>
                        <button
                            type="button"
                            class="jt-experience-tab<?= $tabIndex === 0 ? ' is-active' : '' ?>"
                            id="experience-tab-<?= e($tab['id']) ?>"
                            data-experience-tab="<?= e($tab['id']) ?>"
                            role="tab"
                            aria-selected="<?= $tabIndex === 0 ? 'true' : 'false' ?>"
                            aria-controls="experience-panel-<?= e($tab['id']) ?>"
                        >
                            <span><?= e($tab['label']) ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>

                <?php foreach ($experienceTabs as $tabIndex => $tab): ?>
                    <div
                        class="jt-experience-panel<?= $tabIndex === 0 ? ' is-active' : '' ?>"
                        id="experience-panel-<?= e($tab['id']) ?>"
                        data-experience-panel="<?= e($tab['id']) ?>"
                        role="tabpanel"
                        aria-labelledby="experience-tab-<?= e($tab['id']) ?>"
                    >
                        <div class="jt-timeline">
                            <?php foreach (array_values(array_filter($experience, static fn (array $job): bool => ($job['tab'] ?? '') === $tab['id'])) as $index => $job): ?>
                                <article
                                    class="jt-timeline-card jt-experience-card<?= $tabIndex === 0 && $index === 0 ? ' is-current' : '' ?>"
                                    role="button"
                                    tabindex="0"
                                    data-reveal
                                    data-experience-card
                                    data-company="<?= e($job['company']) ?>"
                                    data-type="<?= e($job['type']) ?>"
                                    data-title="<?= e($job['title']) ?>"
                                    data-period="<?= e($job['period']) ?>"
                                    data-summary="<?= e($job['company_summary']) ?>"
                                    data-objective="<?= e($job['objective']) ?>"
                                    data-intro="<?= e($job['intro']) ?>"
                                    data-functions="<?= e(json_encode($job['functions'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>"
                                    data-wins="<?= e(json_encode($job['wins'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>"
                                >
                                    <div class="jt-timeline-head">
                                        <div>
                                            <span class="jt-mini-kicker"><?= e($job['type']) ?></span>
                                            <h3><?= e($job['title']) ?></h3>
                                            <strong class="jt-company-name"><?= e($job['company']) ?></strong>
                                        </div>
                                        <span class="jt-period"><?= e($job['period']) ?></span>
                                    </div>
                                    <div class="jt-experience-card-body">
                                        <p class="jt-job-summary"><strong>Resumen de empresa:</strong> <?= e($job['company_summary']) ?></p>
                                        <p class="jt-job-summary"><strong>Objetivo del puesto:</strong> <?= e($job['objective']) ?></p>
                                        <div class="jt-experience-achievements">
                                            <h4>Logros realizados</h4>
                                            <ul>
                                                <?php foreach (array_slice($job['wins'], 0, 2) as $win): ?>
                                                    <li><?= e($win) ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    </div>
                                    <span class="jt-experience-open">Abrir experiencia <i class="bi bi-arrow-up-right" aria-hidden="true"></i></span>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <div class="jt-experience-modal" data-experience-modal aria-hidden="true">
            <button class="jt-experience-modal-backdrop" type="button" data-experience-close aria-label="Cerrar detalle de experiencia"></button>
            <article class="jt-experience-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="experience-modal-title" tabindex="-1">
                <button class="jt-experience-modal-close" type="button" data-experience-close aria-label="Cerrar detalle de experiencia">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>
                <div class="jt-experience-modal-head">
                    <div>
                        <span class="jt-mini-kicker" data-experience-modal-type></span>
                        <h2 id="experience-modal-title" data-experience-modal-title></h2>
                        <strong data-experience-modal-company></strong>
                    </div>
                    <span data-experience-modal-period></span>
                </div>
                <div class="jt-experience-modal-grid">
                    <section>
                        <h3>Resumen de empresa</h3>
                        <p data-experience-modal-summary></p>
                    </section>
                    <section>
                        <h3>Objetivo del puesto</h3>
                        <p data-experience-modal-objective></p>
                    </section>
                </div>
                <section class="jt-experience-modal-intro">
                    <h3>Introducción</h3>
                    <p data-experience-modal-intro></p>
                </section>
                <div class="jt-experience-modal-grid">
                    <section>
                        <h3>Funciones</h3>
                        <ul data-experience-modal-functions></ul>
                    </section>
                    <section>
                        <h3>Éxitos realizados</h3>
                        <ul data-experience-modal-wins></ul>
                    </section>
                </div>
            </article>
        </div>

        <section class="jt-section" id="impacto">
            <div class="jt-container">
                <div class="jt-section-heading" data-reveal>
                    <span class="jt-kicker">Impacto y casos destacados</span>
                    <h2>Una lectura mas ejecutiva de lo que aporto cuando entro a una marca, un proyecto o un equipo.</h2>
                    <p>No se trata solo de tareas; se trata de conectar estrategia, mensaje, experiencia y rendimiento en contextos reales.</p>
                </div>

                <div class="jt-impact-grid">
                    <?php foreach ($impactStats as $stat): ?>
                        <article class="jt-impact-card" data-reveal>
                            <strong><?= e($stat['value']) ?></strong>
                            <p><?= e($stat['label']) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>

                <div class="jt-case-grid">
                    <?php foreach ($caseHighlights as $index => $case): ?>
                        <article class="jt-case-card<?= $index === 0 ? ' is-featured' : '' ?>" data-reveal>
                            <span class="jt-mini-kicker"><?= e($case['eyebrow']) ?></span>
                            <h3><?= e($case['title']) ?></h3>
                            <p><?= e($case['summary']) ?></p>
                            <ul>
                                <?php foreach ($case['points'] as $point): ?>
                                    <li><?= e($point) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="jt-section">
            <div class="jt-container">
                <div class="jt-section-heading" data-reveal>
                    <span class="jt-kicker">Forma de trabajo</span>
                    <h2>Un proceso centrado en claridad, criterio visual y optimizacion sostenida.</h2>
                </div>
                <div class="jt-approach-grid">
                    <?php foreach ($approach as $item): ?>
                        <article class="jt-approach-card" data-reveal>
                            <h3><?= e($item['title']) ?></h3>
                            <p><?= e($item['copy']) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="jt-section">
            <div class="jt-container">
                <div class="jt-section-heading" data-reveal>
                    <span class="jt-kicker">Stack y herramientas</span>
                    <h2>Stack, plataformas y herramientas para ejecutar, medir y construir presencia digital.</h2>
                </div>
                <div class="jt-tools-grid">
                    <?php foreach ($toolGroups as $group): ?>
                        <article class="jt-tools-card" data-reveal>
                            <h3><?= e($group['title']) ?></h3>
                            <div class="jt-tool-pills">
                                <?php foreach ($group['items'] as $item): ?>
                                    <span class="jt-tool-pill" style="--jt-tool-accent: <?= e($item['tone']) ?>;">
                                        <span class="jt-tool-logo<?= !empty($item['mark']) ? ' is-mark' : '' ?>" aria-hidden="true">
                                            <?php if (!empty($item['logo'])): ?>
                                                <img src="<?= e($item['logo']) ?>" alt="" loading="lazy" decoding="async">
                                            <?php elseif (!empty($item['icon'])): ?>
                                                <i class="<?= e($item['icon']) ?>"></i>
                                            <?php elseif (!empty($item['mark'])): ?>
                                                <strong><?= e($item['mark']) ?></strong>
                                            <?php endif; ?>
                                        </span>
                                        <span class="jt-tool-name"><?= e($item['name']) ?></span>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="jt-section" id="portafolio" data-slide>
            <div class="jt-container">
                <div class="jt-section-heading" data-reveal>
                    <span class="jt-kicker">Portafolio seleccionado</span>
                    <h2>Casos visuales donde estrategia, sistema y craft digital se sienten mucho mas presentes.</h2>
                    <p>Una seleccion corta pero mejor curada para mostrar como resuelvo presencia, narrativa, marca y ejecucion visual.</p>
                </div>
                <div class="jt-portfolio-grid">
                    <?php foreach ($portfolio as $item): ?>
                        <?php $portfolioTags = implode(', ', $item['tags']); ?>
                        <article
                            class="jt-portfolio-card <?= e($item['layout']) ?>"
                            style="--jt-portfolio-accent: <?= e($item['accent']) ?>;"
                            role="button"
                            tabindex="0"
                            aria-label="Abrir caso: <?= e($item['title']) ?>"
                            data-reveal
                            data-portfolio-card
                            data-title="<?= e($item['title']) ?>"
                            data-category="<?= e($item['category']) ?>"
                            data-focus="<?= e($item['focus']) ?>"
                            data-copy="<?= e($item['copy']) ?>"
                            data-image="<?= e($item['image']) ?>"
                            data-meta="<?= e($item['meta']) ?>"
                            data-role="<?= e($item['role']) ?>"
                            data-deliverables="<?= e($item['deliverables']) ?>"
                            data-outcome="<?= e($item['outcome']) ?>"
                            data-tags="<?= e($portfolioTags) ?>"
                        >
                            <div class="jt-portfolio-media">
                                <img src="<?= e($item['image']) ?>" alt="<?= e($item['title']) ?>" loading="lazy" decoding="async">
                            </div>
                            <div class="jt-portfolio-copy">
                                <div class="jt-portfolio-topline">
                                    <span class="jt-portfolio-badge"><?= e($item['category']) ?></span>
                                    <span class="jt-portfolio-focus"><?= e($item['focus']) ?></span>
                                </div>
                                <h3><?= e($item['title']) ?></h3>
                                <p><?= e($item['copy']) ?></p>
                                <div class="jt-portfolio-proof">
                                    <span><strong>Rol:</strong> <?= e($item['role']) ?></span>
                                    <span><strong>Entregables:</strong> <?= e($item['deliverables']) ?></span>
                                    <span><strong>Impacto:</strong> <?= e($item['outcome']) ?></span>
                                </div>
                                <div class="jt-portfolio-tags">
                                    <?php foreach ($item['tags'] as $tag): ?>
                                        <span><?= e($tag) ?></span>
                                    <?php endforeach; ?>
                                </div>
                                <div class="jt-portfolio-tail">
                                    <span><?= e($item['meta']) ?></span>
                                    <i class="bi bi-arrow-up-right"></i>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <?php foreach ($credentialGroups as $group): ?>
            <section class="jt-section jt-credential-slide" id="<?= e($group['id']) ?>" data-slide>
                <div class="jt-container">
                    <div class="jt-credentials-shell" data-reveal>
                        <div class="jt-credentials-header">
                            <div class="jt-section-heading jt-section-heading-compact">
                                <span class="jt-kicker"><?= e($group['title']) ?></span>
                                <h2><?= e($group['copy']) ?></h2>
                            </div>
                            <div class="jt-credentials-summary">
                                <strong><?= (int) count($group['items']) ?></strong>
                                <span><?= e($group['title']) ?></span>
                                <p><?= e($credentialsSummary['copy']) ?></p>
                            </div>
                        </div>
                        <div class="jt-credentials-grid">
                            <?php $isCertificationGroup = $group['id'] === 'certificaciones'; ?>
                            <?php foreach ($group['items'] as $credential): ?>
                                <?php if ($isCertificationGroup): ?>
                                    <a class="jt-certificate-card" href="<?= e($credential['href']) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= e($credential['name']) ?>">
                                        <div class="jt-certificate-visual">
                                            <span class="jt-credential-type"><?= e($credential['type']) ?></span>
                                            <img src="<?= e($credential['certificate_image'] ?? $credential['logo']) ?>" alt="<?= e($credential['name']) ?>" loading="lazy" decoding="async">
                                        </div>
                                        <div class="jt-certificate-copy">
                                            <h3><?= e($credential['short']) ?></h3>
                                            <strong><?= e($credential['name']) ?></strong>
                                            <p><?= e($credential['description']) ?></p>
                                            <div class="jt-certificate-meta">
                                                <span><strong>Emisor</strong><?= e($credential['issuer'] ?? 'Por agregar') ?></span>
                                                <span><strong>Tipo</strong><?= e($credential['issued'] ?? $credential['type']) ?></span>
                                                <?php if (!empty($credential['credential_id'])): ?>
                                                    <span class="jt-certificate-id"><strong>ID</strong><?= e($credential['credential_id']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <span class="jt-credential-link">Ver certificado <i class="bi bi-arrow-up-right"></i></span>
                                        </div>
                                    </a>
                                <?php else: ?>
                                    <a class="jt-credential-card" href="<?= e($credential['href']) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= e($credential['name']) ?>">
                                        <span class="jt-credential-type"><?= e($credential['type']) ?></span>
                                        <div class="jt-credential-logo">
                                            <img src="<?= e($credential['logo']) ?>" alt="<?= e($credential['name']) ?>" loading="lazy" decoding="async">
                                        </div>
                                        <div class="jt-credential-copy">
                                            <h3><?= e($credential['short']) ?></h3>
                                            <strong><?= e($credential['name']) ?></strong>
                                            <p><?= e($credential['description']) ?></p>
                                        </div>
                                        <span class="jt-credential-link">Ver referencia <i class="bi bi-arrow-up-right"></i></span>
                                    </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </section>
        <?php endforeach; ?>

        <section class="jt-section" id="blog" data-slide>
            <div class="jt-container">
                <div class="jt-section-heading" data-reveal>
                    <span class="jt-kicker">Articulos recientes</span>
                    <h2>Una extension editorial de mi trabajo dentro del ecosistema de Marketing Studies.</h2>
                    <p>Las publicaciones recientes funcionan como una capa de pensamiento aplicado: estrategia, performance, SEO, conversion y aprendizaje operativo.</p>
                </div>
                <div class="jt-blog-grid">
                    <?php foreach ($latestPosts as $post): ?>
                        <?php $cover = media_url($post['cover_image'] ?? '') ?: $portraitUrl; ?>
                        <article class="jt-blog-card" data-reveal>
                            <a class="jt-blog-media" href="<?= e(post_url($post, $locale)) ?>">
                                <img src="<?= e($cover) ?>" alt="<?= e($post['title']) ?>" loading="lazy" decoding="async">
                            </a>
                            <div class="jt-blog-copy">
                                <div class="jt-blog-meta">
                                    <span><?= e($post['category_name'] ?? 'Marketing') ?></span>
                                    <span><?= (int) ($post['read_time'] ?? 1) ?> min</span>
                                </div>
                                <h3><a href="<?= e(post_url($post, $locale)) ?>"><?= e($post['title']) ?></a></h3>
                                <p><?= e($post['excerpt']) ?></p>
                                <a class="jt-blog-link" href="<?= e(post_url($post, $locale)) ?>">Leer entrada <i class="bi bi-arrow-right-short"></i></a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
                <div class="jt-blog-footer" data-reveal>
                    <a class="jt-btn jt-btn-secondary" href="<?= e($blogUrl) ?>">Explorar blog completo</a>
                </div>
            </div>
        </section>

        <section class="jt-section" id="etica" data-slide>
            <div class="jt-container">
                <div class="jt-ethics-grid">
                    <div class="jt-ethics-panel" data-reveal>
                        <span class="jt-kicker">Codigo de etica y ORCID</span>
                        <h2>Transparencia, atribucion y datos usados con responsabilidad.</h2>
                        <p>
                            Este marco conecta la practica de marketing con principios verificables: claridad en credenciales,
                            consentimiento en datos, evidencia en resultados y una identidad profesional lista para ORCID.
                        </p>
                        <div class="jt-ethics-badge">
                            <i class="bi bi-patch-check-fill" aria-hidden="true"></i>
                            <span><?= $orcidId !== '' ? 'ORCID iD verificado' : 'ORCID iD listo para verificacion' ?></span>
                        </div>
                    </div>

                    <div class="jt-ethics-principles">
                        <?php foreach ($ethicsPrinciples as $principle): ?>
                            <article class="jt-ethics-principle" data-reveal>
                                <h3><?= e($principle['title']) ?></h3>
                                <p><?= e($principle['copy']) ?></p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="jt-reference-grid">
                    <?php foreach ($ethicsReferences as $reference): ?>
                        <a class="jt-reference-card" href="<?= e($reference['href']) ?>" target="_blank" rel="noopener noreferrer" data-reveal>
                            <span class="jt-reference-icon"><i class="<?= e($reference['icon']) ?>"></i></span>
                            <span>
                                <strong><?= e($reference['label']) ?></strong>
                                <small><?= e($reference['copy']) ?></small>
                            </span>
                            <i class="bi bi-arrow-up-right"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="jt-section jt-contact-section" id="contacto">
            <div class="jt-container">
                <div class="jt-contact-shell" data-reveal>
                    <div>
                        <span class="jt-kicker">Contacto</span>
                        <h2>Disponible para proyectos, colaboraciones, posiciones estrategicas o trabajo creativo con enfoque digital.</h2>
                        <p>
                            Si estas buscando a alguien que piense con criterio de negocio, ejecute con sensibilidad visual
                            y pueda moverse entre estrategia, performance, contenido y experiencia digital, podemos conversar.
                        </p>
                    </div>
                    <div class="jt-contact-actions">
                        <a class="jt-btn jt-btn-primary" href="mailto:<?= e($contactEmail) ?>"><?= e($contactEmail) ?></a>
                        <a class="jt-btn jt-btn-secondary" href="<?= e($cvUrl) ?>">Ver CV adaptable</a>
                        <a class="jt-btn jt-btn-ghost" href="#portafolio">Ver trabajos</a>
                    </div>
                    <div class="jt-social-row">
                        <?php if ($socialLinks): ?>
                            <?php foreach ($socialLinks as $link): ?>
                                <a href="<?= e($link['url']) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= e($link['label']) ?>">
                                    <i class="<?= e($link['icon']) ?>"></i>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <a href="mailto:<?= e($contactEmail) ?>" aria-label="Email"><i class="bi bi-envelope-fill"></i></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <div class="jt-portfolio-modal" data-portfolio-modal aria-hidden="true">
        <div class="jt-portfolio-modal-backdrop" data-portfolio-close></div>
        <article class="jt-portfolio-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="jt-portfolio-modal-title" tabindex="-1">
            <button class="jt-portfolio-modal-close" type="button" data-portfolio-close aria-label="Cerrar caso de portafolio">
                <i class="bi bi-x-lg" aria-hidden="true"></i>
            </button>
            <div class="jt-portfolio-modal-media">
                <img src="" alt="" data-portfolio-modal-image>
            </div>
            <div class="jt-portfolio-modal-copy">
                <div class="jt-portfolio-modal-topline">
                    <span data-portfolio-modal-category></span>
                    <strong data-portfolio-modal-focus></strong>
                </div>
                <h2 id="jt-portfolio-modal-title" data-portfolio-modal-title></h2>
                <p data-portfolio-modal-copy></p>
                <div class="jt-portfolio-modal-proof">
                    <span><strong>Rol:</strong> <em data-portfolio-modal-role></em></span>
                    <span><strong>Entregables:</strong> <em data-portfolio-modal-deliverables></em></span>
                    <span><strong>Impacto:</strong> <em data-portfolio-modal-outcome></em></span>
                </div>
                <div class="jt-portfolio-modal-tags" data-portfolio-modal-tags></div>
                <span class="jt-portfolio-modal-meta" data-portfolio-modal-meta></span>
            </div>
        </article>
    </div>

    <nav class="jt-section-controls" aria-label="Navegacion por secciones">
        <button class="jt-section-arrow" type="button" data-section-prev aria-label="Ir a la parte anterior">
            <i class="bi bi-arrow-left-short" aria-hidden="true"></i>
            <span>Anterior</span>
        </button>
        <button class="jt-section-arrow is-primary" type="button" data-section-next aria-label="Ir a la siguiente parte">
            <span>Siguiente</span>
            <i class="bi bi-arrow-right-short" aria-hidden="true"></i>
        </button>
    </nav>

    <script src="<?= e(jovani_local_asset_url('site.js')) ?>"></script>
</body>
</html>
