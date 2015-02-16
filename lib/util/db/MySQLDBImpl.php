<?php
namespace ChristianBudde\Part\util\db;

use ChristianBudde\Part\Config;
use ChristianBudde\Part\util\file\File;
use ChristianBudde\Part\util\file\Folder;
use ChristianBudde\Part\util\file\FolderImpl;
use PDO;

/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 6/11/12
 * Time: 10:09 AM
 * To change this template use File | Settings | File Templates.
 */
class MySQLDBImpl implements DB
{
    /** @var  PDO */
    private $connection = null;
    private $database = null;
    private $host = null;
    private $password = null;
    private $username = null;
    private $folders = [];

    /** @var PDO */
    private $mailConnection = null;
    private $mailDatabase = null;
    private $mailHost = null;
    private $mailUsername = null;
    private $mailPassword = null;

    private $version;


    public function __construct(Config $config)
    {

        $connectionArray = $config->getMySQLConnection();
        if ($connectionArray !== null) {
            $this->database = $connectionArray['database'];
            $this->host = $connectionArray['host'];
            $this->password = $connectionArray['password'];
            $this->username = $connectionArray['user'];
            $this->folders = $connectionArray['folders'];
        }

        $connectionArray = $config->getMailMySQLConnection();
        if ($connectionArray !== null) {
            $this->mailDatabase = $connectionArray['database'];
            $this->mailHost = $connectionArray['host'];
            $this->mailUsername = $connectionArray['user'];
        }
    }


    /**
     * This returns the current connection, with info provided in config.
     * @return PDO
     */
    public function getConnection()
    {
        if ($this->connection === null) {
            $this->connection = new PDO(
                'mysql:dbname=' . $this->database . ';host=' . $this->host,
                $this->username,
                $this->password,
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        }

        return $this->connection;
    }

    /**
     * @param string $password
     * @return PDO
     */
    public function getMailConnection($password)
    {
        if ($this->mailConnection === null || $this->mailPassword != $password) {
            $this->mailPassword = $password;
            $this->mailConnection = new PDO(
                'mysql:dbname=' . $this->mailDatabase . ';host=' . $this->mailHost,
                $this->mailUsername,
                $password,
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        }

        return $this->mailConnection;
    }

    /**
     * Updates the database according to the sql files
     * in the designated db folders.
     *
     * @return void
     */
    public function update()
    {
        $this->setUpVersion();
        $connection = $this->getConnection();
        foreach ($this->folders as $name => $path) {
            $folder = new FolderImpl($path);
            if (!$folder->exists()) {
                continue;
            }
            /** @var File[] $files */
            $files = $folder->listFolder(Folder::LIST_FOLDER_FILES);

            usort($files, function (File $a, File $b) {
                return $this->leadingNumber($a->getFilename()) - $this->leadingNumber($b->getFilename());
            });
            $lastFile = null;
            $version = $this->getVersion($name);
            foreach ($files as $file) {
                if ($file->getExtension() != 'sql') {
                    continue;
                }

                if ($this->leadingNumber($file->getFilename()) <= $version) {
                    continue;
                }

                $stmt = $connection->prepare($file->getContents());
                $stmt->execute();

                while ($stmt->nextRowset()) ; //Polling row sets

                $lastFile = $file;
            }
            if ($lastFile == null) {
                continue;
            }
            $this->setVersion($name, $this->leadingNumber($lastFile->getFilename()));

        }
    }

    /**
     * @param string $name
     * @return array|string If $name is not empty a version string will be returned else an array containing
     *                      name=>version entries.
     */
    public
    function getVersion($name = "")
    {
        $this->setUpVersion();
        return empty($name) ? $this->version : (isset($this->version[$name]) ? $this->version[$name] : 0);
    }


    private
    function leadingNumber($string)
    {
        preg_match("/^[0-9]+/", $string, $matches);
        return intval($matches[0]);
    }

    private
    function setUpVersion()
    {
        if ($this->version != null) {
            return;
        }
        $this->version = [];
        try {
            $stm = $this->getConnection()->query("SELECT * FROM _db_version");
        } catch (\PDOException $e) {
            return;
        }
        while ($r = $stm->fetch(PDO::FETCH_ASSOC)) {
            $this->version[$r['name']] = $r['version'];
        }


    }

    private
    function setVersion($name, $version)
    {

        $connection = $this->getConnection();
        $connection->beginTransaction();
        $connection->exec("
        CREATE TABLE IF NOT EXISTS `_db_version` (
        `name` VARCHAR(255) NOT NULL,
        `version` INT(255) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

        try {
            $connection->exec("
            ALTER TABLE `_db_version`
            ADD UNIQUE KEY `name` (`name`);");

        } catch (\PDOException $p) {

        }


        $connection->commit();
        $connection->prepare("
        INSERT INTO _db_version
          (name, version)
        VALUES
          (?, ?)
        ON DUPLICATE KEY UPDATE
  version     = VALUES(version)")->execute([$name, $version]);

        $this->version[$name] = $version;
    }
}
