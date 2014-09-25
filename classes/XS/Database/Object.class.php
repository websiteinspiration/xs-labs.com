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

class XS_Database_Object implements ArrayAccess, Iterator
{
    private static $_hasStatic = false;
    protected static $_db      = NULL;
    protected $_tableName      = '';
    protected $_pKey           = '';
    protected $_id             = 0;
    protected $_record         = array();
    protected $_updatedRecord  = array();
    
    public function __construct( $tableName, $id = 0 )
    {
        if( !self::$_hasStatic )
        {
            self::_setStaticVars();
        }
        
        $tableName = strtoupper( $tableName );
        
        if( !self::$_db->tableExists( $tableName ) )
        {
            throw new XS_Database_Object_Exception
            (
                'The table \'' . $tableName . '\' does not exist in the database',
                XS_Database_Object_Exception::EXCEPTION_NO_TABLE
            );
        }
        
        $this->_tableName = $tableName;
        $this->_id        = ( int )$id;
        $this->_pKey      = self::$_db->getPrimaryKey( $tableName );
        $pKey             = $this->_pKey;
        
        if( $this->_id > 0 )
        {
            $record = self::$_db->getRecord( $this->_tableName, $this->_id );
            
            if( is_object( $record ) )
            {
                $this->_id = $record->$pKey;
                
                foreach( $record as $key => $value )
                {
                    $this->_record[ $key ] = $value;
                }
            }
            else
            {
                throw new XS_Database_Object_Exception
                (
                    'The record #' . $this->_id . ' for table \'' . $this->_tableName . '\' does not exist in the database',
                    XS_Database_Object_Exception::EXCEPTION_NO_RECORD
                );
            }
        }
    }
    
    public function __get( $name )
    {
        $name = ( string )$name;
        
        if( !self::$_db->columnExists( $this->_tableName, $name ) )
        {
            throw new XS_Database_Object_Exception
            (
                'The field \'' . $name . '\' does not exist in the table table \'' . $this->_tableName . '\'',
                XS_Database_Object_Exception::EXCEPTION_NO_FIELD
            );
        }
        
        if( isset( $this->_updatedRecord[ $name ] ) )
        {
            return $this->_updatedRecord[ $name ];
        }
        elseif( isset( $this->_record[ $name ] ) )
        {
            return $this->_record[ $name ];
        }
        
        return false;
    }
    
    public function __set( $name, $value )
    {
        $name = ( string )$name;
        
        if( !self::$_db->columnExists( $this->_tableName, $name ) )
        {
            throw new XS_Database_Object_Exception
            (
                'The field \'' . $name . '\' does not exist in the table table \'' . $this->_tableName . '\'',
                XS_Database_Object_Exception::EXCEPTION_NO_FIELD
            );
        }
        
        if( $name === $this->_pKey )
        {
            throw new XS_Database_Object_Exception
            (
                'The primary key cannot be set',
                XS_Database_Object_Exception::EXCEPTION_SET_PKEY
            );
        }
        
        $this->_updatedRecord[ $name ] = $value;
    }
    
    public function __isset( $name )
    {
        return isset( $this->_record[ $name ] );
    }
    
    public function __unset( $name )
    {
        unset( $this->_record[ $name ] );
        unset( $this->_updatedRecord[ $name ] );
    }
    
    public function offsetGet( $name )
    {
        return $this->$name;
    }
    
    public function offsetSet( $name, $value )
    {
        $this->$name = $value;
    }
    
    public function offsetExists( $name )
    {
        return isset( $this->$name );
    }
    
    public function offsetUnset( $name )
    {
        unset( $this->$name );
    }
    
    public function current()
    {
        $key = key( $this->_record );
        
        return ( isset( $this->_updatedRecord[ $key ] ) ) ? $this->_updatedRecord[ $key ] : $this->_record[ $key ];
    }
    
    public function next()
    {
        next( $this->_record );
    }
    
    public function key()
    {
        return key( $this->_record );
    }
    
    public function valid()
    {
        if( next( $this->_record ) !== false )
        {
            prev( $this->_record );
        }
        
        return false;
    }
    
    public function rewind()
    {
        reset( $this->_record );
    }
    
    private static function _setStaticVars()
    {
        self::$_db        = XS_Database_Layer::getInstance();
        self::$_hasStatic = true;
    }
    
    public static function getAllObjects( $tableName, $orderBy = '' )
    {
        if( !self::$_hasStatic )
        {
            self::_setStaticVars();
        }
        
        $tableName = ( string )$tableName;
        $records   = self::$_db->getAllRecords( $tableName, $orderBy );
        
        foreach( $records as $id => $row )
        {
            $object      = new self( $tableName );
            $object->_id = $id;
            
            foreach( $row as $field => $value )
            {
                $object->_record[ $field ] = $value;
            }
            
            $records[ $id ] = $object;
        }
        
        return $records;
    }
    
    public static function getObjects( $tableName, array $keys, $orderBy = '' )
    {
        if( !self::$_hasStatic )
        {
            self::_setStaticVars();
        }
        
        $tableName = ( string )$tableName;
        $records   = self::$_db->getRecords( $tableName, $keys, $orderBy );
        
        foreach( $records as $id => $row )
        {
            $object      = new self( $tableName );
            $object->_id = $id;
            
            foreach( $row as $field => $value )
            {
                $object->_record[ $field ] = $value;
            }
            
            $records[ $id ] = $object;
        }
        
        return $records;
    }
    
    public static function getObjectsByFields( $tableName, array $fieldsValues, $orderBy = '', $limit = 0, $offset = 0 )
    {
        if( !self::$_hasStatic )
        {
            self::_setStaticVars();
        }
        
        $tableName = ( string )$tableName;
        $records   = self::$_db->getRecordsByFields( $tableName, $fieldsValues, $orderBy, $limit, $offset );
        
        foreach( $records as $id => $row )
        {
            $object      = new self( $tableName );
            $object->_id = $id;
            
            foreach( $row as $field => $value )
            {
                $object->_record[ $field ] = $value;
            }
            
            $records[ $id ] = $object;
        }
        
        return $records;
    }
    
    public static function getObjectsWhere( $tableName, $whereClause, $orderBy = '', $limit = 0, $offset = 0 )
    {
        if( !self::$_hasStatic )
        {
            self::_setStaticVars();
        }
        
        $tableName = ( string )$tableName;
        $records   = self::$_db->getRecordsWhere( $tableName, $whereClause, $orderBy, $limit, $offset );
        
        foreach( $records as $id => $row )
        {
            $object      = new self( $tableName );
            $object->_id = $id;
            
            foreach( $row as $field => $value )
            {
                $object->_record[ $field ] = $value;
            }
            
            $records[ $id ] = $object;
        }
        
        return $records;
    }
    
    public function commit()
    {
        if( $this->_id > 0 )
        {
            if( !count( $this->_updatedRecord ) )
            {
                return true;
            }
            
            if( self::$_db->updateRecord( $this->_tableName, $this->_id, $this->_updatedRecord ) )
            {
                $this->_record        = array_merge( $this->_record, $this->_updatedRecord );
                $this->_updatedRecord = array();
                
                return true;
            }
            
            return false;
        }
        else
        {
            if( self::$_db->insertRecord( $this->_tableName, $this->_updatedRecord ) )
            {
                $this->_id                     = self::$_db->lastInsertId();
                $this->_record[ $this->_pKey ] = $this->_id;
                $this->_record                 = array_merge( $this->_record, $this->_updatedRecord );
                $this->_updatedRecord          = array();
                
                return true;
            }
            
            return false;
        }
    }
    
    public function delete( $deleteFromTable = false )
    {
        if( $this->_id > 0 )
        {
            return self::$_db->deleteRecord( $this->_tableName, $this->_id, $deleteFromTable );
        }
        
        return false;
    }
    
    public function getTableName()
    {
        return $this->_tableName;
    }
    
    public function getId()
    {
        return $this->_id;
    }
}
