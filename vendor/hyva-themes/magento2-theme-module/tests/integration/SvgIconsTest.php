<?php
/**
 * Hyvä Themes - https://hyva.io
 * Copyright © Hyvä Themes 2020-present. All rights reserved.
 * This product is licensed per Magento install
 * See https://hyva.io/license
 */

declare(strict_types=1);

namespace Hyva\Theme;

use Hyva\Theme\ViewModel\SvgIcons;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Asset\File\NotFoundException;
use Magento\Framework\View\DesignInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Theme\Model\Theme\Registration;
use PHPUnit\Framework\TestCase;

// phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps, Generic.Files.LineLength.TooLong

/**
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @magentoComponentsDir ../../../../vendor/hyva-themes/magento2-theme-module/tests/integration/_files/design
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class SvgIconsTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var string[]|null */
    private $testViewFiles = [];

    protected function setUp(): void
    {
        $this->testViewFiles = [];
        $this->objectManager = Bootstrap::getObjectManager();
        /** @var CacheInterface $cache */
        $cache = $this->objectManager->get(CacheInterface::class);
        $cache->clean([SvgIcons::CACHE_TAG]);
        ThemeFixture::registerTestThemes();
    }

    protected function tearDown(): void
    {
        foreach ($this->testViewFiles as $testViewFile) {
            \unlink($testViewFile);
        }
    }

    private function givenCurrentTheme(string $themePath): void
    {
        /** @var Registration $registration */
        $registration = $this->objectManager->get(Registration::class);
        $registration->register();

        /** @var DesignInterface $design */
        $design = $this->objectManager->get(DesignInterface::class);
        $design->setDesignTheme($themePath);
    }

    private function createViewFile(string $viewFile, string $viewFileContents): void
    {
        $viewFilePath = __DIR__ . '/_files/design/frontend/Hyva/test/' . $viewFile . '';
        \file_put_contents(
            $viewFilePath,
            $viewFileContents
        );
        $this->testViewFiles[] = $viewFilePath;
    }

    public function dataSvg()
    {
        return [
            'check'    => [
                'check',
                'checkHtml',
                <<<'SVG'
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="24" height="24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                SVG,
            ],
            'arrow-up' => [
                'arrow-up',
                'arrowUpHtml',
                <<<'SVG'
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="24" height="24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                </svg>
                SVG,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider dataSvg
     */
    public function renders_svg_with_code(string $code, string $method, string $expectedSvg)
    {
        /** @var \Hyva\Theme\ViewModel\HeroiconsOutline $svgIcons */
        $svgIcons = $this->objectManager->get(\Hyva\Theme\ViewModel\HeroiconsOutline::class);
        $this->assertEquals($expectedSvg, trim($svgIcons->renderHtml($code)));
    }

    /**
     * @test
     * @dataProvider dataSvg
     */
    public function renders_svg_with_magic_method(string $code, string $method, string $expectedSvg)
    {
        /** @var \Hyva\Theme\ViewModel\HeroiconsOutline $svgIcons */
        $svgIcons = $this->objectManager->get(\Hyva\Theme\ViewModel\HeroiconsOutline::class);
        $this->assertEquals(
            $expectedSvg,
            trim($svgIcons->$method())
        );
    }

    /**
     * @test
     */
    public function svg_can_be_overridden_in_theme()
    {
        $this->givenCurrentTheme('Hyva/test');
        $overriddenSvg = <<<'SVG'
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="24" height="24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="5" d="M5 13l4 4L19 7"/>
            </svg>
            SVG;
        $this->createViewFile('Hyva_Theme/web/svg/heroicons/outline/check.svg', $overriddenSvg);
        /** @var \Hyva\Theme\ViewModel\HeroiconsOutline $svgIcons */
        $svgIcons = $this->objectManager->get(\Hyva\Theme\ViewModel\HeroiconsOutline::class);
        $this->assertEquals(
            $overriddenSvg,
            trim($svgIcons->checkHtml())
        );
    }

    /**
     * @test
     */
    public function can_use_arbitrary_icon_set_in_theme()
    {
        $this->givenCurrentTheme('Hyva/test');
        $svg = <<<'SVG'
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="24" height="24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="10" d="M5 13l4 4L19 7"/>
            </svg>
            SVG;
        $this->createViewFile('Hyva_Theme/web/svg/custom/custom-icon.svg', $svg);
        /** @var \Hyva\Theme\ViewModel\SvgIcons $svgIcons */
        $svgIcons = $this->objectManager->create(\Hyva\Theme\ViewModel\SvgIcons::class, ['iconSet' => 'custom']);
        $this->assertEquals(
            $svg,
            trim($svgIcons->renderHtml('custom-icon'))
        );
    }

    /**
     * @test
     */
    public function can_be_used_without_icon_set_in_theme()
    {
        $this->givenCurrentTheme('Hyva/test');
        $svg = <<<'SVG'
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="24" height="24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="10" d="M5 13l4 4L19 7"/>
            </svg>
            SVG;
        $this->createViewFile('Hyva_Theme/web/svg/custom-icon.svg', $svg);
        /** @var \Hyva\Theme\ViewModel\SvgIcons $svgIcons */
        $svgIcons = $this->objectManager->create(\Hyva\Theme\ViewModel\SvgIcons::class);
        $this->assertEquals(
            $svg,
            trim($svgIcons->renderHtml('custom-icon'))
        );
        $this->assertEquals(
            $svg,
            trim($svgIcons->customIconHtml())
        );
    }

    /**
     * @test
     */
    public function adds_css_classes()
    {
        /** @var \Hyva\Theme\ViewModel\HeroiconsOutline $svgIcons */
        $svgIcons = $this->objectManager->get(\Hyva\Theme\ViewModel\HeroiconsOutline::class);
        $expectedSvg = <<<'SVG'
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="h-6 w-6" width="24" height="24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            SVG;
        $this->assertEquals($expectedSvg, trim($svgIcons->renderHtml('check', 'h-6 w-6')));
    }

    /**
     * @test
     */
    public function adds_width_and_height()
    {
        /** @var \Hyva\Theme\ViewModel\HeroiconsOutline $svgIcons */
        $svgIcons = $this->objectManager->get(\Hyva\Theme\ViewModel\HeroiconsOutline::class);
        $expectedSvg = <<<'SVG'
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="12">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            SVG;
        $this->assertEquals($expectedSvg, trim($svgIcons->renderHtml('check', '', 16, 12)));
    }

    /**
     * @test
     */
    public function replaces_existing_attributes()
    {
        $this->givenCurrentTheme('Hyva/test');
        $svg = <<<'SVG'
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="" width="500" height="500">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="10" d="M5 13l4 4L19 7"/>
            </svg>
            SVG;
        $expectedSvg = <<<'SVG'
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="h-5 w-5" width="12" height="12">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="10" d="M5 13l4 4L19 7"/>
            </svg>
            SVG;
        $this->createViewFile('Hyva_Theme/web/svg/custom-icon.svg', $svg);
        /** @var \Hyva\Theme\ViewModel\SvgIcons $svgIcons */
        $svgIcons = $this->objectManager->create(\Hyva\Theme\ViewModel\SvgIcons::class);
        $this->assertEquals(
            $expectedSvg,
            trim($svgIcons->renderHtml('custom-icon', 'h-5 w-5', 12, 12))
        );
    }

    /**
     * @test
     */
    public function adds_classes_width_and_height_with_magic_method()
    {
        /** @var \Hyva\Theme\ViewModel\HeroiconsOutline $svgIcons */
        $svgIcons = $this->objectManager->get(\Hyva\Theme\ViewModel\HeroiconsOutline::class);
        $expectedSvg = <<<'SVG'
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-red" width="16" height="12">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            SVG;
        $this->assertEquals($expectedSvg, trim($svgIcons->checkHtml('text-red', 16, 12)));
    }

    /**
     * @test
     */
    public function renders_repeated_icon_fast()
    {
        /** @var \Hyva\Theme\ViewModel\HeroiconsOutline $svgIcons */
        $svgIcons = $this->objectManager->get(\Hyva\Theme\ViewModel\HeroiconsOutline::class);
        $startTime = microtime(true);
        for ($i=0; $i<100; ++$i) {
            $svgIcons->renderHtml('clock');
        }
        $seconds = microtime(true) - $startTime;
        $this->assertLessThan(0.05, $seconds, 'Rendering the same SVG 100 times should take less than 50ms');
    }

    /**
     * @test
     */
    public function caches_icons_based_on_width_and_height()
    {
        /** @var \Hyva\Theme\ViewModel\HeroiconsOutline $svgIcons */
        $svgIcons = $this->objectManager->get(\Hyva\Theme\ViewModel\HeroiconsOutline::class);
        $first = $svgIcons->renderHtml('cake', 'w-6 h-6', 32, 32);
        $second = $svgIcons->renderHtml('cake', 'w-6 h-6', 16, 16);
        $this->assertNotEquals($first, $second, 'Different width + height parameters should result in different SVGs');
    }

    /**
     * @test
     */
    public function caches_icons_based_on_attributes()
    {
        /** @var \Hyva\Theme\ViewModel\HeroiconsOutline $svgIcons */
        $svgIcons = $this->objectManager->get(\Hyva\Theme\ViewModel\HeroiconsOutline::class);
        $first = $svgIcons->renderHtml('cake', 'w-6 h-6', 16, 16, ['title' => 'Test A']);
        $second = $svgIcons->renderHtml('cake', 'w-6 h-6', 16, 16, ['title' => 'Test B']);
        $this->assertNotEquals($first, $second, 'Different attribute parameters should result in different SVGs');
    }

    /**
     * @test
     */
    public function caches_icons_based_on_class_names()
    {
        /** @var \Hyva\Theme\ViewModel\HeroiconsOutline $svgIcons */
        $svgIcons = $this->objectManager->get(\Hyva\Theme\ViewModel\HeroiconsOutline::class);
        $first = $svgIcons->renderHtml('document', 'w-6 h-6');
        $second = $svgIcons->renderHtml('document', 'w-5 h-5');
        $this->assertNotEquals($first, $second, 'Different class names should result in different SVGs');
    }

    /**
     * @test
     */
    public function caches_icons_based_on_icon_set()
    {
        /** @var \Hyva\Theme\ViewModel\SvgIcons $outlineIcons */
        /** @var \Hyva\Theme\ViewModel\SvgIcons $solidIcons */
        $outlineIcons = $this->objectManager->create(\Hyva\Theme\ViewModel\SvgIcons::class, ['iconSet' => 'heroicons/outline']);
        $solidIcons = $this->objectManager->create(\Hyva\Theme\ViewModel\SvgIcons::class, ['iconSet' => 'heroicons/solid']);
        $first = $outlineIcons->renderHtml('eye', 'w-6 h-6');
        $second = $solidIcons->renderHtml('eye', 'w-6 h-6');
        $this->assertNotEquals($first, $second, 'Different icon sets for the same icon should result in different SVGs');
    }

    /**
     * @test
     */
    public function caches_icons_based_on_icon_path_prefix()
    {
        $this->givenCurrentTheme('Hyva/test');
        $this->createViewFile('Hyva_PaymentIcons/web/svg/dark/eye.svg', <<<'SVG'
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="" width="500" height="500">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="10" d="M5 13l4 4L19 7"/>
            </svg>
            SVG
        );
        $iconsA = $this->objectManager->create(\Hyva\Theme\ViewModel\SvgIcons::class, ['iconPathPrefix' => 'Hyva_Theme::svg/heroicons/outline']);
        $iconsB = $this->objectManager->create(\Hyva\Theme\ViewModel\SvgIcons::class, ['iconPathPrefix' => 'Hyva_PaymentIcons::svg/dark']);
        $first = $iconsA->renderHtml('eye', 'w-6 h-6');
        $second = $iconsB->renderHtml('eye', 'w-6 h-6');
        $this->assertNotEquals($first, $second, 'Different iconPathPrefix for the same icon should result in different SVGs');
    }

    /**
     * @test
     */
    public function applies_icon_path_prefix_di_config()
    {
        $this->givenCurrentTheme('Hyva/test');
        $idealSvg = <<<'SVG'
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="blue" class="" width="24" height="24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="10" d="M5 13l4 4L19 7"/>
            </svg>
            SVG;
        $cartSvg = <<<'SVG'
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="black" class="" width="24" height="24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="10" d="M5 13l4 4L19 7"/>
            </svg>
            SVG;
        $this->createViewFile('Hyva_PaymentIcons/web/svg/dark/ideal.svg', $idealSvg);
        $this->createViewFile('web/svg/cart.svg', $cartSvg);
        /** @var \Hyva\Theme\ViewModel\SvgIcons $icons */
        $icons = $this->objectManager->create(\Hyva\Theme\ViewModel\SvgIcons::class, [
            'pathPrefixMapping' => [
                'payment-icons' => 'Hyva_PaymentIcons::svg',
            ],
        ]);

        $this->assertNotEmpty($icons->renderHtml('heroicons/solid/shopping-cart'));
        $this->assertSame($idealSvg, trim($icons->renderHtml('payment-icons/dark/ideal')));
        $this->assertSame($cartSvg, trim($icons->renderHtml('cart')));
    }

    /**
     * @test
     */
    public function throws_beginner_friendly_error()
    {
        $icons = $this->objectManager->create(\Hyva\Theme\ViewModel\SvgIcons::class);

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Unable to find the SVG icon "non-existent-icon');
        $icons->renderHtml('non-existent-icon');
    }
}
