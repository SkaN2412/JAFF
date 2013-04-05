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
     * Open connection with database
     */
    public function __construct()
    {
        // Get database server data from config
        $conn_data = Config::get("database");

        try { // Try to connect
            parent::__construct("mysql:host={$conn_data['server']};dbname={$conn_data['db']}", $conn_data['login'], $conn_data['password']);
        } catch ( PDOException $e ) { // If there's any errors, throw exception
            throw new inviException( inviErrors::DB_CONN_FAIL, "[{$e->getCode}] {$e->getMessage}" );
        }

        // Errors will throw exceptions
        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    }

    /**
     * Method returns entries selected
     *
     * @param string $table Source table. If the only given, will return all entries
     * @param null $optionals [optional] Array with optional parameters. See the documentation page for more info.
     * @return array Multi-dimensional array with entries returned
     */
    public function selectEntries($table, $optionals = NULL)
    {
        $query = "SELECT ";
        // If there's optional parameters given
        if ($optionals != NULL)
        {
            if (isset($optionals['distinct']))
            {
                $query .= "DISTINCT ";
                unset($optionals['distinct']);
            }
            if (isset($optionals['rows']))
            { // Rows to return
                $rows = explode(", ", $optionals['rows']);
                foreach ($rows as $row)
                {
                    $query .= "`{$row}`";
                    if (end($rows) != $row)
                    {
                        $query .= ", ";
                    }
                }
                unset($rows, $row, $optionals['rows']);
            } else {
                $query .= "* ";
            }
            $query .= " FROM `".$table."`";
            if (isset($optionals['cases']))
            { // Cases
                $query .= " WHERE ";
                $data = array();
                foreach ($optionals['cases'] as $row => $v)
                {
                    $query .= "`{$row}` = ?";
                    $data[] = $v;
                    end($optionals['cases']);
                    if ($row != key($optionals['cases']))
                    {
                        $query .= " AND ";
                    }
                }
                unset($v, $row, $optionals['cases']);
            }
            if (isset($optionals['order']))
            {
                $optionals['order'] = explode(" ", $optionals['order']);
                $query .= " ORDER BY `{$optionals['order']}`";
                switch ($optionals['order'][1])
                {
                    case 'desc':
                        $query .= " DESC";
                        break;
                    case 'asc':
                    default:
                        $query .= " ASC";
                }
                unset($optionals['order']);
            }
            if (isset($optionals['limit']))
            {
                $query .= " LIMIT ".$optionals['limit'];
                unset($optionals['limit']);
            }
        } else {
            $query .= "* FROM `$table`";
            unset($table);
        }
        $this->query($query, $data);
        return $this->getReturnedData();
    }

    /**
     * Works similar to selectEntries, but returns number of entries
     *
     * @param string $table Source table.
     * @param array $optionals [optional] Array with optional parameters
     * @return int Number of entries in table
     */
    public function countEntries($table, $optionals = null)
    {
        $query = "SELECT ";
        if ($optionals != NULL)
        {
            if (isset($optionals['distinct']))
            {
                $query .= "DISTINCT ";
                unset($optionals['distinct']);
            }
            if (isset($optionals['rows']))
            {
                $rows = explode(", ", $optionals['rows']);
                $query .= "COUNT(";
                foreach ($rows as $row)
                {
                    $query .= "`{$row}`";
                    if (end($rows) != $row)
                    {
                        $query .= ", ";
                    } else {
                        $query .= ") ";
                    }
                }
                unset($rows, $row, $optionals['rows']);
            } else {
                $query .= "* ";
            }
            $query .= "FROM `".$table."`";
            if (isset($optionals['cases']))
            {
                $query .= " WHERE ";
                $data = array();
                foreach ($optionals['cases'] as $row => $v)
                {
                    $query .= "`{$row}` = ?";
                    $data[] = $v;
                    end($optionals['cases']);
                    if ($row != key($optionals['cases']))
                    {
                        $query .= " AND ";
                    }
                }
                unset($v, $row, $optionals['cases']);
            }
            if (isset($optionals['order']))
            {
                $optionals['order'] = explode(" ", $optionals['order']);
                $query .= " ORDER BY `{$optionals['order']}`";
                switch ($optionals['order'][1])
                {
                    case 'desc':
                        $query .= " DESC";
                        break;
                    case 'asc':
                    default:
                        $query .= " ASC";
                }
                unset($optionals['order']);
            }
            if (isset($optionals['limit']))
            {
                $query .= " LIMIT ".$optionals['limit'];
                unset($optionals['limit']);
            }
        } else {
            $query .= "COUNT(*) FROM `$table`";
            $optionals['cases'] = array();
            unset($table);
        }
        $this->query($query, $data);
        $data = $this->getReturnedData("num");
        return (int)$data[0][0];
    }

    /**
     * Selects only one entry from table
     *
     * @param string $table Source table
     * @param array $cases Cases of entry needed
     * @param string $rows [optional] Rows to select
     * @return array One-dimensional array with data
     */
    public function selectEntry($table, $cases, $rows = NULL) {
        $opt = array(
            'cases' => $cases,
            'limit' => "1"
        );
        if ($rows != NULL)
        {
            $opt['rows'] = $rows;
        }
        $data = $this->selectEntries($table, $opt);
        if ($data !== NULL)
        {
            return $data[0];
        } else {
            return NULL;
        }
    }

    /**
     * Create entry in the table
     *
     * @param string $table Table to insert to
     * @param array $values Values to insert
     */
    public function insertData($table, $values)
    {
        $query = "INSERT INTO `".$table."` ";
        if (!isset($values[0]))
        {
            $query .= "(";
            foreach ($values as $row => $v)
            {
                $query .= "`".$row."`";
                end($values);
                if ($row != key($values))
                {
                    $query .= ", ";
                }
            }
            $query .= ") ";
            unset($row, $v, $table);
        }
        $query .= "VALUES (";
        $nvalues = array();
        foreach ($values as $k=>$value)
        {
            $nvalues[] = $value;
            $query .= "?";
            end($values);
            if ($k != key($values))
            {
                $query .= ", ";
            }
        }
        $values = $nvalues;
        unset($nvalues);
        $query .= ")";
        return $this->query($query, $values);
    }

    /**
     * Change entry(ies) in the table
     *
     * @param string $table Source table
     * @param array $values Array with new values
     * @param array $cases Array with entry cases
     */
    public function updateData($table, $values, $cases = null)
    {
        $query = "UPDATE ".$table." SET ";
        $data = array();
        foreach ($values as $row => $v)
        {
            $data[] = $v;
            $query .= "`{$row}` = ?";
            end($values);
            if ($row != key($values))
            {
                $query .= ", ";
            }
            unset($values);
        }
        if ($cases != null)
        {
            $query .= " WHERE ";
            foreach ($cases as $row => $v)
            {
                $data[] = $v;
                $query .= "`{$row}` = ?";
                end($cases);
                if ($row != key($cases))
                {
                    $query .= " AND ";
                }
            }
            unset($v, $row);
        }
        return $this->query($query, $data);
    }

    /**
     * Remove entry(ies)
     *
     * @param string $table Source table
     * @param array $cases [optional] Cases for entry to remove. If not given, will empty table
     */
    public function deleteData($table, $cases = array())
    {
        $query = "DELETE FROM `".$table."`";
        if ($cases != array())
        {
            $query .= " WHERE ";
            $data = array();
            foreach ($cases as $row => $v)
            {
                $data[] = $v;
                $query .= "`{$row}` = ?";
                end($cases);
                if ($row != key($cases))
                {
                    $query .= " AND ";
                }
            }
            unset($v, $row);
        }
        return $this->query($query, $cases);
    }

    /**
     * @param string $query SQL statement
     * @param array $data Array with data for statement, if any needed
     * @return void
     * @throws inviException
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
            throw new inviException( inviErrors::DB_EXEC_FAIL, "[{$error[0]}]: {$error[2]}" );
        }
    }

    /**
     * Method fetch is required for getting data returned by server
     * 
     * @param string $fetch_mode Should be "assoc" or "num". If unknown mode given, will be fetched assoc
     * @return array Multi-dimensional array with data returned or NULL
     */
    public function getReturnedData($fetch_mode = "assoc")
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