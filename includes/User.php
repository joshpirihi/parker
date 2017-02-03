<?php

class User {
    
    /**
     *
     * @var int
     */
    public $id;
    
    /**
     *
     * @var string
     */
    public $username;
    
    /**
     *
     * @var string
     */
    private $password;
    
    /**
     *
     * @var string
     */
    private $salt;
    
    /**
     * Returns a User instance if the login details are correct, otherwise false
     * 
     * @param type $username
     * @param type $password
     * @return User|false
     */
    public static function verify($username, $password) {
        
        $rows = dbh_query('SELECT * FROM `users` WHERE `username` = ?;', [$username]);
        
        if (count($rows) == 0) {
            return false;
        }
        
        $instance = new self();
        $instance->loadFromDBRow($rows[0]);
        
        if (sha1($password.$instance->salt) == $instance->password) {
            return $instance;
        }
        
        return false;
    }
    
    private function loadFromDBRow($row) {
        $this->id = $row['id'];
        $this->username = $row['username'];
        $this->password = $row['password'];
        $this->salt = $row['salt'];
    }
    
}
