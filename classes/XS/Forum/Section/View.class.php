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

class XS_Forum_Section_View extends XS_Forum_Base
{
    protected $_id      = 0;
    protected $_section = NULL;
    
    public function __construct( $id )
    {
        parent::__construct();
        
        $this->_id      = ( int )$id;
        $this->_section = new XS_Database_Object( self::TABLE_SECTIONS, $this->_id );
        
        $title      = $this->_content->div;
        $categories = $this->_content->div;
        
        $title[ 'class' ] = 'section-header';
        
        $title->h3 = $this->_section->name;
        
        $this->_listCategories( $this->_id, $categories );
    }
    
    protected function _listCategories( $section, XS_Xhtml_Tag $container )
    {
        $categories = XS_Database_Object::getObjectsByFields( self::TABLE_CATEGORIES, array( 'id_forum_section' => $section ), 'name' );
        $i          = 0;
        
        foreach( $categories as $key => $value ) {
            
            $div             = $container->div;
            $title           = $div->div;
            $description     = $div->div;
            $details         = $div->div;
            $link            = $title->h4->a;
            $descriptionLink = $description->a;
            
            $div[ 'class' ]         = ( $i % 2 ) ? 'category-odd' : 'category-even';
            $title[ 'class' ]       = 'category-name';
            $description[ 'class' ] = 'category-description';
            $details[ 'class' ]     = 'category-details';
            
            $link[ 'href' ]            = $this->_menu->getCurrentPageUrl( array( 'forum[category]' => $value->getId() ) );
            $descriptionLink[ 'href' ] = $link[ 'href' ];
            
            $link->addTextData( $value->name );
            $descriptionLink->addTextData( $value->description );
            
            $i++;
            
            $this->_getPostCount( $details, $value->getId() );
        }
    }
    
    protected function _getPostCount( XS_Xhtml_Tag $container, $category )
    {
        $threadPrimaryKey = $this->_db->getPrimaryKey( self::TABLE_THREADS );
        $postPrimaryKey   = $this->_db->getPrimaryKey( self::TABLE_POSTS );
        $threadsQuery     = 'SELECT '
                          . $threadPrimaryKey
                          . ' FROM '
                          . self::TABLE_THREADS
                          . ' WHERE deleted = 0 AND id_forum_category = '
                          . $category;
        
        $threads = $this->_db->prepare( $threadsQuery );
        
        $threads->execute( array() );
        
        $threadsCount = 0;
        $postsCount   = 0;
        $threadsIds   = array();
        
        while( $thread = $threads->fetchObject() ) {
            
            $threadsIds[] = $thread->$threadPrimaryKey;
            
            $threadsCount++;
        }
        
        if( $threadsCount > 0 ) {
            
            $postsQuery = $this->_db->query(
                'SELECT COUNT( * ) FROM '
              . self::TABLE_POSTS
              . ' WHERE deleted = 0 AND id_forum_thread IN ( '
              . implode( ',', $threadsIds )
              . ' )'
            );
            
            $postsCount = $postsQuery->fetchColumn();
        }
        
        $container->addTextData( $this->_lang->threadsCount . $threadsCount . '<br />' . $this->_lang->postsCount . $postsCount );
    }
}
