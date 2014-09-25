<?php

################################################################################
# Copyright (c) 2010, Jean-David Gadina <macmade@xs-labs.com>                  #
# All rights reserved.                                                         #
#                                                                              #
# Redistribution and use in source and binary forms, with or without           #
# modification, are permitted provided that the following conditions are met:  #
#                                                                              #
#  -   Redistributions of source code must retain the above copyright notice,  #
#      this list of conditions and the following disclaimer.                   #
#  -   Redistributions in binary form must reproduce the above copyright       #
#      notice, this list of conditions and the following disclaimer in the     #
#      documentation and/or other materials provided with the distribution.    #
#  -   Neither the name of 'Jean-David Gadina' nor the names of its            #
#      contributors may be used to endorse or promote products derived from    #
#      this software without specific prior written permission.                #
#                                                                              #
# THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"  #
# AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE    #
# IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE   #
# ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE    #
# LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR          #
# CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF         #
# SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS     #
# INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN      #
# CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)      #
# ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE   #
# POSSIBILITY OF SUCH DAMAGE.                                                  #
################################################################################

# $Id$

final class XS_Database_Layer
{
    CONST DB_DRIVER    = 'mysql';
    CONST DB_HOST      = 'localhost';
    CONST DB_USER      = '';
    CONST DB_PASS      = '';
    CONST DB_DATABSASE = '';
    
    private static $_instance = NULL;
    private $_tables          = array();
    private $_pKeys           = array();
    private $_pdo             = NULL;
    private $_drivers         = array();
    private $_dsn             = '';
    
    private function __construct()
    {
        if( !class_exists( 'PDO' ) )
        {
            throw new XS_Database_Layer_Exception
            (
                'PDO is not available',
                XS_Database_Layer_Exception::EXCEPTION_NO_CONNECTION
            );
        }
        
        $this->_drivers = array_flip( PDO::getAvailableDrivers() );
        
        if( !isset( $this->_drivers[ self::DB_DRIVER ] ) )
        {
            throw new XS_Database_Layer_Exception
            (
                'Driver ' . $driver . ' is not available in PDO',
                XS_Database_Layer_Exception::EXCEPTION_NO_PDO_DRIVER
            );
        }
        
        $this->_dsn = self::DB_DRIVER . ':host=' . self::DB_HOST . ';dbname=' . self::DB_DATABSASE;
        $dsnSchema  = self::DB_DRIVER . ':host=' . self::DB_HOST . ';dbname=information_schema';
        
        try
        {
            $this->_pdo = new PDO( $this->_dsn, self::DB_USER, self::DB_PASS, array( PDO::ATTR_PERSISTENT => true ) );
            $schema     = new PDO( $dsnSchema,  self::DB_USER, self::DB_PASS, array( PDO::ATTR_PERSISTENT => true ) );
        }
        catch( Exception $e )
        {
            throw new XS_Database_Layer_Exception
            (
                $e->getMessage(),
                XS_Database_Layer_Exception::EXCEPTION_NO_CONNECTION
            );
        }
        
        $this->_getTables( $schema );
        
        $schema = NULL;
    }
    
    public function __destruct()
    {
        $this->_pdo = NULL;
    }
    
    public function __call( $name, array $args = array() )
    {
        if( !is_callable( array( $this->_pdo, $name ) ) )
        {
            throw new XS_Database_Layer_Exception
            (
                'The method \'' . $name . '\' cannot be called on the PDO object',
                XS_Database_Layer_Exception::EXCEPTION_BAD_METHOD
            );
        }
        
        $argCount = count( $args );
        
        switch( $argCount )
        {
            case 1:
                
                return $this->_pdo->$name( $args[ 0 ] );
            
            case 2:
                
                return $this->_pdo->$name( $args[ 0 ], $args[ 1 ] );
            
            case 3:
                
                return $this->_pdo->$name( $args[ 0 ], $args[ 1 ], $args[ 2 ] );
            
            case 4:
                
                return $this->_pdo->$name( $args[ 0 ], $args[ 1 ], $args[ 2 ], $args[ 3 ] );
            
            case 5:
                
                return $this->_pdo->$name( $args[ 0 ], $args[ 1 ], $args[ 2 ], $args[ 3 ], $args[ 4 ] );
            
            default:
                
                return $this->_pdo->$name();
        }
    }
    
    public function __clone()
    {
        throw new XS_Singleton_Exception
        (
            'Class ' . __CLASS__ . ' cannot be cloned',
            XS_Singleton_Exception::EXCEPTION_CLONE
        );
    }
    
    public static function getInstance()
    {
        if( !is_object( self::$_instance ) )
        {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
    
    protected function _getTables( $pdo )
    {
        $query  =  $pdo->prepare( 'SELECT TABLE_NAME FROM TABLES WHERE TABLE_SCHEMA = :schema ORDER BY TABLE_NAME' );
        $params = array
        (
            ':schema' => self::DB_DATABSASE
        );
        
        $query->execute( $params );
        
        while( $table = $query->fetchObject() )
        {
            $this->_tables[ $table->TABLE_NAME ] = array();
            $this->_getColumns( $pdo, $table->TABLE_NAME );
            $this->_getPrimaryKey( $pdo, $table->TABLE_NAME );
        }
    }
    
    protected function _getColumns( $pdo, $table )
    {
        $query  =  $pdo->prepare( 'SELECT COLUMN_NAME FROM COLUMNS WHERE TABLE_SCHEMA = :schema AND TABLE_NAME = :table ORDER BY COLUMN_NAME' );
        $params = array
        (
            ':schema' => self::DB_DATABSASE,
            ':table'  => $table
        );
        
        $query->execute( $params );
        
        while( $column = $query->fetchObject() )
        {
            $this->_tables[ $table ][ $column->COLUMN_NAME ] = true;
        }
    }
    
    protected function _getPrimaryKey( $pdo, $table )
    {
        $query  =  $pdo->prepare( 'SELECT COLUMN_NAME FROM KEY_COLUMN_USAGE WHERE CONSTRAINT_NAME = "PRIMARY" AND TABLE_SCHEMA = :schema AND TABLE_NAME = :table LIMIT 1' );
        $params = array
        (
            ':schema' => self::DB_DATABSASE,
            ':table'  => $table
        );
        
        $query->execute( $params );
        
        $pKey = $query->fetchObject();
        
        $this->_pKeys[ $table ] = $pKey->COLUMN_NAME;
    }
    
    protected function _checkTableName( $table )
    {
        $table = strtoupper( $table );
        
        if( !isset( $this->_tables[ $table ] ) )
        {
            throw new XS_Database_Layer_Exception
            (
                'The table \'' . $table . '\' does not exist in the database',
                XS_Database_Layer_Exception::EXCEPTION_NO_TABLE
            );
        }
        
        return $table;
    }
    
    public function tableExists( $table )
    {
        return isset( $this->_tables[ strtoupper( $table ) ] );
    }
    
    public function columnExists( $table, $column )
    {
        $table = $this->_checkTableName( $table );
        
        return isset( $this->_tables[ $table ][ ( string )$column ] );
    }
    
    public function getPrimaryKey( $table )
    {
        $table = $this->_checkTableName( $table );
        
        return $this->_pKeys[ $table ];
    }
    
    public function getRecord( $table, $id )
    {
        $table = $this->_checkTableName( $table );
        $pKey  = $this->_pKeys[ $table ];
        
        $params = array
        (
            ':id' => ( int )$id
        );
        
        $query = $this->prepare
        (
            'SELECT * FROM ' . $table . '
             WHERE ' . $pKey . ' = :id
             AND deleted = 0
             LIMIT 1'
        );
        
        $query->execute( $params );
        
        return $query->fetchObject();
    }
    
    public function getAllRecords( $table, $orderBy = '' )
    {
        $table   = $this->_checkTableName( $table );
        $pKey    = $this->_pKeys[ $table ];
        $orderBy = ( $orderBy ) ? ' ORDER BY ' . $orderBy : ' ORDER BY mtime';
        $sql     = 'SELECT * FROM ' . $table . ' WHERE deleted = 0 ' . $orderBy;
        $params  = array();
        $query   = $this->prepare( $sql );
        
        $query->execute( $params );
        
        $rows = array();
        
        while( $row = $query->fetchObject() )
        {
            $rows[ $row->$pKey ] = $row;
        }
        
        return $rows;
    }
    
    public function getRecords( $table, array $keys, $orderBy = '', $limit = 0, $offset = 0 )
    {
        $table   = $this->_checkTableName( $table );
        $pKey    = $this->_pKeys[ $table ];
        $orderBy = ( $orderBy ) ? ' ORDER BY ' . $orderBy : ' ORDER BY mtime';
        $sql     = 'SELECT * FROM ' . $table . ' WHERE deleted = 0 AND ';
        $params  = array();
        
        foreach( $keys as $key => $value )
        {
            $keys[ $key ] = ( int )$value;
        }
        
        $sql .= $pKey . ' IN( ' . implode( ',', $keys ) . ' )';
        $sql .= $orderBy;
        
        if( ( int )$limit > 0 )
        {
            $sql .= ' LIMIT ' . ( int )$limit;
        }
        
        if( ( int )$offset > 0 )
        {
            $sql .= ' OFFSET ' . ( int )$offset;
        }
        
        $query = $this->prepare( $sql );
        
        $query->execute( $params );
        
        $rows = array();
        
        while( $row = $query->fetchObject() )
        {
            $rows[ $row->$pKey ] = $row;
        }
        
        return $rows;
    }
    
    public function getRecordsByFields( $table, array $fieldsValues, $orderBy = '', $limit = 0, $offset = 0 )
    {
        $table   = $this->_checkTableName( $table );
        $pKey    = $this->_pKeys[ $table ];
        $orderBy = ( $orderBy ) ? ' ORDER BY ' . $orderBy : ' ORDER BY mtime';
        $sql     = 'SELECT * FROM ' . $table . ' WHERE deleted = 0 AND ';
        $params  = array();
        
        if( isset( $fieldsValues[ 'deleted' ] ) )
        {
            unset( $fieldsValues[ 'deleted' ] );
        }
        
        foreach( $fieldsValues as $fieldName => $fieldValue )
        {
            $params[ ':' . $fieldName ] = $fieldValue;
            
            $sql .= $fieldName . ' = :' . $fieldName . ' AND ';
        }
        
        $sql  = substr( $sql, 0, -5 );
        $sql .= $orderBy;
        
        if( ( int )$limit > 0 )
        {
            $sql .= ' LIMIT ' . ( int )$limit;
        }
        
        if( ( int )$offset > 0 )
        {
            $sql .= ' OFFSET ' . ( int )$offset;
        }
        
        $query = $this->prepare( $sql );
        $query->execute( $params );
        
        $rows = array();
        
        while( $row = $query->fetchObject() )
        {
            $rows[ $row->$pKey ] = $row;
        }
        
        return $rows;
    }
    
    public function getRecordsWhere( $table, $where, $orderBy = '', $limit = 0, $offset = 0 )
    {
        $table   = $this->_checkTableName( $table );
        $pKey    = $this->_pKeys[ $table ];
        $orderBy = ( $orderBy ) ? ' ORDER BY ' . $orderBy : ' ORDER BY mtime';
        $sql     = 'SELECT * FROM ' . $table . ' WHERE deleted = 0 AND ' . $where . $orderBy;
        $params  = array();
        
        if( ( int )$limit > 0 )
        {
            $sql .= ' LIMIT ' . ( int )$limit;
        }
        
        if( ( int )$offset > 0 )
        {
            $sql .= ' OFFSET ' . ( int )$offset;
        }
        
        $query = $this->prepare( $sql );
        
        $query->execute( $params );
        
        $rows = array();
        
        while( $row = $query->fetchObject() )
        {
            $rows[ $row->$pKey ] = $row;
        }
        
        return $rows;
    }
    
    public function insertRecord( $table, array $values )
    {
        if( !count( $values ) )
        {
            return false;
        }
        
        $table  = $this->_checkTableName( $table );
        $params = array();
        $time   = time();
        $sql    = 'INSERT INTO ' . $table . ' SET'
                . ' ctime = :ctime,'
                . ' mtime = :mtime,';
        
        $params[ ':ctime' ] = $time;
        $params[ ':mtime' ] = $time;
        
        foreach( $values as $fieldName => $value )
        {
            $params[ ':' . $fieldName ] = $value;
            
            $sql .= ' ' . $fieldName . ' = :' . $fieldName . ',';
        }
        
        $sql   = substr( $sql, 0, -1 );
        $query = $this->prepare( $sql );
        
        $query->execute( $params );
        
        return $this->lastInsertId();
    }
    
    public function updateRecord( $table, $id, array $values )
    {
        if( !count( $values ) )
        {
            return false;
        }
        
        $table = $this->_checkTableName( $table );
        $pKey  = $this->_pKeys[ $table ];
        $sql   = 'UPDATE ' . $table . ' SET mtime = :mtime, ';
        
        $params = array
        (
            ':' . $pKey => ( int )$id,
            ':mtime'    => time()
        );
        
        foreach( $values as $fieldName => $value )
        {
            $params[ ':' . $fieldName ] = $value;
            
            $sql .= ' ' . $fieldName . ' = :' . $fieldName . ',';
        }
        
        $sql   = substr( $sql, 0, -1 );
        $sql  .= ' WHERE ' . $pKey . ' = :' . $pKey;
        $query = $this->prepare( $sql );
        
        return $query->execute( $params );
    }
    
    public function deleteRecord( $table, $id, $deleteFromTable = false )
    {
        if( $deleteFromTable )
        {
            $table  = $this->_checkTableName( $table );
            $pKey   = $this->_pKeys[ $table ];
            $sql    = 'DELETE FROM ' . $table . ' WHERE ' . $pKey . ' = :id';
            $params = array
            (
                ':id' => ( int )$id
            );
            
            $query = $this->prepare( $sql );
            
            return $this->execute( $params );
        }
        
        return $this->updateRecord( $table, $id, array( 'deleted' => 1 ) );
    }
    
    public function removeDeletedRecords( $table )
    {
        $table  = $this->_checkTableName( $table );
        $params = array();
        $query  = $this->prepare
        (
            'DELETE FROM ' . $table . ' WHERE deleted = 1'
        );
        
        return $this->execute( $params );
    }
}
