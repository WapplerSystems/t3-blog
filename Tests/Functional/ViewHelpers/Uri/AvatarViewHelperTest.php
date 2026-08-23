<?php

declare(strict_types=1);

/*
 * This file is part of the package wapplersystems/blog.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace WapplerSystems\Blog\Tests\Functional\ViewHelpers\Uri;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\TypoScript\AST\Node\RootNode;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;
use WapplerSystems\Blog\Domain\Model\Author;

final class AvatarViewHelperTest extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = [
        'form'
    ];

    protected array $testExtensionsToLoad = [
        'typo3conf/ext/blog'
    ];

    #[Test]
    #[DataProvider('renderDataProvider')]
    public function render(string $template, string $expected): void
    {
        /** @phpstan-ignore-next-line */
        $frontendTypoScript = new FrontendTypoScript(new RootNode(), [], [], []);
        $frontendTypoScript->setSetupArray([]);
        $request = (new ServerRequest())
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE)
            ->withAttribute('frontend.typoscript', $frontendTypoScript);
        $this->get(ConfigurationManagerInterface::class)->setRequest($request);

        $author = new Author();
        $author->setEmail('info+gravatar@typo3.com');
        $author->setName('Info Gravatar');
        $author->setAvatarProvider('WapplerSystems\Blog\AvatarProvider\GravatarProvider');

        $context = $this->get(RenderingContextFactory::class)->create();
        $context->getTemplatePaths()->setTemplateSource($template);
        $view = (new TemplateView($context));
        $view->assign('author', $author);

        self::assertSame($expected, $view->render());
    }

    public static function renderDataProvider(): array
    {
        return [
            'simple' => [
                '{blogvh:uri.avatar(author: author)}',
                'https://www.gravatar.com/avatar/edce5ecb76b1dcb9f9d7647a13e7fc97?s=64',
            ],
            'size' => [
                '{blogvh:uri.avatar(author: author, size: 32)}',
                'https://www.gravatar.com/avatar/edce5ecb76b1dcb9f9d7647a13e7fc97?s=32',
            ]
        ];
    }
}
