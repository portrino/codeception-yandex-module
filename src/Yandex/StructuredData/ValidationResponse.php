<?php

/**
 * @namespace
 */
namespace Codeception\Module\Yandex\StructuredData;

/**
 * Class ValidationResponse
 * @package Codeception\Module\Yandex\StructuredData
 */
class ValidationResponse
{
    const MICROFORMAT = 'microformat';
    const RDFA = 'rdfa';
    const MICRODATA = 'microdata';
    const JSONLD = 'json-ld';

    /**
     * @var string
     */
    protected $id;

    /**
     * @var mixed
     */
    protected $data;

    /**
     * ValidationResponse constructor.
     * @param string $id
     * @param array $data
     */
    public function __construct($id, array $data)
    {
        $this->id = $id;
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id)
    {
        $this->id = $id;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * @param string $format
     * @return bool
     */
    public function isValid($format = '')
    {
        $result = true;
        $data = $this->getData();

        if ($format === '') {
            foreach ($data as $formatData) {
                if (is_array($formatData)) {
                    foreach ($formatData as $singleFormatData) {
                        if (isset($singleFormatData['#error'])) {
                            $result = false;
                            break;
                        }
                    }
                }
            }
        } else {
            if (isset($data[$format])) {
                $formatData = $data[$format];
                foreach ($formatData as $singleFormatData) {
                    if (isset($singleFormatData['#error'])) {
                        $result = false;
                        break;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @param string $format
     * @return array
     */
    public function getErrors($format = '')
    {
        $result = [];
        $data = $this->getData();
        if ($format === '') {
            foreach ($data as $formatData) {
                if (is_array($formatData)) {
                    foreach ($formatData as $singleFormatData) {
                        if (isset($singleFormatData['#error'])) {
                            if (is_array($singleFormatData['#error'])) {
                                foreach ($singleFormatData['#error'] as $error) {
                                    $result[] = $error;
                                }
                            } else {
                                $result[] = $singleFormatData['#error'];
                            }
                        }
                    }
                }
            }
        } else {
            if (isset($data[$format])) {
                $formatData = $data[$format];
                foreach ($formatData as $singleFormatData) {
                    if (isset($singleFormatData['#error'])) {
                        if (is_array($singleFormatData['#error'])) {
                            foreach ($singleFormatData['#error'] as $error) {
                                $result[] = $error;
                            }
                        } else {
                            $result[] = $singleFormatData['#error'];
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @param string $format
     * @return array
     */
    public function getErrorsFormatted($format = '')
    {
        $result = [];
        $errors = $this->getErrors($format);
        foreach ($errors as $error) {
            $result[] = implode(';', $error);
        }
        return $result;
    }
}
