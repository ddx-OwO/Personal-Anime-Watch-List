<?php
class Core{
	
	var $rand_key;
	
	function RIDIncludeFile(){
		$this->rand_key = 'iOJt2k49aZ';
	}
	/*
	==================================
	Main Functions
	==================================
	*/

	/**
	 * Initial variabel untuk konek ke database
	 * @param string $host 
	 * @param string $username 
	 * @param string $password 
	 * @param string $database 
	 * @param string $tablename 
	 * @param int $port 
	 * @return void
	 */
	function InitDB($host, $username, $password, $database, $tablename, $port = 3306)
	{
		$this->host = $host;
		$this->uname = $username;
		$this->pass = $password;
		$this->dbname = $database;
		$this->tablename = $tablename;
		$this->port = $port;
		date_default_timezone_set("Asia/Jakarta");
	}
	
	
	function connectToDatabase()
	{
		$this->conn = mysqli_connect($this->host, $this->uname, $this->pass, $this->dbname, $this->port);
		
		if(!$this->conn){
			die("Cannot connect to database ". mysqli_connect_error() ."<br/>");
			return false;
		}
		return true;
	}
	
	function isDatabaseExist($database)
	{
		$this->conn = mysqli_connect($this->host, $this->uname, $this->pass, $database, $this->port);
		// Check connection
		if (!$this->conn) {
			echo "Connection failed: " . mysqli_connect_error();
			//$this->createDatabase($database);
			return false;
		}
		
		if(!mysqli_select_db($this->conn, $database))
		{
			return false;
		}
		//mysqli_close($this->conn);
		return true;
		
	}
	
	function createDatabase($database)
	{
		// Create connection
		$this->conn = mysqli_connect($this->host, $this->uname, $this->pass);
		// Check connection
		if (!$this->conn) {
			die("Connection failed: " . mysqli_connect_error());
			return false;
		}

		// Create database
		$this->setQuery("CREATE DATABASE $database");
		if(!mysqli_query($this->conn, $this->query)) 
		{
			echo "Error creating database: " . mysqli_error($this->conn);
		}
		mysqli_close($this->conn);
	}
	
	function setQuery($query)
	{
		$this->query = $query;
	}
	
	function isTableExist()
	{
		$result = mysqli_query($this->conn, "SHOW COLUMNS FROM $this->tablename");
		if(!$result || mysqli_num_rows($result) <= 0)
		{
			return false;
		}
		return true;
	}
	
	function createTable()
	{
		if(empty($this->query))
		{
			echo "Error: Query not defined <br/>";
			return true;
		}
		$qry = mysqli_query($this->conn, $this->query);
		if(!$qry)
		{
			echo "Error:". mysqli_error($this->conn);
		}
		else{
			echo "Table ".$this->tablename." has successfully created <br/>";
		}
		return true;
	}
	
	function insertToDatabase()
	{
		if(empty($this->query))
		{
			echo "Error: Query not defined <br/>";
			return true;
		}
		$qry = mysqli_query($this->conn, $this->query);
		if(!$qry)
		{
			echo "Error:". mysqli_error($this->conn). "<br/>";
		}
		else
		{
			echo "Data has successfully recorded to database<br/>";
		}
		return true;
	}
	/*
	function isDataExist($datarow, $data)
	{
		if(!$this->connectToDatabase())
		{
			return $this->connectToDatabase();
		}
		$result = mysqli_query($this->conn, "SELECT * FROM $this->tablename WHERE $datarow = '$data'");
		if(!$result || mysqli_num_rows($result) <= 0)
		{
			return false;
		}
		return true;
	}*/
	
	/*
	=======================================
	Security Functions
	=======================================
	*/
	
	//DEPRECATED
	function doubleEncrypt($string)
	{
		return md5(md5($string));
	}
	
	function sessionRandVar()
	{
		$sess_var = password_hash($this->rand_key, PASSWORD_BCRYPT);
		$sess_var = 'user_' . substr($sess_var, 0, 10);
		return $sess_var;
	}
	
	/*
	=======================================
	Misc Functions
	=======================================
	*/
	function redirectToURL($url)
    {
        header("Location: $url");
        exit;
    }
	
	function redirectToURLWithDelay($url, $time = 5)
	{
		header("Refresh:" .$time."; url=" .$url);
		exit;
	}
	
	function writeDebug($string, $writetoconsole = true)
	{
		if($writetoconsole == "true")
		{
			echo '<script>console.log( "RichmanIDCore Debug: ' . $string . '" );</script>';
		}
		echo $string . "<br/>";
	}
	
}
?>