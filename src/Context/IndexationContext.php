<?php declare(strict_types=1);

namespace MOrtola\BehatSEOContexts\Context;

use Behat\Behat\Context\Environment\InitializedContextEnvironment;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Exception;
use PHPUnit\Framework\Assert;

class IndexationContext extends BaseContext
{
    /**
     * @var RobotsContext
     */
    private $robotsContext;

    /**
     * @var MetaContext
     */
    private $metaContext;

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope): void
    {
        $env = $scope->getEnvironment();

        if ($env instanceof InitializedContextEnvironment) {
            $this->robotsContext = $env->getContext(RobotsContext::class);
            $this->metaContext   = $env->getContext(MetaContext::class);
        }
    }

    /**
     * @Then the page should not be indexable
     */
    public function thePageShouldNotBeIndexable(): void
    {
        $this->assertInverse(
            [$this, 'thePageShouldBeIndexable'],
            'The page is indexable.'
        );
    }

    /**
     * @throws Exception
     *
     * @Then the page should be indexable
     */
    public function thePageShouldBeIndexable(): void
    {
        $this->metaContext->thePageShouldNotBeNoindex();
        $this->robotsContext->iShouldBeAbleToCrawl($this->getCurrentUrl());

        $robotsHeaderTag = $this->getResponseHeader('X-Robots-Tag');

        if ($robotsHeaderTag) {
            Assert::assertNotContains(
                'noindex',
                strtolower($robotsHeaderTag),
                sprintf(
                    'Url %s should not send X-Robots-Tag HTTP header with noindex value: %s',
                    $this->getCurrentUrl(),
                    $robotsHeaderTag
                )
            );
        }
    }
}
