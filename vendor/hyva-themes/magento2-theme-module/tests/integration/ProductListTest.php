<?php
/**
 * Hyvä Themes - https://hyva.io
 * Copyright © Hyvä Themes 2020-present. All rights reserved.
 * This product is licensed per Magento install
 * See https://hyva.io/license
 */

declare(strict_types=1);

namespace Hyva\Theme;

use Hyva\Theme\ViewModel\ProductList;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductLinkInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Catalog\Model\Product\Visibility as ProductVisibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Quote\Model\Cart\Data\CartItem;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;

use function array_map as map;
use function array_values as values;

class ProductListTest extends TestCase
{
    private function assertIds(array $expectedIds, array $products): void
    {
        $actualIds = values(map(function (ProductInterface $product): int {
            return (int) $product->getId();
        }, $products));
        $this->assertSame($expectedIds, $actualIds);
    }

    private function assertSkus(array $expectedSkus, array $products): void
    {
        $actualSkus = values(map(function (ProductInterface $product): string {
            return $product->getSku();
        }, $products));
        $this->assertSame($expectedSkus, $actualSkus);
    }

    /**
     * Product ID 1 - no category (product_simple.php)
     * Product ID 6 - category 333 (second_product_simple.php, category_with_two_products.php)
     * Product ID 333 - Category ID 333 (category_product.php)
     *
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Catalog/_files/category_with_two_products.php
     */
    public function testAppliesCategoryIdsFilter(): void
    {
        /** @var ProductList $sut */
        $sut = ObjectManager::getInstance()->create(ProductList::class);
        $sut->addFilter('category_id', [333], 'in');
        $sut->addAscendingSortOrder('id');
        $items = $sut->getItems();
        $this->assertCount(2, $items);
        $this->assertIds([6, 333], $items);
    }

    /**
     * This is based on the data fixture but uses fix product link types
     *
     * @see Magento Data Fixture Magento/Catalog/_files/multiple_related_products.php
     */
    public static function createMultipleLinkedProductsFixture()
    {
        /** @var ProductFactory $factory */
        $factory = ObjectManager::getInstance()->get(ProductFactory::class);
        /** @var ProductLinkInterfaceFactory $linkFactory */
        $linkFactory = ObjectManager::getInstance()->get(ProductLinkInterfaceFactory::class);
        /** @var ProductResource $productResource */
        $productResource = ObjectManager::getInstance()->get(ProductResource::class);

        $rootProductCount = 1;
        $rootSku = 'simple-related-';
        $simpleProducts = [];
        for ($i =1; $i <= $rootProductCount; $i++) {
            /** @var Product $product */
            $product = $factory->create();
            $product->setTypeId(ProductType::TYPE_SIMPLE)
                    ->setAttributeSetId(4)
                    ->setName('Simple Related Product #' .$i)
                    ->setSku($rootSku .$i)
                    ->setPrice(10)
                    ->setVisibility(ProductVisibility::VISIBILITY_BOTH)
                    ->setStatus(ProductStatus::STATUS_ENABLED)
                    ->setWebsiteIds([1]);
            $productResource->save($product);
            $simpleProducts[$i] = $product;
        }

        $linkTypes = ['crosssell', 'related', 'upsell'];
        foreach ($simpleProducts as $simpleI => $product) {
            $linkedCount = 6;
            $links = [];
            for ($i = 0; $i < $linkedCount; $i++) {
                /** @var Product $linkedProduct */
                $linkedProduct = $factory->create();
                $linkedSku = 'related-product-' .$simpleI .'-' .$i;
                $linkedProduct->setTypeId(ProductType::TYPE_SIMPLE)
                              ->setAttributeSetId(4)
                              ->setName('Related product #' .$simpleI .'-' .$i)
                              ->setSku($linkedSku)
                              ->setPrice(10)
                              ->setVisibility(ProductVisibility::VISIBILITY_BOTH)
                              ->setStatus(ProductStatus::STATUS_ENABLED)
                              ->setWebsiteIds([1]);
                $productResource->save($linkedProduct);
                $link = $linkFactory->create();
                $link->setSku($product->getSku());
                $link->setLinkedProductSku($linkedSku);
                $link->setPosition($i + 1);
                $link->setLinkType($linkTypes[$i % count($linkTypes)]);
                $links[] = $link;
            }
            $product->setProductLinks($links);
            $productResource->save($product);
        }
    }

    /**
     * Fixture products:
     *
     * simple-related-1
     * related-product-1- . n (0 ... 5)
     * relation type for n(0, 3) -> crosssell
     * relation type for n(1, 4) -> related
     * relation type for n(2, 5) -> upsell
     *
     * @magentoDataFixture createMultipleLinkedProductsFixture
     */
    public function testReturnsRelatedItems()
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = ObjectManager::getInstance()->get(ProductRepositoryInterface::class);
        /** @var ProductInterface|Product $product */
        $product = $productRepository->get('simple-related-1');
        /** @var QuoteItem $cartItem */
        $quoteItem1 = ObjectManager::getInstance()->create(QuoteItem::class);
        $quoteItem2 = ObjectManager::getInstance()->create(QuoteItem::class);
        $quoteItem1->setProduct($product);
        $quoteItem2->setProduct($product);

        /** @var ProductList $sut */
        $sut = ObjectManager::getInstance()->create(ProductList::class);

        $crosssell = $sut->addAscendingSortOrder('id')->getCrosssellItems($quoteItem1, $quoteItem2);
        $this->assertSkus(['related-product-1-0', 'related-product-1-3'], $crosssell);

        $related = $sut->addAscendingSortOrder('id')->getRelatedItems($product);
        $this->assertSkus(['related-product-1-1', 'related-product-1-4'], $related);

        $upsell = $sut->addAscendingSortOrder('id')->getUpsellItems($product);
        $this->assertSkus(['related-product-1-2', 'related-product-1-5'], $upsell);
    }
}
