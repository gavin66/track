<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/8/17
 * Time: 14:43
 */

namespace Track\WeChat\Foundation;


use Psr\Http\Message\ResponseInterface;

trait ResponseTrait
{
    protected function castResponseToType( ResponseInterface $response, $type = 'array' )
    {
        switch ( $type ) {
            case 'array':
                return $this->toArray( $response );
            case 'object':
                return $this->toObject( $response );
            default:
                throw new \InvalidArgumentException( '未设置返回类型' );
        }
    }

    protected function toArray( ResponseInterface $response )
    {
        $content = $response->getBody()->getContents();

        if ( false !== stripos( $response->getHeaderLine( 'Content-Type' ), 'xml' ) || 0 === stripos( $content, '<xml' ) ) {
            return XML::parse( $content );
        }

        $array = json_decode( $content, true );

        if ( JSON_ERROR_NONE === json_last_error() ) {
            return (array)$array;
        }

        return [];
    }

    protected function toObject( ResponseInterface $response )
    {
        return (object)$this->toArray( $response );
    }

}