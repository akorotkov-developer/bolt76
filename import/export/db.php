<?php
set_time_limit(10);
$ROOT   = 0xFFFFFFF6;
$NOLINK = 0xFFFFFFFF;

class DB
{
	public $con = 0;

	// подключится к базе. $server - IP или dns имя сервера. $con_str - строка подключения. $owner - код SQL доступа
	//"DSN=DB;UID=Master;PWD=ECCD0D84;DATABASE=DB;"
	//"DSN=DB;UID=Master;PWD=ECCD0D84;DATABASE=DB;"
	//DSN=DB;UID=Master;PWD=ECCD0D84;
	function connect( $server = "213.150.73.162", $con_str = "DSN=DB;", $owner = 'ECCD0D84')
	{
		$this->con = odbtp_connect($server, $con_str);

		if( !$this->con )
		{
			echo "Conection failed (".$this->con->errno.") ".$this->con->error."";
			return false;
		}
		odbtp_query("Set Owner='$owner'", $this->con );
		return true;
	}

	// выполнить запрос. $query строка
	function query( $query )
	{
		if( $this->con )
			return odbtp_query( $query, $this->con );
		return 0;
	}

	// получить результат из запроса. $query - это идентификатор запроса, его получают предыдущей функцией.
	//n
	function fetch_assoc( $query )
	{
		return odbtp_fetch_assoc( $query );
	}

	// установить размер поля
	function set_field_len( $query, $field, $len )
	{
		return odbtp_bind_field( $query, $field, ODB_BINARY, $len );
	}

	// выполнить запрос результат записать в массив. $query - строка
	function fetch_array( $query )
	{
		if( $this->con ) {
			$result = array();
			$_query = odbtp_query( $query, $this->con );
			while( ( $rec = odbtp_fetch_assoc( $_query ) ) )
				$result[] = $rec;
			return $result;
		}
		return 0;
	}

	// Иерархический запрос. получить список записей вызвать для каждой опять эту функцию.
	// Ограничения:
	// 1. Запрос должен содержать в конце WHERE и в нем последнее поле должно быть поле иерархии
	// 	  Например: fetch_tree( "SELECT * FROM Costs WHERE Razdel=" )
	//              значение для поля подставит сама функция
	// 2. Будет плохо если будет зацикливание.
	// 3. В запросе обязательно не должно быть поля 'childs'.
	// 4. В запросе обязательно должно быть поле ROWID и это идентификатор записи,
	//    который используется в иерархии
	function fetch_tree( $query, $root = 0xFFFFFFF6 )
	{
		$result = $this->fetch_array( $query.$root.'order by PorNomer' );
		if( $result )
		{
			$count = count( $result );
//				for( $i = 1; $i <= $count; ++$i )
			for( $i = 0; $i < $count; ++$i )
				$result[ $i ][ 'childs' ] = $this->fetch_tree( $query, $result[ $i ][ 'ROWID' ] );
		}
		return $result;
	}

	// Закрыть соединение
	function close()
	{
		if( $this->con )
			odbtp_close( $this->con );
	}
}
?>