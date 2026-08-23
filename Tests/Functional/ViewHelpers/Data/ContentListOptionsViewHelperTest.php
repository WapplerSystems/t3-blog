<?php

declare(strict_types=1);

/*
 * This file is part of the package wapplersystems/blog.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace WapplerSystems\Blog\Tests\Functional\ViewHelpers\Data;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Domain\RecordFactory;
use TYPO3\CMS\Core\Schema\Capability\TcaSchemaCapability;
use TYPO3\CMS\Core\Schema\TcaSchemaFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WapplerSystems\Blog\Constants;
use WapplerSystems\Blog\Tests\Functional\SiteBasedTestCase;

final class ContentListOptionsViewHelperTest extends SiteBasedTestCase
{
    #[Test]
    public function render(): void
    {
        $this->createTestSite();

        $data = $this->renderContentObjectData('blog_header');

        $expected = [
            'uid' => Constants::LISTTYPE_TO_FAKE_UID_MAPPING['blog_header'],
            'CType' => 'blog_header',
            'layout' => '0',
            'frame_class' => 'default',
        ];

        self::assertSame($expected, array_intersect_key($data, $expected));
    }

    #[Test]
    public function renderWithOverwrite(): void
    {
        $this->createTestSite();
        $this->addTypoScriptToTemplateRecord(
            self::ROOT_UID,
            implode("\n", [
                'plugin.tx_blog.settings.contentListOptions.blog_header {',
                '   space_before_class = small',
                '   frame_class = secondary',
                '}',
            ])
        );

        $data = $this->renderContentObjectData('blog_header');

        $expected = [
            'space_before_class' => 'small',
            'frame_class' => 'secondary',
            'uid' => Constants::LISTTYPE_TO_FAKE_UID_MAPPING['blog_header'],
            'CType' => 'blog_header',
            'layout' => '0',
        ];

        self::assertSame($expected, array_intersect_key($data, $expected));
    }

    #[Test]
    public function renderCarriesEverySystemFieldDeclaredByTtContent(): void
    {
        $this->createTestSite();

        $data = $this->renderContentObjectData('blog_header');
        $schema = GeneralUtility::makeInstance(TcaSchemaFactory::class)->get('tt_content');

        foreach (TcaSchemaCapability::getSystemCapabilities() as $capability) {
            if (!$schema->hasCapability($capability)) {
                continue;
            }
            $capabilityInstance = $schema->getCapability($capability);
            /** @phpstan-ignore method.notFound */
            self::assertArrayHasKey($capabilityInstance->getFieldName(), $data);
        }
    }

    #[Test]
    public function renderedRowIsAcceptedByRecordFactory(): void
    {
        $this->createTestSite();

        $data = $this->renderContentObjectData('blog_header');
        $record = GeneralUtility::makeInstance(RecordFactory::class)
            ->createResolvedRecordFromDatabaseRow('tt_content', $data);

        self::assertSame(Constants::LISTTYPE_TO_FAKE_UID_MAPPING['blog_header'], $record->getUid());
    }

    /**
     * @return array<string, mixed>
     */
    private function renderContentObjectData(string $listType): array
    {
        $template = sprintf(
            '<blogvh:data.contentListOptions listType="%s" />{contentObjectData -> f:format.json() -> f:format.raw()}',
            $listType
        );

        return json_decode(
            $this->renderFluidTemplateInTestSite($template),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }
}
