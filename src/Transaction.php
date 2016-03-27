<?php

namespace DKulyk\Eloquent;

use Exception;
use Illuminate\Database\Eloquent\Model as Eloquent;

/**
 * Trait Transaction.
 *
 * @mixin Eloquent
 *
 * @property bool $savingTransaction
 */
trait Transaction
{
    /**
     * Save the model to the database.
     *
     * @param array $options
     *
     * @throws Exception
     *
     * @return bool
     */
    public function save(array $options = [])
    {
        if (!property_exists($this, 'savingTransaction') || $this->savingTransaction) {
            return $this->saveWithinTransaction($options);
        }

        return parent::save($options);
    }

    /**
     * Save the model to the database with in transaction.
     *
     * @param array $options
     *
     * @throws Exception
     *
     * @return bool
     */
    public function saveWithinTransaction(array $options = [])
    {
        $connection = $this->getConnection();
        $connection->beginTransaction();
        try {
            if ($saved = parent::save($options)) {
                $connection->commit();
            } else {
                $connection->rollBack();
            }

            return $saved;
        } catch (Exception $e) {
            $connection->rollBack();
            throw  $e;
        }
    }
}
