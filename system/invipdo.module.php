<?php
/**
 * Class inviPDO extends PDO and redefines some his methods for simplifying work with it 
 * 
 * @author Andrey "SkaN" Kamozin <andreykamozin@gmail.com>
 */
class inviPDO extends PDO {
    /**
     * This variable init object of PDOStatement class
     * 
     * @var object Object of PDOStatement class
     */
    public $stmt;

    /**
    * Method for connect to database
    * 
    * @throws inviException In case of connection error
    * @return void
    */
    
    public function __construct()
    {
        // Get database server data from config
        $conn_data = Config::get("database");

        try { // Try to connect
            parent::__construct("mysql:host={$conn_data['server']};dbname={$conn_data['db']}", $conn_data['login'], $conn_data['password']);
        } catch ( PDOException $e ) { // If there's any errors, throw exception
            throw new inviException( (int)$e->getCode(), $e->getMessage() );
        }

        // Errors will throw exceptions
        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    }
    
    /**
     * Method executes query given with data if any.
     * 
     * @param type $query
     * @param type $data
     * @throws inviException In case of MySQL error
     */
    public function query($query, $data = array())
    {
        // Prepare query
        $this->stmt = $this->prepare($query);
        
        // Execute statement with data given
        $this->stmt->execute((array)$data);
        
        // Check for errors. If any, throw inviException
        if ( $this->stmt->errorCode() != "00000" )
        {
            $error = $this->stmt->errorInfo();
            throw new inviException( $error[0], $error[2] );
        }
    }

    /**
     * Method fetch is required for getting data returned by server
     * 
     * @param string $fetch_mode Should be assoc or num. If unknown mode given, will be fetched assoc
     * @return array Multi-dimensional array with data returned or NULL
     */
    public function fetch($fetch_mode = "assoc")
    {
        // Set fetch mode, default is assoc.
        switch ($fetch_mode)
        {
            case "num":
                $this->stmt->setFetchMode(PDO::FETCH_NUM);
                break;
            case "assoc":
            default:
                $this->stmt->setFetchMode(PDO::FETCH_ASSOC);
        }
        
        // If nothing is returned, throw exception
        if ($this->stmt->rowCount() == 0)
        {
            return NULL;
        }
        
        // Fetch $data array with rows
        $data = array();
        while ($row = $this->stmt->fetch())
        {
            $data[] = $row;
        }
        return $data;
    }
}