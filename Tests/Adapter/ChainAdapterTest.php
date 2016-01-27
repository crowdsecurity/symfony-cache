<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Cache\Tests\Adapter;

use Cache\IntegrationTests\CachePoolTest;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\ChainAdapter;
use Symfony\Component\Cache\Tests\Fixtures\ExternalAdapter;

/**
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class ChainAdapterTest extends CachePoolTest
{
    protected $skippedTests = array(
        'testDeferredSaveWithoutCommit' => 'Assumes a shared cache which ArrayAdapter is not.',
        'testSaveWithoutExpire' => 'Assumes a shared cache which ArrayAdapter is not.',
        'testDeferredExpired' => 'Failing for now, needs to be fixed.',
    );

    public function createCachePool()
    {
        if (defined('HHVM_VERSION')) {
            $this->skippedTests['testDeferredSaveWithoutCommit'] = 'Fails on HHVM';
        }
        if (!function_exists('apcu_fetch') || !ini_get('apc.enabled') || ('cli' === PHP_SAPI && !ini_get('apc.enable_cli'))) {
            $this->markTestSkipped('APCu extension is required.');
        }

        return new ChainAdapter(array(new ArrayAdapter(), new ExternalAdapter(), new ApcuAdapter(__CLASS__)));
    }

    /**
     * @expectedException \Symfony\Component\Cache\Exception\InvalidArgumentException
     */
    public function testLessThanTwoAdapterException()
    {
        new ChainAdapter(array());
    }

    /**
     * @expectedException \Symfony\Component\Cache\Exception\InvalidArgumentException
     */
    public function testInvalidAdapterException()
    {
        new ChainAdapter(array(new \stdClass(), new \stdClass()));
    }
}
