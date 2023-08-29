<?php

namespace Adyen\Core\Tests\BusinessLogic\Common\MockComponents;

use Adyen\Core\Infrastructure\ORM\QueryFilter\QueryFilter;

trait MockConditionalDelete
{
    public function deleteWhere(QueryFilter $queryFilter = null): void
    {
        // IMPORTANT NOTICE:
        // This is a mock implementation and it
        // should not be used as a implementation guideline.
        $entities = $this->select($queryFilter);
        foreach ($entities as $entity) {
            $this->delete($entity);
        }
    }
}
