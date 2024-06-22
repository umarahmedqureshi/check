<?php
/*
 * DB Class
 * This class is used for database related (connect, insert, update, and delete) operations
 * @author   https://zehntech.com/
 */
class DB
{
    private $dbHost     = "localhost";
    private $dbUsername = "root";
    private $dbPassword = 'q5gO2Ovmqf46BSFfIA';
    private $dbName     = "shopify_gyatagpt";


    public function __construct()
    {
        // Connect to the database if the connection object is not set
        if (!isset($this->db)) {
            $conn = new mysqli($this->dbHost, $this->dbUsername, $this->dbPassword, $this->dbName);
            if ($conn->connect_error) {
                die("Failed to connect with MySQL: " . $conn->connect_error);
            } else {
                $this->db = $conn;
            }
        }
    }


    /*
     * creating table in database if not exist
     * @param string name of the table
     * @return boolean true if table is created successful, false otherwise
     */
    public function createTable($tableName){
        $sql = "CREATE TABLE IF NOT EXISTS ".$tableName." (
            id INT AUTO_INCREMENT PRIMARY KEY,
            store_id INT(20),
            shopify_store_id INT(20),
            customer_id VARCHAR(100) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            country_code VARCHAR(5) NULL,
            phone_number VARCHAR(15),
            otp VARCHAR(50) NULL,
            password_hash VARCHAR(255) NOT NULL
        )";
        if ($this->db->query($sql) === TRUE) {
            // echo "Table created successfully";
            return true;
        }else {
            echo "Error creating table: " . $this->db->error;
            return false;
        }
    }

    /*
     * Returns rows from the database based on the conditions
     * @param string name of the table
     * @param array select, where, order_by, limit and return_type conditions
     * @return mixed array of rows or false if no rows found
     */
    public function getRows($table, $conditions = array())
    {
        $sql = 'SELECT ';
        $sql .= array_key_exists("select", $conditions) ? $conditions['select'] : '*';
        $sql .= ' FROM ' . $table;
        if (array_key_exists("where", $conditions)) {
            $sql .= ' WHERE ';
            $i = 0;
            foreach ($conditions['where'] as $key => $value) {
                $pre = ($i > 0) ? ' AND ' : '';
                $sql .= $pre . $key . " = '" . $value . "'";
                $i++;
            }
        }


        if (array_key_exists("order_by", $conditions)) {
            $sql .= ' ORDER BY ' . $conditions['order_by'];
        } else {
            $sql .= ' ORDER BY id DESC ';
        }

        if (array_key_exists("start", $conditions) && array_key_exists("limit", $conditions)) {
            $sql .= ' LIMIT ' . $conditions['start'] . ',' . $conditions['limit'];
        } elseif (!array_key_exists("start", $conditions) && array_key_exists("limit", $conditions)) {
            $sql .= ' LIMIT ' . $conditions['limit'];
        }


        try {
            $result = $this->db->query($sql);
            if ($result == '') {
                throw new Exception("Error executing query: " . $this->db->error);
            }
        } catch (Exception $e) {

            echo "Error: " . $e->getMessage();
        }

        $data = array(); // Initialize data array

        if (array_key_exists("return_type", $conditions) && $conditions['return_type'] != 'all') {
            switch ($conditions['return_type']) {
                case 'count':
                    $data = ($result->num_rows > 0) ? $result->num_rows : 0;
                    break;
                case 'single':
                    $data = $result->fetch_assoc();
                    break;
                default:
                    $data = '';
            }
        } else {
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $data[] = $row;
                }
            }
        }
        return !empty($data) ? $data : false;
    }

    /*
     * Insert data into the database
     * @param string name of the table
     * @param array the data for inserting into the table
     * @return int|boolean inserted id or false if insert fails
     */
    public function insert($table, $data)
    {
        if (!empty($data) && is_array($data)) {
            $columns = '';
            $values  = '';
            $i = 0;
            if (!array_key_exists('created', $data)) {
                $data['created'] = date("Y-m-d H:i:s");
            }
            if (!array_key_exists('modified', $data)) {
                $data['modified'] = date("Y-m-d H:i:s");
            }
            foreach ($data as $key => $val) {
                $pre = ($i > 0) ? ', ' : '';
                $columns .= $pre . $key;
                $values  .= $pre . "'" . $this->db->real_escape_string($val) . "'";
                $i++;
            }
            $query = "INSERT INTO " . $table . " (" . $columns . ") VALUES (" . $values . ")";
            $insert = $this->db->query($query);
            return $insert ? $this->db->insert_id : false;
        } else {
            return false;
        }
    }

    /*
     * Insert data into the database in bulk upload
     * @param string name of the table
     * @param array the data for inserting into the table
     * @return int|boolean inserted id or false if insert fails
     */
    public function customerBulkUpload($table, $data){
        if($table == ''){
            return false;
        } else {
            if(empty($data)){
                return false;
            } else {
                $query = "INSERT INTO " . $table . " (store_id, customer_id, email, phone_number, otp) VALUES ";  //(" . $values . ")
                foreach($data as $key => $value){
                    if(!empty($value)){
                        $store_id = !empty($value['store_id']) ? $value['store_id'] : '';
                        $customer_id = !empty($value['customer_id']) ? $value['customer_id'] : '';
                        $customer_email = !empty($value['email']) ? $value['email'] : '';
                        $customer_phone = !empty($value['phone_number']) ? $value['phone_number'] : 'NULL';
                        $customer_otp =  !empty($value['otp']) ? $value['otp'] : mt_rand(100000, 999999);    // Generate a 6-digit OTP
                        $query .=  "(".$store_id.", '".$customer_id."', '".$customer_email."', '".$customer_phone."', ".$customer_otp.")";
                    }
                }
                $query = str_replace(")(", "),(", $query);
                $insert = $this->db->query($query);
                return $insert ? $this->db->insert_id : false;
            }
        }
    }
    /*
     * Update data into the database
     * @param string name of the table
     * @param array the data for updating into the table
     * @param array where condition on updating data
     * @return int|boolean number of affected rows or false if update fails
     */
    public function update($table, $data, $conditions)
    {
        if (!empty($data) && is_array($data)) {
            $colvalSet = '';
            $whereSql = '';
            $i = 0;
            if (!array_key_exists('modified', $data)) {
                $data['modified'] = date("Y-m-d H:i:s");
            }
            foreach ($data as $key => $val) {
                $pre = ($i > 0) ? ', ' : '';
                $colvalSet .= $pre . $key . "='" . $this->db->real_escape_string($val) . "'";
                $i++;
            }
            if (!empty($conditions) && is_array($conditions)) {
                $whereSql .= ' WHERE ';
                $i = 0;
                foreach ($conditions as $key => $value) {
                    $pre = ($i > 0) ? ' AND ' : '';
                    $whereSql .= $pre . $key . " = '" . $value . "'";
                    $i++;
                }
            }
            $query = "UPDATE " . $table . " SET " . $colvalSet . $whereSql;
            $update = $this->db->query($query);
            return $update ? $this->db->affected_rows : false;
        } else {
            return false;
        }
    }

    /*
     * Delete data from the database
     * @param string name of the table
     * @param array where condition on deleting data
     * @return boolean true if delete is successful, false otherwise
     */
    public function delete($table, $conditions)
    {
        $whereSql = '';
        if (!empty($conditions) && is_array($conditions)) {
            $whereSql .= ' WHERE ';
            $i = 0;
            foreach ($conditions as $key => $value) {
                $pre = ($i > 0) ? ' AND ' : '';
                $whereSql .= $pre . $key . " = '" . $value . "'";
                $i++;
            }
        }
        $query = "DELETE FROM " . $table . $whereSql;
        $delete = $this->db->query($query);
        return $delete ? true : false;
    }

}
