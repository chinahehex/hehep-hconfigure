<?php
namespace hehe\core\hconfigure\parser;

/**
 * ini 格式解析
 *<B>说明：</B>
 *<pre>
 *  略
 *</pre>
 */
class IniParser
{
    /**
     * 解析配置信息
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param string $file 配置文件
     * @return array
     */
    public static  function parse(string $file):array
    {
        return parse_ini_string(file_get_contents($file), true);
    }
}
