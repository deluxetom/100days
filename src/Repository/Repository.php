<?php

namespace Days\Repository;

abstract class Repository
{
    protected $dbRead;
    protected $dbWrite;
    protected $tableName;
    protected $primaryKey;

    public function __construct($dbConnectorRead, $dbConnectorWrite, $tableName, $primaryKey)
    {
        $this->dbRead = $dbConnectorRead;
        $this->dbWrite = $dbConnectorWrite;
        $this->tableName = $tableName;
        $this->primaryKey = $primaryKey;
    }

    public function add(array $data, $ignore = false)
    {
        try {
            $this->dbWrite->insert($this->tableName, $data);
        } catch (\Exception $e) {
            if ($e->getCode() == $this->getDuplicateEntryExceptionCode() && $ignore) {
                return false;
            } else {
                throw $e;
            }
        }
        return $this->dbWrite->lastInsertId();
    }

    public function insert(array $data, $ignore = false)
    {
        return $this->findByPk($this->add($data, $ignore));
    }

    public function all()
    {
        $query = sprintf('SELECT * FROM `%s`', $this->tableName);
        return $this->getRows($query);
    }

    public function deleteByPk($value)
    {
        return $this->deleteOneBy($this->primaryKey, $value);
    }

    public function deleteOneBy($field, $value)
    {
        $result = $this->dbWrite->createQueryBuilder()
            ->delete($this->tableName)
            ->where(sprintf('%s=:key', $field))
            ->setParameter('key', $value)
            ->setMaxResults(1)
            ->execute();
        return $result;
    }

    public function deleteByConditions($conditions)
    {
        $qb = $this->dbRead->createQueryBuilder()
            ->delete($this->tableName);
        foreach ($conditions as $field => $value) {
            $qb->andWhere($field . '=:key_'.$field)
                ->setParameter('key_'.$field, $value);
        }
        return $qb->execute();
    }

    public function findBy($field, $value, array $fieldsToRetrieve = array())
    {
        $rows = $this->dbRead->createQueryBuilder()
            ->select(empty($fieldsToRetrieve) ? '*' : implode(',', $fieldsToRetrieve))
            ->from($this->tableName, 't')
            ->where('t.' . $field . '=:key')
            ->setParameter('key', $value)
            ->execute()
            ->fetchAll();
        return $rows;
    }

    public function findByPk($value, array $fieldsToRetrieve = array())
    {
        return $this->findOneBy($this->primaryKey, $value, $fieldsToRetrieve);
    }

    public function findOneBy($field, $value, array $fieldsToRetrieve = array())
    {
        $row = $this->dbRead->createQueryBuilder()
            ->select(empty($fieldsToRetrieve) ? '*' : implode(',', $fieldsToRetrieve))
            ->from($this->tableName, 't')
            ->where('t.' . $field . '=:key')
            ->setParameter('key', $value)
            ->setMaxResults(1)
            ->execute()
            ->fetch();
        return $row;
    }

    public function findOneByConditions($conditions, array $fieldsToRetrieve = array())
    {
        $qb = $this->dbRead->createQueryBuilder()
            ->select(empty($fieldsToRetrieve) ? '*' : implode(',', $fieldsToRetrieve))
            ->from($this->tableName, 't');
        foreach ($conditions as $field => $value) {
            $qb->andWhere('t.' . $field . '=:key_'.$field)
                ->setParameter('key_'.$field, $value);
        }
        $qb->setMaxResults(1);
        return $qb->execute()->fetch();
    }

    protected function formatFieldsForSelectQuery(array $fields = array())
    {
        if (empty($fields)) {
            return '*';
        }
        $inlineFields = '`' . implode('`,`', $fields) . '`';
        return $inlineFields;
    }

    private function getDuplicateEntryExceptionCode()
    {
        return 23000;
    }

    public function getRows($query)
    {
        $statement = $this->dbRead->prepare($query);
        $statement->execute();
        $rows = $statement->fetchAll();
        return $rows;
    }

    public function increment($field, $value, array $identifier)
    {
        $queryMask = 'UPDATE %s SET %s = %s + %d WHERE %s = ?';
        $conditions = implode(' = ? AND ', array_keys($identifier));
        $query = sprintf($queryMask, $this->tableName, $field, $field, $value, $conditions);
        return $this->dbWrite->executeUpdate($query, array_values($identifier));
    }

    public function update(array $data, $conditions)
    {
        return $this->dbWrite->update($this->tableName, $data, $conditions);
    }

    public function updateWithPrimaryKey(array $data, $keyValue)
    {
        return $this->update($data, array($this->primaryKey => $keyValue));
    }

    public function getSingleValueFromQuery($query)
    {
        $statement = $this->dbRead->prepare($query);
        $statement->execute();
        return $this->getSingleValueFrom($statement);
    }

    public function getMinimumPrimaryKey()
    {
        $queryMask = 'SELECT MIN(`%s`) FROM `%s`';
        $query = sprintf($queryMask, $this->primaryKey, $this->tableName);
        return $this->getSingleValueFromQuery($query);
    }

    public function getMaximumPrimaryKey()
    {
        $queryMask='SELECT MAX(`%s`) FROM `%s`';
        $query = sprintf($queryMask, $this->primaryKey, $this->tableName);
        return $this->getSingleValueFromQuery($query);
    }

    public function getSingleValueFrom($statement)
    {
        $row = $statement->fetch();
        return $row[0];
    }

    public function findAll($fieldsToFilter = array(), $fieldsToRetrieve = array(), $fieldsToOrder = array(), $maxRows = null)
    {
        // Execute query
        $qb = $this->dbRead->createQueryBuilder()
            ->select(empty($fieldsToRetrieve) ? '*' : implode(',', $fieldsToRetrieve))
            ->from($this->tableName, 't');
        foreach ($fieldsToFilter as $field => $value) {
            if (is_array($value)) {
                $qb->andWhere('t.' . $field . " IN ('" . implode("', '", $value) . "')");
            } else {
                $qb->andWhere('t.' . $field . '=:key_'.$field)
                    ->setParameter('key_'.$field, $value);
            }
        }
        foreach ($fieldsToOrder as $field => $order) {
            $qb->addOrderBy($field, $order);
        }
        if (!is_null($maxRows) && is_numeric($maxRows)) {
            $qb->setMaxResults($maxRows);
        }
        return $qb->execute()
            ->fetchAll();
    }
}