<?php

use Symfony\Component\Yaml\Yaml;

it('routes Appwrite API and realtime to port 80 and the console to port 3000', function () {
    $compose = file_get_contents(__DIR__.'/../../templates/compose/appwrite.yaml');

    expect($compose)->toContain('# port: 80');

    $composes = [['SERVICE_URL', $compose]];

    foreach (['service-templates.json', 'service-templates-latest.json'] as $templateFile) {
        $templates = json_decode(
            file_get_contents(__DIR__."/../../templates/{$templateFile}"),
            associative: true,
            flags: JSON_THROW_ON_ERROR,
        );

        expect($templates['appwrite']['port'])->toBe('80');

        $prefix = $templateFile === 'service-templates.json' ? 'SERVICE_FQDN' : 'SERVICE_URL';
        $composes[] = [$prefix, base64_decode($templates['appwrite']['compose'], strict: true)];
    }

    foreach ($composes as [$prefix, $content]) {
        $services = Yaml::parse($content)['services'];

        expect($services['appwrite']['environment'])->toContain("{$prefix}_APPWRITE_80=/v1");
        expect($services['appwrite-realtime']['environment'])->toContain("{$prefix}_APPWRITE_80=/v1/realtime");
        expect($services['appwrite-console']['environment'])->toContain("{$prefix}_APPWRITE_3000=/");
    }
});
