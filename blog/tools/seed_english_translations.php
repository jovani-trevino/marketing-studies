<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$pdo = new PDO('sqlite:' . $root . '/storage/blog.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$now = date('c');
$items = [
    1 => [
        'title' => 'How to build a content funnel that attracts high-intent leads',
        'slug' => 'content-funnel-high-intent-leads',
        'excerpt' => 'A simple structure to connect SEO, lead magnets and nurture flows without relying only on cold traffic.',
        'content' => "## The real problem\n\nMany blogs publish a lot, but they do not connect content with a clear offer. The result is traffic without conversion.\n\n## A funnel that works\n\n1. Map the audience problem.\n2. Publish a practical guide around the search intent.\n3. Offer a useful download that continues the topic.\n4. Send a nurture sequence that helps the reader decide.\n5. Measure which topics become qualified conversations.\n\n## What to improve first\n\nStart with one cluster, one lead magnet and one simple email sequence. Once the path is working, scale the library.",
    ],
    2 => [
        'title' => '7 quick fixes to improve Google Ads ROAS',
        'slug' => 'google-ads-roas-quick-fixes',
        'excerpt' => 'Small changes in structure, search terms and creative that can unlock efficiency in just a few weeks.',
        'content' => "## Start with the search term report\n\nBefore changing budgets, review where intent is leaking.\n\n## Key adjustments\n\n1. Add cross-campaign negatives.\n2. Separate brand and non-brand traffic.\n3. Reinforce landing pages with social proof.\n4. Match landing pages by keyword cluster.\n5. Exclude low-quality audiences.\n6. Review schedules and devices.\n7. Raise bids only where margin exists.\n\n## What not to do\n\nDo not increase spend on campaigns with broken tracking or generic landing page messages.",
    ],
    3 => [
        'title' => 'A fast landing page audit framework to lift conversions',
        'slug' => 'landing-page-audit-framework-conversions',
        'excerpt' => 'A quick method to review value proposition, visual hierarchy, social proof and friction.',
        'content' => "## The 20-minute audit\n\nAsk four questions before touching design details.\n\n1. Is the offer clear in five seconds?\n2. Is there one primary action?\n3. Is social proof visible above the fold?\n4. Is the form asking only for what is needed?\n\n## The highest-leverage changes\n\nImprove the headline, reduce form friction and move proof closer to the call to action. Small clarity wins usually outperform decorative redesigns.",
    ],
    5 => [
        'title' => 'Test post',
        'slug' => 'test-post',
        'excerpt' => 'Temporary test content for validating the CMS.',
        'content' => "## Test\n\nThis is a temporary article used to validate publishing, comments and translations.",
    ],
    6 => [
        'title' => 'Email sequences that activate leads without sounding automated',
        'slug' => 'email-sequences-activate-leads',
        'excerpt' => 'A practical sequence for nurturing MQLs with timing, context and calls to action that feel human.',
        'content' => "## The classic mistake\n\nMany email flows talk about a demo too soon and not enough about the problem the audience is trying to solve.\n\n## A useful sequence\n\n1. Context and pain point.\n2. A practical example.\n3. A decision checklist.\n4. A short case study.\n5. A low-pressure next step.\n\n## The goal\n\nThe best nurture flow makes the next conversation feel obvious, not forced.",
    ],
    7 => [
        'title' => 'How to know when attribution is lying and costing you budget',
        'slug' => 'attribution-lies-costing-budget',
        'excerpt' => 'Signals that reveal broken tracking, inflated conversions and dashboards that push poor decisions.',
        'content' => "## When numbers look too good\n\nIf every campaign looks like a winner, you may not be measuring the full journey.\n\n## Where to check first\n\n1. Duplicate conversion events.\n2. Missing offline revenue.\n3. Overweighted last-click reports.\n4. Channels claiming the same lead.\n5. Dashboards without quality signals.\n\n## Better decisions\n\nUse attribution as a decision aid, not as the only truth source.",
    ],
    8 => [
        'title' => 'Technical SEO checklist to speed up indexation without twenty plugins',
        'slug' => 'technical-seo-indexation-checklist',
        'excerpt' => 'A practical list to review crawlability, internal links, speed and indexability signals.',
        'content' => "## Before publishing more\n\nCheck whether Google can truly understand your site.\n\n## Quick checklist\n\n1. Clean and updated sitemap.\n2. Robots rules without accidental blocks.\n3. Correct canonicals.\n4. Sensible internal pagination.\n5. Core Web Vitals on key templates.\n\n## What unlocks speed\n\nThe best combo is internal architecture, contextual links and clean pages without weak templates.",
    ],
    9 => [
        'title' => 'Campaign architecture to scale Google Ads without losing control',
        'slug' => 'google-ads-campaign-architecture-control',
        'excerpt' => 'How to separate intent, creative and landing pages so budgets can grow without operational chaos.',
        'content' => "## The risk of scaling too fast\n\nWhen the structure is weak, more budget only accelerates waste.\n\n## A useful architecture\n\n1. Separate brand.\n2. Group high intent by cluster.\n3. Keep creative testing contained.\n4. Match landing pages to the promise.\n5. Review budget by margin and signal quality.\n\n## Scale with control\n\nThe account should make decisions easier as spend grows, not harder.",
    ],
    10 => [
        'title' => 'A CRO experiment map for teams done testing by intuition',
        'slug' => 'cro-experiment-map',
        'excerpt' => 'A system to prioritize conversion hypotheses with evidence, expected impact and learning speed.',
        'content' => "## The problem\n\nTesting random ideas can feel productive, but it rarely builds a repeatable advantage.\n\n## Build a living backlog\n\n1. Hypothesis with insight.\n2. Previous evidence.\n3. Expected impact.\n4. Implementation effort.\n5. Learning value.\n\n## Make tests compound\n\nA good CRO program gets smarter every week because every result updates the map.",
    ],
    11 => [
        'title' => 'CRM nurture loops: from cold lead to opportunity with less friction',
        'slug' => 'crm-nurture-loops-opportunity',
        'excerpt' => 'A framework to connect scoring, automation and useful content without pushing the sale too early.',
        'content' => "## Nurture is not just sending emails\n\nA strong loop synchronizes behavior, context and sales timing.\n\n## Key components\n\n1. Reliable behavior events.\n2. Segments that reflect intent.\n3. Content mapped to the buying stage.\n4. Alerts when the account is ready.\n5. Clean handoff rules.\n\n## Less friction\n\nThe buyer should feel guided, not chased.",
    ],
    12 => [
        'title' => 'Attribution dashboard for founders: metrics to watch and ignore',
        'slug' => 'attribution-dashboard-founders',
        'excerpt' => 'A compact dashboard to avoid vanity metrics when you need to decide budget quickly.',
        'content' => "## Fewer widgets, better reading\n\nIf the dashboard looks like an airplane cockpit, no one will use it consistently.\n\n## Metrics that matter\n\n1. CAC by channel.\n2. Conversion by stage.\n3. Pipeline value.\n4. Payback period.\n5. Lead quality by source.\n\n## What to ignore\n\nAvoid reports that look impressive but do not change the next decision.",
    ],
    13 => [
        'title' => 'Case study: B2B ecommerce reduced CAC with comparative content',
        'slug' => 'b2b-ecommerce-cac-comparative-content-case-study',
        'excerpt' => 'A breakdown of how comparisons, intent-based landings and remarketing reduced CAC without increasing budget.',
        'content' => "## Context\n\nA B2B ecommerce company had solid organic traffic, but most visitors arrived through informational content with low conversion.\n\n## Challenge\n\nThe team needed to capture higher intent without raising media spend.\n\n## What changed\n\nThey built comparison pages, connected them to remarketing and used clearer product decision criteria.\n\n## Result\n\nThe funnel produced better-qualified visitors and lowered CAC by focusing on decision-stage content.",
    ],
    14 => [
        'title' => 'Case study: professional service tripled meetings with a sharper landing page',
        'slug' => 'professional-services-landing-page-case-study',
        'excerpt' => 'How an objection-led redesign, stronger proof and contextual CTAs converted more visits into meetings.',
        'content' => "## Context\n\nA professional services firm had qualified traffic, but the site explained too much and persuaded too little.\n\n## Challenge\n\nThe main landing page did not clearly show who the service was for or why prospects should book a call.\n\n## What changed\n\nThe team rewrote the promise, moved proof higher and simplified the CTA path.\n\n## Result\n\nThe page created clearer intent and tripled meeting requests.",
    ],
    15 => [
        'title' => 'Case study: SaaS recovered dormant pipeline with nurture loops',
        'slug' => 'saas-nurture-loops-pipeline-case-study',
        'excerpt' => 'A case about scoring, automation and useful content to reactivate old leads without overwhelming inboxes.',
        'content' => "## Context\n\nA B2B SaaS company had thousands of historic leads in the CRM, but very few new conversations came from that base.\n\n## Challenge\n\nThe team needed to reactivate pipeline without sending generic blasts.\n\n## What changed\n\nThey rebuilt scoring, created stage-based content and triggered sales alerts only when behavior showed intent.\n\n## Result\n\nDormant leads became useful opportunities again with less noise and better timing.",
    ],
];

$stmt = $pdo->prepare("
    INSERT INTO post_translations (
        post_id, locale, title, slug, excerpt, content_markdown, content_html,
        seo_title, seo_description, created_at, updated_at
    ) VALUES (
        :post_id, 'en', :title, :slug, :excerpt, :content_markdown, '',
        :seo_title, :seo_description, :created_at, :updated_at
    )
    ON CONFLICT(post_id, locale) DO UPDATE SET
        title = excluded.title,
        slug = excluded.slug,
        excerpt = excluded.excerpt,
        content_markdown = excluded.content_markdown,
        content_html = excluded.content_html,
        seo_title = excluded.seo_title,
        seo_description = excluded.seo_description,
        updated_at = excluded.updated_at
");

foreach ($items as $postId => $item) {
    $stmt->execute([
        ':post_id' => $postId,
        ':title' => $item['title'],
        ':slug' => $item['slug'],
        ':excerpt' => $item['excerpt'],
        ':content_markdown' => $item['content'],
        ':seo_title' => $item['title'] . ' | Marketing Studies',
        ':seo_description' => $item['excerpt'],
        ':created_at' => $now,
        ':updated_at' => $now,
    ]);
}

echo 'Seeded ' . count($items) . " English translations\n";
