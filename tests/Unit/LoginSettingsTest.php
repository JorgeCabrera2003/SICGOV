<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\Security\LoginSettings;

class LoginSettingsTest extends TestCase
{
    #[Test]
    public function testGetRecaptchaKeys(): void
    {
        $settings = new LoginSettings();
        
        $siteKey = $settings->get_recaptcha_sitekey();
        $this->assertIsString($siteKey);
        $this->assertNotEmpty($siteKey);
        $this->assertStringStartsWith('6L', $siteKey);

        $secretKey = $settings->get_recaptcha_secret();
        $this->assertIsString($secretKey);
        $this->assertNotEmpty($secretKey);
        $this->assertStringStartsWith('6L', $secretKey);
    }
}
