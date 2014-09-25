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

require_once( 'Zend' . DIRECTORY_SEPARATOR . 'Search' . DIRECTORY_SEPARATOR . 'Lucene.php' );

class XS_Search
{
    const INDEX_TYPE_PAGE  = 0x01;
    const INDEX_TYPE_FORUM = 0x02;
    
    protected $_queryVar       = '';
    protected $_indexDirectory = '';
    protected $_search         = '';
    protected $_lang           = '';
    protected $_indexTtl       = 86400;
    protected $_index          = NULL;
    protected $_menu           = NULL;
    protected $_l10n           = NULL;
    
    public function __construct( $postVar = 'q' )
    {
        $this->_l10n           = XS_Language_File::getInstance( get_class( $this ) );
        $this->_indexDirectory = __ROOTDIR__ . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'index';
        $this->_queryVar       = ( string )$postVar;
        $this->_search         = ( isset( $_POST[ $this->_queryVar ] ) ) ? $_POST[ $this->_queryVar ] : ( ( isset( $_GET[ $this->_queryVar ] ) ) ? urldecode( $_GET[ $this->_queryVar ] ) : '' );
        $pathInfos             = explode( '/', $_SERVER[ 'REQUEST_URI' ] );
        $this->_lang           = ( isset( $pathInfos[ 1 ] ) ) ? $pathInfos[ 1 ] : 'en';
        $this->_menu           = XS_Menu::getInstance();
        
        $this->_checkIndex();
        
        $this->_index = Zend_Search_Lucene::open( $this->_indexDirectory );
    }
    
    public function __toString()
    {
        if( !$this->_search && isset( $_POST[ $this->_queryVar ] ) )
        {
            $result = new XS_Xhtml_Tag( 'div' );
            
            $result->addTextData( $this->_l10n->noSearchWord );
        }
        elseif( !$this->_search )
        {
            return '';
        }
        else
        {
            try
            {
                $query = new Zend_Search_Lucene_Search_Query_MultiTerm();
                $words = preg_split
                (
                    '/[\s,]*"([^"]+)"[\s,]*|' . '[\s,]*\'([^\']+)\'[\s,]*|' . '[\s,]+/',
                    $this->_search,
                    0,
                    PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
                );
                
                foreach( $words as $word )
                {
                    $query->addTerm( new Zend_Search_Lucene_Index_Term( strtolower( trim( $word ) ) ) );
                }
                
                $hits   = $this->_index->find( $query );
                $result = new XS_Xhtml_Tag( 'div' );
                
                if( !count( $hits ) )
                {
                    $result = new XS_Xhtml_Tag( 'div' );
                    
                    $result->addTextData( $this->_l10n->noResult );
                }
                else
                {
                    $this->_updateWordCount( $words );
                    
                    foreach( $hits as $hit )
                    {
                        $doc  = $hit->getDocument();
                        
                        if( ( int )( $doc->type ) === self::INDEX_TYPE_PAGE )
                        {
                            if( $doc->path === '/' )
                            {
                                continue;
                            }
                            
                            if( !file_exists( $doc->path ) )
                            {
                                continue;
                            }
                            
                            $relPath  = substr( $doc->path, strlen( __ROOTDIR__ ), -( strlen( 'index.php' ) ) );
                            $menuPath = substr( $relPath, strpos( $relPath, '/', 1 ) );
                            
                            if( $this->_menu->isPreview( $menuPath ) )
                            {
                                continue;
                            }
                            
                            $container            = $result->div;
                            $mtime                = filemtime( $doc->path );
                            $title                = $container->h2;
                            $abstract             = $container->div;
                            $infos                = $container->div;
                            $url                  = $infos->div;
                            $date                 = $infos->div;
                            $titleLink            = $title->a;
                            $urlLink              = $url->a;
                            $titleLink[ 'href' ]  = $this->_menu->getPageUrl( $menuPath );
                            $urlLink[ 'href' ]    = $this->_menu->getPageUrl( $menuPath );
                            $titleLink[ 'title' ] = $title->a;
                            $urlLink[ 'title' ]   = $url->a;
                            $title[ 'class' ]     = 'search-title';
                            $abstract[ 'class' ]  = 'search-abstract';
                            $infos[ 'class' ]     = 'search-infos';
                            $url[ 'class' ]       = 'search-infos-url';
                            $date[ 'class' ]      = 'search-infos-date';
                            $container[ 'class' ] = 'search-result';
                            
                            
                            $titleLink->addTextData( $doc->title );
                            $abstract->addTextData( XS_String_Utils::getInstance()->crop( $doc->content, 250 ) );
                            $urlLink->addTextData( $relPath );
                            $date->addTextData( date( 'd.m.Y', $mtime ) );
                        }
                        elseif( ( int )( $doc->type ) === self::INDEX_TYPE_FORUM )
                        {
                            $container            = $result->div;
                            $title                = $container->h2;
                            $abstract             = $container->div;
                            $titleLink            = $title->a;
                            $titleLink[ 'href' ]  = $doc->path;
                            $titleLink[ 'title' ] = $title->a;
                            $title[ 'class' ]     = 'search-title';
                            $abstract[ 'class' ]  = 'search-abstract';
                            $container[ 'class' ] = 'search-result';
                            
                            
                            $titleLink->addTextData( $doc->title );
                            $abstract->addTextData( XS_String_Utils::getInstance()->crop( $doc->content, 250 ) );
                        }
                    }
                }
            }
            catch( Exception $e )
            {
                return $e->getMessage();
            }
        }
        
        return ( string )$result;
    }
    
    protected function _updateWordCount( array $words )
    {
        foreach( $words as $word )
        {
            $row  = NULL;
            $rows = XS_Database_Object::getObjectsByFields( 'SEARCH_WORDS', array( 'word' => $word ) );
            
            if( !is_array( $rows ) || !count( $rows ) )
            {
                $query = new Zend_Search_Lucene_Search_Query_Term( new Zend_Search_Lucene_Index_Term( $word ) );
                $hits  = $this->_index->find( $query );
                
                if( count( $hits ) )
                {
                    $row        = new XS_Database_Object( 'SEARCH_WORDS' );
                    $row->word  = strtolower( $word );
                    $row->times = 0;
                }
            }
            else
            {
                $row = array_shift( $rows );
            }
            
            if( $row )
            {
                $row->times++;
                $row->commit();
            }
        }
    }
    
    protected function _checkIndex()
    {
        if( !file_exists( $this->_indexDirectory ) )
        {
            $this->_createIndex();
            $this->_updateIndex();
        }
        else
        {
            $mtime = filemtime( $this->_indexDirectory );
            $now   = time();
            
            if( $now > $mtime + $this->_indexTtl )
            {
                $this->_removeIndex();
                $this->_createIndex();
                $this->_updateIndex();
            }
        }
    }
    
    protected function _removeIndex()
    {
        $files = scandir( $this->_indexDirectory );
        
        foreach( $files as $file )
        { 
            if( $file === '.' || $file === '..' )
            {
                continue;
            }
            
            $path = $this->_indexDirectory . DIRECTORY_SEPARATOR . $file;
            
            if( is_dir( $path ) )
            {
                rmdir( $path );
            }
            else
            {
                unlink( $path );
            }
        }
        
        rmdir( $this->_indexDirectory );
    }
    
    protected function _createIndex()
    {
        Zend_Search_Lucene::create( $this->_indexDirectory );
    }
    
    protected function _updateIndex()
    {
        $index = Zend_Search_Lucene::open( $this->_indexDirectory );
        
        $this->_indexPages( $index );
        $this->_indexForums( $index );
        
        $index->commit();
        $index->optimize();
        
        touch( $this->_indexDirectory );
    }
    
    protected function _indexPages( Zend_Search_Lucene_Proxy $index )
    {
        $dir      = new RecursiveDirectoryIterator( __ROOTDIR__ . DIRECTORY_SEPARATOR . $this->_lang );
        $iterator = new RecursiveIteratorIterator( $dir );
        
        foreach( $iterator as $key => $value )
        {
            $file = $value->getFileName();
            
            if( $file === 'index.php' )
            {
                $this->_indexFile( $value->getPathName(), $index );
            }
        }
    }
    
    protected function _indexForums( Zend_Search_Lucene_Proxy $index )
    {
        $threads = XS_Database_Object::getAllObjects( 'FORUM_THREADS' );
        $url     = $this->_menu->getPageUrl( '/support/discussions/' );
        $url     = substr( $url, strpos( $url, '/' ) );
        
        foreach( $threads as $thread )
        {
            $title = $this->_l10n->forums . $thread->title;
            $posts = XS_Database_Object::getObjectsByFields( 'FORUM_POSTS', array( 'id_forum_thread' => $thread->getId() ) );
            $path  = $url . '?' . urlencode( 'forum[thread]' ) . '=' . $thread->getId();
            
            foreach( $posts as $post )
            {
                $doc = new Zend_Search_Lucene_Document();
                
                $doc->addField( Zend_Search_Lucene_Field::UnIndexed( 'type',    self::INDEX_TYPE_FORUM ) );
                $doc->addField( Zend_Search_Lucene_Field::UnIndexed( 'path',    $path ) );
                $doc->addField( Zend_Search_Lucene_Field::Text(      'title',   $title ) );
                $doc->addField( Zend_Search_Lucene_Field::Text(      'content', $post->message ) );
                
                $index->addDocument( $doc );
            }
        }
    }
    
    protected function _indexFile( $path, Zend_Search_Lucene_Proxy $index )
    {
        $text    = file_get_contents( $path );
        $text    = strip_tags( $text );
        $text    = str_replace( '&nbsp;', ' ', $text );
        $text    = preg_replace( '/\s+/', ' ', $text );
        $doc     = new Zend_Search_Lucene_Document();
        $relPath = substr( $path, strlen( __ROOTDIR__ ) + 1, -( strlen( 'index.php' ) ) );
        
        $title   = $this->_menu->getPageTitle( substr( $relPath, strpos( $relPath, '/' ) ) );
        
        $doc->addField( Zend_Search_Lucene_Field::UnIndexed( 'type',    self::INDEX_TYPE_PAGE ) );
        $doc->addField( Zend_Search_Lucene_Field::UnIndexed( 'path',    $path ) );
        $doc->addField( Zend_Search_Lucene_Field::Text(      'title',   $title ) );
        $doc->addField( Zend_Search_Lucene_Field::Text(      'content', $text ) );
        
        $index->addDocument( $doc );
    }
}
