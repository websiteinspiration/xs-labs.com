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

abstract class XS_Forum_Base
{
    const TABLE_SECTIONS     = 'FORUM_SECTIONS';
    const TABLE_CATEGORIES   = 'FORUM_CATEGORIES';
    const TABLE_THREADS      = 'FORUM_THREADS';
    const TABLE_POSTS        = 'FORUM_POSTS';
    const TABLE_USERS        = 'USERS';
    const TABLE_USER_OPTIONS = 'USER_OPTIONS';
    const DATETIME_FORMAT    = '%e %B %Y / %H:%M';
    
    protected $_lang        = NULL;
    protected $_db          = NULL;
    protected $_str         = NULL;
    protected $_menu        = NULL;
    protected $_emoticon    = NULL;
    protected $_content     = NULL;
    protected $_user        = NULL;
    protected $_userOptions = NULL;
    protected $_session     = NULL;
    protected $_params      = array();
    
    public function __construct()
    {
        $class                     = get_class( $this );
        $this->_session            = XS_Session::getInstance();
        $this->_lang               = XS_Language_File::getInstance( $class );
        $this->_db                 = XS_Database_Layer::getInstance();
        $this->_str                = XS_String_Utils::getInstance();
        $this->_menu               = XS_Menu::getInstance();
        $this->_emoticon           = XS_Emoticon_Helper::getInstance();
        $this->_content            = new XS_Xhtml_Tag( 'div' );
        $this->_content[ 'class' ] = $class;
        
        $userId      = $this->_session->user_id;
        $userSession = $this->_session->getId();
        
        if( $userId && $userSession ) {
            
            try {
                
                $user    = new XS_Database_Object( self::TABLE_USERS, ( int )$userId );
                $options = XS_Database_Object::getObjectsByFields( self::TABLE_USER_OPTIONS, array( 'id_user' => $user->getId() ) );
                
            } catch( XS_Database_Object_Exception $e ) {
                
                unset( $this->_session->user_id );
            }
            
            if( $user->session != $userSession ) {
                
                unset( $this->_session->user_id );
                
            } else {
                
                $this->_user        = $user;
                $this->_userOptions = array_shift( $options );
            }
        }
        
        if( isset( $_GET[ 'forum' ] ) ) {
            
            $this->_params = $_GET[ 'forum' ];
        }
    }
    
    public function __toString()
    {
        $forum            = new XS_Xhtml_Tag( 'div' );
        $forum[ 'class' ] = 'forum';
        
        $forum->addChildNode( $this->_content );
        
        return ( string )$forum;
    }
    
    public function getContent()
    {
        return $this->_content;
    }
}
