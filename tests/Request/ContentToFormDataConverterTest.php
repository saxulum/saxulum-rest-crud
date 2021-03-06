<?php

namespace Saxulum\Tests\RestCrud\Request;

use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;
use Saxulum\RestCrud\Request\ContentToFormDataConverter;
use Saxulum\RestCrud\Request\Converter\JsonConverter;
use Saxulum\RestCrud\Request\Converter\XmlConverter;
use Symfony\Component\HttpFoundation\Request;

class ContentToFormDataConverterTest extends \PHPUnit_Framework_TestCase
{
    public function testConvertJson()
    {
        $converter = $this->getConverter();

        $formData = array(
            'form' => array(
                'field1' => 1,
                'field2' => 2,
                'field3' => array(
                    'subfield1' => 1,
                    'subfield2' => 2,
                ),
            ),
        );

        $originalRequest = new Request(
            array(),
            array(),
            array(),
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode($formData)
        );

        $request = $converter->convert($originalRequest);

        $this->assertSame($originalRequest, $request);

        $this->assertEquals('application/x-www-form-urlencoded', $request->headers->get('content-type'));
        $this->assertEquals($formData, $request->request->all());
        $this->assertEquals(http_build_query($formData), $request->getContent());
    }

    public function testConvertXml()
    {
        $converter = $this->getConverter();

        $xml = '<?xml version="1.0" encoding="UTF-8"?><form name="form"><form name="field1">1</form><form name="field2">2</form><form name="field3"><form name="subfield1">1</form><form name="subfield2">2</form></form></form>';

        $formData = array(
            'form' => array(
                'field1' => 1,
                'field2' => 2,
                'field3' => array(
                    'subfield1' => 1,
                    'subfield2' => 2,
                ),
            ),
        );

        $originalRequest = new Request(
            array(),
            array(),
            array(),
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/xml'),
            $xml
        );

        $request = $converter->convert($originalRequest);

        $this->assertSame($originalRequest, $request);

        $this->assertEquals('application/x-www-form-urlencoded', $request->headers->get('content-type'));
        $this->assertEquals($formData, $request->request->all());
        $this->assertEquals(http_build_query($formData), $request->getContent());
    }

    /**
     * @return ContentToFormDataConverter
     */
    protected function getConverter()
    {
        $serializer = $this->getSerializer();

        return new ContentToFormDataConverter(array(
            new JsonConverter($serializer),
            new XmlConverter($serializer),
        ));
    }

    /**
     * @return SerializerInterface
     */
    protected function getSerializer()
    {
        return SerializerBuilder::create()->build();
    }
}
