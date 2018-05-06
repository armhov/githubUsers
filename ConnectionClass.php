<?php

namespace GitHub;

require_once __DIR__.'/ConnectDatabaseException.php';


class ConnectionClass
{
    /** @var string */
    private $host;
    /** @var string */
    private $user;
    /** @var string */
    private $password;
    /** @var string */
    private $database;
    /** @var \mysqli */
    private $connection;

    /**
     * ConnectionClass constructor.
     * @param string $host
     * @param string $user
     * @param string $password
     * @param string $database
     */
    public function __construct(string $host, string $user,string $password, string $database)
    {
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        $this->database = $database;

        $connection = mysqli_connect($this->host,$this->user,$this->password,$this->database);

        if (mysqli_connect_errno()) {
            throw new ConnectDatabaseException("Failed to connect to MySQL: " . mysqli_connect_error());
        }

        $this->connection = $connection;
    }

    /**
     * @return \mysqli
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @return bool|\mysqli_result
     * @throws ConnectDatabaseException
     */
    public function createUserTable()
    {
        $sql = "
            CREATE TABLE `user` (
              `github_id` int(11) UNSIGNED NOT NULL,
              `github_login` varchar(255) NOT NULL,
              PRIMARY KEY (github_id)
            ) ENGINE=InnoDB;
        ";

        return $this->getConnection()->query($sql);
    }

    /**
     * @param string $githubId
     * @return array|bool
     */
    public function findUser(string $githubId)
    {
        $sql = "
          SELECT * FROM `user` WHERE github_id = '%s';
        ";
        $sql = sprintf($sql, $githubId);

        return $this->getConnection()->query($sql)->fetch_assoc();
    }

    /**
     * @param string $githubId
     * @param string $githubLogin
     * @return bool
     */
    public function crateUser(string $githubId, string $githubLogin)
    {
        $sql = "
          INSERT INTO `user` (github_id, github_login)
          VALUES ('%s', '%s');
        ";
        $sql = sprintf($sql, $githubId, $githubLogin);


        return $this->getConnection()->query($sql);
    }

    /**
     * @param string $githubId
     * @param string $githubLogin
     * @return bool
     */
    public function updateUser(string $githubId, string $githubLogin)
    {
        $sql = "
          UPDATE `user` SET github_id = '%s', github_login = '%s' WHERE github_id = '%s';
        ";
        $sql = sprintf($sql, $githubId, $githubLogin, $githubId);

        return $this->getConnection()->query($sql);
    }
}