<?php
/**
 * Created by PhpStorm.
 * User: Gavin
 * Date: 2018/6/22
 * Time: 14:47
 */

namespace Track\Session;


interface SessionContract
{
    /**
     * Get the name of the session.
     *
     * @return string
     */
    public function getName();

    /**
     * Get the current session ID.
     *
     * @return string
     */
    public function getId();

    /**
     * Set the session ID.
     *
     * @param  string $id
     *
     * @return void
     */
    public function setId( $id );

    /**
     * Start the session, reading the data from a handler.
     *
     * @return bool
     */
    public function start();

    /**
     * Save the session data to storage.
     *
     * @return bool
     */
    public function save();

    /**
     * Get all of the session data.
     *
     * @return array
     */
    public function all();

    /**
     * Checks if a key exists.
     *
     * @param  string|array $key
     *
     * @return bool
     */
    public function exists( $key );

    /**
     * Checks if an a key is present and not null.
     *
     * @param  string|array $key
     *
     * @return bool
     */
    public function has( $key );

    /**
     * Get an item from the session.
     *
     * @param  string $key
     * @param  mixed  $default
     *
     * @return mixed
     */
    public function get( $key, $default = null );

    /**
     * Put a key / value pair or array of key / value pairs in the session.
     *
     * @param  string|array $key
     * @param  mixed        $value
     *
     * @return void
     */
    public function put( $key, $value = null );

    /**
     * Get the CSRF token value.
     *
     * @return string
     */
    public function token();

    /**
     * Remove an item from the session, returning its value.
     *
     * @param  string $key
     *
     * @return mixed
     */
    public function remove( $key );

    /**
     * Remove one or many items from the session.
     *
     * @param  string|array $keys
     *
     * @return void
     */
    public function forget( $keys );

    /**
     * Remove all of the items from the session.
     *
     * @return void
     */
    public function flush();

    /**
     * Generate a new session ID for the session.
     *
     * @param  bool $destroy
     *
     * @return bool
     */
    public function migrate( $destroy = false );

    /**
     * Get the session handler instance.
     *
     * @return \SessionHandlerInterface
     */
    public function getHandler();

}