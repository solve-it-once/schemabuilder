<?php
namespace NYPL\Schema;

use NYPL\Schema\Model\Schema;

abstract class Outputter
{
    const SCHEMA_BASE_URL = 'http://schema.org';

    /**
     * @var Schema
     */
    public $schema;

    /**
     * @param Schema $schema
     */
    public function __construct(Schema $schema)
    {
        $this->setSchema($schema);
    }

    /**
     * @return Schema
     */
    protected function getSchema()
    {
        return $this->schema;
    }

    /**
     * @param Schema $schema
     */
    protected function setSchema(Schema $schema)
    {
        $this->schema = $schema;
    }
}
