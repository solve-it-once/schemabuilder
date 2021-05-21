<?php

namespace NYPL\SchemaBuilder;

use NYPL\SchemaBuilder\Outputter\JsonLdOutputter;
use NYPL\SchemaBuilder\Outputter\MicrodataOutputter;
use Stringy\Stringy;

class Schema extends Model {

  const EXCEPTION_SCHEMA_TYPE_REQUIRED = 'Type is required for Schema.org object';

  const EXCEPTION_SCHEMA_TYPE_INVALID = 'Schema.org type does not appear to be valid';

  const EXCEPTION_PROPERTY_NAME_REQUIRED = 'Property name is required';

  const EXCEPTION_PROPERTY_VALUE_EMPTY = 'Property value cannot be null';

  const EXCEPTION_PROPERTY_VALUE_INVALID = 'Property value does not appear to be a valid type';

  const EXCEPTION_PROPERTY_DOES_NOT_EXIST = 'Property specified does not exist';

  const EXCEPTION_PROPERTY_ALREADY_EXISTS = 'Property specified already exists';

  /**
   * @var string
   */
  public $type = '';

  /**
   * @var string
   */
  public $schemaId = '';

  /**
   * @var array
   */
  public $properties = [];

  /**
   * @var MicrodataOutputter
   */
  public $microdataOutputter;

  /**
   * @var JsonLdOutputter
   */
  public $jsonLdOutputter;

  /**
   * @var string
   */
  public $parentPropertyName = '';

  /**
   * @param string $type
   */
  public function __construct($type = '') {
    $this->checkType($type);

    $this->setType($type);
  }

  /**
   * @param string $type
   */
  public function checkType($type = '') {
    if (!$type) {
      throw new \BadMethodCallException(self::EXCEPTION_SCHEMA_TYPE_REQUIRED);
    }

    if (Stringy::create(substr($type, 0, 1))->isLowerCase()) {
      throw new \InvalidArgumentException(self::EXCEPTION_SCHEMA_TYPE_INVALID . ': ' . $type);
    }
  }

  /**
   * @return string
   */
  public function getType() {
    return $this->type;
  }

  /**
   * @param string $type
   */
  public function setType($type) {
    $this->type = $type;
  }

  /**
   * @param string $propertyName
   * @param mixed $propertyValue
   */
  public function addProperty($propertyName = '', $propertyValue = NULL) {
    $this->checkPropertyName($propertyName);
    $this->checkPropertyValue($propertyValue);

    if ($this->isPropertyExists($propertyName)) {
      throw new \RuntimeException(self::EXCEPTION_PROPERTY_ALREADY_EXISTS);
    }

    if ($propertyValue instanceof Schema) {
      $propertyValue->setParentPropertyName($propertyName);
    }

    $this->appendProperty($propertyName, $propertyValue);
  }

  /**
   * @param string $propertyName
   */
  public function checkPropertyName($propertyName = '') {
    if (!$propertyName) {
      throw new \BadMethodCallException(self::EXCEPTION_PROPERTY_NAME_REQUIRED);
    }
  }

  /**
   * @param mixed $propertyValue
   *
   * @return void
   */
  public function checkPropertyValue($propertyValue = NULL) {
    if ($propertyValue === NULL) {
      throw new \InvalidArgumentException(self::EXCEPTION_PROPERTY_VALUE_EMPTY . ': ' . $propertyValue);
    }

    if (!is_bool($propertyValue) && !is_int($propertyValue) && !is_float($propertyValue) &&
      !is_string($propertyValue) && !$propertyValue instanceof Schema
    ) {
      throw new \InvalidArgumentException(
        self::EXCEPTION_PROPERTY_VALUE_INVALID . ': ' . gettype($propertyValue)
      );
    }
  }

  /**
   * @param string $propertyName
   *
   * @return bool
   */
  public function isPropertyExists($propertyName = '') {
    if (!array_key_exists($propertyName, $this->properties)) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * @param string $propertyName
   * @param mixed $propertyValue
   */
  public function appendProperty($propertyName = '', $propertyValue = NULL) {
    $this->properties[$propertyName] = $propertyValue;
  }

  /**
   * @param string $propertyName
   * @param string $wrapper
   * @param WrapperAttribute[] $wrapperAttributes
   *
   * @return void
   */
  public function outputMicrodata($propertyName = '', $wrapper = '', array $wrapperAttributes = []) {
    echo $this->getMicrodata($propertyName, $wrapper, $wrapperAttributes);
  }

  /**
   * @param string $propertyName
   * @param string $wrapper
   * @param WrapperAttribute[] $wrapperAttributes
   *
   * @return string
   */
  public function getMicrodata($propertyName = '', $wrapper = '', array $wrapperAttributes = []) {
    return $this->getMicrodataOutputter()
      ->get($propertyName, $wrapper, $wrapperAttributes);
  }

  /**
   * @return MicrodataOutputter
   */
  public function getMicrodataOutputter() {
    if (!$this->microdataOutputter) {
      $this->setMicrodataOutputter(new MicrodataOutputter($this));
    }

    return $this->microdataOutputter;
  }

  /**
   * @param MicrodataOutputter $microdataOutputter
   */
  public function setMicrodataOutputter(MicrodataOutputter $microdataOutputter) {
    $this->microdataOutputter = $microdataOutputter;
  }

  public function outputJsonLd() {
    echo $this->getJsonLd();
  }

  public function getJsonLd() {
    return $this->getJsonLdOutputter()->get();
  }

  /**
   * @return JsonLdOutputter
   */
  public function getJsonLdOutputter() {
    if (!$this->jsonLdOutputter) {
      $this->setJsonLdOutputter(new JsonLdOutputter($this));
    }

    return $this->jsonLdOutputter;
  }

  /**
   * @param JsonLdOutputter $jsonLdOutputter
   */
  public function setJsonLdOutputter($jsonLdOutputter) {
    $this->jsonLdOutputter = $jsonLdOutputter;
  }

  /**
   * @param string $propertyName
   *
   * @return void
   */
  public function outputProperty($propertyName = '') {
    echo $this->getProperty($propertyName);
  }

  /**
   * @param string $propertyName
   *
   * @return string|Schema
   */
  public function getProperty($propertyName = '') {
    $this->checkPropertyName($propertyName);

    if (!$this->isPropertyExists($propertyName)) {
      throw new \OutOfBoundsException(self::EXCEPTION_PROPERTY_DOES_NOT_EXIST . ': ' . $propertyName);
    }
    else {
      return $this->properties[$propertyName];
    }
  }

  /**
   * @return string
   */
  public function getParentPropertyName() {
    return $this->parentPropertyName;
  }

  /**
   * @param string $parentPropertyName
   */
  public function setParentPropertyName($parentPropertyName) {
    $this->parentPropertyName = $parentPropertyName;
  }

  /**
   * @return array
   */
  public function getProperties() {
    return $this->properties;
  }

  /**
   * @return string
   */
  public function getSchemaId() {
    return $this->schemaId;
  }

  /**
   * @param string $schemaId
   */
  public function setSchemaId($schemaId) {
    $this->schemaId = $schemaId;
  }
}
