<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/8/21
 * Time: 11:59
 */

namespace Track\WeChat\OfficialAccount;


use Track\WeChat\Foundation\HttpClient;

class Menu extends HttpClient
{

    /**
     *  获取所有菜单
     *
     * @return mixed
     */
    public function menuList()
    {
        return $this->httpGet( 'cgi-bin/menu/get' );
    }

    /**
     * Get current menus.
     *
     * @return mixed
     */
    public function current()
    {
        return $this->httpGet( 'cgi-bin/get_current_selfmenu_info' );
    }

    /**
     * Add menu.
     *
     * @param array $buttons
     * @param array $matchRule
     *
     * @return mixed
     */
    public function create( array $buttons, array $matchRule = [] )
    {
        if ( ! empty( $matchRule ) ) {
            return $this->httpPostJson( 'cgi-bin/menu/addconditional', [
                'button'    => $buttons,
                'matchrule' => $matchRule,
            ] );
        }

        return $this->httpPostJson( 'cgi-bin/menu/create', [ 'button' => $buttons ] );
    }

    /**
     * Destroy menu.
     *
     * @param int $menuId
     *
     * @return mixed
     */
    public function delete( $menuId = null )
    {
        if ( is_null( $menuId ) ) {
            return $this->httpGet( 'cgi-bin/menu/delete' );
        }

        return $this->httpPostJson( 'cgi-bin/menu/delconditional', [ 'menuid' => $menuId ] );
    }

    /**
     * Test conditional menu.
     *
     * @param string $userId
     *
     * @return mixed
     */
    public function match( $userId )
    {
        return $this->httpPostJson( 'cgi-bin/menu/trymatch', [ 'user_id' => $userId ] );
    }
}