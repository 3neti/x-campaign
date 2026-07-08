<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\PayCodeGenerationGateway;
use LBHurtado\XCampaign\Gateways\NullPortableCodeGenerationGateway;

it('binds the pay code generation gateway to the null portable code gateway baseline', function () {
    expect(app(PayCodeGenerationGateway::class))->toBeInstanceOf(NullPortableCodeGenerationGateway::class);
});
