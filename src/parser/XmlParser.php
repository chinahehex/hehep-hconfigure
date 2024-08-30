<?php
namespace hehe\core\hconfigure\parser;

/**
 * Xml 格式解析
 *<B>说明：</B>
 *<pre>
 *  略
 *</pre>
 */
class XmlParser
{
    /**
     * 解析配置信息
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param string $file 配置信息
     * @return array
     */
    public static  function parse(string $file):array
    {
        $content = simplexml_load_string(file_get_contents($file));

        $result = (array) $content;
        foreach ($result as $key => $val) {
            if (is_object($val)) {
                $result[$key] = (array) $val;
            }
        }

        return $result;
    }
}
