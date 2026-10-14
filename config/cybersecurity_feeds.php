<?php

return [
    'lookback_days' => 3,
    'max_items_per_feed' => 10,
    'category_slug' => 'cybersecurity-daily',
    'sources' => [
        [
            'name' => 'CISA',
            'url' => 'https://www.cisa.gov/cybersecurity-advisories/all.xml',
            'host' => 'www.cisa.gov',
            'image_hosts' => ['cisa.gov'],
            'locale' => 'en',
        ],
        [
            'name' => 'CERT-FR',
            'url' => 'https://www.cert.ssi.gouv.fr/feed/',
            'host' => 'www.cert.ssi.gouv.fr',
            'image_hosts' => ['cert.ssi.gouv.fr'],
            'locale' => 'fr',
        ],
    ],
];