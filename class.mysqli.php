<?php
class MyDB{
	public $version	= '2015-12-20';
	
	public $setUTF8 = TRUE;
	
	// Forbidden words for values
	public $forbidden = array( 'SELECT', 'UPDATE', 'REPLACE', 'INSERT', 'DELETE', 'DROP' );
	public $mylink;

	protected $security	= TRUE;
	protected $badsql		= array();
	protected $timer		= 0;
			
	function __construct( $connect, $security=TRUE ){
		$this->connect( $connect, $security );
	}
	
	public function connect( $connect, $security=TRUE ){
		$this->security = $security;
		
		if( !$connect['password'] && $connect['pass'] ){
			$connect['password'] = $connect['pass'];
		}

		$this->mylink = new mysqli( $connect['host'], $connect['user'], $connect['password'], $connect['db'] );
		
		if ( $this->mylink->connect_error ){
			die('Connect Error (' .$this->mylink->connect_errno . ') '. $this->mylink->connect_error);
		}
		
		if ( $this->setUTF8 ){
			$this->mylink->query('SET NAMES utf8');
		}
		
		$this->sqlinit();
	}

	public function close(){
		$this->mylink->close();
	}

	protected function sqlinit(){
		foreach ( $this->forbidden as $key ){
			$length	= strlen( $key );
			$sql		= preg_split('//', $key, -1, PREG_SPLIT_NO_EMPTY);
			$value	= '';
			
			foreach ( $sql as $s ){
				$value .= str_repeat( $s, $length );
			}
			
			$this->badsql['into'][$key]		= $value;
			$this->badsql['from'][$value]	= $key;
		}
	}

	// Short command for "SELECT [$fields|*] $where $limit"
	public function q( $table, $where=NULL, $limit=NULL, $fields=NULL ){
		$query	= 'SELECT ';
		$table	= $this->prepareTable( $table );
		
		if ( $fields ){
			if ( is_array( $fields ) ){
				$fields = implode( ', ', $fields );
			}
			
			$fields = $this->escapeString( $fields );
		}
		else{
			$fields = '*';
		}
		
		$query	.= $fields . ' FROM ' . $table . $this->whereForQuery( $where );
		
		if ( $limit ){
			if( is_array( $limit ) ){
				$query	.= ' LIMIT ' . intval( $limit[0] ) . ',' . intval( $limit[1] );
			}
			elseif( is_numeric( $query ) ){
				$query .= ' LIMIT ' . $limit;
			}
		}
		
		return $this->select( $query );
	}
	
	protected function whereForQuery( $where ){
		if( ! ( $where && is_array( $where ) ) ){
			return '';
		}
		
		$w = array();
		foreach ( $where as $key=>$val ){
			if ( $val ){
				$val = "='{$this->escapeString( $val )}'";
			}
			else{
				$val = ' IS NULL';
			}
			
			$w[] = "`$key`$val";
		}
		
		$where = implode( ' AND ', $w );
		
		return ' WHERE ' . $where;
	}

	public function query( $query, $direct=FALSE ){
		$this->timer	= microtime( TRUE );
		
		$query = $this->SQLto( trim( $query ) );
		
		if( preg_match( '#^select#Umi', $query ) ){
			return $this->select( $query, $direct );
		}
		
		if( ! $this->mylink->query( $query ) ){
			return NULL;
		}
		
		$arresult = array(
			'query'	=> $query,
			'rows'	=> $this->mylink->affected_rows,
			'time'	=> $this->getTimer()
		);
		
		return $arresult;
	}

	protected function prepareTable( $table ){
		if( FALSE === strpos( '`', $table) ){
			return "`{$table}`";
		}

		return $table;
	}
	
	public function delete( $table, $where=NULL, $limit=NULL ){
		$query = 'DELETE FROM ' . $this->prepareTable( $table );
		
		if ( $where ){
			$query .= ' WHERE ' . $where;
		}
		
		if ( $limit ){
			$query .= ' LIMIT ' . $limit;
		}
		
		return $this->query( $query );
	}

	public function escapeString( $string ){
		return $this->mylink->real_escape_string( $string );
	}

	public function select( $query, $direct=FALSE ){
		$arresult	= array(
			'query'		=> $query,
			'rows'		=> 0,
			'time'		=> 0,
			'isArray'	=> FALSE,
			'data'		=> array(),
			'src'			=> NULL
		);
		
		$arresult['src']	= $this->mylink->query( $query );
		
		if( ! $arresult['src'] ){
			return $arresult;
		}
		
		$arresult['rows'] = $arresult['src']->num_rows;
		
		if( $arresult['rows'] > 1000 || $direct ){
			$arresult['time']	= $this->getTimer();
			return $arresult;
		}
		
		
		$arresult['data']			= array();
		$arresult['isArray']	= TRUE;

		while ( $row = $arresult['src']->fetch_assoc() ){
			$arresult['data'][] = $this->parseRow( $row );
		}
		
		if( 1 == $arresult['rows'] && isset( $arresult['data'][0]['id'] ) ){
			$arresult['id']	= $arresult['data'][0]['id'];
		}

		$arresult['src']->free();
		$arresult['src']	= NULL;
		$arresult['time']	= $this->getTimer();
		
		return $arresult;
	}
	
	protected function parseRow( $row ){
		$row	= $this->SQLfrom( $row );

		if( ! $this->security ){
			return $row;
		}
		
		foreach ( $row as $key => $value ) {
			if( is_numeric( $value ) ){
				continue;
			}
			
			$row[ $key ]	= $this->strDecode( $value );
		}
		
		return $row;
	}


	protected function getTimer(){
		$timer				= microtime( TRUE ) - $this->timer;
		$this->timer	= 0;
		
		return $timer;
	}

	public function update( $table, $data, $where=NULL, $limit=NULL ){
		if ( !is_array( $data ) ){
			return NULL;
		}
		
		$this->timer	= microtime( TRUE );
		
		$data = $this->checkData( $data );

		// create UPDATE query
		$query = array();
		
		foreach ( $data['keys'] as $key ){
			$query[] = "`{$key}`=?";
		}

		$table	= $this->prepareTable( $table );
		
		$query = "UPDATE {$table} SET " . implode( ', ', $query );
		
		if ( $where ){
			$query .= ' WHERE ' . $where;
		}
		
		if ( $limit ){
			$query .= ' LIMIT ' . $limit;
		}

		return $this->execute( $query, $data['vals'] );
	}
	
	public function updateById( $table, $data, $id, $limit=NULL ){
		$where	= "`id`='{$id}'";
		return $this->update( $table, $data, $where, $limit );
	}

	public function insert( $table, $data ){
		if ( !is_array( $data ) ){
			return NULL;
		}
		
		$this->timer	= microtime( TRUE );
		
		$data		= $this->checkData( $data );
		$table	= $this->prepareTable( $table );
		
		// create INSERT query
		$query	= "INSERT INTO {$table} (`" . implode( '`,`', $data['keys'] ) ."`) "
							. "VALUES (" . implode( ',', $data['query'] ) . ")";
		
		return $this->execute( $query, $data['vals'] );
	}

	protected function checkData( $data ){
		$fields	= array_keys( $data );
		$vals		= array_values( $data );
		$types	= '';
		$query	= $matches	= array();
		
		foreach ( $vals as $i=>$d ){
			
			if ( preg_match( '#^(i|d|s|b|null):(.*)$#s', $d, $matches ) ){
				if ( 'null' == $matches[1] ){
					$matches[1] = NULL;
				}

				$types		.= $matches[1];
				$vals[$i]	 = $matches[2];
			}
			else{
				$types .= 's';
			}

			if( 's' == $types && $this->security ){
				$vals[ $i ]	= $this->strEncode( $vals[ $i ] );
			}
			
			$vals[ $i ]	= $this->SQLto( $vals[ $i ] );
			$query[]		= '?';
		}
		
		array_unshift( $vals, $types );
		
		$data = array(
			'keys'	=> $fields,
			'vals'	=> $vals,
			'types'	=> $types,
			'query'	=> $query
		);
		
		return $data;
	}
	
	public function strEncode( $str ){
		return urlencode( $str );
	}
	
	public function strDecode( $str ){
		return urldecode( $str );
	}

	protected function execute( $query, $vals ){
		$arresult = array( 'query' => $query, 'values'=>$vals, 'time' => 0, 'rows' => 0 );
		
		$stmt = $this->mylink->prepare( $query );
		
		if( ! $stmt ){
			return $arresult;
		}
		
		call_user_func_array( array( $stmt, 'bind_param' ), $this->refValues( $vals ) );
		
		$stmt->execute();
		
		if( preg_match( '#^insert#Umi', $query ) ){
			$arresult['id'] = $stmt->insert_id;
		}
		
		$arresult['rows'] = $stmt->affected_rows;
		$stmt->close();
		
		$arresult['time']	= $this->getTimer();
		
		return $arresult;
	}

	protected function refValues( $arr ){
		if ( strnatcmp( phpversion(), '5.3' ) >= 0 ){
			$refs = array();
			
			foreach( $arr as $key => $value ) {
				$refs[$key] = &$arr[ $key ];
			}
			
			return $refs;
		}
		
		return $arr;
	}

	// Warning: This unsafe function !!!
	public function multiQuery( $query ){
		$this->timer	=  microtime( TRUE );
		
		if ( is_array( $query ) ){
			$query = implode( ';', $query );
		}
		
		$result	= $this->mylink->multi_query( $query );
		if( ! $result ){
			return array( 'error' => $this->mylink->error );
		}
		
		$data = array(
			'query'	=> $query,
			'time'	=> 0,
			'data'	=> array()
		);
		$i = 0;
	
		do {
			/* store first result set */
			$result = $this->mylink->store_result();
			
			if( ! $result ){
				break;
			}

			while( $row = $result->fetch_assoc() ){
				$data['data'][ $i ][] = $row;
			}

			$result->free();
			
			if ( ! $this->mylink->more_results() ){
				break;
			}
			
			++$i;
			
		} while( $this->mylink->next_result() );

		return $data;
	}
	
	//$force - check all, even first sql-command.
	//Very good for self::insert()
	protected function SQLto( $query, $force=FALSE ){ 
		$sql	= array_keys( $this->badsql['into'] );
		$re		= implode( '|', $sql );
		$re		= "#^($re)(.+)$#mis";
		
		$matches	= array();
		
		if ( ! preg_match( $re, $query, $matches ) ){
			return $query;
		}
		
		foreach ( $sql as $s ){
			if ( $force ){
				$matches[1] = str_ireplace( $s, $this->badsql['into'][ $s ], $matches[1] );
			}

			$matches[2] = str_ireplace( $s, $this->badsql['into'][ $s ], $matches[2] );
		}

		return $matches[1] . $matches[2];
	}
	
	protected function SQLfrom( $row ){
		$sql = array_keys( $this->badsql['from'] );
		
		foreach ( $row as $key=>$val ){
			$nkey = $key;
		
			foreach ( $sql as $s ){
				$nkey	= str_ireplace( $s, $this->badsql['from'][$s], $nkey );
				$val	= str_ireplace( $s, $this->badsql['from'][$s], $val );
			}
			
			unset( $row[$key] );
			$row[$nkey] = $val;
		}
		
		return $row;
	}

	public static function normalDate( $mysqldate ){
		if ( is_numeric( $mysqldate ) ){
			return $mysqldate;
		}
		
		return strtotime( $mysqldate );
	}

	public static function mysqlDate( $unixdate=null ){
		if ( !$unixdate ){
			$unixdate = time();
		}
		
		if ( !is_numeric( $unixdate ) ){
			return $unixdate;
		}
		
		return date( 'Y-m-d H:i:s', $unixdate );
	}
}