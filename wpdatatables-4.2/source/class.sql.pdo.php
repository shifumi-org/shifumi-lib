<?php

defined('ABSPATH') or die('Access denied.');

/**
 * PDO abstract layer for the WPDataTables module
 *
 * */
class PDOSql {

    private $vendor;
    private $link;
    private $error;

    /**
     * Constructor
     * @param string $dsn
     * @param string $user
     * @param string $password
     * @throws \Exception
     */
    public function __construct( $vendor, $dsn, $user, $password ) {
        $this->vendor = $vendor;

        try {
            $this->link = new PDO ($dsn,"$user","$password");
        } catch (PDOException $e) {
            throw new Exception('There was a problem with your SQL connection - ' .((is_admin()) ? $e->getMessage() : 'Please contact the administrator'));
        }
    }

    /**
     * Determines if the connection is established
     */
    public function isConnected(){
        return !empty( $this->link );
    }

    /**
     * Do a query without expected result (insert, update, delete)
     * separated by comma
     * @return boolean
     */
    public function doQuery() {
        if ($result = $this->prepare(func_get_args())) {
            return true;
        }

        return false;
    }

    /**
     * Get a single field value from query result
     * separated by comma
     * @return boolean Get
     */
    public function getField() {
        if ($stmt = $this->prepare(func_get_args())) {
            $row = $stmt->fetch(PDO::FETCH_NUM);

            return $row[0];
        }

        return false;
    }

    /**
     * Get a single row from query result
     * separated by comma
     * @return boolean
     */
    public function getRow() {
        if ($stmt = $this->prepare(func_get_args())) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row;
        }

        return false;
    }

    /**
     * Get all results of a query as an indexed array
     *
     * @return array|bool
     */
    public function getArray() {
        $tmp = null;

        if ($stmt = $this->prepare(func_get_args())) {
            return $stmt->fetchAll(PDO::FETCH_NUM);
        }

        return false;
    }

    /**
     * Get all results of a query as an assoc array
     * separated by comma
     * @return array|bool
     */
    public function getAssoc() {
        if ($stmt = $this->prepare(func_get_args())) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        return false;
    }

    /**
     * Returns the last PDO error
     */
    public function getLastError(){
        return $this->error;
    }

    /**
     * Prepares the query and the parameters passed
     */
    private function prepare($properties) {
        $this->error = '';
        $stmt = null;

        try {
            $stmt = $this->link->query($properties[0]);
        } catch(PDOException $e) {
            $this->error = $e->getMessage();
            return false;
        }

        return $stmt;
    }

    public function getLastInsertId(){
        return $this->link->lastInsertId();
    }

}