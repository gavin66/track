<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/6/1
 * Time: 14:43
 */

namespace Track\Http\Concerns;


use Track\Support\Str;

/**
 * Trait RequestWithContentTypes
 * 请求 content type 相关
 *
 * @package Track\Http\Concerns
 */
trait RequestWithContentTypes
{
    /**
     * 发送的请求是否是 json 类型
     *
     * @return mixed
     */
    public function isJson()
    {
        return Str::contains( $this->header( 'CONTENT_TYPE' ), [ '/json', '+json' ] );
    }

    /**
     * 当前请求是否可接受任何返回类型(content type)
     *
     * @return bool
     */
    public function acceptsAnyContentType()
    {
        $acceptable = $this->getAcceptableContentTypes();

        return count($acceptable) === 0 || (
                isset($acceptable[0]) && ($acceptable[0] === '*/*' || $acceptable[0] === '*')
            );
    }

    /**
     * 当前请求是否可返回 json 类型
     *
     * @return bool
     */
    public function expectsJson()
    {
        return ($this->ajax() && ! $this->pjax() && $this->acceptsAnyContentType()) || $this->wantsJson();
    }

    /**
     * 当前请求是否强制返回 json 类型
     *
     * @return bool
     */
    public function wantsJson()
    {
        $acceptable = $this->getAcceptableContentTypes();

        return isset($acceptable[0]) && Str::contains($acceptable[0], ['/json', '+json']);
    }


}