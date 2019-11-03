<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsynchronousImportSourceDataRetrieving\Model\SourceDataRetrievingStrategy;

use Magento\AsynchronousImportSourceDataRetrievingApi\Api\Data\SourceInterface;
use Magento\AsynchronousImportSourceDataRetrievingApi\Model\RetrieveSourceDataStrategyInterface;

/**
 * Json strategy for retrieving source data
 */
class Json implements RetrieveSourceDataStrategyInterface
{
    /**
     * @inheritdoc
     */
    public function execute(SourceInterface $source): \Traversable
    {
        $raw = $source->getSourceDefinition();
        $data = json_decode(base64_decode($raw), true);
        return new \ArrayIterator($data);
    }
}
