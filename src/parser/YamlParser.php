<?php
namespace hehe\core\hconfigure\parser;

/**
 * Yaml 格式解析
 *<B>说明：</B>
 *<pre>
 *  略
 *</pre>
 */
class YamlParser
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
        return  \yaml_parse(file_get_contents($file));
    }
}
