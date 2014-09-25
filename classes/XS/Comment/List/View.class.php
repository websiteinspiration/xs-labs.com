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

class XS_Comment_List_View extends XS_Comment_Base
{
    protected $_path = '';
    
    public function __construct( $path = '' )
    {
        parent::__construct();
        
        if( $path )
        {
            $this->_path = ( string )$path;
        }
        
        $comments = XS_Database_Object::getObjectsByFields( self::TABLE_COMMENTS, array( 'path' => $this->_path ) );
        
        if( isset( $_GET[ 'comment' ][ 'delete' ] ) )
        {
            $id = ( int )$_GET[ 'comment' ][ 'delete' ];
            
            $this->_deleteComment( $id );
            unset( $comments[ $id ] );
        }
        
        if( count( $comments ) )
        {
            $this->_content->h4 = $this->_lang->comments;
            $list               = $this->_content->div;
            $list[ 'class' ]    = 'comment-list';
            
            $this->_listComments( $comments, $list );
        }
    }
    
    protected function _deleteComment( $id )
    {
        try
        {
            $comment = new XS_Database_Object( self::TABLE_COMMENTS, $id );
            
            $comment->delete();
            
        }
        catch( Exception $e )
        {}
    }
    
    protected function _listComments( array $comments, XS_Xhtml_Tag $container )
    {
        $i = 0;
        
        foreach( $comments as $key => $value )
        {
            $div             = $container->div;
            $details         = $div->div;
            $description     = $div->div;
            $author          = $details->div;
            $date            = $details->div;
            
            $div[ 'class' ]         = ( $i % 2 ) ? 'comment-odd' : 'comment-even';
            $description[ 'class' ] = 'comment-description';
            $details[ 'class' ]     = 'comment-details';
            $author[ 'class' ]      = 'comment-author';
            $date[ 'class' ]        = 'comment-date';
            
            $date->addTextData( sprintf( $this->_lang->date, strftime( self::DATETIME_FORMAT, $value->ctime ) ) );
            $author->addTextData( sprintf( $this->_lang->author, $value->author ) );
            
            $description->div = $this->_emoticon->replaceSymbols( nl2br( stripslashes( $value->comment ) ) );
            
            $i++;
            
            if( is_object( $this->_userOptions ) && $this->_userOptions->delete_comments == 1 )
            {
                $delete               = $div->div;
                $deleteLink           = $delete->a;
                $deleteLink[ 'href' ] = $this->_menu->getCurrentPageUrl( array( 'comment[delete]' => $value->getId() ) );
                $delete[ 'class' ]    = 'comment-delete';
                
                $deleteLink->addTextData( $this->_lang->deleteComment );
            }
            
        }
    }
}
